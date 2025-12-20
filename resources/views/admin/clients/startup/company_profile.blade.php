@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.css" />
@stop

@section('style')
<style>
.resize-none {
  resize: none !important;
}

.radius-15 {
  border-radius: 15px !important;
}

.light-shadow {
  box-shadow: 0px 2px 0px 0px #F2F1F3;
}

.section-3 {
  width: 100%;
}

.card-icon {
  width: 55px;
  height: 55px;
}

.sm-card-icon {
  width: 35px;
  height: 35px;
}

.text-xs {
  font-size: 12px;
}

.flex-container {
  display: flex;
  gap: 10px;
}

.flex-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}


.masonry {
  /* display: flex; */
  /* flex-wrap: wrap; */
  column-count: 1;
  column-gap: 20px;
}

.masonry .card {
  display: inline-block;
  width: 100%;
  margin-bottom: 20px;
  break-inside: avoid;
}

@media screen and (min-width: 768px) {
  .masonry {
    column-count: 2;
    column-gap: 20px;
  }

  .section-3 {
    width: 75%;
  }
}

.screening-gavel {
  position: fixed;
  background-color: var(--bs-primary-border-subtle);
  height: 56px;
  width: 70px;
  display: grid;
  place-items: center;
  top: 45%;
  @if($l == 'en')
    right: 0;
    border-top-left-radius: 35px;
    border-bottom-left-radius: 35px;
  @else
    left: 0;
    border-top-right-radius: 35px;
    border-bottom-right-radius: 35px;
  @endif
  z-index: 1000;
  border: 1px solid var(--bs-primary);
  cursor: pointer;
}
.transposed-icon {
  transform: rotate(360deg);
}
.screening-gavel:hover {
  background-color: var(--bs-white);
  transition: all 0.3s ease-in-out, transform 0.3s ease-in-out;
  transform: scale(1.2);
}

/* Milestones Carousel Styles */
.milestone-card {
  min-width: 0; /* Ensures flex items can shrink */
}

#milestonesContainer {
  transition: transform 0.3s ease;
}

/* Responsive adjustments for milestones */
@media (max-width: 991px) {
  .milestone-card {
    width: 50% !important; /* Show 2 cards on tablet */
  }
}

@media (max-width: 767px) {
  .milestone-card {
    width: 100% !important; /* Show 1 card on mobile */
  }
}

/* Navigation button hover effects */
#milestonesPrev:not(:disabled):hover,
#milestonesNext:not(:disabled):hover {
  background-color: var(--bs-primary);
  color: white !important;
  border-color: var(--bs-primary);
  transform: scale(1.1);
  transition: all 0.2s ease;
}

#milestonesPrev:not(:disabled):hover i,
#milestonesNext:not(:disabled):hover i {
  color: white !important;
}
</style>
@stop

@php
$env = env('APP_ENV');

$pills = ['overview', 'milestones', 'team', 'pitch_deck', 'programs_summary', 'financial_report'];

$pillsIcons = ['briefcase', 'target', 'users', 'file-text', 'components', 'database-dollar'];

if( $_user->hasAnyRole('Admin|organizationManager') || ( $_user->hasRole('registered') && $company?->id === $_user?->company_id ) )
{
  $pills[] = 'startup_growth';
  $pillsIcons[] = 'chart-dots-2';
}
@endphp

@section('content')
@if($_user->hasRole('Judger'))
  @include('admin.clients.startup.partials._startup_screening_canvas')
@endif

<div class="content-wrapper position-relative">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
			@if( session()->has('success') )
			<div class="col-md-12 mb-2">
				<div role="alert" class="alert alert-success alert-dismissible">
					{{ session()->get('success') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</div>
			@endif
			@if( session()->has('error') )
			<div class="col-md-12 mb-2">
				<div role="alert" class="alert alert-danger alert-dismissible">
					{{ session()->get('error') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</div>
			@endif
		</div>
    @if($_user->hasRole('Judger'))
      <div class="d-flex align-items-center gap-3 mb-6">
        <a id="save-screening-btn" class="btn btn-sm btn-icon shadow btn-white rounded-circle">
          <i class="ti ti-chevron-{{ $l == 'en' ? 'left' : 'right' }} ti-md text-dark"></i>
        </a>
        <a id="save-screening-btn" class="d-flex align-items-center gap-2 text-capitalize">
          <i class="ti ti-gavel fs-4 text-primary"></i>
          <strong class="text-dark">{{ __('admin.Screening') }}</strong>
        </a>
        <i class="ti ti-chevrons-{{ $l== 'en' ? 'right' : 'left' }}"></i>
        <div class="d-flex align-items-center gap-2 text-capitalize">
          <i class="ti ti ti-file-text fs-4 text-primary"></i>
          <strong class="text-dark">{{ __('admin.Company profile') }}</strong>
        </div>
      </div>
    @else
      <div class="d-flex flex-wrap justify-content-between align-items-md-center mb-4">
        <div class="d-flex align-items-center mb-3 mb-md-0">
          <h4 class="fw-bold mb-0">{{ __('startup.startup_profile_heading') }}</h4>
          <h6 class="mb-0 ms-2">
            ( {{ $company && $company->CreatedTax == '1' ? __('startup.registered') : __('startup.not_registered') }} )
          </h6>
        </div>
        <div class="d-flex align-items-center gap-3">
          @if( $_user->hasAnyRole('Admin|organizationManager') || ( $_user->ableTo('company.profile_view_mine', $permissions) && $_user->hasRole('registered') && $_user->company_id == $company->id ) )
            <a href="/dashboard/myCompany/{{ $company->id }}" class="btn btn-white text-primary shadow-sm border">
              {{ __('startup.edit_info') }} <i class="ti ti-settings ms-2"></i>
            </a>
          @else
            <button data-bs-toggle="modal" data-bs-target="#MessageModal" class="btn btn-label-primary">
              {{ __('admin.Contact') }} <i class="ti ti-message ms-2"></i>
            </button>
          @endif
        </div>
      </div>
    @endif

    @include('admin.clients.startup.partials._startup_info')
    
    <nav class="nav-align-top mb-8">
      <ul class="nav nav-pills flex-column flex-md-row gap-2 gap-lg-0" role="tablist">
        @foreach ($pills as $key => $pill)
          <li class="nav-item">
            <a href="#{{ $pill }}" 
              class="nav-link {{$key == 0 ? 'active' : ''}}" 
              data-bs-toggle="tab"
              role="tab" 
              aria-controls="{{ $pill }}" 
              aria-selected="true" 
            >
              <i class="ti-sm ti ti-{{ $pillsIcons[$key] }} me-1_5"></i>
              {{ __('startup.' . $pill) }}
            </a>
          </li>
        @endforeach
      </ul>
    </nav>

    <div class="tab-content p-0 rounded mb-6">
      <!-- Overview Section -->
      <section class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
        @include('admin.clients.startup.partials._startup_overview')
        @include('admin.clients.startup.partials._startup_about')
      </section>
      
      <!-- Milestones Section -->
      <section class="tab-pane fade" id="milestones" role="tabpanel" aria-labelledby="milestones-tab">
        @include('admin.clients.startup.partials._startup_milestones')
      </section>
      
      <!-- Team Section -->
      <section class="tab-pane fade" id="team" role="tabpanel" aria-labelledby="team-tab">
        <div class="row">
          @include('admin.clients.startup.partials._startup_founders')
          @include('admin.clients.startup.partials._startup_shareholders')
          @include('admin.clients.startup.partials._startup_investors')
        </div>
      </section>

      <!-- Pitch deck Section -->
      <section class="tab-pane fade" id="pitch_deck" role="tabpanel" aria-labelledby="pitch-deck-tab">
        @include('admin.clients.startup.partials._startup_pitch_deck')
      </section>

      @if( $_user->hasAnyRole('Admin|organizationManager') || ( $_user->hasRole('registered') && $company?->id === $_user?->company_id ) )
      <!-- Startup growth Section -->
      <section class="tab-pane fade" id="startup_growth" role="tabpanel" aria-labelledby="startup-growth-tab">
        @include('admin.clients.startup.partials._startup_growth')
      </section>
      @endif

      <!-- Programs Section -->
      <section class="tab-pane fade" id="programs_summary" role="tabpanel" aria-labelledby="programs-summery-tab">
        @include('admin.clients.startup.partials._startup_programs')
      </section>
      
      <!-- Financial Section -->
      <section class="tab-pane fade" id="financial_report" role="tabpanel" aria-labelledby="financial-report-tab">
        @include('admin.clients.startup.partials._startup_financial_report')
      </section>
    </div>
    {{-- <section class="mt-6 px-4">
      <div class="row">
        @if ($company->cycle)
        @hasanyrole('Admin|organizationManager')
        <div class="card">
          <div class="card-body">
            <h5 class="mb-4">{{ __('admin.Cycle informations') }}</h5>
            <table class="table">
              <tbody>
                <tr>
                  <td class="width-200"> {{ __('admin.Cycle') }}:</td>
                  <td>{{ $company->cycledata->title }}</td>
                </tr>
                <tr>
                  <td>{{ __('admin.Current Step') }}:</td>
                  <td><b>{{ $company->stepdata ? $company->stepdata->arr : '' }}:</b>
                    {{ $company->stepdata ? $company->stepdata->title : '' }}</td>
                </tr>
                <tr>
                  <td>{{ __('admin.Screening result') }}:</td>
                  <td>
                    @php
                    $form = \App\Step::where('cycle_id', $company->cycle)
                    ->where('stype', 1)
                    ->orderBy('arr')
                    ->first(['id']);
                    $weight = $company->companyWeight($company->id, $form?->id, 0);
                    $judges = $company->judges($company->id, $form?->id);
                    $judges = \App\User::whereIn('id', $judges)->get();
                    @endphp
                    <span
                      class="badge badge-light-{{ $weight < 25 ? 'danger' : ($weight < 50 ? 'warning' : 'success') }}">
                      {{ $weight }} %</span>
                  </td>
                </tr>
                <tr>
                  <td>
                    {{ __('admin.Screening by') }}:
                  </td>
                  <td>
                    @if( $judges->count() > 0 )
                    @foreach ($judges as $ju)
                    ({{ $ju->id }})
                    {{ $ju->{'first_name_' . $l} }}
                    {{ $ju->{'last_name_' . $l} }},
                    @endforeach
                    @endif
                  </td>
                </tr>
              </tbody>
            </table>
            @php($fdata = \App\Formdata::where('user_id', $company?->user?->id)->with(['field'])->get())
            <br /><br />
            <h5 class="mb-4 pb-3">
              {{ __('admin.Data collected through Cycle') }}
            </h5>
            @if (count($fdata) > 0)
            <table class="table">
              <tbody>
                @foreach ($fdata as $field)
                <tr>
                  <td class="width-200">{{ $field->question }}:</td>
                  <td>
                    @if( isset( $field?->field?->options?->type ) && $field?->field?->options?->type == 'file' )
                      <a target="_blank" href="{{ URL::to('/'. $field->answer) }}"> {{ __('admin.Download File') }} </a>
                    @else
                      {!! strpos($field->answer, 'http') === 0
                      ? '<a target="_blank" href="' . $field->answer . '"> ' . __('admin.Download File') . ' </a>'
                      : $field->answer !!}
                    @endif
                    @php(
                    $comments = \App\Formdata::where('field_id', $field->field_id)->where('company_id',
                    $company->id)->first(['comments'])->comments
                    )
                    @if ($comments != null)
                    @foreach ($comments as $key => $cc)
                    @if ($cc)
                    <div class="warning">{{ __('admin.comment') }}({{ $key }}):
                      {{ $cc }}</div>
                    @endif
                    @endforeach
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @endif
          </div>
        </div>
        @endrole
        @endif
      </div>
    </section> --}}
  </div>
</div>

<!-- Modal -->
<div class="modal fade text-left" id="MessageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel33">
          {{ __('messages.SendMessageTo') }}
          {{ $company?->user?->{'first_name_' . $l} }}
          {{ $company?->user?->{'last_name_' . $l} }}
          ({{ $company->{'name_' . $l} }}).
        </h4>
      </div>
      <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
        {!! csrf_field() !!}
        {{--  <input type="hidden" name="sender" value="{{ $_user->id }}">  --}}
        <input type="hidden" name="company_id" value="{{ $company->id }}">
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="form-label fs-6 mb-1">{{ __('messages.Subject') }}:</label>
            <input name="subject" type="text" placeholder="{{ __('messages.Subject') }}" class="form-control" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-6 mb-1">{{ __('messages.Message') }}:
            </label>
            <textarea class="form-control" id="message" name="message" rows="8"
              placeholder="{{ __('messages.Write your message') }}" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('messages.Close') }}</span>
          </button>
          <button type="submit" class="btn btn-primary ml-1">
            <span class="d-none d-sm-block">{{ __('messages.Send') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- / Modal -->

<!-- Warning Modal -->
  @include(
    'partials.warning-modal',
    [
      'title' => 'Confirm Save',
      'message' => 'Confirm_Save_Screening',
      'onConfirm' => 'saveScreening()',
    ]
  )
<!--/ Warning Modal -->

<!-- Targeted Market Modal -->
<div class="modal fade" id="targetedMarketModal" tabindex="-1" aria-labelledby="targetedMarketModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="targetedMarketModalLabel">{{ __('admin.Targeted_Market') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
      </div>
      <div class="modal-body d-flex flex-wrap gap-2">
        @if(!empty($countryIds))
        @if (is_array($countryIds) && count($countryIds) > 0)
          @foreach ($countryIds as $countryId)
            <span class="text-xs badge bg-label-light text-dark rounded-pill px-3">
              {{ $targetedCountries[$countryId]->{'name_' . $l} ?? __('admin.N/A') }}
            </span>
          @endforeach
        @else
          <span class="text-xs">{{ __('admin.N/A') }}</span>
        @endif
        @endif
      </div>
    </div>
  </div>
</div>

@stop

@section('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/form-wizard-numbered.js"></script>
  <script src="/vuexy/assets/js/form-wizard-validation.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var url = "{{ $company->pitch_deck }}";
      
      // Check if URL exists and is valid
      if (!url || url.trim() === '' || url === 'null' || url === 'undefined') {
        console.log('No PDF URL provided');
        return;
      }
      
      // Check if PDF.js is available
      if (typeof pdfjsLib !== 'undefined') {
        // Set the worker source for PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        try {
          // Validate URL format
          var testUrl = new URL(url, window.location.origin);
          
          var loadingTask = pdfjsLib.getDocument({
            url: testUrl.href,
            httpHeaders: {
              'Cache-Control': 'no-cache'
            }
          });
          
          loadingTask.promise.then(function(pdf) {
            console.log('PDF loaded successfully');
            pdf.getPage(1).then(function(page) {
              var scale = 1.5;
              var viewport = page.getViewport({ scale: scale });

              var canvas = document.getElementById('pdfViewer');
              if (canvas) {
                var context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                var renderContext = {
                  canvasContext: context,
                  viewport: viewport
                };
                page.render(renderContext);
              }
            }).catch(function(error) {
              console.error('Error rendering PDF page:', error);
            });
          }).catch(function(error) {
            console.error('Error loading PDF:', error);
            // Handle different types of PDF loading errors
            if (error.name === 'InvalidPDFException') {
              console.error('The file is not a valid PDF');
            } else if (error.name === 'MissingPDFException') {
              console.error('PDF file not found');
            } else if (error.name === 'UnexpectedResponseException') {
              console.error('Unexpected server response while loading PDF');
            }
          });
        } catch (urlError) {
          console.error('Invalid URL format:', urlError);
        }
      } else {
        console.error('PDF.js library not loaded. Please check if the script is properly included.');
      }
    });
  </script>

  <script>
    $('#save-screening-btn').on('click', function() {
      $('#warningModal').modal('show');
    });
  </script>

  @if( $_user->hasRole('Judger') && !is_null( $evaluationForm ) && !empty( $evaluationForm ) && $evaluationForm->isPublished == false )
    <script type="text/javascript">
      $('#save_screening_btn').on('click', function(e) {
        e.preventDefault();
        submitJudgerScreeningForm('draft');
      });

      $('#publish_screening_btn').on('click', function(e) {
        e.preventDefault();
        submitJudgerScreeningForm('publish');
      });

      function submitJudgerScreeningForm(type)
      {
        let url = "{{ route('screening.evaluate_save_draft') }}";
        if( type == 'publish' ) { url = "{{ route('screening.evaluate_publish') }}"; }
        $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
        var data = $('#screening_evaluation_form').serializeArray();
        let result = {
          id: null,
          type: type,
          program: null,
          evaluation: [],
          final_comment: ''
        };
        var tempEvalMap = {};
        var tempFormDataMap = {};
        data.forEach(item => {
          const name = item.name;
          const value = item.value;
          if (name === 'evaluation_form_id') {
              result.id = parseInt(value);
          } else if (name === 'evaluation_program_id') {
              result.program = parseInt(value);
          } else if (name === 'is_approved') {
              result.is_approved = value;
          } else if (name === 'final_comments') {
              result.final_comment = value;
          } else if (name.startsWith('formComment_')) {
              const formDataId = name.split('_')[1];
              tempFormDataMap[formDataId] = tempFormDataMap[formDataId] || { id: parseInt(formDataId), comment: '' };
              tempFormDataMap[formDataId].comment = value ? value.trim() : null;
          } else if (name.startsWith('score_')) {
              const evalId = name.split('_')[1];
              tempEvalMap[evalId] = tempEvalMap[evalId] || { id: parseInt(evalId), score: null, comment: '' };
              tempEvalMap[evalId].score = value ? parseInt(value) : null;
          } else if (name.startsWith('comment_')) {
              const evalId = name.split('_')[1];
              tempEvalMap[evalId] = tempEvalMap[evalId] || { id: parseInt(evalId), score: null, comment: '' };
              tempEvalMap[evalId].comment = value;
          }
        });
        result.evaluation = Object.values(tempEvalMap).filter(entry => entry.score !== null || entry.comment !== '');
        result.comments = Object.values(tempFormDataMap).filter(entry => entry.comment !== '');

        // Show a loading indicator
        const saveBtn = document.getElementById('save_screening_btn');
        const publishBtn = document.getElementById('publish_screening_btn');
        const originalSaveBtnHtml = saveBtn.innerHTML;
        const originalPublishBtnHtml = publishBtn.innerHTML;
        
        if (type === 'draft') {
          const saveIcon = saveBtn.querySelector('i');
          if (saveIcon) {
            saveIcon.classList = "spinner-border spinner-border-sm ms-2";
          }
          saveBtn.disabled = true;
        } else {
          const publishIcon = publishBtn.querySelector('i');
          if (publishIcon) {
            publishIcon.classList = "spinner-border spinner-border-sm ms-2";
          }
          publishBtn.disabled = true;
        }

        $.ajax({
          type: 'POST',
          url: url,
          data: result,
          success: function(response) {
            // Reset buttons
            if (type === 'draft') {
              saveBtn.innerHTML = originalSaveBtnHtml;
              saveBtn.disabled = false;
            } else {
              publishBtn.innerHTML = originalPublishBtnHtml;
              publishBtn.disabled = false;
            }
            
            // Update evaluation view with latest data
            if (typeof updateEvaluationView === 'function') {
              updateEvaluationView();
            }
            
            // Navigate to the evaluation tab to show the results
            document.getElementById('pills-view-evaluation-tab').click();
            
            // Show success message
            const successMsg = type === 'publish' 
              ? '{{ __("admin.Evaluation published successfully") }}' 
              : '{{ __("admin.Evaluation saved successfully") }}';
              
            // You can use a toast or alert to show the success message
            alert(successMsg);
            // window.location.reload();
          },
          error: function(error) { 
            // Reset buttons
            if (type === 'draft') {
              saveBtn.innerHTML = originalSaveBtnHtml;
              saveBtn.disabled = false;
            } else {
              publishBtn.innerHTML = originalPublishBtnHtml;
              publishBtn.disabled = false;
            }
            
            console.log("DB Error occurred, please try again");
            alert("Error: " + (error.responseJSON?.message || "Failed to submit evaluation. Please try again."));
          },
        });
      }
    </script>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Handle dropdown selection for sub-criteria
        const subcriteriaSelects = document.querySelectorAll('.subcriteria-select');
        subcriteriaSelects.forEach(select => {
          select.addEventListener('change', function() {
            const target = this.value;
            const tabElement = document.querySelector(target);
            const tabLink = document.querySelector(`[data-bs-target="${target}"]`);
            
            if (tabElement && tabLink) {
              // Deactivate all tabs
              const allTabs = document.querySelectorAll(`.subcriteria-tabs .nav-link[data-bs-target^="#tab${select.dataset.criteriaKey}_"]`);
              allTabs.forEach(tab => {
                tab.classList.remove('active');
                tab.setAttribute('aria-selected', 'false');
              });
              
              // Deactivate all panes
              const allPanes = document.querySelectorAll(`.tab-pane[id^="tab${select.dataset.criteriaKey}_"]`);
              allPanes.forEach(pane => {
                pane.classList.remove('show', 'active');
              });
              
              // Activate the selected tab
              tabLink.classList.add('active');
              tabLink.setAttribute('aria-selected', 'true');
              
              // Activate the selected pane
              tabElement.classList.add('show', 'active');
            }
          });
        });
        
        // Auto-switch to dropdown mode when there are more than 4 sub-criteria
        document.querySelectorAll('.subcriteria-container').forEach(container => {
          const tabs = container.querySelectorAll('.nav-item');
          if (tabs.length > 4) {
            // Add class for many criteria to apply specific styles
            container.classList.add('many-criteria');
            
            const dropdownContainer = container.querySelector('.subcriteria-dropdown');
            const tabsContainer = container.querySelector('.subcriteria-tabs');
            
            // On mobile, show the dropdown (handled by CSS)
            
            // On desktop, make tabs scrollable
            if (window.innerWidth > 992) {
              // Add visual indicator if there are more tabs to scroll
              const tabsNav = container.querySelector('.nav-tabs');
              if (tabsNav) {
                tabsNav.addEventListener('scroll', function() {
                  checkScrollIndicators(tabsNav);
                });
                
                // Initial check
                setTimeout(() => checkScrollIndicators(tabsNav), 100);
              }
            }
          }
        });
        
        // Helper function to check if scroll indicators are needed
        function checkScrollIndicators(element) {
          const isScrollable = element.scrollWidth > element.clientWidth;
          const isScrolledToEnd = element.scrollLeft + element.clientWidth >= element.scrollWidth - 10;
          const isScrolledToStart = element.scrollLeft <= 10;
          
          element.classList.toggle('has-more-right', isScrollable && !isScrolledToEnd);
          element.classList.toggle('has-more-left', isScrollable && !isScrolledToStart);
        }
        
        // Sync tab changes with dropdown
        document.querySelectorAll('.subcriteria-tabs .nav-link').forEach(tab => {
          tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.dataset.bsTarget;
            const criteriaKey = targetId.split('_')[0].replace('#tab', '');
            const dropdown = document.getElementById('subcriteria-select-' + criteriaKey);
            
            if (dropdown) {
              dropdown.value = targetId;
            }
          });
        });

        // Initialize real-time evaluation tab update
        initializeEvaluationUpdater();
      });

      // Initialize real-time evaluation update functionality
      function initializeEvaluationUpdater() {
        // Get all form elements that need to be monitored
        const scoreSelects = document.querySelectorAll('.score-select');
        const commentTextareas = document.querySelectorAll('textarea[id^="comment_"]');
        const finalCommentsTextarea = document.getElementById('final_comments');

        // Add event listeners for real-time updates
        scoreSelects.forEach(select => {
          select.addEventListener('change', updateEvaluationTab);
        });

        commentTextareas.forEach(textarea => {
          textarea.addEventListener('input', debounce(updateEvaluationTab, 500));
          textarea.addEventListener('blur', updateEvaluationTab);
        });

        if (finalCommentsTextarea) {
          finalCommentsTextarea.addEventListener('input', debounce(updateEvaluationTab, 500));
          finalCommentsTextarea.addEventListener('blur', updateEvaluationTab);
        }

        // Initial update of evaluation tab
        updateEvaluationTab();
      }

      // Debounce function to prevent excessive updates while typing
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

      // Main function to update the evaluation tab
      function updateEvaluationTab() {
        updateCategoryPercentages();
        updateEvaluationComments();
        updateFinalCommentsDisplay();
      }

      // Update category percentage scores in real-time
      function updateCategoryPercentages() {
        // Get all categories from the evaluation form
        const evaluationCategories = document.querySelectorAll('#pills-view-evaluation .row.mb-6 .col-3');
        
        evaluationCategories.forEach((categoryElement, categoryIndex) => {
          const categoryTitle = categoryElement.querySelector('h6');
          if (!categoryTitle) return;
          
          const categoryName = categoryTitle.textContent.trim();
          
          // Find all score inputs for this category
          let totalScore = 0;
          let scoreCount = 0;
          
          // Get all criteria containers
          const criteriaContainers = document.querySelectorAll('.content');
          
          criteriaContainers.forEach(container => {
            const containerCategoryName = container.querySelector('.content-header h6');
            if (containerCategoryName && containerCategoryName.textContent.trim() === categoryName) {
              // Find all score selects within this criteria container
              const scoreSelects = container.querySelectorAll('.score-select');
              scoreSelects.forEach(select => {
                if (select.value && select.value !== '') {
                  totalScore += parseInt(select.value);
                  scoreCount++;
                }
              });
            }
          });
          
          // Calculate percentage
          const percentage = scoreCount > 0 ? ((totalScore / (scoreCount * 5)) * 100) : 0;
          
          // Update the display
          const percentageDisplay = categoryElement.querySelector('.fs-5.fw-bold.text-dark');
          if (percentageDisplay) {
            percentageDisplay.textContent = percentage.toFixed(2);
          }
        });
      }

      // Update evaluation comments section in real-time
      function updateEvaluationComments() {
        const commentSections = document.querySelectorAll('#pills-view-evaluation .col-12.mb-5.border-bottom');
        
        commentSections.forEach(section => {
          const categoryHeader = section.querySelector('h5');
          if (!categoryHeader) return;
          
          const categoryName = categoryHeader.textContent.replace(' {{ __("admin.Comments") }}', '').trim();
          const commentItems = section.querySelectorAll('.d-flex.flex-column.mb-4');
          
          commentItems.forEach((item, index) => {
            const subcriteriaNameElement = item.querySelector('.fs-5.mb-0.text-dark');
            if (!subcriteriaNameElement) return;
            
            const subcriteriaName = subcriteriaNameElement.textContent.trim();
            
            // Find the corresponding comment textarea
            let matchingComment = '{{ __("admin.No comment provided") }}';
            
            // Look through all criteria containers
            const criteriaContainers = document.querySelectorAll('.content');
            
            criteriaContainers.forEach(container => {
              const containerCategoryName = container.querySelector('.content-header h6');
              if (containerCategoryName && containerCategoryName.textContent.trim() === categoryName) {
                // Find the subcriteria tabs
                const subcriteriaButtons = container.querySelectorAll('.nav-link');
                subcriteriaButtons.forEach(button => {
                  const buttonText = button.textContent.trim();
                  if (buttonText === subcriteriaName) {
                    // Get the target tab ID
                    const targetId = button.getAttribute('data-bs-target');
                    if (targetId) {
                      const targetTab = document.querySelector(targetId);
                      if (targetTab) {
                        const textarea = targetTab.querySelector('textarea[id^="comment_"]');
                        if (textarea && textarea.value.trim()) {
                          matchingComment = textarea.value.trim();
                        }
                      }
                    }
                  }
                });
              }
            });
            
            // Update the comment display
            const commentDisplay = item.querySelector('.text-light.line-clamp-3');
            if (commentDisplay) {
              commentDisplay.textContent = matchingComment;
            }
          });
        });
      }

      // Update final comments display in real-time
      function updateFinalCommentsDisplay() {
        const finalCommentsTextarea = document.getElementById('final_comments');
        const finalCommentsDisplay = document.querySelector('#pills-view-evaluation .col-12:last-child .text-light.line-clamp-3');
        
        if (finalCommentsTextarea && finalCommentsDisplay) {
          const finalCommentText = finalCommentsTextarea.value.trim() || '{{ __("admin.No final comments provided") }}';
          finalCommentsDisplay.textContent = finalCommentText;
        }
        
        // Alternative selector in case the first one doesn't work
        if (finalCommentsTextarea && !finalCommentsDisplay) {
          const alternativeDisplay = document.querySelector('#pills-view-evaluation h5:contains("{{ __("admin.Final Comments")}}") + small');
          if (alternativeDisplay) {
            const finalCommentText = finalCommentsTextarea.value.trim() || '{{ __("admin.No final comments provided") }}';
            alternativeDisplay.textContent = finalCommentText;
          }
        }
        
        // Most reliable approach - find by the heading text
        if (finalCommentsTextarea) {
          const headings = document.querySelectorAll('#pills-view-evaluation h5');
          headings.forEach(heading => {
            if (heading.textContent.includes('{{ __("admin.Final Comments") }}')) {
              const commentDisplay = heading.nextElementSibling;
              if (commentDisplay && commentDisplay.tagName === 'SMALL') {
                const finalCommentText = finalCommentsTextarea.value.trim() || '{{ __("admin.No final comments provided") }}';
                commentDisplay.textContent = finalCommentText;
              }
            }
          });
        }
      }
    </script>

    <script>
      function saveScreening() {
        // Go back
        window.history.back();
      }
    </script>

    {{-- <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Initial update of the evaluation view with existing data
        updateEvaluationView();
        
        // Update evaluation view when score or comment changes
        const scoreSelects = document.querySelectorAll('.score-select');
        const commentAreas = document.querySelectorAll('textarea[id^="comment_"]');
        const finalCommentArea = document.getElementById('final_comments');
        const nextButtons = document.querySelectorAll('.btn-next');
        const tabLinks = document.querySelectorAll('[data-bs-toggle="pill"]');
        
        // Add event listeners
        scoreSelects.forEach(select => {
          select.addEventListener('change', updateEvaluationView);
        });
        
        commentAreas.forEach(textarea => {
          textarea.addEventListener('blur', updateEvaluationView);
        });
        
        if (finalCommentArea) {
          finalCommentArea.addEventListener('blur', updateEvaluationView);
        }
        
        nextButtons.forEach(button => {
          button.addEventListener('click', updateEvaluationView);
        });
        
        // Previous buttons should also update the view
        const prevButtons = document.querySelectorAll('.btn-prev');
        prevButtons.forEach(button => {
          button.addEventListener('click', updateEvaluationView);
        });
        
        // View Evaluation button in wizard
        const viewEvalButtons = document.querySelectorAll('.view-evaluation-btn');
        viewEvalButtons.forEach(button => {
          button.addEventListener('click', function() {
            updateEvaluationView();
            document.getElementById('pills-view-evaluation-tab').click();
          });
        });
        
        tabLinks.forEach(tab => {
          tab.addEventListener('click', function(e) {
            if (e.target.getAttribute('data-bs-target') === '#pills-view-evaluation') {
              updateEvaluationView();
            }
          });
        });
        
        // Save and Publish buttons trigger evaluation update
        const saveBtn = document.getElementById('save_screening_btn');
        const publishBtn = document.getElementById('publish_screening_btn');
        
        if (saveBtn) {
          saveBtn.addEventListener('click', updateEvaluationView);
        }
        
        if (publishBtn) {
          publishBtn.addEventListener('click', updateEvaluationView);
        }
      });
                                
      function updateEvaluationView() {
        // Collect all evaluation data
        const scoreSelects = document.querySelectorAll('.score-select');
        const commentAreas = document.querySelectorAll('textarea[id^="comment_"]');
        const finalCommentArea = document.getElementById('final_comments');
        
        // Get container elements
        const evaluationSummaryContainer = document.getElementById('evaluation-summary-container');
        const finalCommentsDisplay = document.getElementById('final-comments-display');
        const noEvaluationsMessage = document.getElementById('no-evaluations-message');
        const noFinalComment = document.getElementById('no-final-comment');
        
        // Summary statistics
        const totalScoreElement = document.getElementById('total-score');
        const totalCategoriesElement = document.getElementById('total-categories');
        const totalCriteriaElement = document.getElementById('total-criteria');
        
        // Collect data from the form
        let hasEvaluations = false;
        let totalScore = 0;
        let scoredCriteria = 0;
        
        // Create a comprehensive data structure for evaluations
        const evaluationsData = {};
        const categories = new Set();
        
        // Process scores and comments together to build a combined data structure
        scoreSelects.forEach(select => {
          const criteriaId = select.id.split('_')[1];
          const criteriaName = select.closest('.tab-pane').querySelector('.nav-link.active').textContent.trim();
          const categoryName = select.closest('.content').querySelector('.content-header h6').textContent.trim();
          
          // Initialize category and criteria in the data structure if they don't exist
          if (!evaluationsData[categoryName]) {
            evaluationsData[categoryName] = {};
          }
          
          if (!evaluationsData[categoryName][criteriaName]) {
            evaluationsData[categoryName][criteriaName] = {
              criteriaId,
              score: '',
              comment: ''
            };
          }
          
          // Add score if it exists
          if (select.value) {
            hasEvaluations = true;
            totalScore += parseInt(select.value);
            scoredCriteria++;
            categories.add(categoryName);
            evaluationsData[categoryName][criteriaName].score = select.value;
          }
        });
        
        // Process comments
        commentAreas.forEach(textarea => {
          const criteriaId = textarea.id.split('_')[1];
          const criteriaName = textarea.closest('.tab-pane').querySelector('.nav-link.active').textContent.trim();
          const categoryName = textarea.closest('.content').querySelector('.content-header h6').textContent.trim();
          
          // Initialize category and criteria in the data structure if they don't exist
          if (!evaluationsData[categoryName]) {
            evaluationsData[categoryName] = {};
          }
          
          if (!evaluationsData[categoryName][criteriaName]) {
            evaluationsData[categoryName][criteriaName] = {
              criteriaId,
              score: '',
              comment: ''
            };
          }
          
          // Add comment if it exists
          if (textarea.value.trim()) {
            hasEvaluations = true;
            evaluationsData[categoryName][criteriaName].comment = textarea.value.trim();
          }
        });
        
        // Update evaluation summary section - combined scores and comments
        if (hasEvaluations) {
          if (noEvaluationsMessage) noEvaluationsMessage.style.display = 'none';
          let evalHTML = '';
          
          // For each category
          Object.keys(evaluationsData).forEach(categoryName => {
            // Calculate category total score
            let categoryTotalScore = 0;
            let categoryScoreCount = 0;
            Object.values(evaluationsData[categoryName]).forEach(criteria => {
              if (criteria.score) {
                categoryTotalScore += parseInt(criteria.score);
                categoryScoreCount++;
              }
            });
            
            // Create category header with total score
            evalHTML += `
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">${categoryName}</h5>
                    ${categoryScoreCount > 0 ? 
                      `<div class="d-flex align-items-center gap-2">
                        <span class="text-secondary">{{ __('admin.Category Score') }}:</span>
                        <span class="badge bg-primary rounded-pill fs-6">${categoryTotalScore}/${categoryScoreCount * 5}</span>
                      </div>` : ''}
                  </div>
                </div>
                <div class="card-body">
            `;
            
            // For each criteria in this category
            Object.keys(evaluationsData[categoryName]).forEach(criteriaName => {
              const criteria = evaluationsData[categoryName][criteriaName];
              
              evalHTML += `
                <div class="border-bottom pb-3 mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">${criteriaName}</h6>
                    ${criteria.score ? 
                      `<span class="badge bg-primary rounded-pill">${criteria.score}/5</span>` : 
                      `<span class="badge bg-label-secondary">{{ __('admin.No Score') }}</span>`}
                  </div>
              `;
              
              // Add comment if it exists
              if (criteria.comment) {
                evalHTML += `
                  <div class="mt-2">
                    <p class="mb-0 text-dark">${criteria.comment}</p>
                  </div>
                `;
              } else {
                evalHTML += `
                  <div class="mt-2">
                    <em class="text-muted">{{ __('admin.No comment provided') }}</em>
                  </div>
                `;
              }
              
              evalHTML += `</div>`;
            });
            
            evalHTML += `
                </div>
              </div>
            `;
          });
          
          evaluationSummaryContainer.innerHTML = evalHTML;
        } else {
          if (noEvaluationsMessage) noEvaluationsMessage.style.display = 'block';
        }
        
        // Update final comments
        if (finalCommentArea && finalCommentArea.value.trim()) {
          if (noFinalComment) noFinalComment.style.display = 'none';
          finalCommentsDisplay.innerHTML = finalCommentArea.value.trim();
        } else {
          if (noFinalComment) noFinalComment.style.display = 'block';
        }
        
        // Update summary statistics
        if (totalScoreElement) totalScoreElement.textContent = totalScore;
        if (totalCategoriesElement) totalCategoriesElement.textContent = categories.size;
        if (totalCriteriaElement) totalCriteriaElement.textContent = scoredCriteria;
      }
    </script> --}}

  @endif

  <script>
    // Milestones Carousel Functionality
    document.addEventListener('DOMContentLoaded', function() {
      const milestonesContainer = document.getElementById('milestonesContainer');
      const prevBtn = document.getElementById('milestonesPrev');
      const nextBtn = document.getElementById('milestonesNext');
      
      if (!milestonesContainer || !prevBtn || !nextBtn) return;
      
      const cards = document.querySelectorAll('.milestone-card');
      const totalCards = cards.length;
      let currentIndex = 0;
      
      function getCardsPerView() {
        if (window.innerWidth <= 767) return 1; // Mobile
        if (window.innerWidth <= 991) return 2; // Tablet
        return 3; // Desktop
      }
      
      function updateCarousel() {
        const cardsPerView = getCardsPerView();
        const cardWidth = 100 / cardsPerView;
        const maxIndex = Math.max(0, totalCards - cardsPerView);
        
        // Adjust currentIndex if it exceeds new maxIndex
        if (currentIndex > maxIndex) {
          currentIndex = maxIndex;
        }
        
        const translateX = -(currentIndex * cardWidth);
        milestonesContainer.style.transform = `translateX(${translateX}%)`;
        
        // Update card widths for responsive behavior
        cards.forEach(card => {
          if (cardsPerView === 1) {
            card.style.width = '100%';
          } else if (cardsPerView === 2) {
            card.style.width = '50%';
          } else {
            card.style.width = '33.333%';
          }
        });
        
        // Update button states
        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex >= maxIndex || totalCards <= cardsPerView;
        
        // Update button styles
        prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
        nextBtn.style.opacity = (currentIndex >= maxIndex || totalCards <= cardsPerView) ? '0.5' : '1';
        
        // Hide navigation if not needed
        const navButtons = document.querySelector('.d-flex.align-items-center.gap-2');
        if (navButtons) {
          navButtons.style.display = totalCards <= cardsPerView ? 'none' : 'flex';
        }
      }
      
      // Event listeners
      prevBtn.addEventListener('click', function() {
        if (currentIndex > 0) {
          currentIndex--;
          updateCarousel();
        }
      });
      
      nextBtn.addEventListener('click', function() {
        const cardsPerView = getCardsPerView();
        const maxIndex = Math.max(0, totalCards - cardsPerView);
        if (currentIndex < maxIndex) {
          currentIndex++;
          updateCarousel();
        }
      });
      
      // Handle responsive behavior
      function handleResize() {
        updateCarousel();
      }
      
      window.addEventListener('resize', handleResize);
      
      // Initialize carousel
      updateCarousel();
      
      // Touch/swipe support for mobile
      let startX = 0;
      let isDragging = false;
      
      milestonesContainer.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        isDragging = true;
      });
      
      milestonesContainer.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
      });
      
      milestonesContainer.addEventListener('touchend', function(e) {
        if (!isDragging) return;
        isDragging = false;
        
        const endX = e.changedTouches[0].clientX;
        const diffX = startX - endX;
        const cardsPerView = getCardsPerView();
        const maxIndex = Math.max(0, totalCards - cardsPerView);
        
        // Minimum swipe distance
        if (Math.abs(diffX) > 50) {
          if (diffX > 0 && currentIndex < maxIndex) {
            // Swipe left - next
            currentIndex++;
            updateCarousel();
          } else if (diffX < 0 && currentIndex > 0) {
            // Swipe right - previous
            currentIndex--;
            updateCarousel();
          }
        }
      });
    });
  </script>
@stop
