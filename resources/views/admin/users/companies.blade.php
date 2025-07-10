@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <style>
    .radius-15 {
      border-radius: 15px;
    }

    .text-sm {
      font-size: 0.875rem;
    }

    .text-xs {
      font-size: 0.75rem;
    }

    .text-xxs {
      font-size: 0.65rem;
    }

    .custom-shadow {
      box-shadow: 0px 2px 0px 0px #F2F1F3;
    }

    .company-image-container {
      height: 140px;
      border-radius: 15px;
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
    }

    #search-table {
      color: var(--bs-primary);
    }

    #search-table::placeholder {
      color: var(--bs-dark);
    }
  </style>
@stop

@section('content')
  <!-- BEGIN: Content-->
  @if (isset($errorMessage) && !empty($errorMessage))
    <div class="alert alert-danger">
      {{ $errorMessage }}
    </div>
  @endif
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <h4>{{ $l == 'en' ? 'Companies' : 'الشركات' }}</h4>
        <!-- Alert -->
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible mb-2" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible mb-2" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
          @endif
        </section>
        <!-- / Alert -->
        <!-- Header -->
        <div class="card shadow-sm mb-6" style="border-radius: 20px;">
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-lg-4">
                <div class="d-flex shadow-sm bg-label-info p-2 radius-15 border" style="width: fit-content;">
                  <div class="bg-white d-flex justify-content-center align-items-center radius-15 mx-1"
                    style="width: 50px; height: 50px;">
                    <img src="/icons/filled-rocket.svg" width="30" height="30" />
                  </div>
                  <div class="mx-4" style="height: 50px;">
                    <p class="fw-medium fs-5" style="margin-bottom: -3px;">
                      {{ $l == 'en' ? 'Companies' : 'الشركات' }}
                    </p>
                    <span class="text-black fs-4">
                      <!-- Total Companies Count -->
                      {{ count($companies) }}
                    </span>
                  </div>
                </div>
              </div>
                <div class="col-12 col-lg-8">
                <form method="GET" action="{{ route('users.view_companies') }}" class="row h-100 align-items-center" id="search-form">
                <div class="col-12 col-lg-2">
                 <div class="d-flex align-items-center justify-content-end gap-3">
                 @if(request()->query())
                    <button class="btn bg-label-secondary p-3 justify-content-center align-items-center"
                        style="border-radius: 17px; width: 40px; height: 40px;" type="button"
                        onclick="window.location.href='/dashboard/companies'">
                        <i class="ti ti-reload ti-sm"></i>
                    </button>
                @endif
                <button class="btn bg-primary p-3 justify-content-center align-items-center"
                    style="border-radius: 17px; width: 40px; height: 40px;" type="submit">
                    <i class="ti ti-filter text-white ti-sm"></i>
                </button>
            </div>
        </div>
        <div class="col-12 col-lg-10 d-flex flex-wrap align-items-center gap-2">
            <div class="SearchSector flex-grow-1">
                @php
                    $sectors = App\Sector::whereNull('parent_id')->get();
                @endphp
                <select id="selectsector" class="form-select select2 sector" name="sector" data-placeholder="{{ __('admin.Sector') }}">
                    <option value="" selected>{{ __('admin.Sector') }}</option>
                    @foreach ($sectors as $sector_group)
                        <optgroup label="{{ $sector_group->name }}">
                            @foreach ($sector_group->children as $sector)
                                <option value="{{ $sector->name_en }}"
                                    {{ $sector->name_en == request('sector') ? 'selected' : '' }}>{{ $sector->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="SearchStage flex-grow-1">
                <select class="form-select select2 stage" name="stage" data-placeholder="{{ __('admin.stage') }}">
                    <option value="">{{ __('admin.stage') }}</option>
                    @php
                        $stages = App\Stage::all();
                    @endphp
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" {{ $stage->id == request('stage') ? 'selected' : '' }}>
                            {{ $stage->{'name_' . $l} ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            @role('Admin')
                <div class="SearchOrg flex-grow-1">
                    @php
                        $orgs = App\Organization::all();
                    @endphp
                    <select class="form-select select2" name="orgnization" data-placeholder="{{ __('admin.Organizations') }}">
                        <option value="" selected>{{ __('admin.Organizations') }}</option>
                        @foreach ($orgs as $org)
                            <option value="{{ $org->id }}"
                                {{ $org->id == request('orgnization') ? 'selected' : '' }}>{{ $org->{'name_' . $l} }}</option>
                        @endforeach
                    </select>
                </div>
            @endrole
            <div class="position-relative flex-grow-1">
                <div class="position-absolute top-50 translate-middle-y end-0 mx-3 search-icon">
                    <i class="ti ti-search text-muted"></i>
                </div>
                <input
                    id="search-input"
                    class="form-control search border rounded bg-transparent"
                    style="width: 250px;"
                    placeholder="{{ __('admin.Search') }}"
                    name="search"
                    value="{{ request('search') }}"
                />
            </div>
        </div>
    </form>
</div>
            </div>
          </div>
        </div>
        <!-- / Header -->
        <!-- Company List -->
        <section class="mb-10 mt-4">
          <div class="d-flex justify-content-between align-items-center w-100 pb-3 mb-6 border-bottom">
            <h5 class="mb-0">
              {{ $l == 'en' ? 'Companies List' : 'قائمة الشركات' }}
            </h5>
            {{-- <div class="position-relative">
              <div class="position-absolute top-50 translate-middle-y end-0 mx-3 search-icon">
                <i class="ti ti-search text-muted"></i>
              </div>
              <input
                id="search-input"
                class="form-control search border rounded bg-transparent"
                style="width: 250px;"
                id="search-table" 
                placeholder="{{ __('admin.Search') }}" 
                name="search"
                value="{{ request('search') }}" 
              />
            </div> --}}
          </div>
          <div class="row">
            @if (count($companies) == 0)
              @include('includes.no-result')
            @endif

            @foreach ($companies as $company)
              <!-- Company Card -->
              <div class="col-md-4">
                <div class="bg-white d-flex flex-column flex-fill justify-content-between border shadow-sm p-4 mb-3 radius-15 card-container" style="min-height: 420px;"
                  {{-- onclick="window.location.href='/dashboard/companyProfile/{{ $company->id }}'" --}}>
                  @php
                    $client = App\User::where('company_id', $company->id)->first();
                  @endphp
                  <div class="w-100">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                      <a style="width: 80%" href="/dashboard/companyProfile/{{ $company->id }}">
                        <h5 class="text-dark mb-0 fw-bold line-clamp-1 text-truncate">{{ $company->{'name_' . $l} }}
                        </h5>
                      </a>
                      <div class="d-flex algin-items-center gap-3">
                        @if ($company->facebook)
                          <a class="d-flex align-items-center"
                            href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                            target='_blank'>
                            <i class="fa-brands fa-facebook-f fs-4 text-primary"></i>
                          </a>
                        @endif
                        @if ($company->linkedin)
                          <a class="d-flex align-items-center"
                            href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                            target='_blank'>
                            <img src="/icons/linkedin.svg" width="22" height="22" />
                          </a>
                        @endif
                        @if ($company->country_code_w_a && $company->whatsapp)
                          <a href="{{ 'https://wa.me/' . $company->country_code_w_a . $company->whatsapp }}"
                            target="_blank" class="d-flex align-items-center">
                            <i class="fa-brands fa-whatsapp fs-4 text-success"></i>
                          </a>
                        @endif
                      </div>
                      {{-- <span class="text-muted text-sm fw-semibold">
                        {{ $l == 'en' ? 'Startup' : 'الشركة' }}
  
                        <a href="{{ route('users.edit', ['user' => $client->id]) }}"
                          class="bg-white px-2 py-1 radius-15 custom-shadow border text-info"><i
                            class="ti ti-user-circle"></i></a>
                      </span> --}}
                      {{-- <a href="/dashboard/companyProfile/{{ $company->id }}"
                        class="bg-white px-2 py-1 radius-15 custom-shadow border text-info">
                        <i class="ti ti-world me-2 fs-5"></i>
                        <span class="text-xs fw-bold">
                          {{ $l == 'en' ? 'More Info' : 'المزيد' }}
                        </span>
                      </a> --}}
                    </div>
                    <div class="mt-2 border custom-shadow radius-15 text-center p-4 w-100">
                      <img src="{{ $company->logo ? $company->logo : '/images/logo-placeholder.png' }}"
                        onerror="this.src='/images/logo-placeholder.png'" class="rounded"
                        style="height: 120px; width: 100%; object-fit: contain;" alt="logo" />
                    </div>
                  </div>
                  <div class="w-100 d-flex align-items-center justify-content-between mt-2">
                    <h6 class="fw-semibold mt-2 text-capitalize line-clamp-1" style="width: 45%;">
                      @php
                        $sector = App\Sector::where('id', $company->sector)->first();
                      @endphp
                      {{ $sector ? $sector->{'name_' . $l} : $company->sector }}
                    </h6>
                    @if ($company->stage && optional($company->stagE)->{'name_' . $l})
                      <div class="badge bg-label-secondary text-dark d-flex align-items-center">
                        <i class="ti ti-building-estate me-1 text-primary"></i>
                        <span class="line-clamp-1">
                          {{ optional($company->stagE)->{'name_' . $l} }}
                        </span>
                      </div>
                    @endif
                    @php
                      // تحويل السلسلة إلى مصفوفة بعد إزالة الشرطات المائلة
                      //  $Sustainables = explode(',', $company->Sustainable_development);
                      $Sustainables = json_decode($company->Sustainable_development);
                    @endphp
                    @if (!empty($Sustainables) && count($Sustainables) > 0)
                      <a class="btn btn-label-secondary" href="#" data-bs-toggle="modal"
                        style="width: 30px; margin-inline-start: -0.5rem;" data-bs-target="#EditLabor"><i
                          class="ti ti-target-arrow ti-16px text-primary"></i>
                      </a>
                      <div class="modal fade" id="EditLabor" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                          <div class="modal-content">
                            <div class="modal-body">
                              <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                              <div class="text-center mb-4">
                                <h4 class="mb-3 fw-bold">{{ $l == 'en' ? 'Sustainability Goals' : 'اهداف الاستدامة' }}
                                </h4>
                                <div class="row justify-content-center">
                                  @foreach ($Sustainables as $Sustainable)
                                    @php
                                      $icon = App\SustainableIcon::where('title', $Sustainable)->first();
                                    @endphp
                                    <div class="col-md-3 col-sm-4 col-6 mb-3">
                                      <div
                                        class="border shadow-sm radius-15 p-3 d-flex justify-content-center align-items-center"
                                        style="height: 120px;">
                                        @if ($icon && $icon->icon_ar)
                                          <img
                                            src="/images/sustainability/{{ $l }}/{{ $icon->icon_ar ?? '1.png' }}"
                                            alt="icon" class="img-fluid"
                                            style="max-width: 100%; max-height: 100%;">
                                        @endif
                                      </div>
                                    </div>
                                  @endforeach
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif
                  </div>
                  <div class="w-100 d-none flex-column justify-content-between" style="height: 80px;">
                    {{-- <h6 class="mt-3 fw-bold text-capitalize">{{ $company->{'name_' . $l} }}</h6> --}}
                    @php
                      $sector = App\Sector::where('id', $company->sector)->first();
                    @endphp
                    <div style="margin-top: -10px;" class="d-flex justify-content-between align-items-center">
                      @if ($company->est_date)
                        <div class="p-1 bg-label-info rounded">
                          <div class="d-flex align-items-center justif-content-center">
                            <div class="d-flex align-items-center">
                              <i class="ti ti-calendar-time fs-5"></i>
                              <span class="mx-1 fw-bold text-black text-xxs text-nowrap fs-bold">
                                {{ $company->est_date }}
                              </span>
                            </div>
                            <span class="text-nowrap text-xxs fw-bold">
                              {{ __('admin.Established date') }}
                            </span>
                          </div>
                        </div>
                      @else
                        <span class="text-nowrap text-sm">
                          {{ $l == 'en' ? 'Social Links' : 'روابط التواصل' }}
                        </span>
                      @endif
                      <div class="d-flex align-items-center" style="gap: 10px;">
                        @if ($company->facebook)
                          <a class="d-flex align-items-center"
                            href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                            target='_blank'>
                            <i class="fa-brands fa-facebook-f fs-6"></i>
                          </a>
                        @endif
                        @if ($company->linkedin)
                          <a class="d-flex align-items-center"
                            href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                            target='_blank'>
                            <img src="/icons/linkedin.svg" width="15" height="15" />
                          </a>
                        @endif
                        <a href="#" class="d-flex align-items-center">
                          <i class="fa-brands fa-whatsapp fs-5 text-success"></i>
                        </a>
                        @if ($company->twitter)
                          <a class="d-flex align-items-center"
                            href="{{ strpos($company->twitter, 'http') === 0 ? $company->twitter : 'http://' . $company->twitter }}"
                            target='_blank'>
                            <i class="fa-brands fa-twitter fs-6 text-info"></i>
                          </a>
                        @endif
                        @if ($company->youtube)
                          <a class="d-flex align-items-center"
                            href="{{ strpos($company->youtube, 'http') === 0 ? $company->youtube : 'http://' . $company->youtube }}"
                            target='_blank'>
                            <i class="fa-brands fa-youtube fs-6 text-danger"></i>
                          </a>
                        @endif
                      </div>
                    </div>
                  </div>

                  <!-- Founders -->
                  @php
                    $founders = App\Team_member::where([
                        ['TeamMember', 'Founder'],
                        ['Company_id', $company->id],
                    ])->get();
                  @endphp

                  @if (!empty($founders) && count($founders) > 0)
                    <div>
                      <span class="text-sm">{{ __('admin.Founders') }}</span>
                      <div class="row pe-4">
                        <div class="col-12" style="min-width: 100%; overflow-x: auto;">
                          <div class="d-flex align-items-center mt-1 gap-2 ">
                            @foreach ($founders as $founder)
                              <div class="pb-2" style="width: fit-content;">
                                <div
                                  class="btn btn-xs btn-outline-secondary d-flex align-items-center gap-1 rounded-pill">
                                  <img src="{{ $founder->image ? $founder->image : '/icons/account.svg' }}"
                                    onerror="this.src='/icons/account.svg'" class="rounded-circle" width="24"
                                    height="24" />
                                  <div class="text-xs text-nowrap">
                                    {{ $founder->Name }}
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        </div>
                      </div>
                    </div>
                  @endif

                  <!-- / Founders -->
                  {{-- <div class="mt-2 border p-2 radius-15">
                    <span class="text-muted text-xs fw-medium">{{ __('admin.Sector') }}</span>
                    <div class="d-flex align-items-center gap-2 mt-1">
                      <div class="bg-label-secondary px-1 rounded">
                        <span
                          class="text-xxs text-primary fw-bold">{{ $company->sector ? optional($company->sectoR)->{'name_' . $l} : __('admin.N/A') }}</span>
                      </div>
                    </div>
                  </div> --}}
                  {{-- @if ($company->stage)
                    <div class="mt-2">
                      <div class="badge bg-label-warning text-dark">
                        <i class="ti ti-building-estate me-2 text-warning"></i>
                        {{ $company->stage ? optional($company->stagE)->{'name_' . $l} : __('admin.N/A') }}
                      </div>
                    </div>
                  @endif --}}

                  @if ($company->est_date)
                    <div class="d-flex align-items-center gap-1 mt-2">
                      <i class="ti ti-calendar text-info"></i>
                      <span class="text-muted text-xs">
                        {{ __('admin.Established date') }}
                      </span>
                      <span class="border px-2 rounded">
                        <span class="text-xs fw-bold">
                          {{ $company->est_date }}
                        </span>
                      </span>
                    </div>
                  @endif

                  <div class="mt-3 d-flex align-items-center gap-2">
                    <i class="ti ti-map-pin text-danger"></i>
                    <span class="text-muted text-xs">{{ optional($company->state)->{'name_' . $l} }}</span>
                    <div class="border px-2 rounded">
                      <span class="text-xs fw-bold">
                        {{ optional($company->city)->{'name_' . $l} }},
                        {{ optional($company->country)->{'name_' . $l} }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </section>
        <!-- / Company List -->

        {{-- <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <hr style="margin:-10px 0 5px 0;">
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table mb-0">
                    <thead>
                      <tr class="table-light">
                        <th>#</th>
                        <th>{{ __('cycles.Company') }}</th>
                        <th>{{ __('admin.Phone') }}</th>
                        <th>{{ __('admin.Sector') }}</th>
                        <th>{{ __('admin.founder') }}</th>
                        <th>{{ __('cycles.Created at') }}</th>
                        <th>{{ __('admin.Cycle') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($companies as $key => $company)
                        <tr>
                          <td>
                            {{ $key + 1 }}
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <a class="media-left mr-50" href="/dashboard/companyProfile/{{ $company->id }}">
                                <img src="/191014/{{ $company->logo ?? '/app-assets/images/logo_placeholder.png' }}"
                                  alt="avatar" class="rounded-circle" height="40" width="40">
                              </a>
                              <a href="/dashboard/companyProfile/{{ $company->id }}">
                                <div class="d-flex align-items-center gap-1">
                                  {{ $company->{'name_' . $l} }}
                                </div>
                              </a>
                            </div>
                          </td>
                          <td>
                            {{ $company->phone }}
                          </td>
                          <td>
                            {{ __($company->sector) }}
                          </td>
                          <td>
                            {{ $company->founder }}
                          </td>
                          <td>
                            {{ $company->created_at }}
                          </td>
                          <td>
                            {{ $company->cycledata->title ?? __('admin.No Cycle') }}
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </section> --}}
      </div>
    </div>
  </div>
  <!-- END: Content-->
@stop

@section('pagescripts')
  <script>
    $('#search-input').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      $('.card-container').each(function() {
        var companyName = $(this).find('h5').text().toLowerCase();
        $(this).toggle(companyName.indexOf(value) > -1);
      });
    });
  </script>
  <script>
    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    $('#search-input').on('keyup', debounce(function() {
      $('#search-form').submit();
    }, 500));
  </script>
  
  <script type="text/javascript">
    $(document).ready(function() {
      $('.table').DataTable({
        @if ($l == 'ar')
          "language": {
            "sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
            "sLoadingRecords": "جارٍ التحميل...",
            "sProcessing": "جارٍ التحميل...",
            "sLengthMenu": "أظهر _MENU_ مدخلات",
            "sZeroRecords": "لم يعثر على أية سجلات",
            "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
            "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
            "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
            "sInfoPostFix": "",
            "sSearch": "ابحث:",
            "sUrl": "",
            "oPaginate": {
              "sFirst": "الأول",
              "sPrevious": "السابق",
              "sNext": "التالي",
              "sLast": "الأخير"
            },
            "oAria": {
              "sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
              "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
            }
          }
        @endif
      });
    });
  </script>
  {{-- <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script> --}}
@stop
