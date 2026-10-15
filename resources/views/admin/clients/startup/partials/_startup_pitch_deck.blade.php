<div class="row">
  @if ($company->pitch_deck_summary)
    <div class="col-12 mb-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">{{ __('admin.Pitch_Deck_Information') }}
          </h5>
          <div class="d-flex flex-column gap-2">
            <div class="card" style="height: 132px;  overflow-y: auto;">
              <div class="card-body">
                <h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                  {{ $company->pitch_deck_summary }}
                </h6>
                <p class="text-muted line-clamp-2 fs-6">
                  {{ $company->pitch_deck_description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
  <div class="col-12 col-md-6 mb-6">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title">{{ __('admin.Pitch_Deck') }}:</h5>
        @if ($company->viewerFileUrl('pitch_deck'))
        <div class="card-body text-center h-100">
          <div class="d-flex flex-column justify-content-between align-items-center h-100">
            <iframe src="{{ $company->viewerFileUrl('pitch_deck') }}" target="_blank"
              style="width: 100%; height: 350px; object-fit:" class="img-fluid">
              <p>
                {{ $l == 'en' ? 'Your browser does not support iframes.' : 'متصفحك لا يدعم إطارات iframe.' }}
              </p>
            </iframe>

            @if($_user->hasAnyRole('Registered'))
              <div class="mt-auto d-flex flex-column mb-6">
                <span class="text-muted">
                  {{ $l == 'en' ? 'To get analysis for your pitch deck please' : 'للحصول على تحليل لعرضك التقديمي، يرجى' }}
                </span>
                <a role="button"
                  onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 0)"
                  class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                  {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
                </a>
              </div>
            @endif
          </div>
        </div>
        @else
        <div class="card-body text-center h-100">
          <div class="d-flex flex-column justify-content-between align-items-center h-100">
            <img src="/images/deck.svg" style="width: 350px; height: 350px;" />

            @if($_user->hasAnyRole('Registered'))
              <div class="mt-auto d-flex flex-column mb-6">
                <span class="text-muted">
                  {{ $l == 'en' ? 'To get analysis for your pitch deck please upload your pitch deck' : 'للحصول على تحليل لعرضك التقديمي، يرجى تحميل عرضك التقديمي' }}
                </span>
                <a role="button"
                  onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 0)"
                  class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                  {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
                </a>
              </div>
            @endif
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 mb-6">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title">{{ __('admin.Pitch_Deck_Video') }}:</h5>
        <div class="d-flex flex-column justify-content-between align-items-center" style="height: 90%">
          @if ($company && $company->pitch_deck_video)
          @php
          $videoUrl = $company->pitch_deck_video;
          $youtubePattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/]+\/[^\/]+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
          $isYouTube = preg_match($youtubePattern, $videoUrl, $matches);
          $videoId = $isYouTube ? $matches[1] : null;
          @endphp

          @if ($videoId)
          {{-- Embed YouTube Video --}}
          <iframe class="h-100 w-100" src="https://www.youtube.com/embed/{{ $videoId }}"
            title="YouTube video player" frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen>
          </iframe>
          @elseif (Str::contains($videoUrl, 'vimeo.com'))
          {{-- Embed Vimeo Video --}}
          @php
          $vimeoId = preg_replace('/[^0-9]/', '', parse_url($videoUrl, PHP_URL_PATH));
          @endphp
          <iframe class="h-100 w-100" src="https://player.vimeo.com/video/{{ $vimeoId }}" frameborder="0"
            allow="autoplay; fullscreen; picture-in-picture" allowfullscreen>
          </iframe>
          @elseif (Str::endsWith($videoUrl, ['.mp4', '.webm', '.ogg']))
          {{-- Direct Video File --}}
          <video class="h-100 w-100" controls>
            <source src="{{ $videoUrl }}" type="video/mp4">
              {{ $l == 'en' ? 'Your browser does not support the video tag.' : 'متصفحك لا يدعم علامة الفيديو.' }}
          </video>
          @else
          {{-- Unknown format --}}
          <a href="{{ $videoUrl }}" target="_blank" class="btn btn-primary">
            {{ $l == 'en' ? 'View Video' : 'عرض الفيديو' }}
          </a>
          @endif

          @else
          <img id="deck-video-image" src="/images/deck-video.svg" style="width: 350px; height: height: 350px;" />
          @endif

          @if($_user->hasAnyRole('Registered'))
            <div class="mt-auto d-flex flex-column pt-4 text-center">
              <span class="text-muted">
                {{ __('admin.To get analysis for your pitch deck please upload your pitch deck video') }}
              </span>
              <a role="button"
                onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 1)"
                class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>