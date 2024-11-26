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
      height: 150px;
      border-radius: 15px;
      background-size: cover;
      background-position: center;
    }

    /* @media screen and (min-width: 1440px) {
      .card-container {
        width: 390px;
      }
    } */
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
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex shadow-sm bg-label-info p-2 radius-15 border">
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
              <div class="d-flex align-items-center justify-content-end">
                <!-- Filter -->
                <form method="GET" action="{{ route('users.companies') }}">
                  <div class="d-flex align-items-center gap-3">
                    <div style="display: flex; align-items: center;">
                      <button class="btn bg-primary p-3 d-none d-md-flex justify-content-center align-items-center"
                        style="border-radius: 17px; width: 40px; height: 40px;" type="submit">
                        <i class="ti ti-filter text-white ti-24px"></i>
                      </button>
                    </div>
                    <!-- Search Bar -->
                    <div class="position-relative d-none d-md-flex">

                      <div class="SearchName">
                      <div class="position-absolute top-50 translate-middle-y search-icon" style="left: 1rem;">
                        <i class="ti ti-search text-primary ti-24px"></i>
                      </div>

                      <input class="form-control search border rounded mx-1 bg-transparent text-primary px-10"
                        style="width: 300px;" id="search-table" placeholder="Search" name="search"
                        value="{{ request('search') }}"  />
                      </div>


          <div class="SearchSector">
            @php
              $sectors = App\Sector::whereNull('parent_id')->get();
            @endphp

                                    <select id="selectsector"
                          class="form-control sector border rounded mx-1 bg-transparent text-primary px-10"style="width: 300px;" name="sector">
                          <option value="" selected >{{ __('admin.Sector') }}
                          </option>
                          @foreach ($sectors as $sector_group)
                            <optgroup label="{{ $sector_group->name }}">
                              @foreach ($sector_group->children as $sector)
                                <option value="{{ $sector->name_en }} " {{ $sector->name_en == request('sector') ? 'selected' : '' }} >{{ $sector->name }}
                                </option>
                              @endforeach
                            </optgroup>
                          @endforeach
                        </select>
          </div>
                        

                        <div class="SearchStage">
                        
                    <select class="form-control stage border rounded mx-1 bg-transparent text-primary px-10"
                            style="width: 300px;" name="stage">
                          <option value="">{{ __('admin.stage') }}</option>
                          <option value="idea"
                            {{ 'idea' == request('stage') ? 'selected' : '' }}>
                            {{ __('admin.stage_idea') }}
                          </option>
                          <option value="prototype"
                            {{ "prototype" == request('stage') ? 'selected' : '' }}>
                            {{ __('admin.stage_prototype') }}
                          </option>
                          <option value="ready_product"
                            {{ "ready_product" == request('stage') ? 'selected' : '' }}>
                            {{ __('admin.stage_ready_product') }}
                          </option>
                          <option value="company_in_market"
                            {{ "company_in_market" == request('stage') ? 'selected' : '' }}>
                            {{ __('admin.stage_company_in_market') }}
                          </option>
                    </select>


                          </div>

                          @role('Admin')
                          <div class="SearchOrg">
                            @php
                              $orgs = App\Organization::all();
                              $orgrequest = App\Organization::where('id', request('orgnization'))->get();
                            @endphp
                
                                                    <select
                                          class="form-control sector border rounded mx-1 bg-transparent text-primary px-10" style="width: 300px;" name="orgnization">
                                          <option value="" selected >{{ __('admin.Organizations') }}
                                          </option>
                                          @foreach ($orgs as $org)
                                                <option value="{{ $org->id }} " {{ $org->id == request('orgnization') ? 'selected' : '' }} >{{ $org->{'name_'.$l} }}
                                                </option>
                                          @endforeach
                                        </select>
                          </div>
                          @endrole

                        
                    </div>
                  </div>
                </form>
                <!-- Add New Program Button -->
                {{-- <button class="btn btn-primary btn-sm d-sm-inline-block d-md-none" style="border-radius: 15px;"
                    onclick="DoChange(2)">
                    <span class="mx-2">{{ __('admin.New Program') }}</span>
                    <i class="menu-icon tf-icons ti ti-components ti-24px"></i>
                  </button>
                  <button class="btn btn-primary d-none d-md-inline-block" style="border-radius: 15px; text-wrap: nowrap;"
                    onclick="DoChange(2)">
                    <span class="mx-2">{{ __('admin.New Program') }}</span>
                    <i class="menu-icon tf-icons ti ti-components ti-24px"></i>
                  </button> --}}
              </div>
            </div>
            <!-- Search Bar (Mobile) -->
            <div class="d-flex align-items-center justify-content-between mx-2">
              <div class="position-relative me-3 mt-4 d-block d-md-none">
                <div class="position-absolute top-50 translate-middle-y search-icon" style="left: 1rem;">
                  <i class="ti ti-search text-primary ti-24px"></i>
                </div>
                <input class="form-control search border rounded mx-1 bg-transparent text-primary px-10"
                  style="width: 300px;" id="search-table" placeholder="Search" />
              </div>
              <button class="btn bg-primary p-3 d-flex d-md-none justify-content-center align-items-center mt-3"
                style="border-radius: 17px; width: 40px; height: 40px;">
                <i class="ti ti-filter text-white ti-24px"></i>
              </button>
            </div>
            <!-- / Search Bar (Mobile) -->
          </div>
        </div>
        <!-- / Header -->
        <!-- Company List -->
        <section class="mb-10 mt-4">
          <h5 class="border-bottom pb-2 mb-6 mx-4 mx-md-0">
            {{ $l == 'en' ? 'Companies List' : 'قائمة الشركات' }}
          </h5>
          <div class="row">
            @foreach ($companies as $company)
              <!-- Company Card -->
              <div class="col-md-4">
                <div 
                  class="bg-white border shadow-sm p-4 mb-3 radius-15 card-container cursor-pointer" 
                  style="height: 580px;"
                  onclick="window.location.href='/dashboard/companyProfile/{{ $company->id }}'"
                >
                @php
                  $client = App\User::where('company_id',$company->id)->first();
                @endphp
                  <div class="w-100 d-flex justify-content-between align-items-center">
                    <span class="text-muted text-sm fw-semibold">
                      {{ $l == 'en' ? 'Startup' : 'الشركة' }}
                      
                      <a 
                      href="{{ route('users.edit', ['user' => $client->id]) }}"
                      class="bg-white px-2 py-1 radius-15 custom-shadow border text-info"
                    ><i class="ti ti-user-circle"></i></a>
                    </span>
                    <a 
                      href="/dashboard/companyProfile/{{ $company->id }}"
                      class="bg-white px-2 py-1 radius-15 custom-shadow border text-info"
                    >
                      <i class="ti ti-world me-2 fs-5"></i>
                      <span class="text-xs fw-bold">
                        {{ $l == 'en' ? 'More Info' : 'المزيد' }}
                      </span>
                    </a>
                  </div>
                  <div 
                    class="company-image-container mt-2 border custom-shadow radius-15"
                    style="background-image: url('{{ $company->logo ?? '../app-assets/images/logo_placeholder.png' }}')"
                  >
                  </div>
                  <div class="w-100 d-flex flex-column justify-content-between" style="height: 80px;">
                    <h6 class="mt-3 fw-bold text-capitalize">{{ $company->{'name_' . $l} }}</h6>
                    <p class="text-muted text-xs" style="margin-top: -10px;">{{ $company->sector }}</p>
                    <div 
                      style="margin-top: -10px;" 
                      class="d-flex justify-content-between align-items-center"
                    >
                      @if($company->est_date)
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
                          <a
                            class="d-flex align-items-center" 
                            href="{{ strpos($company->facebook,"http")===0?$company->facebook:'http://'.$company->facebook }}"
                            target='_blank'>
                            <i class="fa-brands fa-facebook-f fs-6"></i>
                          </a>
                        @endif
                        @if ($company->linkedin)
                          <a
                            class="d-flex align-items-center" 
                            href="{{ strpos($company->linkedin,"http")===0?$company->linkedin:'http://'.$company->linkedin }}"
                            target='_blank'>
                            <img src="/icons/linkedin.svg" width="15" height="15" />
                          </a>
                        @endif
                        <a href="#" class="d-flex align-items-center">
                          <i class="fa-brands fa-whatsapp fs-5 text-success"></i>
                        </a>
                        @if ($company->twitter)
                          <a
                            class="d-flex align-items-center" 
                            href="{{ strpos($company->twitter,"http")===0?$company->twitter:'http://'.$company->twitter }}"
                            target='_blank'>
                            <i class="fa-brands fa-twitter fs-6 text-info"></i>
                          </a>
                        @endif
                        @if ($company->youtube)
                          <a
                            class="d-flex align-items-center" 
                            href="{{ strpos($company->youtube,"http")===0?$company->youtube:'http://'.$company->youtube }}"
                            target='_blank'>
                            <i class="fa-brands fa-youtube fs-6 text-danger"></i>
                          </a>
                        @endif

                        @php
                  // تحويل السلسلة إلى مصفوفة بعد إزالة الشرطات المائلة
                  //  $Sustainables = explode(',', $company->Sustainable_development);
                  $Sustainables = json_decode(
                      $company->Sustainable_development,
                  );
                @endphp
                @if (!empty($Sustainables) && count($Sustainables) > 0)
                  
                    <a class="btn btn-primary" href="#"  data-bs-toggle="modal" data-bs-target="#EditLabor"><i class="ti ti-user"></i></a>
<!-- Edit Customer Modal -->
<div class="modal fade" id="EditLabor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
      <div class="modal-content">
          <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-6">
                  <h4 class="mb-2">{{ $l == 'en' ? 'Sustainability Goals' : 'اهداف الاستدامة' }}</h4>
                  <div class="row w-75">
                    @foreach ($Sustainables as $Sustainable)
                      @php
                        $icon = App\SustainableIcon::where(
                            'title',
                            $Sustainable,
                        )->first();
                      @endphp
                      <div class="col-md-2 mb-2">
                        <div class="border light-shadow radius-15" style="display: grid; place-items: center;">
                          @if ($icon->icon_ar)
                            <img
                              src="/images/sustainability/{{ $l }}/{{ $icon->icon_ar ?? '1.png' }}"
                              alt="icon"
                              style="width: 100px; height: 100px;">
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
<!--/ Edit JO Modal -->
                  
                  @endif
                        
                      </div>
                    </div>
                  </div>
                  <!-- Founders -->
                  @php
                    $founders = App\Team_member::where([
                        ['TeamMember', 'Founder'],
                        ['Company_id', $company->id]
                    ])->get();
                @endphp
                  
                  <div class="mt-3">
                    <span class="text-muted text-xs fw-medium">{{ __('admin.Founders') }}</span>
                    <div class="d-flex align-items-center mt-1 gap-2">
                      @if(!empty($founders))
                      @foreach($founders as $founder)
                        <div class="col-md-4">
                          <div 
                            class="px-3 border redius-15 text-center radius-15 flex flex-column g-1" 
                            style="height: 70px; width: 90px;"
                          >
                            <i class="ti ti-user fs-5 mt-1"></i> <br />
                            <div>
                              <span class="text-xs line-clamp-1">
                                {{ $company->founder }}
                              </span>
                              <span class="text-muted text-xs">{{ $founder->Name }}</span>
                            </div>
                          </div>
                        </div>
                        <!-- Company Card -->
                      @endforeach
                      @else
                      <div class="col-md-4">
                        <div 
                          class="px-3 border redius-15 text-center radius-15 flex flex-column g-1" 
                          style="height: 70px; width: 90px;"
                        >
                          <i class="ti ti-user fs-5 mt-1"></i> <br />
                          <div>
                            <span class="text-xs line-clamp-1">
                              {{ __('admin.N/A') }}
                            </span>
                            <span class="text-muted text-xs">{{ __('admin.N/A') }}</span>
                          </div>
                        </div>
                      </div>
                      <!-- Company Card -->
                      @endif
                    </div>
                  </div>
                  
                  <!-- / Founders -->
                  <div class="mt-2 border p-2 radius-15">
                    <span class="text-muted text-xs fw-medium">{{ __('admin.Sector') }}</span>
                    <div class="d-flex align-items-center gap-2 mt-1">
                      <div class="bg-label-secondary px-1 rounded">
                        <span class="text-xxs text-primary fw-bold">{{ $company->sector ? __('admin.' . $company->sector) : __('admin.N/A') }}</span>
                      </div>
                      
                      
                    </div>
                  </div>
                  <div class="mt-2 border p-2 radius-15">
                    <span class="text-muted text-xs">{{ __('admin.Stage') }}</span>
                    <div class="d-flex align-items-center gap-2 mt-1">
                      <div class="bg-label-warning px-1 rounded">
                        <span class="text-xxs text-warning fw-bold">{{ $company->stage ? __('admin.stage_'.$company->stage) : __('admin.N/A') }}</span>
                      </div>
                      
                    </div>
                  </div>
                  

                  <div class="mt-2 d-flex align-items-center justify-content-center gap-2">
                    <i class="ti ti-map-pin text-danger"></i>
                    <span class="text-muted text-xs">{{ optional($company->state)->{'name_'.$l} }}</span>
                    <div class="border px-2 rounded">
                      <span class="text-xs">
                        {{ optional($company->city)->{'name_'.$l} }}, 
                        {{ optional($company->country)->{'name_'.$l} }}
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
                                <img src="/191014/{{ $company->logo ?? '../app-assets/images/logo_placeholder.png' }}"
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
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
@stop
