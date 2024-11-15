@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
<style>
  .radius-15 {
    border-radius: 15px;
  }

  .light-shadow {
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .btn {
    border-radius: 15px !important;
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .badge {
    border-radius: 10px !important;
  }
  .text-xs {
    font-size: 12px;
  }
</style>
@stop

@php
$orgCollection = collect([
['startups', 'building-skyscraper', '5', 'primary'],
['steps', 'stack-2', '5', 'info'],
['mentors', 'user', '5', 'light'],
['mentorship requests', 'report', '5', 'warning'],
['mentorship sessions duration (hours)', 'clock-hour-5', '5', 'success'],
['company attendance rate', 'heart-rate-monitor', '5', 'danger'],
['training sessions', 'layers-subtract', '5', 'light'],
['training sessions duration (hours)', 'clock-hour-5', '5', 'success'],
['filtration', 'transform', '5', 'warning'],
['evaluation', 'presentation', '5', 'danger']
]);

$screenCollection = collect([
['the judges', 'gavel', '5', 'warning'],
['startups', 'building-skyscraper', '5', 'primary'],
  ['filtration', 'transform', '5', 'warning'],
['filtration pending', 'rotate-clockwise-2', '5', 'info'],
['filtration done', 'checkbox', '5', 'success'],
]);

$mentorshipCollection = collect([
['mentors', 'user', '5', 'secondary'],
['mentors in program', 'users', '5', 'success'],
['mentorship sessions duration (hours)', 'clock', '5', 'danger'],
['mentorship done requests count', 'check', '5', 'warning'],
['mentorship requests', 'message', '5', 'info'],
['new requests only', 'plus', '5', 'light'],
['approved requests', 'checkbox', '5', 'dark'],
['disapproved requests', 'trash-x', '5', 'primary'],
['mentorship pending requests count', 'rotate-clockwise-2', '5', 'secondary'],
]);

$trainingCollection = collect([
['training sessions', 'user-screen', '5', 'light'],
['training sessions duration (hours)', 'clock', '5', 'success'],
['educational content', 'device-desktop', '5', 'primary'],
['online training', 'world', '5', 'warning'],
['traditional training', 'home-2', '5', 'info'],
]);

$finalScreenCollection = collect([
['the judges', 'gavel', '5', 'warning'],
['startups', 'building-skyscraper', '5', 'primary'],
['evaluation', 'presentation', '5', 'danger'],
['traditional pending', 'rotate-clockwise-2', '5', 'info'],
['evaluation done', 'checkbox', '5', 'success'],
]);

$convertToArray = function ($collection) {
return $collection->map(function ($item) {
return [
'label' => $item[0],
'icon' => $item[1],
'count' => $item[2],
'color' => $item[3],
];
});
};

$orgCountList = $convertToArray($orgCollection);
$screenCountList = $convertToArray($screenCollection);
$mentorshipCountList = $convertToArray($mentorshipCollection);
$trainingCountList = $convertToArray($trainingCollection);
$finalScreenCountList = $convertToArray($finalScreenCollection);

$mentortotal = [
(object) ['id' => 1, 'name' => 'Mentor 1', 'image_url' => '/images/background/mentor1.jpg'],
(object) ['id' => 2, 'name' => 'Mentor 2', 'image_url' => '/images/background/mentor2.jpg'],
(object) ['id' => 3, 'name' => 'Mentor 3', 'image_url' => '/images/background/mentor3.jpg'],
(object) ['id' => 4, 'name' => 'Mentor 4', 'image_url' => '/images/background/mentor4.jpg'],
(object) ['id' => 5, 'name' => 'Mentor 5', 'image_url' => '/images/background/mentor5.jpg'],
]
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="d-flex gap-6">
              <div class="d-none d-md-flex flex-shrink-0 w-px-100">
                <div class="w-px-70 h-px-100">
                  <img src="/images/background/logo.png" class="w-100 h-100 border radius-15"
                    style="object-fit: contain;" alt="Program Image" />
                </div>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between mb-2">
                  <h4 class="text-primary mb-0">Program Name</h4>
                  <a href="#" class="btn btn-primary">
                    <i class="ti ti-file-analytics me-2"></i> Results
                  </a>
                </div>
                <div class="d-flex justify-content-end mb-2">
                  <div class="btn btn-label-light rounded-pill px-2 h-px-30">
                    <img src="/icons/star.svg" width="17" height="17" class="me-1" />
                    <span class="text-warning fw-bold">4.5</span>
                  </div>
                </div>
                <div class="d-flex justify-content-between mb-4">
                  <h5 class="mb-0">Organizations</h5>
                  <a id="private-link" href="#" class="d-flex align-items-center text-muted">
                    Private Link <i class="ti ti-circle-check ms-2 text-success ti-24px"></i>
                  </a>
                </div>
                <div class="row border radius-15 py-3 mb-3">
                  <div class="col-md-3">
                    @foreach ($orgCountList->slice(0, 2) as $item)
                    <div class="col-12 mb-2">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                          <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <span class="me-2 fw-semibold text-capitalize">{{ $item['label'] }}</span>
                          <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                            {{ $item['count'] }}
                          </small>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                  <div class="col-md-4">
                    @foreach ($orgCountList->slice(2, 4) as $item)
                    <div class="col-12 mb-2">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                          <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <span class="me-2 fw-semibold text-capitalize">{{ $item['label'] }}</span>
                          <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                            {{ $item['count'] }}
                          </small>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                  <div class="col-md-5">
                    @foreach ($orgCountList->slice(6, 10) as $item)
                    <div class="col-12 mb-2">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                          <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                        </div>
                        <div class="d-flex align-items-center">
                          <span class="me-2 fw-semibold text-capitalize">{{ $item['label'] }}</span>
                          <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                            {{ $item['count'] }}
                          </small>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-user-circle text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Responsible')}}</span>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($mentortotal as $mentor)
                        <a class="btn btn-sm btn-outline-primary rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('show_mentor_profile', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$mentor->name}}</span>
                        </a>
                        @endforeach
                        @foreach($mentortotal as $mentor)
                        <a class="btn btn-sm btn-outline-primary rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('show_mentor_profile', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$mentor->name}}</span>
                        </a>
                        @endforeach
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">
                          Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur, at a magni
                          natus vel doloremque, iusto deleniti maiores id veritatis. Consequuntur iure ab quos ratione,
                          quaerat illum nostrum natus?
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">20-10-2000</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">30-11-2024</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mt-4 mt-md-0">
                  <div class="col-12">
                    <div class="d-flex align-items-center">
                      <span class="fw-bold mx-1">80%</span>
                      <div style="width: 100%; height: 7px;" class="progress rounded-pill">
                        <div role="progressbar" class="progress-bar" style="background: linear-gradient(to {{$l == 'en' ? 'right' : 'left'}},
                          rgb(164, 157, 233), #857ce6, #7367f0); width: 80%;" aria-valuenow="80" aria-valuemin="0"
                          aria-valuemax="100">
                        </div>
                      </div>
                      <span class="fw-bold mx-1">8/10</span>
                      <div class="mx-2 position-relative">
                        <div class="dropdown"></div>
                        <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                          data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="ti ti-dots-vertical ti-24px"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                          <li>
                            <button class="dropdown-item ccycle text-primary">
                              <i class="ti ti-edit me-2"></i>
                              {{ __('cycles.Edit') }}
                            </button>
                          </li>
                          <li>
                            @can('delete_user')
                            <button class="dropdown-item text-danger" id="deleteButton">
                              <i class="ti ti-trash me-2"></i>
                              {{ __('cycles.Delete') }}
                            </button>
                            @endcan
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Custom Step -->
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-4 mt-md-0">
              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <div>
                    <a href="#" class="btn btn-outline-primary rounded-pill">
                      Custom Step
                    </a>
                  </div>
                  <div class="d-flex align-items-center">
                    <h6 class="mb-0">Step 1</h6>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-2">
                  <h5 class="text-primary mb-0">Custom Step</h5>
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-primary me-2 p-2">
                      <i class="ti ti-building-skyscraper text-primary ti-24px"></i>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">Startups</span>
                      <small>5</small>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex align-items-center">
                      <i class="ti ti-notes text-primary ti-20px me-2"></i>
                      <div class="px-4 py-1 bg-light text-black rounded-pill" style="max-width: 85%;">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur.
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">
                          Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur, at a magni
                          natus vel doloremque, iusto deleniti maiores id veritatis. Consequuntur iure ab quos ratione,
                          quaerat illum nostrum natus?
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">20-10-2000</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">30-11-2024</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Custom step -->
      <!-- Screening Step -->
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-4 mt-md-0">
              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <a href="#" class="btn btn-outline-primary rounded-pill">
                    Screening Step
                  </a>
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0">Step 2</h6>
                    <a href="#" class="btn btn-primary">
                      <i class="ti ti-file-analytics me-2"></i> Results
                    </a>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-5">
                  <h5 class="text-primary mb-0">Screening Name</h5>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center border radius-15 p-3 mb-6">
                  @foreach ($screenCountList as $item)
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                      <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ $item['label'] }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $item['count'] }}
                      </small>
                    </div>
                  </div>
                  @endforeach
                </div>
                <div class="row">
                  <div class="col-8 col-md-9">
                    <div class="d-flex align-items-center gap-3 mb-3">
                      <div class="d-flex align-items-center">
                        <i class="ti ti-gavel text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">The judges</span>
                      </div>
                      <small class="badge bg-primary fw-normal">Assign one to all users</small>
                    </div>
                  </div>
                  <div class="col-4 col-md-3">
                    <a id="private-link" href="#" class="d-flex align-items-center text-black">
                      Notify them <i class="ti ti-circle-check ms-2 text-success ti-24px"></i>
                    </a>
                  </div>
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($mentortotal as $mentor)
                        <a class="btn btn-sm btn-outline-warning rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('show_mentor_profile', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$mentor->name}}</span>
                        </a>
                        @endforeach
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">
                          Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur, at a magni
                          natus vel doloremque, iusto deleniti maiores id veritatis.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">20-10-2000</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">30-11-2024</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Screening step -->
      <!-- Mentorship Step -->
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-md-0">
              <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
                  <a id="mentorship-step" href="#" class="btn btn-outline-primary rounded-pill mb-3 mb-md-0" style="width: fit-content;">
                    Mentorship Step
                  </a>
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0 text-nowrap">Step 3</h6>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <a id="mentors" href="#" class="btn btn-primary">
                        <i class="ti ti-user me-2"></i> Mentors
                      </a>
                      <a id="manage" href="#" class="btn btn-primary">
                        <i class="ti ti-settings me-2"></i> Manage
                      </a>
                      <a id="sessions" href="#" class="btn btn-primary">
                        <i class="ti ti-cast me-2"></i> Sessions
                      </a>
                      <a id="results" href="#" class="btn btn-primary">
                        <i class="ti ti-file-analytics me-2"></i> Results
                      </a>
                    </div>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-4">
                  <h5 class="text-primary mb-0">Mentorship Name</h5>
                </div>
                <div class="row border radius-15 p-3 mb-4">
                  @foreach ($mentorshipCountList as $item)
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                        <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ $item['label'] }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $item['count'] }}
                        </small>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-bulb text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">The mentors</span>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($mentortotal as $mentor)
                        <a class="btn btn-outline-info rounded-pill" style="height: 40px;" target="_blank"
                          href="{{route('show_mentor_profile', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="30" height="30" class="rounded-circle" />
                          @else
                          <i class="ti ti-user ti-28px"></i>
                          @endif
                          <div class="d-flex flex-column gap-0">
                            <span style="font-size: 12px;" class="mx-1 fw-semibold">{{$mentor->name}}</span>
                            <small style="font-size: 10px;">Computer</small>
                          </div>
                        </a>
                        @endforeach
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">
                          Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur, at a magni
                          natus vel doloremque, iusto deleniti maiores id veritatis.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">20-10-2000</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">30-11-2024</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Mentorship step -->
      <!-- Training Step -->
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-md-0">
              <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
                  <a id="training-step" href="#" class="btn btn-outline-primary rounded-pill mb-3 mb-md-0" style="width: fit-content;">
                    Training Step
                  </a>
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0 text-nowrap">Step 4</h6>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                      <a id="sessions" href="#" class="btn btn-primary">
                        <i class="ti ti-cast me-2"></i> Sessions
                      </a>
                      <a id="results" href="#" class="btn btn-primary">
                        <i class="ti ti-file-analytics me-2"></i> Results
                      </a>
                    </div>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-4">
                  <h5 class="text-primary mb-0">Training Name</h5>
                </div>
                <div class="row border radius-15 p-3 mb-4">
                  @foreach ($trainingCountList as $item)
                  <div class="col-md-4 mb-2">
                    <div class="d-flex align-items-center">
                      <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                        <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                      </div>
                      <div class="d-flex align-items-center">
                        <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ $item['label'] }}</span>
                        <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                          {{ $item['count'] }}
                        </small>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">
                          Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur, at a magni
                          natus vel doloremque, iusto deleniti maiores id veritatis.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">20-10-2000</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">30-11-2024</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Training step -->
      <!-- Final screening Step -->
      <div class="col-12 mb-4">
        <div class="card" style="border-radius: 1rem;">
          <div class="card-body">
            <div class="row mt-4 mt-md-0">
              <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <a href="#" class="btn btn-outline-primary rounded-pill">
                    Final Screening Step
                  </a>
                  <div class="d-flex align-items-center gap-4">
                    <h6 class="mb-0">Step 5</h6>
                    <a id="manage" href="#" class="btn btn-primary">
                      <i class="ti ti-settings me-2"></i> Manage
                    </a>
                    <div class="mx-2 position-relative">
                      <div class="dropdown"></div>
                      <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-24px"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <button class="dropdown-item ccycle text-primary">
                            <i class="ti ti-edit me-2"></i>
                            {{ __('cycles.Edit') }}
                          </button>
                        </li>
                        <li>
                          @can('delete_user')
                          <button class="dropdown-item text-danger" id="deleteButton">
                            <i class="ti ti-trash me-2"></i>
                            {{ __('cycles.Delete') }}
                          </button>
                          @endcan
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-grow-1 justify-content-between mb-5">
                  <h5 class="text-primary mb-0">Final Screening Step Name</h5>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center border radius-15 p-3 mb-6">
                  @foreach ($finalScreenCountList as $item)
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                      <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                    </div>
                    <div class="d-flex align-items-center">
                      <span class="me-2 fw-medium text-capitalize fs-6 text-black">{{ $item['label'] }}</span>
                      <small class="bg-light text-secondary py-1 px-2 rounded-pill">
                        {{ $item['count'] }}
                      </small>
                    </div>
                  </div>
                  @endforeach
                </div>
                <div class="row">
                  <div class="col-12 mb-3">
                    <div class="d-flex align-items-center">
                      <i class="ti ti-notes text-primary ti-20px me-2"></i>
                      <div class="px-4 py-1 bg-light text-black rounded-pill" style="max-width: 85%;">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur.
                      </div>
                    </div>
                  </div>
                  <div class="col-8 col-md-9">
                    <div class="d-flex align-items-center gap-3 mb-3">
                      <div class="d-flex align-items-center">
                        <i class="ti ti-gavel text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">The judges</span>
                      </div>
                    </div>
                  </div>
                  <div class="col-4 col-md-3">
                    <a id="private-link" href="#" class="d-flex align-items-center text-black">
                      Notify them <i class="ti ti-circle-check ms-2 text-success ti-24px"></i>
                    </a>
                  </div>
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($mentortotal as $mentor)
                        <a class="btn btn-sm btn-outline-warning rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('show_mentor_profile', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="this.src='/icons/account.svg'" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$mentor->name}}</span>
                        </a>
                        @endforeach
                      </div>
                    </div>
                    <div class="d-flex flex-column mt-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center px-4" style="width: 85%;">
                        <p class="text-muted">
                          Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestias consequatur, at a magni
                          natus vel doloremque, iusto deleniti maiores id veritatis.
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3 mt-6">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-2" style="width: fit-content;">
                        <h6 class="mb-1 text-center">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">20-10-2000</span>
                        </div>
                        <div class="badge bg-label-info">
                          <i class="ti ti-calendar-clock ti-20px" style="color: blue;"></i>
                          <span class="fw-bold text-black mx-2">30-11-2024</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Final screening step -->
    </div>
  </div>
  <div class="content-backdrop fade"></div>
  <!-- Overlay -->
</div>
<div class="layout-overlay layout-menu-toggle"></div>

<!-- Drag Target Area To SlideIn Menu On Small Screens -->
<div class="drag-target"></div>
@stop

@section('scripts')
<script>
  const deleteButton = document.querySelector('#deleteButton');
console.log('deleteButton', deleteButton)
// Suspend User javascript
if (deleteButton) {
  deleteButton.onclick = function () {
    console.log('clidked')
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert order!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Delete order!',
      customClass: {
        confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'Order has been removed.',
          customClass: {
            confirmButton: 'btn btn-success waves-effect waves-light'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled Delete :)',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success waves-effect waves-light'
          }
        });
      }
    });
  };
}
</script>
@stop

@section('pagescripts')
<script>
//sweet alert

</script>
@stop