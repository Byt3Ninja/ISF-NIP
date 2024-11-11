@extends('layouts.admin')

@section('style')
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
['the judges', 'gavel', '5', 'success'],
['startups', 'rocket', '5', 'danger'],
['filtration', 'filter', '5', 'warning'],
['filtration pending', 'time', '5', 'info'],
['filtration done', 'check', '5', 'light'],
['steps', 'steps', '5', 'dark'],
['mentorship requests', 'message', '5', 'primary'],
]);

$mentorshipCollection = collect([
['mentors', 'user', '5', 'secondary'],
['mentors in program', 'users', '5', 'success'],
['mentorship sessions duration (hours)', 'clock', '5', 'danger'],
['mentorship done requests count', 'check', '5', 'warning'],
['mentorship requests', 'message', '5', 'info'],
['new requests only', 'plus', '5', 'light'],
['approved requests', 'check', '5', 'dark'],
['disapproved requests', 'close', '5', 'primary'],
['mentorship pending requests count', 'time', '5', 'secondary'],
]);

$trainingCollection = collect([
['training sessions', 'book', '5', 'success'],
['training sessions duration (hours)', 'clock', '5', 'danger'],
['educational content', 'book-open', '5', 'warning'],
['online training', 'desktop', '5', 'info'],
['traditional training', 'blackboard', '5', 'light'],
]);

$finalScreenCollection = collect([
['the judges', 'gavel', '5', 'dark'],
['startups', 'rocket', '5', 'primary'],
['evaluation', 'check', '5', 'secondary'],
['traditional pending', 'time', '5', 'success'],
['evaluation done', 'check', '5', 'danger'],
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
(object) ['id' => 6, 'name' => 'Mentor 6', 'image_url' => '/images/background/mentor6.jpg'],
(object) ['id' => 7, 'name' => 'Mentor 7', 'image_url' => '/images/background/mentor7.jpg'],
(object) ['id' => 8, 'name' => 'Mentor 8', 'image_url' => '/images/background/mentor8.jpg'],
(object) ['id' => 9, 'name' => 'Mentor 9', 'image_url' => '/images/background/mentor9.jpg'],
(object) ['id' => 10, 'name' => 'Mentor 10', 'image_url' => '/images/background/mentor10.jpg'],
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
                    <i class="ti ti-chart-bar me-2"></i> Results
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
                  <a href="#" class="d-flex align-items-center text-muted">
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
                  <div class="col-md-3 text-md-center text-nowrap mt-md-8">
                    <h6 class="mb-2">{{__('admin.Duration')}}</h6>
                    <div class="d-flex flex-md-column align-items-center gap-2">
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
                <div class="row mt-4 mt-md-0">
                  <div class="col-12">
                    <div class="d-flex align-items-center">
                      <span class="fw-bold mx-1">80%</span>
                      <div style="width: 100%; height: 7px;" class="progress rounded-pill">
                        <div 
                          role="progressbar"
                          class="progress-bar" 
                          style="background: linear-gradient(to {{$l == 'en' ? 'right' : 'left'}},
                          rgb(164, 157, 233), #857ce6, #7367f0); width: 80%;" 
                          aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
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
                            <button class="dropdown-item ccycle">
                              <i class="ti ti-pencil me-2"></i>
                              {{ __('cycles.Edit') }}
                            </button>
                          </li>
                          <li>
                            @can('delete_user')
                            <button class="dropdown-item">
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
    </div>
  </div>
</div>
@stop