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

<div class="position-relative">
  <div class="">
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

    <div class="d-flex flex-wrap justify-content-between align-items-md-center mb-4">
      <div class="d-flex align-items-center mb-3 mb-md-0">
        <h4 class="mb-0">{{ __('startup.startup_profile_heading') }}</h4>
        <h6 class="mb-0 ms-2">
          ( {{ $company && $company->CreatedTax == '1' ? __('startup.registered') : __('startup.not_registered') }} )
        </h6>
      </div>
      <div class="d-flex align-items-center gap-3">
        @if( $_user->hasAnyRole('Admin') || ( $_user->ableTo('company.profile_view_mine', $permissions) && $_user->hasRole('registered') && $_user->company_id == $company->id ) )
          <a href="/dashboard/myCompany/{{ $company->id }}" class="btn btn-outline-primary">
            {{ __('startup.edit_info') }} <i class="ti ti-settings ms-2"></i>
          </a>
        @else
          <button data-bs-toggle="modal" data-bs-target="#MessageModal" class="btn btn-label-primary">
            {{ __('admin.Contact') }} <i class="ti ti-message ms-2"></i>
          </button>
        @endif
        @php $mvpLink = trim((string) ($company->mvp_prototype_link ?? '')); @endphp
        @if ($mvpLink !== '')
        <a href="{{ $mvpLink }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary shadow-sm border">
          {{ __('startup.view_prototype') }} <i class="ti ti-eye ms-2"></i>
        </a>
        @endif
      </div>
    </div>

    @include('admin.clients.startup.partials._startup_info')

    @php
      $activeKey = request()->has('valuation_date') ? 5 : 0;
    @endphp
    
    <nav class="nav-align-top mb-8">
      <ul class="nav nav-pills flex-column flex-lg-row flex-lg-nowrap overflow-x-auto gap-2 gap-lg-0" role="tablist">
        @foreach ($pills as $key => $pill)
          <li class="nav-item">
            <a
              href="#{{ $pill }}"
              class="nav-link px-4 {{ $key === $activeKey ? 'active' : '' }}"
              data-bs-toggle="tab"
              role="tab"
              aria-controls="{{ $pill }}"
              aria-selected="true"
            >
              <i class="ti-sm ti ti-{{ $pillsIcons[$key] }} me-1"></i>
              {{ __('startup.' . $pill) }}
            </a>
          </li>
        @endforeach
      </ul>
    </nav>

    <div class="tab-content p-0 rounded mb-6">
      <!-- Overview Section -->
      <section class="tab-pane fade {{ $activeKey == 0 ? 'show active' : '' }}" id="overview" role="tabpanel" aria-labelledby="overview-tab">
        @include('admin.clients.startup.partials._startup_overview')
      </section>
      
      <!-- Milestones Section -->
      <section class="tab-pane fade" id="milestones" role="tabpanel" aria-labelledby="milestones-tab">
        @include('admin.clients.startup.partials._startup_milestones')
      </section>

      <!-- Team Section -->
      <section class="tab-pane fade" id="team" role="tabpanel" aria-labelledby="team-tab">
        <h5 class="mb-4">{{ __('startup.employees_statistics') }}</h5>
        <div class="row g-6 mb-6">
          @foreach ($employeesKpis as $kpi)
            <div class="col-md-4">
              <div class="card h-100 card-border-shadow-{{ $kpi['color'] }}">
                <div class="card-body">

                  {{-- Header: icon + value --}}
                  <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                      <span class="avatar-initial rounded bg-label-{{ $kpi['color'] }}">
                        <i class="ti {{ $kpi['icon'] }} ti-28px"></i>
                      </span>
                    </div>
                    <h4 class="mb-0">{{ $kpi['value'] }}</h4>
                  </div>

                  {{-- Label --}}
                  <p class="mb-1">{{ $kpi['title'] }}</p>

                  {{-- Sub-row: split (total) or percentage (male/female) --}}
                  @if ($kpi['type'] === 'split')
                    @if ($kpi['show_bar'])
                      <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        @if ($kpi['has_male'])
                          <span class="badge bg-label-primary">
                            <i class="ti ti-gender-male me-1"></i>{{ $kpi['male_value'] }}M
                          </span>
                        @endif
                        @if ($kpi['has_female'])
                          <span class="badge bg-label-info">
                            <i class="ti ti-gender-female me-1"></i>{{ $kpi['female_value'] }}F
                          </span>
                        @endif
                        <small class="text-muted">
                          @if ($kpi['has_male'] && $kpi['has_female'])
                            {{ $kpi['male_pct'] }}% / {{ $kpi['female_pct'] }}%
                          @elseif ($kpi['has_male'])
                            {{ $kpi['male_pct'] }}%
                          @else
                            {{ $kpi['female_pct'] }}%
                          @endif
                        </small>
                      </div>
                      <div class="progress" style="height: 5px;">
                        @if ($kpi['has_male'])
                          <div class="progress-bar bg-primary" style="width: {{ $kpi['male_pct'] }}%"></div>
                        @endif
                        @if ($kpi['has_female'])
                          <div class="progress-bar bg-info" style="width: {{ $kpi['female_pct'] }}%"></div>
                        @endif
                      </div>
                    @endif
                  @else
                    @if (!$kpi['na'])
                      <p class="mb-2">
                        <span class="badge bg-label-{{ $kpi['color'] }}">{{ $kpi['pct'] }}%</span>
                        <small class="text-muted ms-1">{{ __('startup.of_total') }}</small>
                      </p>
                      <div class="progress" style="height: 3px;">
                        <div class="progress-bar bg-{{ $kpi['color'] }}" style="width: {{ $kpi['pct'] }}%"></div>
                      </div>
                    @endif
                  @endif

                </div>
              </div>
            </div>
          @endforeach
        </div>

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
      <section class="tab-pane fade {{ $activeKey == 5 ? 'show active' : '' }}" id="financial_report" role="tabpanel" aria-labelledby="financial-report-tab">
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
                    {{ __('admin.Screening') }}:
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
      // The pitch deck is shown by the iframe in the pitch-deck tab; this viewer has no canvas
      // on the page, so it is given no URL (it used to request the file for nothing).
      var url = "";
      
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
    // $('#save-screening-btn').on('click', function() {
    //   $('#warningModal').modal('show');
    // });
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
        
        if (type === 'publish') {
          let missingScores = false;
          const scoreSelects = document.querySelectorAll('.score-select');
          const finalComments = document.getElementById('final_comments');

          scoreSelects.forEach(select => {
            if (!select.value || select.value === '') {
              missingScores = true;
              select.classList.add('is-invalid');
            } else {
              select.classList.remove('is-invalid');
            }
          });

          if (missingScores) {
            releaseAlert('warning', {
              hideCancel: true,
              title: '{{ __("admin.required_evaluation_scores_message") }}',
            });
            // Navigate to the criteria tab 
            document.getElementById('pills-screening-criteria-tab').click();
            return;
          }

          if (finalComments && finalComments.value.trim() === '') {
            finalComments.classList.add('is-invalid');
            releaseAlert('warning', {
              hideCancel: true,
              title: '{{ __("admin.required_evaluation_comment_message") }}',
            });
            // Navigate to the final comments tab
            document.getElementById('pills-final-comments-tab').click();
            return;
          } else if (finalComments) {
            finalComments.classList.remove('is-invalid');
          }
        }

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
            // Reset buttons only on draft; on successful publish keep disabled until reload
            if (type === 'draft') {
              saveBtn.innerHTML = originalSaveBtnHtml;
              saveBtn.disabled = false;
            } else if (!response.status) {
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

            if( response.status ) {
              releaseAlert('success', {
                title: '{{ __("admin.Success") }}',
                message: successMsg,
              });
              if (type === 'publish') {
                setTimeout(function() { window.location.reload(); }, 2500);
              }
            } else {
              releaseAlert('danger', {
                title: '{{ __("admin.Error") }}',
                message: response.message || '{{ __("admin.unexpected_error") }}',
              });
              return;
            }
          },
          error: function(xhr, status, error) {
            const errorMsg = xhr?.responseJSON?.message || error?.message || '{{ __("admin.unexpected_error") }}';

            // Reset buttons
            if (type === 'draft') {
              saveBtn.innerHTML = originalSaveBtnHtml;
              saveBtn.disabled = false;
            } else {
              publishBtn.innerHTML = originalPublishBtnHtml;
              publishBtn.disabled = false;
            }

            releaseAlert('danger', {
              title: '{{ __("admin.Error") }}',
              message: errorMsg,
            });
          },
        });
      }
    </script>
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
