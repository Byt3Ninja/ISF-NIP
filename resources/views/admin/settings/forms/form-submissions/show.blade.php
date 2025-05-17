@extends('layouts.admin')

@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Submission Details Header -->
    <div class="d-flex justify-content-between mb-4">
      <h4 class="mb-0 fw-bold">{{ __('admin.Form Submission Details') }}</h4>
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-label-primary btn-print" onclick="window.print();">
          <i class="fas fa-print"></i> <span class="d-none d-md-inline ms-2">{{ __('admin.Print') }}</span>
        </button>
        <button class="btn btn-sm btn-label-danger btn-delete">
          <i class="fas fa-trash-alt"></i> <span class="d-none d-md-inline ms-2">{{ __('admin.Delete') }}</span>
        </button>
      </div>
    </div>
    <!--/ Submission Details Header -->

    <!-- Submission Details Card -->
    <div class="card mb-4">
      <div class="row d-flex align-items-center">
        <div class="col-12 col-md-6 text-center text-md-start ps-8">
          <img src="/images/form-illustration.svg" alt="Illustration" class="h-px-300 w-px-300">
        </div>
        <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-end pe-md-12 px-8 px-md-0 pb-8 pb-md-0">
          <div class="my-auto">
            <div class="metadata-section"></div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Submission Details Card -->

    <!-- Submitted Answers Card -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-4 pb-3">
        <h5 class="mb-0 fw-bold">{{ __('admin.Submitted Answers') }}</h5>
      </div>
      <div class="card-body submission-data-section px-4 py-3">
        <!-- Will be rendered by JS -->
      </div>
    </div>
    <!-- End Submitted Answers Card -->

  </div>
  <!--/ Content -->
</div>
<!-- / Content wrapper -->
@stop

@section('pagescripts')

<script>
  let submissionData = {!! json_encode($data[0]) !!};
  console.log(submissionData);
  document.addEventListener('DOMContentLoaded', function() {
    const submissionData = {!! json_encode($data[0]) !!};

    // Render metadata
    const metadataSection = document.querySelector('.metadata-section');
    if (metadataSection) {
      metadataSection.innerHTML = `
        <div class="bg-label-light px-4 py-1 border rounded-2 d-flex align-items-center justify-content-between mb-2">
          <h6 class="text-muted mb-1 me-4 me-md-12 text-nowrap"><i class="ti ti-16px ti-building-skyscraper me-2 text-primary p-2 rounded-circle bg-white"></i>{{__('admin.Startup')}}</h6>
          <p class="mb-0 fw-semibold ms-0 ms-md-10 text-dark line-clamp-2">${submissionData.startup_name}</p>
        </div>
        <div class="bg-label-light px-4 py-1 border rounded-2 d-flex align-items-center justify-content-between mb-2">
          <h6 class="text-muted mb-1 me-4 me-md-12 text-nowrap"><i class="ti ti-16px ti-building me-2 text-primary p-2 rounded-circle bg-white"></i>{{__('admin.Organization')}}</h6>
          <p class="mb-0 fw-semibold ms-0 ms-md-10 text-dark line-clamp-2">${submissionData.organization_name}</p>
        </div>
        <div class="bg-label-light px-4 py-1 border rounded-2 d-flex align-items-center justify-content-between mb-2">
          <h6 class="text-muted mb-1 me-4 me-md-12 text-nowrap"><i class="ti ti-16px ti-border-all me-2 text-primary p-2 rounded-circle bg-white"></i>{{__('admin.Program')}}</h6>
          <p class="mb-0 fw-semibold ms-0 ms-md-10 text-dark line-clamp-2 text-end">${submissionData.program_name}</p>
        </div>
        <div class="bg-label-light px-4 py-1 border rounded-2 d-flex align-items-center justify-content-between mb-2">
          <h6 class="text-muted mb-1 me-4 me-md-12 text-nowrap"><i class="ti ti-16px ti-user me-2 text-primary p-2 rounded-circle bg-white"></i>{{__('admin.Submitted By')}}</h6>
          <p class="mb-0 fw-semibold ms-0 ms-md-10 text-dark line-clamp-2">${submissionData.submitted_by}</p>
        </div>
        <div class="bg-label-light px-4 py-1 border rounded-2 d-flex align-items-center justify-content-between">
          <h6 class="text-muted mb-1 me-4 me-md-12 text-nowrap"><i class="ti ti-16px ti-calendar me-2 text-primary p-2 rounded-circle bg-white"></i>{{__('admin.Date')}}</h6>
          <p class="mb-0 fw-semibold ms-0 ms-md-10 text-dark line-clamp-2">${submissionData.submissionDate}</p>
        </div>
      `;
    }

    // Render answers
    const answersSection = document.querySelector('.submission-data-section');
    if (answersSection) {
      answersSection.innerHTML = '';
      submissionData.answers.forEach(ans => {
        let html = '';
        if (ans.file_url && ans.file_name) {
          // File field
          let iconClass = 'fas fa-file';
          let linkClass = 'text-decoration-none d-inline-flex align-items-center';
          if (ans.file_name.match(/\.(pdf)$/i)) {
            iconClass = 'fas fa-file-pdf me-2 text-danger';
            linkClass += ' link-primary';
          } else if (ans.file_name.match(/\.(xlsx|xls|csv)$/i)) {
            iconClass = 'fas fa-file-excel me-2 text-success';
            linkClass += ' link-success';
          }
          html = `
            <div class="question-block mb-3 p-3 border-bottom">
              <span class="question-label fw-semibold text-primary">[${ans.label}]</span>
              <div class="submitted-value file-value mt-1">
                <a href="${ans.file_url}" target="_blank" title="Download ${ans.file_name}" class="${linkClass}">
                  <i class="${iconClass}"></i>
                  ${ans.file_name}
                </a>
              </div>
            </div>
          `;
        } else if (ans.noFileSubmitted) {
          // No file submitted
          html = `
            <div class="question-block mb-3 p-3 border-bottom">
              <span class="question-label fw-semibold text-primary">[${ans.label}]</span>
              <div class="submitted-value file-value no-file mt-1 text-muted fst-italic small">
                <i class="fas fa-ban me-1"></i>(No file submitted)
              </div>
            </div>
          `;
        } else if (ans.value && ans.label.toLowerCase().includes('notes')) {
          // Textarea field
          html = `
            <div class="question-block mb-3 p-3 border-bottom">
              <span class="question-label fw-semibold text-primary">[${ans.label}]</span>
              <div class="submitted-value textarea-value mt-1 text-wrap" style="white-space: pre-line;">${ans.value}</div>
            </div>
          `;
        } else {
          // Regular field
          html = `
            <div class="question-block mb-3 p-3 border-bottom">
              <span class="question-label fw-semibold text-primary">[${ans.label}]</span>
              <div class="submitted-value mt-1">${ans.value ?? ''}</div>
            </div>
          `;
        }
        answersSection.innerHTML += html;
      });
    }

    // Delete button functionality
    const deleteButton = document.querySelector('.btn-delete');
    if (deleteButton) {
      deleteButton.addEventListener('click', function() {
      Swal.fire({
        title: '{{ __('admin.Delete Submission') }}',
        text: '{{ __('admin.Delete Submission Confirmation') }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ __('admin.Delete') }}',
        cancelButtonText: '{{ __('admin.Cancel') }}',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: '{{ __('admin.Deleted') }}',
            text: '{{ __('admin.Deleted Successfully') }}',
            icon: 'success',
            confirmButtonText: '{{ __('admin.OK') }}',
            customClass: {
              confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
          });
        }
        });
      });
    }
  });
</script>
@stop