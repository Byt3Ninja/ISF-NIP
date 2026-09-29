@extends('layouts.admin')

@section('content')
<!-- Content wrapper -->
<div class="">
  <!-- Content -->
  <div class="">

    <!-- Submission Details Header -->
    <div class="d-flex justify-content-between mb-4">
      <h4 class="mb-0 fw-bold">{{ __('admin.Form Submission Details') }}</h4>
      <div class="d-flex align-items-center gap-3">
        <a href="{{ route('form_submission_details.export', ['id' => $id]) }}" target="_blank" class="btn btn-sm btn-label-success btn-excel">
          <i class="fas fa-file-excel"></i> <span class="d-none d-md-inline ms-2">{{ __('admin.exportExcel') }}</span>
        </a>
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
  // console.log(submissionData);
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

    // Render answers grouped by startup
    const answersSection = document.querySelector('.submission-data-section');
    if (answersSection) {
      // Show loading state using Bootstrap spinner
      answersSection.innerHTML = `
        <div class="text-center py-5">
          <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">{{ __('admin.Loading') }}...</span>
          </div>
          <p class="text-muted mb-0">{{ __('admin.Loading submission data') }}...</p>
        </div>
      `;
      
      // Simulate loading delay for better UX
      setTimeout(() => {
        answersSection.innerHTML = '';
        
        // Check if answers exist and are grouped
        if (submissionData.answers && Array.isArray(submissionData.answers) && submissionData.answers.length > 0) {
          submissionData.answers.forEach((startupAnswers, index) => {
            if (Array.isArray(startupAnswers) && startupAnswers.length > 0) {
              // Get startup info from first answer
              const startupInfo = startupAnswers[0];
            
            // Create startup header with Vuexy theme styling
            const startupHeader = `
              <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-white fw-bold">
                      <i class="ti ti-building me-2"></i>
                      ${startupInfo.name}
                    </h6>
                    <div class="d-flex gap-2">
                      <!-- <span class="badge bg-light text-primary">ID: ${startupInfo.user_id}</span> -->
                      <span class="badge bg-light text-primary">${startupAnswers.length} {{ __('admin.The Answers') }}</span>
                    </div>
                  </div>
                </div>
                <div class="card-body p-0">
            `;
            
            answersSection.innerHTML += startupHeader;
            
            // Render each answer for this startup using Bootstrap 5 classes
            startupAnswers.forEach((ans, ansIndex) => {
              let html = '';
              const questionNumber = ansIndex + 1;

              // console.log('ans', ans);
              
              if (ans?.value?.file_url && ans?.value?.file_name) {
                // File field using Bootstrap alert and list group
                let iconClass = 'ti ti-file';
                let alertClass = 'alert-secondary';
                let badgeClass = 'bg-secondary';
                let fileType = 'File';
                
                if (ans?.value?.file_name.match(/\.(pdf)$/i)) {
                  iconClass = 'ti ti-file-type-pdf';
                  alertClass = 'alert-danger';
                  badgeClass = 'bg-danger';
                  fileType = 'PDF';
                } else if (ans?.value?.file_name.match(/\.(xlsx|xls|csv)$/i)) {
                  iconClass = 'ti ti-file-spreadsheet';
                  alertClass = 'alert-success';
                  badgeClass = 'bg-success';
                  fileType = 'Excel';
                } else if (ans?.value?.file_name.match(/\.(jpg|jpeg|png|gif)$/i)) {
                  iconClass = 'ti ti-photo';
                  alertClass = 'alert-info';
                  badgeClass = 'bg-info';
                  fileType = 'Image';
                } else if (ans?.value?.file_name.match(/\.(doc|docx)$/i)) {
                  iconClass = 'ti ti-file-text';
                  alertClass = 'alert-primary';
                  badgeClass = 'bg-primary';
                  fileType = 'Word';
                }
                
                html = `
                  <div class="list-group-item border-0 border-bottom mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <span class="fw-semibold text-primary">${questionNumber}. ${ans.label}</span>
                      <span class="badge ${badgeClass}">${fileType}</span>
                    </div>
                    <div class="alert ${alertClass} mb-0">
                        <a href="${ans?.value?.file_url}" download="${ans?.value?.file_name}" target="_blank" rel="noopener noreferrer" class="text-decoration-none d-flex align-items-center">
                        <i class="${iconClass} me-3 fs-4"></i>
                        <div class="flex-grow-1">
                          <div class="fw-semibold">${ans?.value?.file_name}</div>
                          <small class="text-muted">{{ __('admin.Click to view') }}</small>
                        </div>
                        <i class="ti ti-eye ms-2"></i>
                        </a>
                    </div>
                  </div>
                `;
              } else if (ans.noFileSubmitted) {
                // No file submitted using Bootstrap alert
                html = `
                  <div class="list-group-item border-0 border-bottom mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <span class="fw-semibold text-primary">${questionNumber}. ${ans.label}</span>
                      <span class="badge bg-warning">{{ __('admin.No File') }}</span>
                    </div>
                    <div class="alert alert-warning text-center mb-0">
                      <i class="ti ti-file-off fs-2 mb-2 d-block"></i>
                      <small>{{ __('admin.No file was submitted for this question') }}</small>
                    </div>
                  </div>
                `;
              } else if (ans.value && (ans.label.toLowerCase().includes('notes') || ans.value.length > 100)) {
                // Textarea field using Bootstrap card
                html = `
                  <div class="list-group-item border-0 border-bottom mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <span class="fw-semibold text-primary">${questionNumber}. ${ans.label}</span>
                      <span class="badge bg-info">{{ __('admin.Text') }}</span>
                    </div>
                    <div class="card bg-light border-0">
                      <div class="card-body" style="white-space: pre-wrap; line-height: 1.6;">
                        ${ans.value || '<em class="text-muted">{{ __('admin.No answer provided') }}</em>'}
                      </div>
                    </div>
                  </div>
                `;
              } else {
                // Regular field using Bootstrap list group
                html = `
                  <div class="list-group-item border-0 border-bottom mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <span class="fw-semibold text-primary">${questionNumber}. ${ans.label}</span>
                      <span class="badge bg-label-primary">{{ __('admin.Answer') }}</span>
                    </div>
                    <div class="bg-light rounded p-3">
                      <div class="fw-semibold text-body">
                        ${ans.value || '<em class="text-muted">{{ __('admin.No answer provided') }}</em>'}
                      </div>
                    </div>
                  </div>
                `;
              }
              answersSection.innerHTML += html;
            });
            
            // Close startup section
            answersSection.innerHTML += `
                </div>
              </div>
            `;
            }
          });
        } else {
          // Show empty state using Bootstrap empty state
          answersSection.innerHTML = `
            <div class="text-center py-5">
              <div class="mb-4">
                <i class="ti ti-inbox ti-xl text-muted"></i>
              </div>
              <h5 class="text-muted mb-3">{{ __('admin.No Answers Found') }}</h5>
              <p class="text-muted mb-0">{{ __('admin.No answers have been submitted for this form yet') }}.</p>
            </div>
          `;
        }
      }, 500);
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
          confirmButton: 'btn btn-danger me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: '{{ __('admin.Deleted') }}',
            text: '{{ __('admin.Deleted Successfully') }}',
            icon: 'success',
            showCancelButton: false,
            showConfirmButton: true,
            confirmButtonText: "{{ __('admin.OK') }}",
            customClass: {
                confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          });
        }
        });
      });
    }
  });
</script>
@stop