<div class="card px-3 position-relative overflow-hidden bg-primary bg-gradient text-white">
  <!-- Background Pattern -->
  {{-- <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10"
    style="background-image: url('/images/illustrations/pattern-2.svg'); background-size: cover; background-position: center; z-index: 0;">
  </div> --}}
@php

    //  dd($newhackathon);
       
@endphp
  <div class="row position-relative z-1">
    <!-- Text Content -->
    <div class="col-md-8 p-4 w-100">
      <div class="d-flex flex-column justify-content-center h-100 w-100">
        <!-- Title & New Badge -->
        <div class="d-flex justify-content-between align-items-start w-100">
          <div class="d-flex align-items-center gap-2 mb-1">
            <h5 class="m-0 fw-bold text-white">
              {{ $newhackathon?->title }}
            </h5>
            <div class="px-2 py-1 rounded-pill small text-black bg-light bg-opacity-25 backdrop-blur-sm">
              {{ $l == 'ar' ? 'جديد' : 'New' }}
            </div>
          </div>
          
          <!-- Close Button -->
           {{-- <button type="button" class="btn btn-sm btn-transparent text-white p-0" onclick="this.closest('.card').remove()">
            <i class="ti ti-x"></i>
          </button> --}}
        </div>

        <!-- Description -->
        <p class="text-white-90 mb-3">
          {{ $l == 'ar' ? 'انضم إلى هاكاثون '.$newhackathon?->title.' ونافس المبتكرين من جميع أنحاء العالم لبناء المستقبل باستخدام '.$newhackathon?->title.' .' : 'Join our '.$newhackathon?->title.' hackathon and compete with innovators from around the world to build the future with '.$newhackathon?->title.'.' }}
        </p>

        <!-- Buttons -->
        <div class="d-flex gap-2">
          <a href="/dashboard/hackathon/details/{{ $newhackathon?->id }}" class="btn btn-sm btn-light text-primary shadow-sm">
            {{ $l == 'en' ? 'Details' : 'التفاصيل' }}
          </a>
          <a href="/dashboard/hackathon" class="btn btn-sm btn-outline-light">
            {{ $l == 'ar' ? 'تعلم المزيد' : 'Learn More' }}
          </a>
        </div>
      </div>
    </div>

    <!-- Optional Image Section -->
    <!--
    <div class="col-md-4 p-4 d-flex align-items-center justify-content-center">
      <img src="/images/illustrations/hackathon.png" alt="AI Hackathon" class="bg-success" 
           style="max-width: 100%; width: 160px; height: 144px; transition: transform 0.3s;" 
           onmouseover="this.style.transform='scale(1.05)'" 
           onmouseout="this.style.transform='scale(1)'">
    </div>
    -->
  </div>
</div>