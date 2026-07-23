<!DOCTYPE html>
<html
  lang="{{ $l }}"
  dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-compact"
  data-theme="theme-default"
  data-assets-path="/vuexy/assets/"
  data-style="light"
>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ $winner->projectTitle }} - {{ $winner->teamName }}</title>

  <meta name="description" content="{{ Str::limit(strip_tags($winner->description), 160) }}">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="article">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:title" content="{{ $winner->projectTitle }} - {{ $winner->teamName }}">
  <meta property="og:description" content="{{ Str::limit(strip_tags($winner->description), 200) }}">
  <meta property="og:image" content="{{ $winner->logo }}">
  <meta property="og:site_name" content="{{ $hackathon->organization->name }}">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:url" content="{{ url()->current() }}">
  <meta name="twitter:title" content="{{ $winner->projectTitle }} - {{ $winner->teamName }}">
  <meta name="twitter:description" content="{{ Str::limit(strip_tags($winner->description), 200) }}">
  <meta name="twitter:image" content="{{ $winner->logo }}">

  <!-- Canonical URL -->
  <link rel="canonical" href="{{ url()->current() }}">

  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" />
  <link rel="stylesheet" href="/vuexy/assets/css/demo.css" />

  <style>
    html {
      direction: {{ $l == 'ar' ? 'rtl' : 'ltr' }};
    }
    .btn-linkedin { background-color: #0A66C2; border-color: #0A66C2; color: #fff; }
    .btn-linkedin:hover { background-color: #004182; border-color: #004182; color: #fff; }
    .btn-twitter { background-color: #000000; border-color: #000000; color: #fff; }
    .btn-twitter:hover { background-color: #333333; border-color: #333333; color: #fff; }
    .btn-facebook { background-color: #1877F2; border-color: #1877F2; color: #fff; }
    .btn-facebook:hover { background-color: #0d65d9; border-color: #0d65d9; color: #fff; }
    .share-buttons .btn { min-width: 44px; }
    @media (max-width: 576px) {
      .share-buttons .btn span { display: none !important; }
      .share-buttons .btn i { margin: 0 !important; }
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <!-- Back Link -->
        <div class="mb-4">
          <a href="{{ url('hackathon/details/' . $hackathon->id) }}" class="text-muted text-decoration-none">
            <i class="ti ti-arrow-{{ $l == 'ar' ? 'right' : 'left' }} me-1"></i>
            {{ $l == 'en' ? 'Back to Hackathon' : 'العودة إلى الهاكاثون' }}
          </a>
        </div>

        <!-- Solution Card -->
        <div class="card shadow-lg border-0">
          <!-- Header -->
          <div class="card-header bg-light border-0 py-4">
            <div class="d-flex align-items-center">
              <img src="{{ $winner->logo }}" class="rounded-circle me-3 border shadow-sm" alt="{{ $winner->teamName }}" width="64" height="64" style="object-fit: cover;">
              <div>
                <h4 class="mb-1 fw-bold text-dark">{{ $winner->name }}</h4>
                <p class="text-muted mb-0">{{ $winner->teamName }}</p>
              </div>
            </div>
          </div>

          <div class="card-body p-4">
            <!-- Project Title -->
            <h2 class="card-title text-dark mb-3 fw-bold">{{ $winner->projectTitle }}</h2>

            <!-- Project Description -->
            <div class="mb-4">
              <h6 class="text-muted fw-semibold mb-2">{{ $l == 'en' ? 'Description' : 'الوصف' }}</h6>
              <p class="text-dark lh-lg">{{ $winner->description }}</p>
            </div>

            <!-- Team Members -->
            <div class="mb-4">
              <h6 class="text-muted fw-semibold mb-3 d-flex align-items-center">
                <span class="me-2">{{ $l == 'en' ? 'Team Members' : 'أعضاء الفريق' }}</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 small">{{ count($winner->teamMembers) }}</span>
              </h6>
              <div class="row g-2">
                @foreach ($winner->teamMembers as $member)
                <div class="col-md-6">
                  <div class="bg-light rounded p-3 h-100 {{ $member->isLeader ? 'border border-primary' : '' }}">
                    <div class="d-flex align-items-center">
                      <img src="{{ $member->image ?? '/icons/account.svg' }}" class="rounded-circle me-3 border" alt="{{ $member->name }}" width="40" height="40">
                      <div class="flex-grow-1">
                        <div class="fw-medium text-dark small">{{ $member->name }}</div>
                        <small class="text-muted">{{ $member->role }}</small>
                      </div>
                      @if($member->isLeader)
                        <span class="badge bg-primary text-white fw-semibold px-2 py-1 small">
                          <i class="ti ti-crown me-1"></i>{{ $l == 'ar' ? 'قائد' : 'Leader' }}
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            </div>

            <!-- Share Section -->
            <div class="border-top pt-4">
              <h6 class="text-muted fw-semibold mb-3">
                <i class="ti ti-share me-1"></i>
                {{ $l == 'en' ? 'Share this Solution' : 'شارك هذا الحل' }}
              </h6>
              @php
                $shareUrl = url()->current();
                $shareTitle = $winner->projectTitle . ' - ' . $winner->teamName;
                $shareDescription = Str::limit(strip_tags($winner->description), 200);
                $encodedUrl = urlencode($shareUrl);
                $encodedTitle = urlencode($shareTitle);
                $linkedinUrl = "https://www.linkedin.com/sharing/share-offsite/?url={$encodedUrl}";
                $twitterUrl = "https://twitter.com/intent/tweet?url={$encodedUrl}&text={$encodedTitle}&hashtags=hackathon,innovation";
                $facebookUrl = "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}";
              @endphp
              <div class="share-buttons d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-linkedin share-btn" data-share-url="{{ $linkedinUrl }}" data-platform="linkedin" title="{{ $l == 'en' ? 'Share on LinkedIn' : 'شارك على لينكدان' }}">
                  <i class="ti ti-brand-linkedin ti-md"></i>
                  <span class="ms-1">LinkedIn</span>
                </button>
                <button type="button" class="btn btn-twitter share-btn" data-share-url="{{ $twitterUrl }}" data-platform="twitter" title="{{ $l == 'en' ? 'Share on X' : 'شارك على X' }}">
                  <i class="ti ti-brand-twitter ti-md"></i>
                  <span class="ms-1">X</span>
                </button>
                <button type="button" class="btn btn-facebook share-btn" data-share-url="{{ $facebookUrl }}" data-platform="facebook" title="{{ $l == 'en' ? 'Share on Facebook' : 'شارك على فيسبوك' }}">
                  <i class="ti ti-brand-facebook ti-md"></i>
                  <span class="ms-1">Facebook</span>
                </button>
                <button type="button" class="btn btn-secondary copy-link-btn" data-copy-url="{{ $shareUrl }}" title="{{ $l == 'en' ? 'Copy Link' : 'نسخ الرابط' }}">
                  <i class="ti ti-link ti-md"></i>
                  <span class="ms-1">{{ $l == 'en' ? 'Copy Link' : 'نسخ الرابط' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Hackathon Info -->
        <div class="text-center mt-4">
          <p class="text-muted small mb-0">
            {{ $l == 'en' ? 'Part of' : 'جزء من' }}
            <a href="{{ url('hackathon/details/' . $hackathon->id) }}" class="fw-semibold">{{ $hackathon->title }}</a>
            {{ $l == 'en' ? 'by' : 'من' }} {{ $hackathon->organization->name }}
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Core JS -->
  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>

  <!-- Toastr -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/toastr/toastr.css" />
  <script src="/vuexy/assets/vendor/libs/toastr/toastr.js"></script>

  <script>
    (function() {
      'use strict';

      const solutionId = {{ $winner->id }};
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const translations = {
        link_copied: '{{ $l == "en" ? "Link copied to clipboard!" : "تم نسخ الرابط!" }}',
        copy_failed: '{{ $l == "en" ? "Failed to copy link" : "فشل نسخ الرابط" }}'
      };

      // Social share buttons
      document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const shareUrl = this.dataset.shareUrl;
          const platform = this.dataset.platform;

          // Track share event
          trackShare(platform);

          // Open share popup
          const width = 600, height = 400;
          const left = (screen.width - width) / 2;
          const top = (screen.height - height) / 2;
          window.open(shareUrl, 'share_popup', `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no,scrollbars=yes`);
        });
      });

      // Copy link button
      document.querySelectorAll('.copy-link-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const url = this.dataset.copyUrl;
          const button = this;

          // Fallback copy function
          function fallbackCopy() {
            const textArea = document.createElement('textarea');
            textArea.value = url;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.select();
            try {
              document.execCommand('copy');
              trackShare('copy_link');
              toastr.success(translations.link_copied);
              showCopyFeedback(button);
            } catch (err) {
              toastr.error(translations.copy_failed);
            }
            document.body.removeChild(textArea);
          }

          // Show visual feedback on button
          function showCopyFeedback(btn) {
            const icon = btn.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'ti ti-check ti-md';
            setTimeout(() => { icon.className = originalClass; }, 2000);
          }

          // Try clipboard API first, fallback if not available
          if (typeof navigator !== 'undefined' && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            try {
              navigator.clipboard.writeText(url).then(function() {
                trackShare('copy_link');
                toastr.success(translations.link_copied);
                showCopyFeedback(button);
              }).catch(function() {
                fallbackCopy();
              });
            } catch (err) {
              fallbackCopy();
            }
          } else {
            fallbackCopy();
          }
        });
      });

      // Track share event
      function trackShare(platform) {
        fetch('{{ route("hackathon.trackShare") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            solution_id: solutionId,
            platform: platform
          })
        }).catch(err => console.error('Failed to track share:', err));
      }
    })();
  </script>
</body>
</html>
