@extends('layouts.hr-admin')

@section('content')
<!-- Main Content -->
<div class="container py-5">
  {{-- Check if the team object exists --}}
  @if($team)
    <div class="text-center mb-5">
      {{-- Access team properties directly --}}
      <h1 class="display-4 fw-bold">{{ $team->name }}</h1>
      <p class="text-secondary fs-5 mb-2">{{ $l == 'ar' ? 'فكرة الفريق' : 'Team Idea' }}</p>
      <p class="text-muted fs-5 mx-auto" style="max-width: 64rem;">
        {{ $team->desc }}
      </p>
    </div>

    <!-- Team Members Grid -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4 justify-content-center">
      {{-- Loop through the members array within the team object --}}
      @foreach($team->members as $member)
      <div class="col">
        <div class="card card-body text-center h-100">
          <div class="mx-auto position-relative mb-3 w-px-150 h-px-150 rounded-circle shadow-sm border">
            {{-- member's image url, provide a default if it's null --}}
            <img 
              src="{{ '/191014/'. $member->img_url ?? '/vuexy/assets/img/avatars/3.png' }}"
              onerror="this.onerror=null;this.src='/vuexy/assets/img/avatars/3.png';"
              alt="{{ $member->name }}" 
              class="h-100 w-100 rounded-circle img-fluid"
              style="width:96px;height:96px;object-fit:cover;"
            >
          </div>
          <div>
            <h5 class="fw-semibold mb-1">{{ $member->name }}</h5>
            <div class="text-secondary small mb-4">{{ $member->title }}</div>
            <div class="d-flex justify-content-center gap-2">
              @if($member->email)
              <button 
                class="btn btn-light btn-sm rounded-circle p-2"
                onclick="window.location.href='mailto:{{ $member->email }}'">
                <!-- Gmail -->
                <i class="ti ti-mail-filled"></i>
              </button>
              @endif
              {{-- member's LinkedIn profile link --}}
              @if($member->linkedin)
              <a class="btn btn-light btn-sm rounded-circle p-2" href="{{ $member->linkedin }}" target="_blank">
                <!-- LinkedIn -->
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                </svg>
              </a>
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
   @else
    @include('includes.no-result', ['message' => $l == 'ar' ? 'لا توجد معلومات متاحة عن الفريق.' : 'No team information available.'])
  @endif
</div>
@stop