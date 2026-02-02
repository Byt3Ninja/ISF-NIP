@extends('layouts.admin')
@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-profile.css" />
@stop

@php
$portfolios = [
[
'type' => 'video',
'src' => 'https://www.youtube.com/embed/fO4Ha_aMmDs',
'title' => 'TechFlow Growth Transformation',
'description' => 'How we helped TechFlow achieve 5x growth in 6 months with strategic product validation.'
],
[
'type' => 'image',
'src' => 'https://t4.ftcdn.net/jpg/00/03/74/41/360_F_3744121_97lPGmfpiXN8e8y6rGOrP9F6BnhZ7787.jpg',
'title' => 'EcoTrace Product Roadmap',
'description' => 'Collaborative planning session to define product roadmap and priorities.'
],
[
'type' => 'image',
'src' => 'https://i.pinimg.com/236x/61/5d/fe/615dfea87c69172663273d93ed1f9ee0.jpg',
'title' => 'HealthTech Market Entry',
'description' => 'Partnership to develop comprehensive go-to-market strategy for new product.'
],
[
'type' => 'image',
'src' => 'https://i.pinimg.com/236x/61/5d/fe/615dfea87c69172663273d93ed1f9ee0.jpg',
'title' => 'Analytics Platform Success',
'description' => 'Implementing comprehensive analytics framework to drive business growth.'
],
];

$reviews = [
[
'name' => 'Sarah Mitchell',
'role' => 'CEO, TechVenture Inc.',
'service' => 'Idea Validation',
'rating' => 3,
'date' => '2025-01-15',
'joined' => '2023',
'content' => 'Growth Labs transformed our vague idea into a validated product concept...'
],
[
'name' => 'Michael Chen',
'role' => 'Founder, DataStream Solutions',
'service' => 'Growth Strategy',
'rating' => 5,
'date' => '2025-01-10',
'joined' => '2024',
'content' => 'The growth strategy consultation was a game-changer for us...'
],
[
'name' => 'Emily Rodriguez',
'role' => 'Co-Founder, EcoMarket',
'service' => 'MVP Development',
'rating' => 4.5,
'date' => '2025-01-05',
'joined' => '2024',
'content' => 'Working with Growth Labs on our MVP was an incredible experience...'
],
];
@endphp

@section('content')
<!-- Hero Section -->
<section>
  <article class="card mb-6">
    <header class="user-profile-header-banner">
      <img src="/vuexy/assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top" />
    </header>

    <div class="row">
      <div class="col-12">
        <div class="card shadow-none border-0 rounded-bottom rounded-0 mt-n5 position-relative">
          <div class="card-body">
            <!-- Profile Header -->
            <div class="row align-items-start">
              <div class="col-md-8">
                <figure class="flex-shrink-0 mb-6 mx-sm-0 mx-auto" style="margin-top: -80px;">
                  <img src="{{ $client->image_url ?: '/images/user-placeholder.jpeg' }}"
                    alt="{{ $client->company_name ?: 'Company Logo' }}"
                    class="d-block ms-0 ms-sm-6 rounded user-profile-img object-fit-cover" style="height: 130px"
                    onerror="this.onerror=null; this.src='/images/user-placeholder.jpeg';" />
                </figure>

                <!-- Title and Badge -->
                <div class="mb-4 d-flex align-items-center">
                  <h1 class="card-title h4 mb-0 fw-semibold d-inline-block me-8">Products & Platforms</h1>
                  <span class="badge bg-label-primary rounded-pill">{{ __('admin.verified') }}</span>
                </div>

                <!-- Description -->
                <p class="mb-6">
                  Leading product development and platform solutions company specializing in growth
                  strategies, MVP development, and market validation. Helping startups and enterprises build
                  products that matter.
                </p>

                <!-- Services -->
                <div class="mb-6 d-flex flex-wrap gap-2">
                  <span class="badge bg-label-secondary text-dark border py-2 px-3">Product & Business</span>
                  <span class="badge bg-label-secondary text-dark border py-2 px-3">Platform Development</span>
                  <span class="badge bg-label-secondary text-dark border py-2 px-3">Growth Strategy</span>
                  <span class="badge bg-label-secondary text-dark border py-2 px-3">Market Validation</span>
                </div>

                <!-- Meta Information -->
                <div class="d-flex flex-column flex-sm-row gap-4 gap-sm-6">
                  <div class="small d-flex align-items-center gap-2">
                    <i class="ti ti-calendar"></i>
                    <span>{{ __('admin.joined') }} December 2025</span>
                  </div>
                  <div class="small d-flex align-items-center gap-2">
                    <i class="ti ti-map-pin"></i>
                    <span>Egypt, Remote</span>
                  </div>
                  <div class="small d-flex align-items-center gap-1">
                    <i class="ti ti-star"></i>
                    <span>5.0 {{ __('admin.rating') }} (127 {{ __('admin.reviews') }})</span>
                  </div>
                </div>
              </div>

              <!-- Schedule Meeting Button -->
              <div class="col-md-4 mt-6 mt-md-0 d-flex justify-content-md-end">
                <!-- Replace the href with actual scheduling or oppointment link -->
                <a class="btn btn-primary" href="https://tabler.io/icons" target="_blank" rel="noopener noreferrer">
                  <i class="ti ti-calendar-check me-2"></i>{{ __('admin.schedule_meeting') }}
                </a>
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
      <button type="button" class="nav-link active d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#overview" aria-controls="overview" aria-selected="true">
        <i class="ti ti-home"></i>
        <span>{{ __('admin.overview') }}</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#services" aria-controls="services" aria-selected="false">
        <i class="ti ti-briefcase"></i>
        <span>{{ __('admin.Services') }}</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#portfolio" aria-controls="portfolio" aria-selected="false">
        <i class="ti ti-layout-grid"></i>
        <span>{{ __('admin.portfolio') }}</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#clients" aria-controls="clients" aria-selected="false">
        <i class="ti ti-users"></i>
        <span>{{ __('admin.clients') }}</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#reviews" aria-controls="reviews" aria-selected="false">
        <i class="ti ti-star"></i>
        <span>{{ __('admin.reviews') }}</span>
      </button>
    </li>
  </ul>

  <div class="tab-content p-0 bg-transparent border-0 shadow-none">
    <!-- About and Key Stats Section -->
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
      <div class="card border-0 shadow-sm mb-6">
        <div class="card-body">
          <div class="row">
            <!-- About Section -->
            <div class="col-lg-7 col-md-6 mb-4 mb-md-0 pe-8">
              <h2 class="h5 mb-4">{{ __('admin.description') }}</h2>

              <p class="mb-4">
                We specialize in helping early-stage startups validate their product-market fit through data-driven
                methodologies and proven frameworks. Our team has worked with over 200 startups across North America and
                Europe, helping them build successful products and go-to-market strategies.
              </p>

              <p class="mb-0">
                From initial concept validation to MVP development and launch strategy, we provide end-to-end support to
                ensure your startup has the best chance of success in competitive markets.
              </p>
            </div>

            <!-- Key Stats Section -->
            <div class="col-lg-5 col-md-6">
              <div class="bg-light-subtle rounded p-8 h-100">
                {{-- <h3 class="h6 fw-normal mb-4">Key Stats</h3> --}}

                <div class="row g-4 justify-content-center text-center">
                  <!-- Founded Year -->
                  <div class="col-6 d-flex flex-column align-items-center">
                    <div class="fs-4 fw-semibold text-dark mb-1">2019</div>
                    <div class="text-muted small">Founded Year</div>
                  </div>

                  <!-- Team Size -->
                  <div class="col-6 d-flex flex-column align-items-center">
                    <div class="fs-4 fw-semibold text-dark mb-1">15</div>
                    <div class="text-muted small">Team Size</div>
                  </div>

                  <!-- Countries Served -->
                  <div class="col-6 d-flex flex-column align-items-center">
                    <div class="fs-4 fw-semibold text-dark mb-1">8</div>
                    <div class="text-muted small">Countries Served</div>
                  </div>

                  <!-- Clients Served -->
                  <div class="col-6 d-flex flex-column align-items-center">
                    <div class="fs-4 fw-semibold text-dark mb-1">200+</div>
                    <div class="text-muted small">Clients Served</div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Services Section -->
    <div class="tab-pane fade" id="services" role="tabpanel">
      <div class="card mb-6">
        <div class="card-header">
          <h2 class="h5 mb-2">{{ __('admin.Services') }}</h2>
          <div class="row">
            <div class="col-12 col-md-6">
              <p class="mb-0 text-secondary">
                {{ __('admin.provider_services_desc') }}
              </p>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-4">
            @foreach([1,2,3] as $i)
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card h-100 shadow-sm border">
                <div class="card-body d-flex flex-column">
                  <img
                    src="https://c7.alamy.com/comp/WJDPXA/imagge-for-flying-seagulls-over-a-yucatan-peninsula-beach-WJDPXA.jpg"
                    alt="Market Validation" class="rounded mb-4 img-fluid w-100 h-px-150" />

                  <h3 class="card-title h5 mb-3">Market Validation</h3>

                  <div class="mb-3">
                    @php
                    $rating = 4.5; // replace with dynamic value
                    $fullStars = floor($rating);
                    $hasHalf = ($rating - $fullStars) >= 0.5;
                    @endphp

                    @for ($i = 1; $i <= 5; $i++) @if ($i <=$fullStars) <i class="ti ti-star-filled text-warning"></i>
                      @elseif ($i == $fullStars + 1 && $hasHalf)
                        <i class="ti ti-star-half-filled text-warning"></i>
                      @else
                        <i class="ti ti-star text-warning"></i>
                      @endif
                    @endfor
                    <span class="text-muted ms-2 small">4.5 (127 {{ __('admin.reviews') }})</span>
                  </div>

                  <p class="card-text line-clamp-3 mb-4">
                    Comprehensive market research and validation to ensure product-market fit before development begins.
                  </p>

                  {{--  Static variable should be removed --}}
                  @php
                    $id = 1;
                    $status = 1;
                    $created_at = \Carbon\Carbon::now()->subDays(5);
                  @endphp

                  <!-- Date & Status -->
                  @if (!$_user->hasRole('registered'))
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                      <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-calendar ti-sm text-muted"></i>
                        <span class="fw-medium text-muted">{{ $created_at->format('Y-m-d') }}</span>
                      </div>
                      <span class="badge py-1 px-2 bg-label-secondary fw-semibold">
                        <i class="ti ti-point-filled text-{{ $status == 1 ? 'success' : 'danger' }}"></i>
                        {{ $status == 1 ? __('admin.Active') : __('admin.Inactive') }}
                      </span>
                    </div>
                  @endif

                  <!-- Request Service Button -->
                  @if ($_user->hasRole('registered'))
                    <button 
                      class="btn btn-primary w-100 mt-auto"
                      data-service-id="{{ $id }}"
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
          </div>
        </div>
      </div>
    </div>

    <!-- Portfolio Section -->
    <div class="tab-pane fade" id="portfolio" role="tabpanel">
      <div class="card mb-6">
        <div class="card-header">
          <h2 class="h5 mb-2">{{ __('admin.portfolio') }}</h2>
          <div class="row">
            <div class="col-12 col-md-6">
              <p class="mb-0 text-secondary">
                {{ __('admin.provider_portfolio_desc') }}
              </p>
            </div>
          </div>
        </div>

        @php
        $slides = collect($portfolios)->chunk(3);
        @endphp

        <div class="card-body">
          <div id="portfolioCarousel" class="carousel carousel-dark slide" data-bs-ride="false">

            <div class="carousel-inner px-md-5">

              @foreach($slides as $slideIndex => $slideItems)
              <div class="carousel-item {{ $slideIndex === 0 ? 'active' : '' }}">
                <div class="row g-4 justify-content-center">

                  @foreach($slideItems as $item)
                  <div class="col-lg-4 col-md-6">

                    <div
                      class="card border-0 shadow-none h-px-250 text-center cursor-pointer preview-trigger position-relative overflow-hidden"
                      data-type="{{ $item['type'] }}" data-src="{{ $item['src'] }}" data-title="{{ $item['title'] }}"
                      data-bs-toggle="modal" data-bs-target="#portfolioPreviewModal">

                      @if($item['type'] === 'video')
                      {{-- Video Thumbnail --}}
                      <img
                        src="https://img.youtube.com/vi/{{ \Illuminate\Support\Str::afterLast($item['src'], '/') }}/hqdefault.jpg"
                        class="rounded img-fluid object-fit-cover w-100 h-100" alt="{{ $item['title'] }}">
                      @else
                      <img src="{{ $item['src'] }}" class="rounded img-fluid object-fit-cover w-100 h-100"
                        alt="{{ $item['title'] }}">
                      @endif

                      <div
                        class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-50 rounded-circle p-3">
                        @if($item['type'] === 'video')
                        <i class="ti ti-player-play text-white fs-3"></i>
                        @else
                        <i class="ti ti-eye text-white fs-3"></i>
                        @endif
                      </div>
                    </div>

                    <div class="mt-3">
                      <h5 class="mb-2">{{ $item['title'] }}</h5>
                      <p class="small text-muted">{{ $item['description'] }}</p>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
              @endforeach
            </div>

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

          </div>
        </div>
      </div>
    </div>

    <!-- Clients Section -->
    <div class="tab-pane fade" id="clients" role="tabpanel">
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
          <div class="row g-4">
            @foreach([1,2,3,4,5,6] as $i)
            <div class="col-xl-4 col-lg-6 col-md-6">
              <div class="card border">
                <div class="card-body text-center">
                  <div class="mx-auto my-6">
                    <img src="/vuexy/assets/img/avatars/3.png" alt="Avatar Image" class="rounded-circle w-px-100">
                  </div>
                  <h5 class="mb-4 card-title">Mark Gilbert</h5>
                  <a href="javascript:;" class="btn btn-primary">
                    <i class="me-1 ti ti-external-link me-2"></i>View Website
                  </a>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <!-- Reviews Section -->
    <div class="tab-pane fade" id="reviews" role="tabpanel">
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
                  {{ __('admin.all_reviews') }} (247)
                </button>

                <button class="btn btn-outline-secondary" data-rating="5">
                  5 {{ __('admin.stars') }} (198)
                </button>

                <button class="btn btn-outline-secondary" data-rating="4">
                  4 {{ __('admin.stars') }} (42)
                </button>

                <button class="btn btn-outline-secondary" data-rating="3">
                  3 {{ __('admin.stars') }} (7)
                </button>
              </div>
            </div>

            <!-- Select Filters -->
            <div class="col-12 col-lg-5">
              <div class="row g-2">

                <div class="col-12 col-sm-6">
                  <select class="form-select select2 w-100" id="serviceFilter">
                    <option value="all">{{ __('admin.All Services') }}</option>
                    <option value="idea validation">Idea Validation</option>
                    <option value="growth strategy">Growth Strategy</option>
                    <option value="mvp development">MVP Development</option>
                    <option value="investor readiness">Investor Readiness</option>
                    <option value="user experience">User Experience</option>
                    <option value="market research">Market Research</option>
                  </select>
                </div>

                <div class="col-12 col-sm-6">
                  <select class="form-select select2 w-100" id="sortFilter">
                    <option value="recent">{{ __('admin.most_recent') }}</option>
                    <option value="oldest">{{ __('admin.oldest_first') }}</option>
                    <option value="highest">{{ __('admin.highest_rated') }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-4" id="reviewsContainer">
            <div id="noReviewsMessage" class="col-12 d-none">
              @include('includes.no-result', ['message' => 'No reviews found'])
            </div>

            @foreach($reviews as $review)
            <div class="col-12 col-md-6 review-item" data-rating="{{ $review['rating'] }}"
              data-service="{{ strtolower(trim($review['service'])) }}"
              data-date="{{ \Carbon\Carbon::parse($review['date'])->toIso8601String() }}">

              <div class="bg-card rounded-3 p-4 border h-100">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">

                  <div class="d-flex gap-3">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                      style="width: 48px; height: 48px;">
                      <i class="ti ti-user fs-5"></i>
                    </div>

                    <div>
                      <h6 class="mb-1 fw-semibold">{{ $review['name'] }}</h6>
                      <div class="text-muted small">{{ $review['role'] }}</div>

                      <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        <span class="badge bg-light-subtle border text-dark rounded-pill">
                          {{ $review['service'] }}
                        </span>
                        <span class="text-muted small">
                          • {{ __('admin.joined') }} {{ $review['joined'] }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Dynamic Stars -->
                  <div class="text-warning">
                    @php
                    $fullStars = floor($review['rating']);
                    $halfStar = $review['rating'] - $fullStars >= 0.5;
                    @endphp

                    @for($i = 0; $i < $fullStars; $i++) <i class="ti ti-star-filled fs-6"></i>
                      @endfor

                      @if($halfStar)
                      <i class="ti ti-star-half-filled fs-6"></i>
                      @endif
                  </div>
                </div>

                <!-- Content -->
                <p>
                  {{ $review['content'] }}
                </p>

                <!-- Date -->
                <div class="text-muted small">
                  {{ \Carbon\Carbon::parse($review['date'])->format('F j, Y') }}
                </div>
              </div>
            </div>
            @endforeach
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
@if ($_user->hasRole('registered'))
  @include('partials.request-service-modal')
@endif
@stop

@section('pagescripts')
<script>
$(function () {
  const $reviewItems = $('.review-item');
  const $container = $('#reviewsContainer');
  const $noReviewsMsg = $('#noReviewsMessage');
  const $ratingBtns = $('button[data-rating]'); // Specific to buttons only

  const filterState = { rating: 'all', service: 'all', sort: 'recent' };

  // 1. Index data (Matches .review-item)
  const allReviews = $reviewItems.map(function() {
    const $el = $(this);
    return {
      $el,
      rating: parseFloat($el.data('rating')),
      service: String($el.data('service')).toLowerCase().trim(),
      date: new Date($el.data('date'))
    };
  }).get();

  // 2. Event Listeners
  // Filter Buttons - Now specific to BUTTONS
  $ratingBtns.on('click', function () {
    $ratingBtns.removeClass('btn-primary').addClass('btn-outline-secondary');
    $(this).addClass('btn-primary').removeClass('btn-outline-secondary');

    filterState.rating = $(this).data('rating');
    applyFilters();
  });

  // Select Filters
  $('#serviceFilter, #sortFilter').on('change', function () {
    const key = this.id.replace('Filter', ''); 
    filterState[key] = $(this).val().toLowerCase().trim();
    applyFilters();
  });

  function applyFilters() {
    // Filter logic
    const filtered = allReviews.filter(r => {
      const ratingMatch = filterState.rating === 'all' || Math.floor(r.rating) === parseInt(filterState.rating);
      const serviceMatch = filterState.service === 'all' || r.service === filterState.service;
      return ratingMatch && serviceMatch;
    });

    // Sort logic
    const sorters = {
      recent: (a, b) => b.date - a.date,
      oldest: (a, b) => a.date - b.date,
      highest: (a, b) => b.rating - a.rating
    };
    filtered.sort(sorters[filterState.sort] || sorters.recent);

    // UI Updates
    $reviewItems.addClass('d-none'); 

    if (filtered.length > 0) {
      $noReviewsMsg.addClass('d-none');
      // Physically reorder elements in the DOM
      const $elements = filtered.map(r => r.$el.removeClass('d-none'));
      $container.append($elements); 
    } else {
      $noReviewsMsg.removeClass('d-none');
    }
  }

  // 3. Modal logic (Portfolio)
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