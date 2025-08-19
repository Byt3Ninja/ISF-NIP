@php
  $h = $newhackathon ?? $hackathon ?? null;
  $title = $h->title ?? ($pageTitle ?? __('New'));
  $isAr = isset($l) && $l === 'ar';
@endphp

<div class="card px-3 position-relative overflow-hidden bg-primary bg-gradient text-white">
  <div class="row position-relative z-1">
    <div class="col-md-8 p-4 w-100">
      <div class="d-flex flex-column justify-content-center h-100 w-100">
        <div class="d-flex justify-content-between align-items-start w-100">
          <div class="d-flex align-items-center gap-2 mb-1">
            <h5 class="m-0 fw-bold text-white">{{ $title }}</h5>
            <div class="px-2 py-1 rounded-pill small text-black bg-light bg-opacity-25">
              {{ $isAr ? 'جديد' : 'New' }}
            </div>
          </div>
        </div>

        <p class="text-white-90 mb-3">
          @if($isAr)
            {{ $h ? 'انضم إلى هاكاثون '.$title.' ونافس المبتكرين من جميع أنحاء العالم لبناء المستقبل باستخدام '.$title.'.'
                 : 'انضم إلى الهاكاثون ونافس المبتكرين من جميع أنحاء العالم.' }}
          @else
            {{ $h ? 'Join our '.$title.' hackathon and compete with innovators from around the world to build the future with '.$title.'.'
                 : 'Join our hackathon and compete with innovators from around the world.' }}
          @endif
        </p>

        <div class="d-flex gap-2">
          @if($h?->id)
            <a href="{{ url('/dashboard/hackathon/details/'.$h->id) }}" class="btn btn-sm btn-light text-primary shadow-sm">
              {{ $isAr ? 'التفاصيل' : 'Details' }}
            </a>
          @endif
          <a href="{{ url('/dashboard/hackathon') }}" class="btn btn-sm btn-outline-light">
            {{ $isAr ? 'تعلم المزيد' : 'Learn More' }}
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
