@extends('layouts.admin')
@section('title', __('admin.surveys') )
@section('subTitle', __('admin.surveysList'))
@section('content')

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
<style type="text/css">
.surveys-list {
  min-height: 35vh !important;
}
</style>

@stop
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-header">
      <div class="row mb-6 mt-3">
        <div class="col-6">
          <h3 class="fw-bold mb-0">{{__('admin.surveys')}}</h3>
        </div>
        <div class="col-6 d-flex justify-content-end">
          <a href="{{ route('surveys.create') }}" class="btn btn-primary"> <i class="ti ti-plus me-1"></i>
            {{ __('admin.AddNewSurvey')}} </a>
        </div>
      </div>
    </div>
    <div class="content-header row">
      @if( session()->has('success') )
      <div class="col-md-12">
        <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
          {{ __('admin.'. session()->get('success')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
      @endif
      @if( session()->has('error') )
      <div class="col-md-12">
        <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
          {{ __('admin.'.session()->get('error')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
      @endif
    </div>

    <div class="content-body">
      <section class="organizations-list-wrapper">
        <div class="organizations-list-table">
          <div class="card">
            <div class="card-content">
              <div class="card-body">
                @if( session()->has('msg') )
                <div class="row">
                  <div class="col-md-12">
                    <div class="alert alert-danger mb-2" role="alert">
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      <div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>
                          {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
                    </div>
                  </div>
                </div>
                @endif
                @if( count( $errors ) > 0 )
                <div class="row">
                  <div class="col-md-12">
                    <div class="alert alert-danger">
                      <ul>
                        @foreach($errors->all() as $error)
                        <li>{{ $error }} </li>
                        @endforeach
                      </ul>
                    </div>
                  </div>
                </div>
                @endif

                <div class="table-responsive surveys-list">
                  <table id="surveys-list-datatable" class="table">
                    <thead>
                      <tr class="table-light">
                        <th>#</th>
                        <th>{{__('admin.surveyTitle')}}</th>
                        <th class="text-center">{{__('admin.Organization')}}</th>
                        {{-- <th class="text-center">{{ __('admin.Tags') }}</th> --}}
                        <th style="width: 100px;" class="text-center">{{__('admin.Actions')}}</th>
                      </tr>
                    </thead>
                      <tbody>
                        @foreach($surveys as $survey)
                        <tr>
                          <td><a href="{{ route('surveys.edit', $survey->id) }}">{{ $survey->id }}</a></td>
                          <td>{!! $survey->title !!}</td>
                          <td class="text-center">
                                <a href="{{ route('organizations.edit', $survey->organization->id) }}">
                                    {{ $survey->organization->{'name_' . $l} }}
                                </a>
                          </td>
                        {{-- <td class="text-center">
                        @foreach($survey->questions as $question)
                              @foreach($question->tags as $tag)
                                 <span class="badge bg-primary me-1">{{ $tag }}</span>
                              @endforeach
                        @endforeach
                      </td> --}}
            <td class="text-center">
              <div class="btn-group w-100">
                <button class="btn btn-sm border-none" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ti ti-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="{{ route('surveys.questions_manage', ['survey' => $survey->id]) }}" class="dropdown-item">
                        <i class="ti ti-list-check"></i> {{ __('admin.manageQuestions') }}
                    </a>
                    <a href="{{ route('surveys.edit', ['survey' => $survey->id]) }}" class="dropdown-item">
                        <i class="ti ti-edit text-info"></i> {{ __('admin.Edit') }}
                    </a>
                    <a id="deleteAction" data-id="{{ $survey->id }}" data-url="{{ route('surveys.destroy', ['survey' => $survey->id]) }}"
                       href="{{ route('surveys.destroy', ['survey' => $survey->id]) }}" class="dropdown-item">
                        <i class="ti ti-trash text-danger"></i> {{ __('admin.Delete') }}
                    </a>
                </div>
            </div>
        </td>
    </tr>
    @endforeach
</tbody>
                        
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
@stop
@section('scripts')
<script src="/app-assets/js/scripts/pages/page-organizations.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
<script src="/app-assets/js/scripts/modal/components-modal.js"></script>

<script type="text/javascript">
$(document).ready(function() {
  $('#surveys-list-datatable').DataTable({
    'responsive': true,
    'searching': true,
    'lengthChange': false,
    'paging': true,
    'bInfo': false,
    'columnDefs': [{
      "orderable": false,
      "targets": [3]
    }],
    @if($l == 'ar')
    'language': {
      'sEmptyTable': "ليست هناك بيانات متاحة في الجدول",
      'sLoadingRecords': "جارٍ التحميل...",
      'sProcessing': "جارٍ التحميل...",
      'sLengthMenu': "أظهر _MENU_ مدخلات",
      'sZeroRecords': "لم يعثر على أية سجلات",
      'sInfo': "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
      'sInfoEmpty': "يعرض 0 إلى 0 من أصل 0 سجل",
      'sInfoFiltered': "(منتقاة من مجموع _MAX_ مُدخل)",
      'sInfoPostFix': "",
      'sSearch': "ابحث:",
      'sUrl': "",
      'oPaginate': {
        'sFirst': "الأول",
        'sPrevious': "السابق",
        'sNext': "التالي",
        'sLast': "الأخير"
      },
      'oAria': {
        'sSortAscending': ": تفعيل لترتيب العمود تصاعدياً",
        'sSortDescending': ": تفعيل لترتيب العمود تنازلياً"
      }
    }
    @endif
  });
});
$(document).on("click", "#deleteAction", function(e) {
  e.preventDefault();
  var url = $(this).data('url');
  var token = '{{ csrf_token() }}';
 
    Swal.fire({
      title: "{{ __('admin.SureDelete') }}",
      text: "{{ __('admin.Recover') }}",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "{{ __('admin.DYes') }}",
      cancelButtonText: "{{ __('admin.No') }}",
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-secondary'
      },
      buttonsStyling: false
    })
    .then((result) => {
      if (result.value) {
        $.post(url, {
            '_method': 'DELETE',
            '_token': token
          },
          function(response) {
            Swal.fire({
              icon: 'success',
              title: "{{ __('admin.Deleted') }}!",
              html: "{{ __('admin.DeletedSuccessfully') }}.",
              showCancelButton: false,
              showConfirmButton: true,
              confirmButtonText: "{{ __('admin.OK') }}",
              customClass: {
                  confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            });
            location.reload();
          }
        ).fail(function(error) {
          console.log(error);
        });
      }
    })
});
</script>
@stop