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
                  <span class="badge bg-label-primary rounded-pill">Verified</span>
                </div>

                <!-- Description -->
                <p class="mb-6">
                  Leading product development and platform solutions company specializing in growth
                  strategies, MVP development, and market validation. Helping startups and enterprises build
                  products that matter.
                </p>

                <!-- Services -->
                <div class="mb-6">
                  <span class="badge bg-label-secondary text-dark border me-2 py-2 px-3">Product & Business</span>
                  <span class="badge bg-label-secondary text-dark border me-2 py-2 px-3">Platform Development</span>
                  <span class="badge bg-label-secondary text-dark border me-2 py-2 px-3">Growth Strategy</span>
                  <span class="badge bg-label-secondary text-dark border me-2 py-2 px-3">Market Validation</span>
                </div>

                <!-- Meta Information -->
                <div class="small">
                  <i class="ti ti-calendar me-1"></i>
                  <span class="me-3">Joined December 2025</span>
                  <i class="ti ti-map-pin me-1"></i>
                  <span class="me-3">Egypt, Remote</span>
                  <i class="ti ti-star me-1"></i>
                  <span>5.0 Rating (127 reviews)</span>
                </div>
              </div>

              <!-- Book Now Button -->
              <div class="col-md-4 text-end">
                <button class="btn btn-primary px-4">
                  <i class="ti ti-calendar-check me-2"></i>Schedule Meeting
                </button>
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
        <span>Overview</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#services" aria-controls="services" aria-selected="false">
        <i class="ti ti-briefcase"></i>
        <span>Services</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#portfolio" aria-controls="portfolio" aria-selected="false">
        <i class="ti ti-layout-grid"></i>
        <span>Portfolio</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#clients" aria-controls="clients" aria-selected="false">
        <i class="ti ti-users"></i>
        <span>Clients</span>
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link d-flex align-items-center gap-2" role="tab" data-bs-toggle="tab"
        data-bs-target="#reviews" aria-controls="reviews" aria-selected="false">
        <i class="ti ti-star"></i>
        <span>Reviews</span>
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
              <h2 class="h5 mb-4">About</h2>

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
      <h2 class="h5 mb-2">Services</h2>
      <div class="row">
        <div class="col-12 col-md-6">
          <p class="mb-0 text-secondary">
            We offer comprehensive solutions to help startups validate, build, and scale their
            products with proven methodologies and expert guidance.
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
                  <span class="text-muted ms-2 small">4.5 (127 reviews)</span>
              </div>

              <p class="card-text line-clamp-3 mb-4">
                Comprehensive market research and validation to ensure product-market fit before development begins.
              </p>

              <button class="btn btn-primary w-100 mt-auto">Request Service</button>
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
      <h2 class="h5 mb-2">Portfolio</h2>
      <div class="row">
        <div class="col-12 col-md-6">
          <p class="mb-0 text-secondary">
            Explore our portfolio of successful projects and see
            how we've helped startups transform their ideas into thriving businesses.
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
        <button class="carousel-control-prev" type="button" data-bs-target="#portfolioCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#portfolioCarousel" data-bs-slide="next">
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

<div class="tab-pane fade" id="clients" role="tabpanel">
  <div class="card mb-6">
    <div class="card-header">
      <h2 class="h5 mb-2">Clients</h2>
      <div class="row">
        <div class="col-12 col-md-6">
          <p class="mb-0 text-secondary">
            Trusted by leading startups and enterprises worldwide.
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

<div class="tab-pane fade" id="reviews" role="tabpanel">
  <div class="card mb-6">
    <div class="card-header">
      <h2 class="h5 mb-2">Reviews</h2>
      <div class="row">
        <div class="col-12 col-md-5">
          <p class="mb-0 text-secondary">
            Read what our clients have to say about our services and their experiences working with us.
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
            <button class="btn btn-primary active" data-rating="all"
              onclick="filterByRating('all')">
              All Reviews (247)
            </button>

            <button class="btn btn-outline-secondary" data-rating="5" onclick="filterByRating('5')">
              5 Stars (198)
            </button>

            <button class="btn btn-outline-secondary" data-rating="4" onclick="filterByRating('4')">
              4 Stars (42)
            </button>

            <button class="btn btn-outline-secondary" data-rating="3" onclick="filterByRating('3')">
              3 Stars (7)
            </button>
          </div>
        </div>

        <!-- Select Filters -->
        <div class="col-12 col-lg-5">
          <div class="row g-2">

            <div class="col-12 col-sm-6">
              <select class="form-select select2 w-100" id="serviceFilter" onchange="filterByService()">
                <option value="all">All Services</option>
                <option value="Idea Validation">Idea Validation</option>
                <option value="Growth Strategy">Growth Strategy</option>
                <option value="MVP Development">MVP Development</option>
                <option value="Investor Readiness">Investor Readiness</option>
                <option value="User Experience">User Experience</option>
                <option value="Market Research">Market Research</option>
              </select>
            </div>

            <div class="col-12 col-sm-6">
              <select class="form-select select2 w-100" id="sortFilter" onchange="sortReviews()">
                <option value="recent">Most Recent</option>
                <option value="oldest">Oldest First</option>
                <option value="highest">Highest Rated</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews Grid -->
      <div class="row g-4" id="reviewsContainer">
        <div id="noReviewsMessage" class="col-12 d-none">
          @include('includes.no-result', ['message' => 'No reviews found'])
        </div>
        <!-- Review 1 -->
        <div class="col-md-6">
          <div class="bg-white rounded-3 p-4 border" data-rating="5" data-service="Idea Validation"
            data-date="2025-01-15">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex gap-3">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                  style="width: 48px; height: 48px;">
                  <i class="ti ti-user fs-5"></i>
                </div>
                <div>
                  <h6 class="mb-1 fw-semibold">Sarah Mitchell</h6>
                  <div class="text-muted small">CEO, TechVenture Inc.</div>
                  <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-light-subtle border text-dark rounded-pill">Idea Validation</span>
                    <span class="text-muted small">• Joined since 2023</span>
                  </div>
                </div>
              </div>
              <div class="text-warning">
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
              </div>
            </div>
            <p class="text-dark lh-base">"Growth Labs transformed our vague idea into a validated product concept. Their
              market research was incredibly thorough, and they helped us identify our target audience with precision.
              Within 4 months, we had an MVP and our first paying customers. Couldn't have done it without their
              expertise."</p>
            <div class="text-muted small">January 15, 2025</div>
          </div>
        </div>

        <!-- Review 2 -->
        <div class="col-md-6">
          <div class="bg-white rounded-3 p-4 border" data-rating="5" data-service="Growth Strategy"
            data-date="2025-01-10">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex gap-3">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                  style="width: 48px; height: 48px;">
                  <i class="ti ti-user fs-5"></i>
                </div>
                <div>
                  <h6 class="mb-1 fw-semibold">Michael Chen</h6>
                  <div class="text-muted small">Founder, DataStream Solutions</div>
                  <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-light-subtle border text-dark rounded-pill">Growth Strategy</span>
                    <span class="text-muted small">• Joined since 2024</span>
                  </div>
                </div>
              </div>
              <div class="text-warning">
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
              </div>
            </div>
            <p class="text-dark lh-base">"The growth strategy consultation was a game-changer for us. Growth Labs helped
              us identify the right acquisition channels and optimize our conversion funnel. Our user base grew 5x in
              just 6 months, and our CAC decreased by 40%. Highly recommend their services to any startup serious about
              growth."</p>
            <div class="text-muted small">January 10, 2025</div>
          </div>
        </div>

        <!-- Review 3 -->
        <div class="col-md-6">
          <div class="bg-white rounded-3 p-4 border" data-rating="4.5" data-service="MVP Development"
            data-date="2025-01-05">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex gap-3">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                  style="width: 48px; height: 48px;">
                  <i class="ti ti-user fs-5"></i>
                </div>
                <div>
                  <h6 class="mb-1 fw-semibold">Emily Rodriguez</h6>
                  <div class="text-muted small">Co-Founder, EcoMarket</div>
                  <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-light-subtle border text-dark rounded-pill">MVP Development</span>
                    <span class="text-muted small">• Joined since 2024</span>
                  </div>
                </div>
              </div>
              <div class="text-warning">
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-half-filled fs-6"></i>
              </div>
            </div>
            <p class="text-dark lh-base">"Working with Growth Labs on our MVP was an incredible experience. They helped
              us prioritize features, conduct user testing, and iterate quickly. The result was a product our users
              actually loved. We secured seed funding 2 months after launch thanks to the strong foundation they helped
              us build."</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted small">January 5, 2025</span>
            </div>
          </div>
        </div>

        <!-- Review 4 -->
        <div class="col-md-6">
          <div class="bg-white rounded-3 p-4 border" data-rating="5" data-service="Investor Readiness"
            data-date="2024-12-28">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex gap-3">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                  style="width: 48px; height: 48px;">
                  <i class="ti ti-user fs-5"></i>
                </div>
                <div>
                  <h6 class="mb-1 fw-semibold">James Anderson</h6>
                  <div class="text-muted small">CEO, FinanceHub</div>
                  <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-light-subtle border text-dark rounded-pill">Investor Readiness</span>
                    <span class="text-muted small">• Joined since 2023</span>
                  </div>
                </div>
              </div>
              <div class="text-warning">
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
              </div>
            </div>
            <p class="text-dark lh-base">"The investor readiness program was exactly what we needed. Growth Labs helped
              us craft a compelling pitch deck, build realistic financial projections, and prepare for tough investor
              questions. We raised our Series A within 3 months of completing the program. Their network of investors
              was also invaluable."</p>
            <div class="text-muted small">December 28, 2024</div>
          </div>
        </div>

        <!-- Review 5 -->
        <div class="col-md-6">
          <div class="bg-white rounded-3 p-4 border" data-rating="4.5" data-service="User Experience"
            data-date="2024-12-22">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex gap-3">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                  style="width: 48px; height: 48px;">
                  <i class="ti ti-user fs-5"></i>
                </div>
                <div>
                  <h6 class="mb-1 fw-semibold">Lisa Thompson</h6>
                  <div class="text-muted small">Founder, HealthTech Pro</div>
                  <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-light-subtle border text-dark rounded-pill">User Experience</span>
                    <span class="text-muted small">• Joined since 2024</span>
                  </div>
                </div>
              </div>
              <div class="text-warning">
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-half-filled fs-6"></i>
              </div>
            </div>
            <p class="text-dark lh-base">"Outstanding UX consultation! The team conducted comprehensive user research
              and helped us redesign our entire product interface. User satisfaction scores increased from 3.2 to 4.7,
              and our churn rate dropped by 35%. They truly understand what makes users engage with a product."</p>
            <div class="text-muted small">December 22, 2024</div>
          </div>
        </div>

        <!-- Review 6 -->
        <div class="col-md-6">
          <div class="bg-white rounded-3 p-4 border" data-rating="5" data-service="Market Research"
            data-date="2024-12-18">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex gap-3">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                  style="width: 48px; height: 48px;">
                  <i class="ti ti-user fs-5"></i>
                </div>
                <div>
                  <h6 class="mb-1 fw-semibold">David Park</h6>
                  <div class="text-muted small">CTO, CloudScale Systems</div>
                  <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge bg-light-subtle border text-dark rounded-pill">Market Research</span>
                    <span class="text-muted small">• Joined since 2023</span>
                  </div>
                </div>
              </div>
              <div class="text-warning">
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
                <i class="ti ti-star-filled fs-6"></i>
              </div>
            </div>
            <p class="text-dark lh-base">"The market research and competitive analysis provided by Growth Labs gave us
              clarity we desperately needed. They identified a gap in the market we hadn't considered, which became our
              main differentiator. Our product-market fit improved dramatically, and we're now the fastest-growing
              player in our category."</p>
            <div class="text-muted small">December 18, 2024</div>
          </div>
        </div>
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
@stop

@section('scripts')
<script>
let allReviews = [];
let reviewCurrentPage = 1;
const reviewsPerPage = 6;

// Initialize reviews array on page load
document.addEventListener('DOMContentLoaded', function () {
  const reviewCards = document.querySelectorAll('[data-rating]');
  const validCards = Array.from(reviewCards).filter(card => card.classList.contains('bg-white'));
  allReviews = validCards.map(card => ({
    element: card.parentElement,
    rating: parseFloat(card.dataset.rating),
    service: card.dataset.service,
    date: new Date(card.dataset.date)
  }));
});

// Filter by rating
function filterByRating(rating) {
  // Update active button
  document.querySelectorAll('[data-rating]').forEach(btn => {
    if (btn.tagName === 'BUTTON') {
      btn.classList.remove('btn-primary');
      btn.classList.add('btn-outline-secondary');
    }
  });
  event.target.classList.remove('btn-outline-secondary');
  event.target.classList.add('btn-primary');

  // Filter reviews
  const reviewCards = document.querySelectorAll('[data-rating]');
  reviewCards.forEach(card => {
    if (card.tagName !== 'DIV' || !card.classList.contains('bg-white')) return;

    const cardRating = parseFloat(card.dataset.rating);

    if (rating === 'all') {
      card.parentElement.classList.remove('d-none');
    } else if (rating === '5' && cardRating === 5) {
      card.parentElement.classList.remove('d-none');
    } else if (rating === '4' && cardRating >= 4 && cardRating < 5) {
      card.parentElement.classList.remove('d-none');
    } else if (rating === '3' && cardRating >= 3 && cardRating < 4) {
      card.parentElement.classList.remove('d-none');
    } else {
      card.parentElement.classList.add('d-none');
    }
  });

  reviewCurrentPage = 1;
  applyFilters();
}

// Filter by service
function filterByService() {
  applyFilters();
}

// Apply all filters
function applyFilters() {
  const serviceFilter = document.getElementById('serviceFilter').value;
  const reviewCards = document.querySelectorAll('[data-rating]');
  let visibleCount = 0;

  reviewCards.forEach(card => {
    if (card.tagName !== 'DIV' || !card.classList.contains('bg-white')) return;
    if (card.parentElement.classList.contains('d-none')) return;

    const cardService = card.dataset.service;

    if (serviceFilter === 'all' || cardService === serviceFilter) {
      card.parentElement.classList.remove('d-none');
      visibleCount++;
    } else {
      card.parentElement.classList.add('d-none');
    }
  });

  const noReviewsMessage = document.getElementById('noReviewsMessage');
  if (visibleCount === 0) {
    noReviewsMessage.classList.remove('d-none');
  } else {
    noReviewsMessage.classList.add('d-none');
  }
}

// Sort reviews
function sortReviews() {
  const sortValue = document.getElementById('sortFilter').value;
  const container = document.getElementById('reviewsContainer');
  const reviews = Array.from(container.children).filter(el => el.id !== 'noReviewsMessage');

  reviews.sort((a, b) => {
    const cardA = a.querySelector('[data-rating]');
    const cardB = b.querySelector('[data-rating]');

    if (!cardA || !cardB) return 0;

    if (sortValue === 'recent') {
      return new Date(cardB.dataset.date) - new Date(cardA.dataset.date);
    } else if (sortValue === 'oldest') {
      return new Date(cardA.dataset.date) - new Date(cardB.dataset.date);
    } else if (sortValue === 'highest') {
      return parseFloat(cardB.dataset.rating) - parseFloat(cardA.dataset.rating);
    }
    return 0;
  });

  reviews.forEach(review => container.appendChild(review));
}
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
  const previewModal = document.getElementById('portfolioPreviewModal');
  const modalTitle = document.getElementById('previewModalTitle');
  const modalBody = document.getElementById('previewModalBody');

  previewModal.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;

    const type = trigger.getAttribute('data-type');
    const src = trigger.getAttribute('data-src');
    const title = trigger.getAttribute('data-title');

    modalTitle.textContent = title;
    modalBody.innerHTML = '';

    if (type === 'video') {
      modalBody.innerHTML = `
        <div class="ratio ratio-16x9">
          <iframe src="${src}?autoplay=1"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
          </iframe>
        </div>
      `;
    } else {
      modalBody.innerHTML = `
        <img src="${src}" class="img-fluid rounded" />
      `;
    }
  });

  previewModal.addEventListener('hidden.bs.modal', function () {
    modalBody.innerHTML = '';
  });
});
</script>
@stop
