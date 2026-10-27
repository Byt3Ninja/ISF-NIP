@extends('layouts.admin')
@section('title', __('admin.Projects') )

@section('vendorstyle')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('content')
<div class="">
  <div class="">
    @if(session()->has('message'))
    <div class="alert alert-success alert-dismissible mt-2" role="alert">
      {{ __('admin.'.session()->get('message')) }} {{ Session::forget('message') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible mt-2" role="alert">
      {{ __('admin.'.session()->get('error')) }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card mb-6">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex">
            <div class="bg-label-primary rounded p-3">
              <i class="ti ti ti-folder ti-24px"></i>
            </div>
            <div class="mx-4" style="height: 50px;">
              <p class="fw-medium fs-5" style="margin-bottom: -3px;">{{__('admin.Projects')}}</p>
              <span class="text-primary fs-4">
                {{ count($projects) }}
              </span>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
              <i class="ti ti-plus"></i>
              <span class="mx-2">{{ __('admin.Add New')}} </span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="row mb-4 border-bottom pb-4">
            <form method="GET" action="{{ route('projects.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
              <div class="form-group">
              <label for="program_name" class="form-label fs-6 mb-1 fw-semibold">{{ __('admin.filter_by_programs') }}</label>
              <select name="program_name" id="program_name" class="form-select select2" data-placeholder="{{ __('admin.filter_by_programs') }}">
                <option value="">{{ __('admin.filter_by_programs') }}</option>
                @foreach($programs as $program)
                <option value="{{ $program->title }}" {{ request('program_name')===$program->title ? 'selected' : '' }}>
                {{ $program->title }}
                </option>
                @endforeach
              </select>
              </div>
            </div>

            <div class="col-6 col-md-2">
              <div class="form-group">
              <label for="start_date" class="form-label fs-6 mb-1 fw-semibold">{{ __('admin.start_date') }}</label>
              <input type="hidden" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
              <input type="text" class="form-control flatpickr" id="start_date" name="start_date" value="{{ request('start_date') }}" placeholder="{{ __('admin.start_date') }}">
              </div>
            </div>

            <div class="col-6 col-md-2">
              <div class="form-group">
              <label for="end_date" class="form-label fs-6 mb-1 fw-semibold">{{ __('admin.end_date') }}</label>
              <input type="hidden" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
              <input type="text" class="form-control flatpickr" id="end_date" name="end_date" value="{{ request('end_date') }}" placeholder="{{ __('admin.end_date') }}">
              </div>
            </div>

            <div class="col-12 col-md-3 d-flex justify-content-end align-items-center gap-2">
              <button type="submit" class="btn btn-primary">{{ __('admin.Filter') }}</button>
              <a href="{{ route('projects.index') }}" class="btn btn-light">{{ __('admin.Reset') }}</a>
            </div>
            </form>
        </div>
        <div class="table-responsive">
          <table id="projects-datatable" class="table">
            <thead class="table-light">
              <tr>
                <th class="text-center">{{ __('admin.Name') }}</th>
                <th class="text-center">{{ __('admin.Programs') }}</th>
                <th class="text-center">{{ __('admin.Team_members') }}</th>
                <th class="text-center">{{ __('admin.Date') }}</th>
                <th class="text-center">{{ __('admin.Actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($projects as $project)
              <tr>
                <td class="text-center"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                <td class="text-center">{{ $project->programs()->count() }}</td>
                <td class="text-center">{{ $project->teamMembers()->count() }}</td>
                <td class="text-center">{{ $project->created_at->format('Y-m-d') }}</td>
                <td class="text-center">
                  <a href="{{ route('projects.show', $project) }}"
                    class="btn btn-sm btn-icon rounded-circle btn-label-primary" data-tooltip="{{ __('admin.View') }}">
                    <i class="ti ti-eye"></i>
                  </a>

                  <a href="{{ route('projects.edit', $project->id) }}"
                    class="btn btn-sm btn-icon rounded-circle btn-label-info" data-tooltip="{{ __('admin.Edit') }}">
                    <i class="ti ti-edit"></i>
                  </a>

                  <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="delete-form" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-icon rounded-circle btn-label-danger delete-button" data-tooltip="{{ __('admin.Delete') }}">
                      <i class="ti ti-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="content-backdrop fade"></div>
</div>
@stop

@section('scripts')
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
  @include('partials.flatpickr-locale')
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
@stop

@section('pagescripts')
<script src="/vuexy/assets/js/forms-selects.js"></script>
<script>
  $('.flatpickr').flatpickr({
    enableTime: false,
    dateFormat: "Y-m-d",
  });
</script>
<script>
  $(document).ready(function() {
    const lang = "{{ $l }}";

  const datatableLanguage = lang === 'ar' ? {
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
  } : {};
    $('#projects-datatable').DataTable({
        "paging": true,
        "searching": true, 
        "ordering": true,
        "info": true,
        "lengthMenu": [10, 25, 50, 100],
        "language": datatableLanguage
    });

    $('.delete-button').on('click', function(e) {
      e.preventDefault();
      var form = $(this).closest('form');
      releaseAlert('warning', {
        title: "{{__('admin.SureDelete')}}",
        message: "{{__('admin.Recover')}}",
        confirmText: "{{ __('admin.DYes') }}",
        cancelText: "{{ __('admin.No') }}",
        confirmColor: 'danger'
      }).then((ok) => {
          if (ok) {
            form.submit();
          }
        });
      });

    // $('form').on('submit', function(e) {
    //   console.log('start date input', $('#start_date').val());
    //   console.log('end date input', $('#end_date').val());
    //   if ($('#program_name').val() === '' && $('#start_date').val() === '' && $('#end_date').val() === '') {
    //     e.preventDefault();
    //     $('#program_name').select2('focus');
    //     $('#program_name').siblings('.select2-container').addClass('is-invalid');
    //   }
    // });

    // $('#program_name').on('change', function() {
    //   if ($('#program_name').val() !== '') {
    //     $('#program_name').siblings('.select2-container').removeClass('is-invalid');
    //   }
    // });
  });
</script>
@stop