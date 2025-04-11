@extends('layouts.admin')

@section('style')
  <style>
    .line-clamp-4 {
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 4;
      overflow: hidden;
    }
    .fs-sm {
      font-size: 0.8rem;
    }
    .card {
      position: relative;
      overflow: hidden;
    }
    .card-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: var(--bs-white);
      color: var(--bs-white);
      display: flex;
      justify-content: center;
      align-items: center;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }
    .card:hover .card-overlay {
      opacity: 1;
    }
  </style>
@stop

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      @forEach($screeningData as $screening)
        <div class="col-12 col-md-2 col-lg-3 col-xl-4 mb-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <img 
                  src="/images/background/logo.png" 
                  alt="startup-logo" 
                  class="rounded shadow-sm border p-2"
                  style="object-fit: contain; width: 100px; height: 65px;" 
                />
                <div class="d-flex flex-column align-items-center justify-content-center gap-1">
                  <img src="/icons/{{ $screening->status }}.svg" />
                  <span class="text-capitalize fs-sm fs-semibold">
                    {{ str_replace('-', ' ', $screening->status) }}
                  </span>
                </div>
              </div>
              <h5 class="card-title text-truncate mb-2 text-dark">{{ $screening->startupTitle }}</h5>
              <p class="card-text line-clamp-4 text-muted">
                Lorem ipsum dolor sit amet consectetur adipisicing elit.
                Dolorum, itaque nam? Sint dolore, reiciendis soluta eveniet impedit 
                consectetur rerum nam quidem delectus amet nostrum repudiandae, 
                quos aspernatur dolor reprehenderit nesciunt.
              </p>
            </div>
            <div class="card-overlay">
              <h5 class="text-center text-uppercase">{{ str_replace('-', ' ', $screening->status) }}</h5>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@stop

@section('scripts')
@stop