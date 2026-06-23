@extends('layouts.admin')

@section('title', __('admin.Services Categories'))
@section('subTitle', __('admin.Service`s Categories List'))

@section('content')

@php
  $tabs = [
    ['title' => __('admin.All Services'), 'route' => 'services.index', 'icon' => 'arrows-move'],
    ['title' => __('admin.Services Requests'), 'route' => 'service_requests.list_all', 'icon' => 'ti ti-briefcase'],
    // ['title' => __('admin.Services Categories'), 'route' => 'serviceCategories.index', 'icon' => 'layout-grid'],
  ];
  $isOrgMan = $_user->hasRole('organizationManager');
  $org_id = $_user->organization_id ?? 0;
  if( $_user->ableTo('serviceCategories.index', $permissions) )
  {
    $tabs[] = ['title' => __('admin.Services Categories'), 'route' => 'serviceCategories.index', 'icon' => 'layout-grid'];
  }
@endphp

<div>
  <x-navigation-pills :tabs="$tabs" />
</div>

@if (session('msg'))
  <x-alert 
    type="success" 
    :message="__('admin.' . session('msg'))" 
  />
  @php session()->forget('msg'); @endphp
@endif

<div class="card">
  <div class="card-header d-flex justify-content-between flex-wrap align-items-center border-bottom">
    <h5 class="card-title mb-3 mb-md-0">
      {{ __('admin.Services Categories') }}
    </h5>

    <div class="d-flex align-items-center gap-3">
      <div class="dropdown">
        <button class="btn btn-label-secondary dropdown-toggle" type="button"
                id="exportDropdown" data-bs-toggle="dropdown">
          <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
        </button>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item" href="javascript:void(0)" id="exportExcel">
              <i class="ti ti-file-spreadsheet me-2"></i>Excel
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0)" id="exportCsv">
              <i class="ti ti-file-text me-2"></i>CSV
            </a>
          </li>
        </ul>
      </div>

      @if($_user->ableTo('serviceCategories.create', $permissions))
        <button type="button" class="btn btn-primary" id="addNewCategory">
          <i class="ti ti-plus me-2"></i> {{ __('admin.Add_New') }}
        </button>
      @endif
    </div>
  </div>

  <div class="card-content">
    <div class="card-datatable table-responsive">
      <table class="table">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th class="text-center">{{ __('admin.Organization') }}</th>
            <th class="text-center">
              {{ $l == 'en' ? 'Service Name (EN)' : 'اسم الخدمة بالانجليزية' }}
            </th>
            <th class="text-center">
              {{ $l == 'en' ? 'Service Name (AR)' : 'اسم الخدمة بالعربية' }}
            </th>
            <th class="text-center">{{ __('admin.Created At') }}</th>
            @if($_user->ableToAny(['serviceCategories.edit', 'serviceCategories.destroy'], $permissions))
              <th class="text-center">{{ __('admin.Actions') }}</th>
            @endif
          </tr>
        </thead>

        <tbody>
          @foreach ($serviceCategories as $key => $category)
            <tr data-category-id="{{ $category->id }}">
              <td>{{ $key + 1 }}</td>

              <td class="text-center">
                @if($category->organization_id)
                  <a href="{{ $_user->ableToAny(['organization.wizard.edit', 'organizations.edit']) ? route('organization.wizard.edit', $category->organization_id) . '?step=1' : 'javascript:void(0);' }}">
                    {{ optional($category->organization)->{'name_'.$l} ?? '-' }}
                  </a>
                @else
                  @if( $_user->hasRole('organizationManager') )
                    <a href="{{ $_user->ableToAny(['organization.wizard.edit', 'organizations.edit']) ? route('organization.wizard.edit', $category->organization_id) . '?step=1' : 'javascript:void(0);' }}">
                      {{ $_user->organizations?->first()?->name ?? '-' }}
                    </a>
                  @else
                    <span class="badge bg-label-primary">{{ __('admin.All_Organizations') }}</span>
                  @endif
                @endif
              </td>

              <td class="text-center">{{ $category->name_en }}</td>
              <td class="text-center">{{ $category->name_ar }}</td>
              <td class="text-center">{{ $category->created_at }}</td>

              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center">
                  @if ($_user->ableTo('serviceCategories.edit', $permissions))
                    <button 
                      class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill category-edit"
                      data-id="{{ $category->id }}"
                      data-organization-id="{{ $category->organization_id ?? '' }}"
                      data-name-en="{{ $category->name_en }}"
                      data-name-ar="{{ $category->name_ar }}"
                    >
                      <i class="ti ti-pencil ti-md"></i>
                    </button>
                  @endif

                  @if ($_user->ableTo('serviceCategories.destroy', $permissions))
                    <button 
                      class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-record"
                      data-id="{{ $category->id }}"
                      data-url="{{ route('serviceCategories.destroy', $category->id) }}"
                    >
                      <i class="ti ti-trash ti-md"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="categoryModalLabel">{{ __('admin.Add_New') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="categoryForm" method="POST" novalidate>
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <div class="modal-body">
          @php
            $authUser = auth()->user();
            $isSingleOrg = $isOrgMan || ($authUser->isSubAdmin() && count($authUser->organizations_ids) == 1);
            $showOrgDropdown = $authUser->hasRole('Admin') || ($authUser->isSubAdmin() && count($authUser->organizations_ids) > 1);
          @endphp
          
          <div class="mb-3 {{ $isSingleOrg ? 'd-none' : '' }}" id="organizationWrapper">
            <label for="organization_id" class="form-label">{{ __('cycles.Select_Organization')}} <span class="text-danger">*</span></label>
            <select {{ !$isSingleOrg && !$authUser->hasRole('Admin') && !($authUser->isSubAdmin() && count($authUser->organizations_ids) > 1) ? 'required' : '' }} class="form-select" id="organization_id" name="organization_id" data-placeholder="{{ __('cycles.Select_Organization') }}">
              @if( $showOrgDropdown ) <option disabled value="">{{ __('cycles.Select_Organization') }}</option> @endif
              @if($authUser->hasRole('Admin'))
                <option value="all">{{ __('admin.All_Organizations') }}</option>
                @foreach( \App\Organization::all() as $org )
                  <option value="{{ $org->id }}">{{ $org->{'name_' . app()->getLocale()} }}</option>
                @endforeach
              @elseif($authUser->isSubAdmin())
                <option value="all">{{ __('admin.All_Organizations') }}</option>
                @foreach( $authUser->organizations as $org )
                  <option value="{{ $org->id }}">{{ $org->{'name_' . app()->getLocale()} }}</option>
                @endforeach
              @else
                <option selected value="{{ $org_id ?? '' }}">{{ isset($org_id) ? \App\Organization::find($org_id)?->{'name_' . app()->getLocale()} : '' }}</option>
              @endif
            </select>
            <div class="invalid-feedback">{{ __('cycles.Select_Organization') }}</div>
          </div>

          <div class="mb-3">
            <label for="name_en" class="form-label">{{ __('admin.name_english')}} <span class="text-danger">*</span></label>
            <input 
              style="direction: ltr;" 
              type="text" 
              class="form-control" 
              id="name_en" 
              name="name_en"
              required 
              {{-- placeholder="{{__('admin.name_english')}}" --}}
              data-error-msg="{{ __('admin.name_english') }} {{ __('admin.must contain English letters only') }}">
            <div class="invalid-feedback">{{ __('admin.Required_Field') }}</div>
          </div>

          <div class="mb-3">
            <label for="name_ar" class="form-label">{{__('admin.name_arabic')}} <span class="text-danger">*</span></label>
            <input 
              style="direction: rtl;" 
              type="text" 
              class="form-control" 
              id="name_ar" 
              name="name_ar"
              required 
              {{-- placeholder="{{__('admin.name_arabic')}}" --}}
              data-error-msg="{{ __('admin.name_arabic') }} {{ __('admin.must contain Arabic letters only') }}">
            <div class="invalid-feedback">{{ __('admin.Required_Field') }}</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
          <button type="submit" class="btn btn-primary" id="submitBtn">{{ __('admin.Save') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

@stop

@section('scripts')
<script>
$(function() {
  const locale = '{{ app()->getLocale() }}';
  const isArabic = locale === 'ar';
  
  // Initialize DataTable
  const table = $('.table').DataTable({
    responsive: true,
    processing: true,
    pageLength: 10,
    order: [[4, 'desc']],
    dom: '<"row"<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-6"l><"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>>t<"row px-6"<"col-lg-6"i><"col-lg-6 d-flex justify-content-end"p>>',
    language: {
      url: `https://cdn.datatables.net/plug-ins/1.13.6/i18n/${isArabic ? 'ar' : 'en-GB'}.json`,
      sLengthMenu: '_MENU_',
      search: '',
      searchPlaceholder: '{{ __('admin.Search') }}...'
    },
    columnDefs: [{
      targets: 5,
      orderable: false,
      searchable: false
    }],
    buttons: ['excel', 'csv'].map(type => ({
      extend: type,
      title: isArabic ? 'فئات الخدمات' : 'Service Categories',
      exportOptions: { columns: [0, 1, 2, 3, 4] }
    })),
    initComplete() {
      const api = this.api();
      const container = $('<div class="d-flex gap-3 ms-3"></div>').insertAfter('.dataTables_length');

      // Add organization filter
      const select = $(`
        <select id="organizationFilter" class="form-select" style="min-width:150px">
          <option value="">@lang('admin.All_Organizations')</option>
        </select>
      `).appendTo(container);

      api.column(1).data().unique().sort().each(d => {
        const text = $('<div>').html(d).text().replace(/\s+/g, ' ').trim();
        if (text) select.append(`<option value="${text.toLowerCase()}">${text}</option>`);
      });

      select.on('change', function() {
        const val = $(this).val();
        api.column(1).search(val ? `^\\s*${$.fn.dataTable.util.escapeRegex(val)}\\s*$` : '', true, false).draw();
      });

      setTimeout(() => $('.form-control-sm, .form-select-sm').removeClass('form-control-sm form-select-sm'), 300);
    }
  });

  // Export buttons
  $('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
  $('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());

  // Modal and Form handling
  const modal = new bootstrap.Modal('#categoryModal');
  const form = $('#categoryForm');
  const modalTitle = $('#categoryModalLabel');
  const submitBtn = $('#submitBtn');
  const isSingleOrg = {{ $isSingleOrg ? 'true' : 'false' }};
  const isAdmin = {{ $authUser->hasRole('Admin') ? 'true' : 'false' }};
  const isSubAdminMultiOrg = {{ $authUser->isSubAdmin() && count($authUser->organizations_ids) > 1 ? 'true' : 'false' }};

  // Custom validation patterns
  const englishPattern = /^[A-Za-z\s]+$/;
  const arabicPattern = /^[\u0600-\u06FF\s]+$/;

  // Real-time validation
  $('#name_en').on('input', function() {
    const input = $(this);
    const value = input.val();
    
    if (value && !englishPattern.test(value)) {
      input.addClass('is-invalid');
      input.siblings('.invalid-feedback').text('{{ __("admin.name_english") }} {{ __("admin.must contain English letters only") }}');
    } else if (value.length > 100) {
      input.addClass('is-invalid');
      input.siblings('.invalid-feedback').text('{{ __("admin.maximum_length_100") }}');
    } else {
      input.removeClass('is-invalid');
    }
  });

  $('#name_ar').on('input', function() {
    const input = $(this);
    const value = input.val();
    
    if (value && !arabicPattern.test(value)) {
      input.addClass('is-invalid');
      input.siblings('.invalid-feedback').text('{{ __("admin.name_arabic") }} {{ __("admin.must contain Arabic letters only") }}');
    } else if (value.length > 100) {
      input.addClass('is-invalid');
      input.siblings('.invalid-feedback').text('{{ __("admin.maximum_length_100") }}');
    } else {
      input.removeClass('is-invalid');
    }
  });

  $('#organization_id').on('change', function() {
    const input = $(this);
    if (!isSingleOrg && !isAdmin && !isSubAdminMultiOrg && !input.val()) {
      input.addClass('is-invalid');
    } else {
      input.removeClass('is-invalid');
    }
  });

  // Add New Category
  $('#addNewCategory').on('click', function() {
    form[0].reset();
    // form.removeClass('was-validated');
    $('.form-control, .form-select').removeClass('is-invalid');
    form.attr('action', '{{ route("serviceCategories.store") }}');
    $('#formMethod').val('POST');
    modalTitle.text('{{ __("admin.Add_New") }}');
    submitBtn.text('{{ __("admin.Save") }}');
    $('#organization_id').prop('selectedIndex', 0);
    modal.show();
  });

  // Edit Category
  $('.table tbody').on('click', '.category-edit', function() {
    const btn = $(this);
    const id = btn.data('id');
    const organizationId = btn.data('organization-id');
    const nameEn = btn.data('name-en');
    const nameAr = btn.data('name-ar');

    form[0].reset();
    // form.removeClass('was-validated');
    $('.form-control, .form-select').removeClass('is-invalid');
    
    form.attr('action', '{{ url("dashboard/serviceCategories") }}/' + id);
    $('#formMethod').val('PATCH');
    modalTitle.text('{{ __("admin.Edit") }}');
    submitBtn.text('{{ __("admin.Update") }}');
    
    $('#organization_id').val(organizationId || 'all');
    $('#name_en').val(nameEn);
    $('#name_ar').val(nameAr);
    
    modal.show();
  });

  // Form Submit with validation
  form.on('submit', function(e) {
    e.preventDefault();
    
    // Remove previous validation states
    $('.form-control, .form-select').removeClass('is-invalid');
    
    let isValid = true;
    
    // Validate organization if not single org and not admin
    if (!isSingleOrg && !isAdmin && !isSubAdminMultiOrg) {
      const orgSelect = $('#organization_id');
      if (!orgSelect.val()) {
        orgSelect.addClass('is-invalid');
        isValid = false;
      }
    }
    
    // Validate English name
    const nameEn = $('#name_en');
    const nameEnValue = nameEn.val().trim();
    if (!nameEnValue) {
      nameEn.addClass('is-invalid');
      nameEn.siblings('.invalid-feedback').text('{{ __("admin.Required_Field") }}');
      isValid = false;
    } else if (!englishPattern.test(nameEnValue)) {
      nameEn.addClass('is-invalid');
      nameEn.siblings('.invalid-feedback').text('{{ __("admin.name_english") }} {{ __("admin.must contain English letters only") }}');
      isValid = false;
    } else if (nameEnValue.length > 100) {
      nameEn.addClass('is-invalid');
      nameEn.siblings('.invalid-feedback').text('{{ __("admin.maximum_length_100") }}');
      isValid = false;
    }
    
    // Validate Arabic name
    const nameAr = $('#name_ar');
    const nameArValue = nameAr.val().trim();
    if (!nameArValue) {
      nameAr.addClass('is-invalid');
      nameAr.siblings('.invalid-feedback').text('{{ __("admin.Required_Field") }}');
      isValid = false;
    } else if (!arabicPattern.test(nameArValue)) {
      nameAr.addClass('is-invalid');
      nameAr.siblings('.invalid-feedback').text('{{ __("admin.name_arabic") }} {{ __("admin.must contain Arabic letters only") }}');
      isValid = false;
    } else if (nameArValue.length > 100) {
      nameAr.addClass('is-invalid');
      nameAr.siblings('.invalid-feedback').text('{{ __("admin.maximum_length_100") }}');
      isValid = false;
    }
    
    if (!isValid) {
      // form.addClass('was-validated');
      return false;
    }
    
    const formData = new FormData(this);
    const url = form.attr('action');
    const isEdit = $('#formMethod').val() === 'PATCH';
    
    // Disable submit button to prevent double submission
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __("admin.saving") }}...');
    
    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        modal.hide();
        
        releaseSuccessAlert({
          title: isEdit ? '{{ __("admin.Updated") }}!' : '{{ __("admin.Created") }}!',
          html: isEdit ? '{{ __("admin.UpdatedSuccessfully") }}.' : '{{ __("admin.CreatedSuccessfully") }}.'
        });
        
        // Reload page to refresh table
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      },
      error: function(xhr) {
        submitBtn.prop('disabled', false).text(isEdit ? '{{ __("admin.Update") }}' : '{{ __("admin.Save") }}');
        
        let errorMsg = '{{ __("admin.unexpected_error") }}';
        
        if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = xhr.responseJSON.errors;
          
          // Display field-specific errors
          if (errors.organization_id) {
            $('#organization_id').addClass('is-invalid');
            $('#organization_id').siblings('.invalid-feedback').text(errors.organization_id[0]);
          }
          if (errors.name_en) {
            $('#name_en').addClass('is-invalid');
            $('#name_en').siblings('.invalid-feedback').text(errors.name_en[0]);
          }
          if (errors.name_ar) {
            $('#name_ar').addClass('is-invalid');
            $('#name_ar').siblings('.invalid-feedback').text(errors.name_ar[0]);
          }
          
          errorMsg = Object.values(errors).flat().join('<br>');
        }
        
        releaseFailureAlert({
          title: '{{ __("admin.Error") }}!',
          html: errorMsg
        });
      }
    });
  });

  // Delete Record
  $('.table tbody').on('click', '.delete-record', function () {
    const button = $(this);
    const row = table.row(button.closest('tr'));
    const { url } = button.data();

    releaseWarningAlert({
      title: "{{ __('admin.SureDelete') }}",
      message: "{{ __('admin.Recover') }}",
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('admin.No') }}",
      confirmColor: 'danger',
      onConfirm: () => {
        $.post(url, { 
          _method: 'DELETE', 
          _token: '{{ csrf_token() }}' 
        })
        .done(() => {
          releaseSuccessAlert({
            title: "{{ __('admin.Deleted') }}!",
            html: "{{ __('admin.DeletedSuccessfully') }}."
          });

          row.remove().draw();
        })
        .fail(error => {
          releaseFailureAlert({
            title: "{{ __('admin.Error') }}!",
            message: error?.message || "{{ __('admin.unexpected_error') }}"
          });
        });
      }
    });
  });
});
</script>
@stop