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
<section>
  <nav class="nav-align-top">
    <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" href="#overview">Overview</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#services">Services</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#portfolio">Portfolio</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#clients">Clients</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#reviews">Reviews</a>
      </li>
    </ul>
  </nav>
</section>

<!-- About and Key Stats Section -->
<section id="overview" class="mb-6">
  <div class="card border-0 shadow-sm">
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
</section>

<!-- Services Section -->
<section id="services" class="mb-6">
  <div class="card">
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
</section>

<section id="portfolio" class="mb-6">

  <div class="card">
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

                <div class="card border-0 shadow-none h-px-250 text-center cursor-pointer preview-trigger position-relative overflow-hidden"
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
</section>

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

@section('pagescripts')
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