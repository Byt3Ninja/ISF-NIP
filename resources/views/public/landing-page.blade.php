<!DOCTYPE html>
<html
  lang="{{ $l }}"
  dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style"
  data-assets-path="/vuexy/assets/"
>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $page->title }}</title>

  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" />
  <link rel="stylesheet" href="/vuexy/assets/css/demo.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/front-page.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/front-page-landing.css" />
</head>
<body>

  @php
    $isAr = $l == 'ar';
  @endphp

  <!-- Navbar -->
  <nav class="layout-navbar shadow-none py-3">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        @if($page->logo_url)
          <img src="{{ $page->logo_url }}" alt="{{ $page->title }}" style="max-height:40px;object-fit:contain;">
        @endif
        <span class="fw-semibold">{{ $page->title }}</span>
      </div>
      @php
        $socialLinks = [
          'linkedin' => $page->linkedin, 'facebook' => $page->facebook, 'instagram' => $page->instagram,
          'twitter' => $page->x, 'brand-tiktok' => $page->tiktok, 'youtube' => $page->youtube,
        ];
      @endphp
      @if(collect($socialLinks)->filter()->isNotEmpty())
        <div class="d-flex gap-2">
          @foreach($socialLinks as $icon => $link)
            @if($link)
              <a href="{{ $link }}" target="_blank" rel="noopener" class="btn btn-icon btn-text-secondary rounded-circle">
                <i class="ti ti-brand-{{ $icon }}"></i>
              </a>
            @endif
          @endforeach
        </div>
      @endif
    </div>
  </nav>
  <!--/ Navbar -->

  <!-- Hero -->
  <section
    class="section-py position-relative d-flex align-items-center"
    style="
      min-height: 420px;
      @if($page->hero_image_url)
        background-image: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)), url('{{ $page->hero_image_url }}');
        background-size: cover;
        background-position: center;
      @endif
    "
  >
    <div class="container text-center py-8" style="{{ $page->hero_image_url ? 'color:#fff;' : '' }}">
      @php $heroTitleTag = $isAr ? $page->hero_title_tag_ar : $page->hero_title_tag; @endphp
      @if($heroTitleTag)
        <span class="badge bg-label-primary mb-4">{{ $heroTitleTag }}</span>
      @endif

      @php $heroTitle = $isAr ? $page->hero_title_ar : $page->hero_title; @endphp
      @if($heroTitle)
        <h1 class="fw-bold mb-3">{{ $heroTitle }}</h1>
      @endif

      @php $heroDescription = $isAr ? $page->hero_description_ar : $page->hero_description; @endphp
      @if($heroDescription)
        <p class="fs-5 mb-0 {{ $page->hero_image_url ? '' : 'text-secondary' }}">{{ $heroDescription }}</p>
      @endif

      @if(!$heroTitle && !$heroDescription)
        <h1 class="fw-bold mb-3">{{ $page->title }}</h1>
        <p class="fs-5 mb-0 {{ $page->hero_image_url ? '' : 'text-secondary' }}">
          {{ ($isAr ? $page->description_ar : $page->description) ?: ($isAr ? 'لم تتم إضافة وصف.' : 'No description added.') }}
        </p>
      @endif
    </div>
  </section>
  <!--/ Hero -->

  <!-- About Us -->
  @if($page->isAboutRenderable($isAr))
    @php
      $aboutTitle = $isAr ? $page->about_title_ar : $page->about_title;
      $aboutTitleTag = $isAr ? $page->about_title_tag_ar : $page->about_title_tag;
      $aboutDescription = $isAr ? $page->about_description_ar : $page->about_description;
    @endphp
    <section id="about" class="section-py bg-body">
      <div class="container">
        @if($aboutTitleTag)
          <div class="text-center mb-4"><span class="badge bg-label-primary">{{ $aboutTitleTag }}</span></div>
        @endif
        @if($aboutTitle)
          <h4 class="text-center mb-1 fw-extrabold">{{ $aboutTitle }}</h4>
        @endif
        @if($aboutDescription)
          <p class="text-center mb-12">{{ $aboutDescription }}</p>
        @endif

        @if(!empty($page->about_cards))
          <div class="row g-6 justify-content-center">
            @foreach($page->about_cards as $index => $card)
              @php
                $cardTitle = $isAr ? ($card['title_ar'] ?? '') : ($card['title'] ?? '');
                $cardDescription = $isAr ? ($card['description_ar'] ?? '') : ($card['description'] ?? '');
              @endphp
              @if($cardTitle || $cardDescription)
                <div class="col-12 col-md-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <div class="mb-4">
                        <span class="badge bg-label-{{ $colors[$index % count($colors)] }} rounded p-2">
                          @if(!empty($card['icon']))
                            <i class="ti {{ $card['icon'] }} ti-26px"></i>
                          @endif
                        </span>
                      </div>
                      <h5 class="card-title">{{ $cardTitle }}</h5>
                      <p class="card-text">{{ $cardDescription }}</p>
                    </div>
                  </div>
                </div>
              @endif
            @endforeach
          </div>
        @endif
      </div>
    </section>
  @endif
  <!--/ About Us -->

  <!-- Programs -->
  @if($visiblePrograms->isNotEmpty())
    <section id="programs" class="section-py">
      <div class="container">
        <h4 class="text-center mb-12 fw-extrabold">{{ $isAr ? 'برامجنا' : 'Our Programs' }}</h4>
        <div class="row g-6">
          @foreach($visiblePrograms as $cycle)
            <div class="col-12 col-md-6 col-lg-4">
              <div class="card h-100 border shadow-none">
                <div class="card-body d-flex flex-column">
                  @if($cycle->type)
                    <span class="badge bg-label-secondary rounded-pill align-self-start mb-2">
                      {{ __('cycles.' . $cycle->type) }}
                    </span>
                  @endif
                  <h5 class="fw-semibold mb-2">{{ $cycle->title }}</h5>
                  @if($cycle->short_description)
                    <p class="text-secondary">{{ $cycle->short_description }}</p>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif
  <!--/ Programs -->

  <!-- How It Works -->
  @if($page->how_is_enabled && !empty($page->how_steps))
    @php
      $howTitle = $isAr ? $page->how_title_ar : $page->how_title;
      $howTitleTag = $isAr ? $page->how_title_tag_ar : $page->how_title_tag;
      $howDescription = $isAr ? $page->how_description_ar : $page->how_description;
    @endphp
    <section id="how-it-works" class="section-py bg-body">
      <div class="container">
        @if($howTitleTag)
          <div class="text-center mb-4"><span class="badge bg-label-primary">{{ $howTitleTag }}</span></div>
        @endif
        @if($howTitle)
          <h4 class="text-center mb-1 fw-extrabold">{{ $howTitle }}</h4>
        @endif
        @if($howDescription)
          <p class="text-center mb-12">{{ $howDescription }}</p>
        @endif

        <div class="row g-6 justify-content-center">
          @foreach($page->how_steps as $index => $step)
            @php
              $stepTitle = $isAr ? ($step['title_ar'] ?? '') : ($step['title'] ?? '');
              $stepDescription = $isAr ? ($step['description_ar'] ?? '') : ($step['description'] ?? '');
            @endphp
            <div class="col-12 col-md-4">
              <div class="card shadow-none border h-100">
                <div class="card-body text-center">
                  <div class="badge rounded p-4 bg-label-{{ $colors[$index % count($colors)] }} mb-4">
                    @if(!empty($step['icon']))
                      <i class="ti {{ $step['icon'] }} ti-lg"></i>
                    @else
                      <span>{{ $index + 1 }}</span>
                    @endif
                  </div>
                  <h5 class="card-title mb-1">{{ $stepTitle }}</h5>
                  <p class="mb-0">{{ $stepDescription }}</p>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif
  <!--/ How It Works -->

  <!-- Custom Sections -->
  @if($page->customSections->isNotEmpty())
    @foreach($page->customSections->where('visibility', true) as $index => $section)
      @php
        $sectionTitle = $isAr ? $section->title_ar : $section->title;
        $sectionTitleTag = $isAr ? $section->title_tag_ar : $section->title_tag;
        $sectionContent = $isAr ? $section->content_ar : $section->content;
      @endphp
      @if($sectionTitle || $sectionContent)
        <section id="custom-{{ $section->id }}" class="section-py {{ $index % 2 == 0 ? 'bg-body' : '' }}">
          <div class="container d-flex flex-column align-items-center text-center">
            @if($sectionTitleTag)
              <span class="badge bg-label-primary mb-4">{{ $sectionTitleTag }}</span>
            @endif
            @if($sectionTitle)
              <h4 class="fw-extrabold mb-4">{{ $sectionTitle }}</h4>
            @endif
            @if($sectionContent)
              <div class="w-100" style="max-width:720px;">{!! $sectionContent !!}</div>
            @endif
            @if($section->button_text && $section->button_link)
              <a href="{{ $section->button_link }}" target="_blank" rel="noopener" class="btn btn-primary mt-6">
                {{ $section->button_text }}
              </a>
            @endif
          </div>
        </section>
      @endif
    @endforeach
  @endif
  <!--/ Custom Sections -->

  <!-- Footer -->
  <footer class="footer bg-body border-top py-4">
    <div class="container text-center text-secondary small">
      &copy; {{ date('Y') }} {{ $page->title }}. {{ $isAr ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
    </div>
  </footer>
  <!--/ Footer -->

</body>
</html>
