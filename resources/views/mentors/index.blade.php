@extends('layouts.admin')

@section('title', __('dashboard.Mentors'))
@section('subTitle', __('dashboard.Mentors'))

@php
    /*
    |--------------------------------------------------------------------------
    | Mentors directory (Program Management)
    |--------------------------------------------------------------------------
    | Filtering happens server-side in ClientProfileController::showMentors()
    | from the GET parameters below. This view mirrors that state back into the
    | controls, applies the sort order the controller does not, and prepares a
    | card view-model from the already-filtered $mentors collection.
    |
    |   search                    free-text search (name/job title/bio/contact)
    |   sort                      rating | programs | recent | name
    |   programFilter             cycle id
    |   scopeFilter               all | public | <organization id>
    |   rateSearch                minimum average rating (0 - 5, step 0.5)
    |   expertises[]              expertise ids (OR between areas)
    |   linkedin                  '' | 1 | 0
    |   enrolledInProgramFilter   '' | 1 | 0
    |
    | Card  data source     fallback
    | ---------------------------------------------------------------
    | name        $m->name          (localized accessor)
    | job title   $m->title         (localized accessor)
    | avatar      $m->image_url     initials badge when $m->img_url is empty
    | bio         $m->bio->bio_*    "N/A"
    | linkedin    $m->bio->linkedin inert button
    | email       $m->email         inert button
    | phone       $m->phone         inert button
    | rating      $m->avg_rating    0.0 (withAvg over approved requests)
    | programs    $m->mentorCycles  "No program assigned"
    | enrolled    $m->mentorCycles->isNotEmpty()
    | is_public   $m->is_public     set by the controller from InviteMentor
    |--------------------------------------------------------------------------
    */
    $user     = auth()->user();
    $company  = $user->company;
    $getCycle = $company?->cycledata;

    // Founder users request a session straight from the card; admins, org
    // managers, sub-admins and mentors only browse the directory.
    $canRequestMentorship = (bool) (
        $company &&
        $company->cycle !== 0 &&
        (
            $user->hasRole('Registered') ||
            (!$user->hasRole('Mentor') && !$user->hasRole('Admin'))
        )
    );

    // fetchSlots(mentorId, cycleId, organizationId) needs a real cycle and
    // organization, so the booking modal is only reachable when both are known.
    $canBook = $canRequestMentorship && $company->id && $getCycle?->id;

    $tabs = [
        ['title' => __('admin.allMentors'), 'route' => 'mentors.list', 'icon' => 'users'],
        ['title' => __('admin.invitations'), 'route' => 'invitations.index', 'icon' => 'inbox'],
    ];

    /* ======================= Request state ======================= */
    $sortOptions = [
        'rating'   => __('mentor.directory.sort_rating'),
        'programs' => __('mentor.directory.sort_programs'),
        'recent'   => __('mentor.directory.sort_recent'),
        'name'     => __('mentor.directory.sort_name'),
    ];

    $sort         = array_key_exists(request('sort'), $sortOptions) ? request('sort') : 'rating';
    $search       = trim((string) request('search', ''));
    $cyclesById   = collect($cycles)->keyBy('id');
    $programId    = $cyclesById->has((int) request('programFilter')) ? (int) request('programFilter') : 0;

    $scopeFilter  = (string) request('scopeFilter', 'all');
    $association  = $scopeFilter !== '' && $scopeFilter !== 'all' ? $scopeFilter : 'all';
    $rateSearch   = max(0, min(5, (float) request('rateSearch', 0)));
    $expertiseSel = array_values(array_filter(array_map('intval', (array) request('expertises', [])), fn ($id) => $id > 0));
    $linkedin     = in_array(request('linkedin'), ['1', '0'], true) ? request('linkedin') : '';
    $enrolled     = in_array(request('enrolledInProgramFilter'), ['1', '0'], true) ? request('enrolledInProgramFilter') : '';

    $organizationsById = collect($organizations)->keyBy('id');
    $expertiseById     = collect($expertises)->flatMap(fn ($group) => $group->children)->keyBy('id');

    /* ======================= Sort + view-model ======================= */
    $fullName         = fn ($m) => trim($m->name);
    $visibleExpertise = 2;

    $isHttpUrl = fn ($v) => is_string($v) && preg_match('~^https?://\S+$~i', $v) === 1;
    $isEmail   = fn ($v) => is_string($v) && filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
    $telDigits = fn ($v) => is_string($v) ? preg_replace('/[^\d+]/', '', $v) : '';
    $isPhone   = fn ($v) => is_string($v)
        && preg_match('/^\+?[\d\s().-]{7,}$/', $v) === 1
        && preg_match('/^\+?\d{7,}$/', $telDigits($v)) === 1;

    $localizedArea = fn ($e) => $e ? ($l === 'ar' ? ($e->title_ar ?? $e->title) : $e->title) : null;

    $cards = $mentors
        ->sort(function ($a, $b) use ($sort, $fullName) {
            $primary = match ($sort) {
                'programs' => count($b->mentorCycles) <=> count($a->mentorCycles),
                'recent'   => strcmp((string) $b->created_at, (string) $a->created_at),
                'name'     => strcasecmp($fullName($a), $fullName($b)),
                default    => (float) ($b->avg_rating ?? 0) <=> (float) ($a->avg_rating ?? 0),
            };

            return $primary ?: (strcasecmp($fullName($a), $fullName($b)) ?: $a->id <=> $b->id);
        })
        ->values()
        ->map(function ($m) use ($visibleExpertise, $isHttpUrl, $isEmail, $isPhone, $telDigits, $localizedArea, $l, $canBook) {
            $name   = trim($m->name);
            $bio    = $m->bio;
            $areas  = $m->mentorExpertises->map($localizedArea)->filter(fn ($t) => trim((string) $t) !== '')->values();
            $progs  = $m->mentorCycles->map(fn ($c) => $c->name)->filter()->values();
            $bioTxt = trim($bio?->{'bio_' . $l} ?? '');

            $linkedinHref = $isHttpUrl($bio?->linkedin) ? $bio->linkedin : null;
            $mailHref     = $isEmail($m->email) ? 'mailto:' . $m->email : null;
            $telHref      = $isPhone($m->phone) ? 'tel:' . $telDigits($m->phone) : null;

            return [
                'id'             => (int) $m->id,
                'name'           => $name,
                'initials'       => mb_strtoupper(mb_substr((string) $m->first_name, 0, 1) . mb_substr((string) $m->last_name, 0, 1)),
                'badge_class'    => $m->is_public ? 'bg-label-info' : 'bg-label-primary',
                'job_title'      => (string) $m->title,
                'has_image'      => (bool) $m->img_url,
                'image_url'      => (string) $m->image_url,
                'is_public'      => (bool) $m->is_public,
                'rating'         => round((float) ($m->avg_rating ?? 0), 1),
                'programs_count' => count($m->mentorCycles),
                'program_name'   => $progs->first(),
                'program_names'  => $progs->implode(' · '),
                'joined'         => $m->created_at?->toDateString() ?? '',
                'joined_label'   => $m->created_at ? $m->created_at->format('d-m-Y') : '',
                'bio'            => $bioTxt,
                'bio_empty'      => $bioTxt === '',
                'shown_areas'    => $areas->slice(0, $visibleExpertise),
                'hidden_areas'   => $areas->slice($visibleExpertise),
                'has_slots'      => $m->schedule->isNotEmpty(),
                'can_book_now'  => $canBook && $m->schedule->isNotEmpty(),
                'contacts'       => [
                    ['icon' => 'ti-brand-linkedin', 'href' => $linkedinHref,
                     'label' => $linkedinHref
                         ? __('mentor.directory.open_linkedin', ['name' => $name])
                         : __('mentor.directory.no_linkedin')],
                    ['icon' => 'ti-mail', 'href' => $mailHref,
                     'label' => $mailHref
                         ? __('mentor.directory.email_mentor', ['name' => $name])
                         : __('mentor.directory.no_email')],
                    ['icon' => 'ti-phone', 'href' => $telHref,
                     'label' => $telHref
                         ? __('mentor.directory.call_mentor', ['name' => $name, 'phone' => $m->phone])
                         : __('mentor.directory.no_phone')],
                ],
            ];
        });

    /* ======================= Filter UI state ======================= */
    $activeCount = ($programId ? 1 : 0) + ($association !== 'all' ? 1 : 0) + ($rateSearch > 0 ? 1 : 0)
        + count($expertiseSel) + ($linkedin !== '' ? 1 : 0) + ($enrolled !== '' ? 1 : 0);

    $chips = [];
    if ($programId) {
        $chips[] = ['label' => __('mentor.directory.program'),
                    'value' => (string) ($cyclesById[$programId]->name ?? $programId),
                    'url' => request()->fullUrlWithoutQuery('programFilter')];
    }
    if ($association !== 'all') {
        $chips[] = ['label' => __('admin.Mentor_Association'),
                    'value' => $association === 'public'
                        ? __('admin.Not_Associated') . ' (' . __('admin.Public') . ')'
                        : (string) ($organizationsById[$association]->name ?? $association),
                    'url' => request()->fullUrlWithoutQuery('scopeFilter')];
    }
    if ($rateSearch > 0) {
        $chips[] = ['label' => __('admin.rating'),
                    'value' => number_format($rateSearch, 1) . '+',
                    'url' => request()->fullUrlWithoutQuery('rateSearch')];
    }
    foreach ($expertiseSel as $id) {
        $chips[] = ['label' => __('admin.Expertises'),
                    'value' => (string) $localizedArea($expertiseById[$id] ?? null) ?: $id,
                    'url' => request()->fullUrlWithQuery(['expertises' => array_values(array_diff($expertiseSel, [$id]))])];
    }
    if ($linkedin !== '') {
        $chips[] = ['label' => __('mentor.directory.has_linkedin'),
                    'value' => $linkedin === '1' ? __('admin.Yes') : __('admin.No'),
                    'url' => request()->fullUrlWithoutQuery('linkedin')];
    }
    if ($enrolled !== '') {
        $chips[] = ['label' => __('mentor.directory.enrolled_in_program'),
                    'value' => $enrolled === '1' ? __('admin.Yes') : __('admin.No'),
                    'url' => request()->fullUrlWithoutQuery('enrolledInProgramFilter')];
    }

    // Reset clears the search and every filter but keeps the chosen sort order.
    $resetUrl    = url()->current() . ($sort !== 'rating' ? '?' . http_build_query(['sort' => $sort]) : '');
    $total       = $cards->count();
    $ratingLabel = $rateSearch > 0 ? number_format($rateSearch, 1) . '+' : __('mentor.directory.any_rating');

    $noResultsMessage = $programId
        ? __('cycles.No_Mentors_For_Selected_Program')
        : ($enrolled === '1' ? __('cycles.No_Mentors_Enrolled') : __('cycles.No_Mentors_Found'));
@endphp

@section('content')
@if (!Auth::user()->first_name_ar)
<div class="d-none">
  {!! redirect()->route('user.profile_view')->with(['msg' => 'WaitingYourRedirect', 'class' => 'danger']) !!}
</div>
@endif

<div class="">
  <div class="">
    <div class="content-body">
      @if (session()->has('msg'))
        <x-alert
          type="{{ session()->get('class') ?? 'danger' }}"
          message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}"
        />
      @endif

      {{-- Tabs Navigation --}}
      @if ($_user->hasAnyRole('Admin|organizationManager|subadmin'))
        <x-navigation-pills :tabs="$tabs" />
      @endif

      {{-- GV-3554: founders only see the directory once their program reaches
           the mentorship stage; the rest of the page is management tooling. --}}
      @if (!($mentorshipActive ?? true))
        @include('includes.no-result', ['message' => __('cycles.Mentorship_Not_Available_Yet')])
      @else
      <section class="card" aria-labelledby="mentorsTitle">
        <header class="card-header">
          <div class="d-flex flex-column gap-3">
            <div>
              <h1 id="mentorsTitle" class="card-title h4 mb-1">{{ __('dashboard.Mentors') }}</h1>
              <p class="text-secondary mb-0">{{ __('mentor.directory.subtitle') }}</p>
            </div>

            {{-- One GET form drives the whole page. The offcanvas controls join it
                 through the form="mentorFilters" attribute, so the URL always
                 carries the complete search, sort and filter state. --}}
            <form id="mentorFilters" method="GET" action="{{ route('mentors.list') }}" role="search" aria-label="{{ __('mentor.directory.search_label') }}">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="input-group input-group-merge w-auto flex-fill">
                  <span class="input-group-text"><i class="ti ti-search text-muted" aria-hidden="true"></i></span>
                  <input
                    id="debounced-search"
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    data-default=""
                    class="form-control"
                    size="12"
                    placeholder="{{ __('mentor.directory.search_placeholder') }}"
                    aria-label="{{ __('mentor.directory.search_label') }}" />
                </div>

                <div class="input-group w-auto flex-shrink-0">
                  <span class="input-group-text"><i class="ti ti-sort-descending text-muted" aria-hidden="true"></i>
                    <span class="visually-hidden">{{ __('mentor.directory.sort_label') }}</span>
                  </span>
                  <select class="form-select select2" id="sortMentors" name="sort" data-default="rating" aria-label="{{ __('mentor.directory.sort_label') }}">
                    @foreach ($sortOptions as $value => $label)
                      <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>

                <button
                  class="btn btn-primary flex-shrink-0 text-nowrap"
                  type="button"
                  data-bs-toggle="offcanvas"
                  data-bs-target="#filterOffcanvas"
                  aria-controls="filterOffcanvas">
                  <i class="ti ti-filter me-2" aria-hidden="true"></i>
                  <span>{{ __('mentor.directory.filters_btn') }}</span>
                  @if ($activeCount > 0)
                    <span class="badge bg-label-secondary ms-2">{{ $activeCount }}</span>
                  @endif
                </button>
              </div>
            </form>
          </div>

          @if ($chips)
            <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
              <ul class="d-flex flex-wrap gap-2 list-unstyled mb-0" aria-label="{{ __('mentor.directory.active_filters') }}">
                @foreach ($chips as $chip)
                  <li>
                    <a
                      class="badge bg-label-secondary d-inline-flex align-items-center gap-2 text-decoration-none"
                      href="{{ $chip['url'] }}"
                      aria-label="{{ __('mentor.directory.remove_filter', ['label' => $chip['label'], 'value' => $chip['value']]) }}">
                      <span class="text-secondary">{{ $chip['label'] }}:</span>
                      <span>{{ $chip['value'] }}</span>
                      <i class="ti ti-x ti-12px text-secondary" aria-hidden="true"></i>
                    </a>
                  </li>
                @endforeach
              </ul>
              <a href="{{ $resetUrl }}" class="small ms-1">{{ __('mentor.directory.clear_all') }}</a>
            </div>
          @endif
        </header>

        <section class="card-body" aria-labelledby="mentorListTitle">
          <h2 id="mentorListTitle" class="visually-hidden">{{ __('mentor.directory.list') }}</h2>

          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <p class="text-secondary mb-0" role="status">
              {{ __('mentor.directory.mentor_count', ['count' => $total]) }}
            </p>
            <p class="text-secondary small mb-0 d-none d-sm-block">
              <i class="ti ti-info-circle me-1" aria-hidden="true"></i>{{ __('mentor.directory.public_note') }}
            </p>
          </div>

          @if ($cards->isEmpty())
            <div class="d-flex flex-column align-items-center text-center py-6">
              <i class="ti ti-user-search ti-48px text-secondary mb-3" aria-hidden="true"></i>
              <h3 class="h5 mb-1">{{ __('mentor.directory.no_results_title') }}</h3>
              <p class="text-secondary mb-4">{{ $noResultsMessage }}</p>
              <a class="btn btn-label-primary" href="{{ $resetUrl }}">
                <i class="ti ti-refresh me-1" aria-hidden="true"></i>{{ __('mentor.directory.reset_filters') }}
              </a>
            </div>
          @else
            <ul class="row g-4 list-unstyled mb-0">
              @foreach ($cards as $m)
                <li class="col-md-6 col-xl-4">
                  <article class="card h-100 p-2 border card-border-shadow-primary" aria-labelledby="mentor-{{ $m['id'] }}">
                    <div class="card-body p-4 pt-2 d-flex flex-column">
                      {{-- Identity --}}
                      <header class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar avatar-lg flex-shrink-0">
                          @if ($m['has_image'])
                            <img
                              class="mentor-avatar rounded-circle"
                              src="{{ $m['image_url'] }}"
                              alt=""
                              loading="lazy"
                              data-initials="{{ $m['initials'] }}"
                              data-initials-class="{{ $m['badge_class'] }}" />
                          @else
                            <span class="avatar-initial rounded-circle {{ $m['badge_class'] }}">{{ $m['initials'] }}</span>
                          @endif
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                          <h3 id="mentor-{{ $m['id'] }}" class="h6 mb-0 text-truncate">
                            <a class="text-body text-decoration-none" href="{{ route('mentors.profile_view', $m['id']) }}">
                              {{ $m['name'] }}<span class="visually-hidden"> ({{ __('mentor.directory.view_profile_aria', ['name' => $m['name']]) }})</span>
                            </a>
                          </h3>
                          <p class="text-body small text-truncate mb-0">{{ $m['job_title'] }}</p>
                        </div>
                      </header>

                      {{-- Association, rating and program count --}}
                      <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        @if ($m['is_public'])
                          <span class="badge bg-label-info flex-shrink-0"><i class="ti ti-world me-1" aria-hidden="true"></i>{{ __('admin.Public') }}</span>
                        @else
                          <span class="badge bg-label-primary flex-shrink-0"><i class="ti ti-building me-1" aria-hidden="true"></i>{{ __('admin.Organization') }}</span>
                        @endif

                        <p class="ms-auto d-flex align-items-center gap-2 text-nowrap mb-0">
                          <span class="d-flex align-items-center gap-1 text-warning" aria-hidden="true">
                            @for ($i = 1; $i <= 5; $i++)
                              <i class="ti {{ $m['rating'] >= $i - 0.25 ? 'ti-star-filled' : ($m['rating'] >= $i - 0.75 ? 'ti-star-half-filled' : 'ti-star') }} ti-12px"></i>
                            @endfor
                          </span>
                          <data class="fw-medium" value="{{ $m['rating'] }}">
                            {{ number_format($m['rating'], 1) }}<span class="visually-hidden"> {{ __('mentor.directory.out_of_5') }}</span>
                          </data>
                          <span class="text-secondary small">
                            {{ $m['programs_count'] }} {{ $m['programs_count'] === 1 ? __('mentor.directory.program_singular') : __('mentor.directory.programs_plural') }}
                          </span>
                        </p>
                      </div>

                      <p class="line-clamp-2 text-body mb-3">
                        @if ($m['bio_empty'])
                          <span class="text-secondary">{{ __('admin.N/A') }}</span>
                        @else
                          {{ $m['bio'] }}
                        @endif
                      </p>

                      <ul class="d-flex flex-wrap gap-1 list-unstyled mb-3" aria-label="{{ __('mentor.directory.expertise') }}">
                        @foreach ($m['shown_areas'] as $area)
                          <li><span class="badge bg-label-secondary rounded-pill">{{ $area }}</span></li>
                        @endforeach
                        @if ($m['hidden_areas']->isNotEmpty())
                          <li>
                            <span
                              class="badge bg-label-primary rounded-pill expertise-more"
                              data-bs-toggle="tooltip"
                              data-bs-placement="top"
                              tabindex="0"
                              title="{{ $m['hidden_areas']->implode(', ') }}">
                              +{{ $m['hidden_areas']->count() }}<span class="visually-hidden"> {{ __('mentor.directory.more_areas_sr', ['count' => $m['hidden_areas']->count(), 'areas' => $m['hidden_areas']->implode(', ')]) }}</span>
                            </span>
                          </li>
                        @endif
                      </ul>

                      {{-- Pinned to the card bottom so actions align across a row --}}
                      <footer class="mt-auto pt-3 border-top">
                        <p class="d-flex flex-wrap align-items-center gap-2 small text-secondary text-nowrap overflow-hidden mb-0">
                          <span class="d-flex align-items-center gap-1">
                            <i class="ti ti-calendar-event" aria-hidden="true"></i>{{ __('mentor.directory.joined') }}
                            @if ($m['joined'])
                              <time datetime="{{ $m['joined'] }}">{{ $m['joined_label'] }}</time>
                            @else
                              <span>-</span>
                            @endif
                          </span>
                          <span class="vr opacity-50" aria-hidden="true"></span>
                          <span class="d-flex align-items-center gap-1 text-truncate" @if ($m['program_names']) title="{{ $m['program_names'] }}" @endif>
                            <i class="ti ti-users-group flex-shrink-0" aria-hidden="true"></i>
                            {{ $m['program_name'] ?: __('mentor.directory.no_program_assigned') }}
                          </span>
                        </p>

                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                          <div class="d-flex gap-2" role="group" aria-label="{{ __('mentor.directory.contact', ['name' => $m['name']]) }}">
                            @foreach ($m['contacts'] as $contact)
                              @if ($contact['href'])
                                <a
                                  class="btn btn-icon rounded-pill btn-label-primary"
                                  href="{{ $contact['href'] }}"
                                  target="_blank"
                                  rel="noopener noreferrer"
                                  data-bs-toggle="tooltip"
                                  data-bs-placement="top"
                                  title="{{ $contact['label'] }}"
                                  aria-label="{{ $contact['label'] }}">
                                  <i class="ti {{ $contact['icon'] }} ti-16px" aria-hidden="true"></i>
                                </a>
                              @else
                                {{-- Inert but hoverable: .disabled would kill the tooltip explaining why. --}}
                                <a
                                  class="btn btn-icon rounded-pill btn-label-secondary opacity-50"
                                  role="link"
                                  aria-disabled="true"
                                  tabindex="-1"
                                  data-bs-toggle="tooltip"
                                  data-bs-placement="top"
                                  title="{{ $contact['label'] }}"
                                  aria-label="{{ $contact['label'] }}">
                                  <i class="ti {{ $contact['icon'] }} ti-16px" aria-hidden="true"></i>
                                </a>
                              @endif
                            @endforeach
                          </div>

                          @if ($user->hasRole('Registered'))
                              <button
                                type="button"
                                class="request-mentorship-btn ms-auto btn btn-link d-inline-flex align-items-center gap-1 p-0 border-0 text-primary fw-medium text-nowrap text-decoration-none"
                                data-bookable="{{ $m['can_book_now'] ? 1 : 0 }}"
                                data-mentor-id="{{ $m['id'] }}"
                                data-cycle-id="{{ $canBook ? $getCycle->id : 0 }}"
                                data-org-id="{{ $canBook ? ($getCycle->organization_id ?? 0) : 0 }}"
                                data-alert-title="{{ __('mentor.directory.booking_hint_title') }}"
                                data-alert-message="{{ $canBook ? __('mentor.directory.no_slots_hint') : __('mentor.directory.join_program_hint') }}">
                                <span>{{ __('cycles.Request_Mentorship') }}</span>
                                <i class="ti ti-calendar-plus ti-14px" aria-hidden="true"></i>
                              </button>
                          @else
                            <a
                              class="ms-auto d-inline-flex align-items-center gap-1 text-primary fw-medium text-nowrap text-decoration-none"
                              href="{{ route('mentors.profile_view', $m['id']) }}">
                              <span>{{ __('admin.View Profile') }}<span class="visually-hidden"> {{ __('mentor.directory.view_profile_aria', ['name' => $m['name']]) }}</span></span>
                              <i class="ti ti-chevron-right ti-14px scaleX-n1-rtl" aria-hidden="true"></i>
                            </a>
                          @endif
                        </div>
                      </footer>
                    </div>
                  </article>
                </li>
              @endforeach
            </ul>
          @endif
        </section>
      </section>

      @if ($canBook)
        @include('partials.book-session-modal')
      @endif
      @endif

      {{-- ============ Filters (submits the #mentorFilters form) ============ --}}
      <aside class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
        <header class="offcanvas-header border-bottom">
          <h2 class="offcanvas-title h5" id="filterOffcanvasLabel">{{ __('mentor.directory.filters_title') }}</h2>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </header>

        <div class="offcanvas-body">
          <div>
            <label class="form-label" for="filterProgram">{{ __('mentor.directory.program') }}</label>
            <select class="form-select" id="filterProgram" name="programFilter" form="mentorFilters" data-default="">
              <option value="">{{ __('mentor.directory.all_programs') }}</option>
              @foreach ($cycles as $cycle)
                <option value="{{ $cycle->id }}" @selected($programId === (int) $cycle->id)>{{ $cycle->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="border-top mt-4 pt-4">
            <label class="form-label" for="filterScope">{{ __('mentor.directory.mentor_association') }}</label>
            <select class="form-select" id="filterScope" name="scopeFilter" form="mentorFilters" data-default="all">
              <option value="all" @selected($association === 'all')>{{ __('mentor.directory.all_mentors') }}</option>
              <option value="public" @selected($association === 'public')>{{ __('mentor.directory.not_associated') }}</option>
              @foreach ($organizations as $org)
                <option value="{{ $org->id }}" @selected($association === (string) $org->id)>{{ $org->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="border-top mt-4 pt-4">
            <div class="d-flex align-items-center justify-content-between">
              <label class="form-label" for="filterRating">{{ __('mentor.directory.min_rating') }}</label>
              <output id="ratingLabel" for="filterRating" class="fw-normal text-secondary mb-1">{{ $ratingLabel }}</output>
            </div>
            <input
              type="range"
              class="form-range"
              id="filterRating"
              name="rateSearch"
              form="mentorFilters"
              data-default="0"
              min="0"
              max="5"
              step="0.5"
              value="{{ $rateSearch }}" />
            <div class="d-flex justify-content-between text-secondary small" aria-hidden="true">
              <span>{{ __('mentor.directory.any_rating') }}</span>
              <span>5.0</span>
            </div>
          </div>

          <div class="border-top mt-4 pt-4">
            <label class="form-label" for="filterExpertise">{{ __('mentor.directory.area_of_expertise') }}</label>
            <select
              id="filterExpertise"
              name="expertises[]"
              form="mentorFilters"
              class="select2 form-select w-100"
              data-default=""
              data-placeholder="{{ __('mentor.directory.select_areas') }}"
              multiple>
              @foreach ($expertises as $group)
                <optgroup label="{{ $l === 'ar' ? ($group->title_ar ?? $group->title) : $group->title }}">
                  @foreach ($group->children as $expertise)
                    <option value="{{ $expertise->id }}" @selected(in_array((int) $expertise->id, $expertiseSel, true))>
                      {{ $l === 'ar' ? ($expertise->title_ar ?? $expertise->title) : $expertise->title }}
                    </option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          </div>

          <div class="border-top mt-4 pt-4">
            <label class="form-label" for="filterLinkedin">{{ __('mentor.directory.has_linkedin') }}</label>
            <select class="form-select" id="filterLinkedin" name="linkedin" form="mentorFilters" data-default="">
              <option value="" @selected($linkedin === '')>{{ __('admin.All') }}</option>
              <option value="1" @selected($linkedin === '1')>{{ __('admin.Yes') }}</option>
              <option value="0" @selected($linkedin === '0')>{{ __('admin.No') }}</option>
            </select>
          </div>

          <div class="border-top mt-4 pt-4">
            <label class="form-label" for="filterEnrolled">{{ __('mentor.directory.enrolled_in_program') }}</label>
            <select class="form-select" id="filterEnrolled" name="enrolledInProgramFilter" form="mentorFilters" data-default="">
              <option value="" @selected($enrolled === '')>{{ __('admin.All') }}</option>
              <option value="1" @selected($enrolled === '1')>{{ __('admin.Yes') }}</option>
              <option value="0" @selected($enrolled === '0')>{{ __('admin.No') }}</option>
            </select>
          </div>
        </div>

        <footer class="border-top flex-shrink-0 p-4 d-flex gap-2">
          <a class="btn btn-label-secondary flex-grow-1" href="{{ $resetUrl }}">
            <i class="ti ti-refresh me-1" aria-hidden="true"></i>{{ __('admin.Reset') }}
          </a>
          <button type="submit" form="mentorFilters" class="btn btn-primary flex-grow-1">{{ __('mentor.directory.apply') }}</button>
        </footer>
      </aside>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    'use strict';

    var form = document.getElementById('mentorFilters');
    if (!form) return;

    // Sort is applied as soon as it changes.
    $('#sortMentors').on('change', function () {
      $(this).trigger('change.select2');
      form.requestSubmit();
    });

    // Live value next to the rating slider.
    var range = document.getElementById('filterRating');
    var output = document.getElementById('ratingLabel');
    range.addEventListener('input', function () {
      var value = parseFloat(range.value);
      output.textContent = value > 0 ? value.toFixed(1) + '+' : '{{ __('mentor.directory.any_rating') }}';
    });

    // Keep the URL clean: controls left at their default value are not submitted.
    var defaults = Array.prototype.filter.call(form.elements, function (el) {
      return el.hasAttribute('data-default');
    });
    form.addEventListener('submit', function () {
      defaults.forEach(function (el) {
        el.disabled = el.value === el.getAttribute('data-default');
      });
    });
    // Back/forward cache can restore the page with the controls still disabled.
    window.addEventListener('pageshow', function () {
      defaults.forEach(function (el) {
        el.disabled = false;
      });
    });

    // Broken avatar file -> initials badge.
    function swapForInitials(img) {
      var badge = document.createElement('span');
      badge.className = 'avatar-initial rounded-circle ' + (img.getAttribute('data-initials-class') || 'bg-label-primary');
      badge.textContent = img.getAttribute('data-initials') || '';
      img.replaceWith(badge);
    }
    document.querySelectorAll('img.mentor-avatar').forEach(function (img) {
      if (img.complete && img.naturalWidth === 0) swapForInitials(img);
    });
    // `error` does not bubble, hence the capturing listener.
    document.addEventListener(
      'error',
      function (e) {
        if (e.target.matches && e.target.matches('img.mentor-avatar')) swapForInitials(e.target);
      },
      true
    );

    // Request Mentorship: open the booking flow when possible, otherwise
    // explain the reason through the shared releaseAlert() helper.
    document.querySelectorAll('.request-mentorship-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (btn.dataset.bookable === '1' && typeof fetchSlots === 'function') {
          fetchSlots(btn.dataset.mentorId, btn.dataset.cycleId, btn.dataset.orgId);
          return;
        }
        if (typeof window.releaseAlert === 'function') {
          window.releaseAlert('info', {
            title: btn.dataset.alertTitle || '',
            message: btn.dataset.alertMessage || ''
          });
        }
      });
    });
  })();
</script>
@endpush
