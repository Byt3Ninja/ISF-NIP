@extends('layouts.admin')
@section('title', __('admin.Profile'))
@section('subTitle', $provider->company_name ?? __('admin.Edit your profile information'))

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-profile.css" />
<style>
  .review-card-item { position: relative; }
  .reviewed-at-date { position: absolute; bottom: 10px; }
</style>
@stop

@section('content')
<!-- Hero Section -->
<section>
  <article class="card mb-6">
    <header class="user-profile-header-banner">
      <img src="{{ $heroBanner }}" alt="Banner image" class="rounded-top" />
    </header>

    <div class="row">
      <div class="col-12">
        <div class="card shadow-none border-0 rounded-bottom rounded-0 mt-n5 position-relative">
          <div class="card-body">
            <!-- Profile Header -->
            <div class="row align-items-start">
              <div class="col-md-8">
                <figure class="flex-shrink-0 mb-6 mx-sm-0 mx-auto" style="margin-top: -80px;">
                  <img src="{{ $providerLogo }}"
                    alt="{{ $provider->company_name ?: 'Company Logo' }}"
                    class="d-block ms-0 ms-sm-6 rounded user-profile-img object-fit-cover" style="height: 130px"
                    onerror="this.onerror=null; this.src='/images/user-placeholder.jpeg';" />
                </figure>

                <!-- Title and Badge -->
                <div class="mb-4 d-flex align-items-center">
                  <h1 class="card-title h4 mb-0 fw-semibold d-inline-block me-8">{{ $provider->company_name }}</h1>
                  @if($provider->is_verified)
                    <span class="badge bg-label-primary rounded-pill">{{ __('admin.verified') }}</span>
                  @endif
                </div>

                <!-- Description -->
                @if($provider->hero_description)
                  <p class="mb-6">{{ $provider->hero_description }}</p>
                @endif

                <!-- Tags -->
                @if(!empty($tags))
                  <div class="mb-6 d-flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                      <span class="badge bg-label-light rounded-pill text-dark border py-2 px-3">{{ $tag }}</span>
                    @endforeach
                  </div>
                @endif

                <!-- Meta Information -->
                <div class="d-flex flex-column flex-sm-row gap-4 gap-sm-6">
                  @if($joinedDate)
                    <div class="small d-flex align-items-center gap-2">
                      <i class="ti ti-calendar"></i>
                      <span>{{ __('admin.joined') }} {{ $joinedDate }}</span>
                    </div>
                  @endif
                  @if($provider->country_name || $operatingModeLabel)
                    <div class="small d-flex align-items-center gap-2">
                      <i class="ti ti-map-pin"></i>
                      <span>{{ $provider->country_name }}{{ $provider->country_name && $operatingModeLabel ? ', ' : '' }}{{ __('admin.operating_mode.options.' . $operatingModeLabel) }}</span>
                    </div>
                  @endif
                  {{--  @if($provider->review_count > 0)  --}}
                    <div class="small d-flex align-items-center gap-1">
                      <i class="ti ti-star"></i>
                      <span>{{ $provider->avg_rating }} {{ __('admin.rating') }} ({{ $provider->review_count }} {{ __('admin.reviews') }})</span>
                    </div>
                  {{--  @endif  --}}
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="col-md-4 mt-6 mt-md-0 d-flex align-items-center justify-content-md-end">
                @if($canEdit)
                  @if($isOwner)
                    <a href="/dashboard/myProfile" class="btn btn-label-primary me-2">
                      <i class="ti ti-settings me-3 ti-md"></i>
                      <span class="align-middle">{{ $l == 'ar' ? 'تعديل' : 'Edit' }}</span>
                    </a>
                  @else
                    <a href="{{ route('services_providers.edit_profile', $provider->id) }}" class="btn btn-label-primary me-2">
                      <i class="ti ti-settings me-3 ti-md"></i>
                      <span class="align-middle">{{ $l == 'ar' ? 'تعديل' : 'Edit' }}</span>
                    </a>
                  @endif
                @endif
                @if($canSchedule && $provider->appointment_link)
                  <a class="btn btn-primary text-nowrap" href="{{ $provider->appointment_link }}" target="_blank" rel="noopener noreferrer">
                    <i class="ti ti-calendar-check me-2"></i>{{ __('admin.schedule_meeting') }}
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </article>
</section>

<!-- Navigation Tabs Section -->
<div class="nav-align-top mb-6">
  <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0" role="tablist">
    <li class="nav-item">
      <button type="button" class="nav-link {{ $active_tab == 'overview' ? 'active' : '' }} d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#overview" aria-controls="overview" aria-selected="true">
        <i class="ti ti-home"></i>
        <span>{{ __('admin.overview') }}</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link {{ $active_tab == 'services' ? 'active' : '' }} d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#services" aria-controls="services" aria-selected="false">
        <i class="ti ti-briefcase"></i>
        <span>{{ __('admin.Services') }}</span>
      </button>
    </li>
    @if($provider->show_portfolio)
      <li class="nav-item">
        <button type="button" class="nav-link {{ $active_tab == 'portfolio' ? 'active' : '' }} d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
          data-bs-target="#portfolio" aria-controls="portfolio" aria-selected="false">
          <i class="ti ti-layout-grid"></i>
          <span>{{ __('admin.portfolio') }}</span>
        </button>
      </li>
    @endif
    @if($provider->show_clients)
      <li class="nav-item">
        <button type="button" class="nav-link {{ $active_tab == 'clients' ? 'active' : '' }} d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
          data-bs-target="#clients" aria-controls="clients" aria-selected="false">
          <i class="ti ti-users"></i>
          <span>{{ __('admin.clients') }}</span>
        </button>
      </li>
    @endif
    <li class="nav-item">
      <button type="button" class="nav-link {{ $active_tab == 'reviews' ? 'active' : '' }} d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#reviews" aria-controls="reviews" aria-selected="false">
        <i class="ti ti-star"></i>
        <span>{{ __('admin.reviews') }}</span>
      </button>
    </li>
  </ul>

  <div class="tab-content p-0 bg-transparent border-0 shadow-none">
    <!-- About and Key Stats Section -->
    <div class="tab-pane fade {{ $active_tab == 'overview' ? 'show active' : '' }}" id="overview" role="tabpanel">
      <div class="card border-0 shadow-sm mb-6">
        <div class="card-body">
          <div class="row">
            <!-- About Section -->
            <div class="{{ $provider->show_stats && count($stats) > 0 ? 'col-lg-7 col-md-6' : 'col-12' }} mb-4 mb-md-0 pe-8">
              @if($provider->description != null)
                <h2 class="h4 mb-4" style="font-weight: bold">{{ __('admin.About') }}</h2>
                @if($provider->description)
                  <div class="mb-0">{!! nl2br(e($provider->description)) !!}</div>
                @endif
              @endif
            </div>
            <!-- Key Stats Section -->
            @if($provider->show_stats && count($stats) > 0)
              <div class="col-lg-5 col-md-6">
                <div class="bg-light-subtle rounded p-8 h-100">
                  <div class="row g-4 justify-content-center text-center">
                    <!-- Key Stats -->
                    <div class="col-12" >
                      <h3 class="mb-4" style="font-weight: bold;">{{ __('admin.Key_Stats') }}</h3>
                    </div>
                    @foreach($stats as $stat)
                      <div class="col-6 d-flex flex-column align-items-center">
                        <div class="fs-4 fw-semibold text-dark mb-1">{{ $stat['value'] }}</div>
                        <div class="text-muted small">{{ $stat['label'] }}</div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Services Section -->
    <div class="tab-pane fade {{ $active_tab == 'services' ? 'show active' : '' }}" id="services" role="tabpanel">
      <div class="card mb-6">
        <div class="card-header">
          <h2 class="h5 mb-2">{{ __('admin.Services') }}</h2>
          <div class="row">
            <div class="col-12 col-md-6">
              <p class="mb-0 text-secondary">
                {{ $provider->services_short_description ?: __('admin.provider_services_desc') }}
              </p>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if($services->isNotEmpty())
          <div class="row g-4">
            @foreach($services as $service)
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card h-100 shadow-sm border">
                <div class="card-body d-flex flex-column">
                  @php
                    $serviceImg = $service->img_url
                      ? (str_starts_with($service->img_url, 'https://') ? $service->img_url : '/191014/' . $service->img_url)
                      : '/icons/account.svg';
                  @endphp
                  <img
                    src="{{ $serviceImg }}"
                    alt="{{ $service->name ?: $service->name_en }}" class="rounded mb-4 img-fluid w-100 h-px-150 object-fit-cover"
                    onerror="this.onerror=null; this.src='/icons/account.svg';" />

                  <h3 class="card-title h5 mb-1">{{ $service->name ?: $service->name_en }}</h3>
                  <small class="text-muted">{{ $service->category_name }}</small>

                  @if($service->avg_rating > 0 || $service->review_count > 0)
                    <div class="mb-3">
                      @php
                        $svcRating = round($service->avg_rating, 1);
                        $fullStars = floor($svcRating);
                        $hasHalf = ($svcRating - $fullStars) >= 0.5;
                      @endphp
                      @for ($s = 1; $s <= 5; $s++)
                        @if ($s <= $fullStars)
                          <i class="ti ti-star-filled text-warning"></i>
                        @elseif ($s == $fullStars + 1 && $hasHalf)
                          <i class="ti ti-star-half-filled text-warning"></i>
                        @else
                          <i class="ti ti-star text-warning"></i>
                        @endif
                      @endfor
                      <span class="text-muted ms-2 small">{{ $svcRating }} ({{ $service->review_count }} {{ __('admin.reviews') }})</span>
                    </div>
                  @endif

                  <p class="card-text line-clamp-3 mb-4">
                    {!! $service->description !!}
                  </p>
                  
                  <!-- Price -->
                  @if ($service->hide_price == 0)
                    <div class="mb-3">
                      @if ($service->price > 0)
                        <span class="fs-5 fw-semibold">{{ $service->price }} {{ $service->currency }}</span>
                      @else
                        <span class="fs-5 fw-semibold">{{ __('admin.Hidden') }}</span>
                      @endif
                    </div>
                  @endif
                  <!-- Date & Status -->
                  @if ($showDateStatus)
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                      <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-calendar ti-sm text-muted"></i>
                        <span class="fw-medium text-muted">{{ \Carbon\Carbon::parse($service->created_at)->format('Y-m-d') }}</span>
                      </div>
                      {{--  <span class="badge py-1 px-2 bg-label-secondary fw-semibold">
                        <i class="ti ti-point-filled text-{{ $service->status ? 'success' : 'danger' }}"></i>
                        {{ $service->status ? __('admin.Active') : __('admin.Inactive') }}
                      </span>  --}}
                    </div>
                  @endif

                  <!-- Request Service Button -->
                  @if ($canRequestService)
                    <button
                      class="btn btn-primary w-100 mt-auto"
                      data-service-id="{{ $service->id }}"
                      data-bs-toggle="modal"
                      data-bs-target="#requestServiceModal"
                    >
                      <i class="ti ti-affiliate me-2"></i>
                      {{ __('admin.request_service') }}
                    </button>
                  @endif
                </div>
              </div>
            </div>
            
            @endforeach
            {{ $services->links('pagination::bootstrap-5') }}
          </div>
          @else
            @include('includes.no-result', ['message' => __('admin.no_services_available')])
          @endif
        </div>
        
      </div>
    </div>

    <!-- Portfolio Section -->
    @if($provider->show_portfolio)
    <div class="tab-pane fade {{ $active_tab == 'portfolio' ? 'show active' : '' }}" id="portfolio" role="tabpanel">
      <div class="card mb-6">
        <div class="card-header">
          <h2 class="h5 mb-2">{{ __('admin.portfolio') }}</h2>
          <div class="row">
            <div class="col-12 col-md-6">
              <p class="mb-0 text-secondary">
                {{ $provider->portfolio_description ?: __('admin.provider_portfolio_desc') }}
              </p>
            </div>
          </div>
        </div>

        <div class="card-body">
          @if($portfolios->isNotEmpty())
          @php
            $slides = $portfolios->chunk(3);
          @endphp

          <div id="portfolioCarousel" class="carousel carousel-dark slide" data-bs-ride="false">

            <div class="carousel-inner px-md-5">

              @foreach($slides as $slideIndex => $slideItems)
              <div class="carousel-item {{ $slideIndex === 0 ? 'active' : '' }}">
                <div class="row g-4 justify-content-center">

                  @foreach($slideItems as $item)
                  @php
                    $mediaSrc = $item->media_url;
                  @endphp
                  <div class="col-lg-4 col-md-6">

                    <div
                      class="card border-0 shadow-none h-px-250 text-center cursor-pointer preview-trigger position-relative overflow-hidden"
                      data-type="{{ $item->media_type }}" data-src="{{ $mediaSrc }}" data-title="{{ $item->title }}"
                      data-bs-toggle="modal" data-bs-target="#portfolioPreviewModal">

                      @if($item->media_type === 'video')
                      {{-- Video Thumbnail --}}
                      <img
                        src="https://img.youtube.com/vi/{{ \Illuminate\Support\Str::afterLast($item->media_url, '/') }}/hqdefault.jpg"
                        class="rounded img-fluid object-fit-cover w-100 h-100" alt="{{ $item->title }}">
                      @else
                      <img src="{{ $mediaSrc }}" class="rounded img-fluid object-fit-cover w-100 h-100"
                        alt="{{ $item->title }}">
                      @endif

                      <div
                        class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-50 rounded-circle p-3">
                        @if($item->media_type === 'video')
                        <i class="ti ti-player-play text-white fs-3"></i>
                        @else
                        <i class="ti ti-eye text-white fs-3"></i>
                        @endif
                      </div>
                    </div>

                    <div class="mt-3">
                      <h5 class="mb-2">{{ $item->title }}</h5>
                      <p class="small text-muted">{{ $item->description }}</p>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
              @endforeach
            </div>

            @if($slides->count() > 1)
              <!-- Controls -->
              <button class="carousel-control-prev" type="button" data-bs-target="#portfolioCarousel"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
              </button>

              <button class="carousel-control-next" type="button" data-bs-target="#portfolioCarousel"
                data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
              </button>

              <!-- Indicators -->
              <div class="carousel-indicators position-relative mt-4">
                @foreach($slides as $index => $slide)
                <button type="button" data-bs-target="#portfolioCarousel" data-bs-slide-to="{{ $index }}"
                  class="{{ $index === 0 ? 'active bg-primary' : 'bg-primary' }}">
                </button>
                @endforeach
              </div>
            @endif

          </div>
          @else
            @include('includes.no-result', ['message' => __('admin.no_portfolio_items')])
          @endif
        </div>
      </div>
    </div>
    @endif

    <!-- Clients Section -->
    @if($provider->show_clients)
    <div class="tab-pane fade {{ $active_tab == 'clients' ? 'show active' : '' }}" id="clients" role="tabpanel">
      <div class="card mb-6">
        <div class="card-header">
          <h2 class="h5 mb-2">{{ __('admin.clients') }}</h2>
          <div class="row">
            <div class="col-12 col-md-6">
              <p class="mb-0 text-secondary">
                {{ __('admin.provider_clients_desc') }}
              </p>
            </div>
          </div>
        </div>
        <div class="card-body">
          @if($clients->isNotEmpty())
          <div class="row g-4">
            @foreach($clients as $spClient)
            <div class="col-xl-4 col-lg-6 col-md-6">
              <div class="card border">
                <div class="card-body text-center">
                  <div class="mx-auto my-6">
                    @php
                      $clientLogo = $spClient->logo_url
                        ? (str_starts_with($spClient->logo_url, 'https://') ? $spClient->logo_url : '/191014/' . $spClient->logo_url)
                        : '/icons/account.svg';
                    @endphp
                    <img src="{{ $clientLogo }}" alt="{{ $spClient->name }}" class="rounded-circle w-px-100"
                      onerror="this.onerror=null; this.src='/vuexy/assets/img/avatars/3.png';">
                  </div>
                  <h5 class="mb-4 card-title">{{ $spClient->name }}</h5>
                  @if($spClient->external_link)
                    <a href="{{ $spClient->external_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                      <i class="me-1 ti ti-external-link me-2"></i>{{ __('admin.view_website') }}
                    </a>
                  @endif
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @else
            @include('includes.no-result', ['message' => __('admin.no_clients_found')])
          @endif
        </div>
      </div>
    </div>
    @endif

    <!-- Reviews Section -->
    <div class="tab-pane fade {{ $active_tab == 'reviews' ? 'show active' : '' }}" id="reviews" role="tabpanel">
      <div class="card mb-6">
        <div class="card-header">
          <h2 class="h5 mb-2">{{ __('admin.reviews') }}</h2>
          <div class="row">
            <div class="col-12 col-md-5">
              <p class="mb-0 text-secondary">
                {{ __('admin.provider_reviews_desc') }}
              </p>
            </div>
          </div>
        </div>

        <div class="card-body">
          <!-- Filters -->
          <div class="row align-items-center mb-6 g-3">

            <!-- Rating Buttons -->
            <div class="col-12 col-lg-7">
              <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary active" data-rating="all">
                  {{ __('admin.all_reviews') }} ({{ $provider->review_count }})
                </button>
                @foreach([5, 4, 3, 2, 1] as $star)
                  @if(isset($ratingDistribution[$star]) && $ratingDistribution[$star] > 0)
                    <button class="btn btn-outline-secondary" data-rating="{{ $star }}">
                      {{ $star }} {{ __('admin.stars') }} ({{ $ratingDistribution[$star] }})
                    </button>
                  @endif
                @endforeach
              </div>
            </div>

            <!-- Select Filters -->
            <div class="col-12 col-lg-5">
              <div class="row g-2">
                <div class="col-12 col-sm-6">
                  <select class="form-select select2 w-100" id="serviceFilter">
                    <option value="all">{{ __('admin.All Services') }}</option>
                    @foreach($serviceFilterOptions as $svcOption)
                      <option value="{{ $svcOption->id }}" {{ request('service_id') && intval( request('service_id') ) == $svcOption->id ? 'selected' : '' }} >{{ $svcOption->name ?: $svcOption->name_en }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-sm-6">
                  <select class="form-select select2 w-100" id="sortFilter">
                    <option value="recent">{{ __('admin.most_recent') }}</option>
                    <option value="oldest">{{ __('admin.oldest_first') }}</option>
                    <option value="highest">{{ __('admin.highest_rated') }}</option>
                    <option value="lowest">{{ __('admin.lowest_rated') }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-4" id="reviewsContainer">
            @include('admin.clients.partials._reviews-list', ['reviews' => $reviews['data']])
          </div>

          <div id="reviewsPagination">
            @include('admin.clients.partials._reviews-pagination', [
              'currentPage' => 1,
              'lastPage' => $reviews['last_page'],
            ])
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Portfolio Preview Modal -->
<div class="modal fade" id="portfolioPreviewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center" id="previewModalBody">
        <!-- Dynamic Content Injected Here -->
      </div>
    </div>
  </div>
</div>

<!-- Request Service Modal -->
@if ($canRequestService)
  @include('partials.request-service-modal')
@endif
@stop

@section('pagescripts')
<script>
$(function () {
  const providerId = {{ $provider->id }};
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  // Activate tab from URL query parameter (?tab=services)
  const urlTab = new URLSearchParams(window.location.search).get('tab');
  if (urlTab) {
    const $targetBtn = $('button[data-bs-target="#' + urlTab + '"]');
    if ($targetBtn.length) {
      new bootstrap.Tab($targetBtn[0]).show();
    }
  }

  // Update URL when tab changes
  $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
    const tabId = $(this).attr('data-bs-target').replace('#', '');
    const url = new URL(window.location);
    if (tabId === 'overview') {
      url.searchParams.delete('tab');
    } else {
      url.searchParams.set('tab', tabId);
    }
    window.history.replaceState({}, '', url);
  });

  const filterState = { rating: 'all', service_id: 'all', sort: 'recent', page: 1 };

  // Rating Buttons
  $(document).on('click', 'button[data-rating]', function () {
    $('button[data-rating]').removeClass('btn-primary active').addClass('btn-outline-secondary');
    $(this).addClass('btn-primary active').removeClass('btn-outline-secondary');
    filterState.rating = $(this).data('rating');
    filterState.page = 1;
    fetchReviews();
  });

  // Select Filters
  $('#serviceFilter').on('change', function () {
    filterState.service_id = $(this).val();
    filterState.page = 1;
    fetchReviews();
  });

  $('#sortFilter').on('change', function () {
    filterState.sort = $(this).val();
    filterState.page = 1;
    fetchReviews();
  });

  // Pagination
  $(document).on('click', '#reviewsPagination .page-link', function (e) {
    e.preventDefault();
    const page = $(this).data('page');
    if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
      filterState.page = page;
      fetchReviews();
    }
  });

  function fetchReviews() {
    $.ajax({
      url: '/dashboard/service-provider/' + providerId + '/reviews',
      data: filterState,
      headers: { 'X-CSRF-TOKEN': csrfToken },
      success: function (res) {
        $('#reviewsContainer').html(res.html);
        $('#reviewsPagination').html(res.pagination);
      },
    });
  }

  // Portfolio Modal logic
  const $modal = $('#portfolioPreviewModal');
  const $title = $('#previewModalTitle');
  const $body = $('#previewModalBody');

  $modal.on('show.bs.modal', function (e) {
    const { type, src, title } = $(e.relatedTarget).data();
    $title.text(title);
    $body.html(type === 'video'
      ? `<div class="ratio ratio-16x9"><iframe src="${src}?autoplay=1" allowfullscreen></iframe></div>`
      : `<img src="${src}" class="img-fluid rounded">`
    );
  }).on('hidden.bs.modal', () => $body.empty());
});
</script>
@stop
