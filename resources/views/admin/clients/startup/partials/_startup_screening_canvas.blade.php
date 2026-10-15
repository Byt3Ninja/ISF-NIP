@php
  // Show the canvas if the query parameter 'screening' is set to 'true'
  $showCanvas = request()->query('screening') === 'true';
  $hasGuidelines = (trim(strip_tags($evaluationForm->screening_guidelines ?? '')) !== '') || !empty($evaluationForm->screening_guidelines_file);

  // The evaluation is "ready" only when its form is published AND has at least
  // one criterion configured. When it is not ready we still show the judge an
  // icon (a warning instead of the gavel) that, on click, explains evaluation
  // cannot be performed until the criteria are configured. (GV-3786)
  $criteriaCount = ( !empty( $evaluationForm ) && $evaluationForm->categories ) ? $evaluationForm->categories->count() : 0;
  $evaluationReady = !empty( $evaluationForm ) && $evaluationForm->status == 'Published' && $criteriaCount > 0;
@endphp

<style>
  #rightModalCanvas {
    width: 50vw;
  }
  @media screen and (max-width: 768px) {
    #rightModalCanvas {
      width: 100%;
    }
  }

  .selected-score-badge {
    display: none;
    margin-top: 8px;
    font-size: 0.875rem;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
  }
</style>


@if( $evaluationReady )
<div class="screening-gavel" data-bs-target="#rightModalCanvas" data-bs-toggle="offcanvas" aria-controls="rightModalCanvas"
  onclick="document.querySelector('.template-customizer-open-btn').style.display = 'none';">
  <img src="/icons/not-started.svg" />
</div>

<div class="offcanvas offcanvas-end {{ $showCanvas ? 'show' : '' }}" tabindex="-1" id="rightModalCanvas"
  aria-labelledby="rightModalCanvasLabel">
  <div class="offcanvas-body">
    <form id="screening_evaluation_form">
      <div class="d-flex justify-content-between align-items-center mb-6">
        <input type="hidden" id="evaluation_form_id" name="evaluation_form_id" value="{{ $evaluationForm->id }}" />
        <input type="hidden" id="evaluation_program_id" name="evaluation_program_id" value="{{ $company->id }}" />
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        <div class="d-flex align-items-center gap-3">
          @if( !is_null( $evaluationForm ) && !empty( $evaluationForm ) && $evaluationForm->isPublished == true )
          <button onclick="event.preventDefault();" class="btn btn-primary disabled" disabled>
            <i class="ti ti-check me-2"></i> {{ __('admin.Published') }}
          </button>
          @else
          @if( $_user->ableTo('screening.evaluate_publish', $permissions) )
          <button type="submit" class="btn btn-primary" id="publish_screening_btn">
            {{ __('admin.Publish')}}
            <i class="ti ti-world ms-2"></i>
          </button>
          @endif
          @if( $_user->ableTo('screening.evaluate_save_draft', $permissions) )
          <button type="button" class="btn btn-outline-primary" id="save_screening_btn">
            {{ __('admin.Save')}}
            <i class="ti ti-circle-check ms-2"></i>
          </button>
          @endif
          @endif
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <ul class="nav nav-pills mb-3 pb-3 border-bottom" id="pills-tab" role="tablist">
            @if($hasGuidelines)
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="pills-guidelines-tab" data-bs-toggle="pill"
                data-bs-target="#pills-guidelines" type="button" role="tab" aria-controls="pills-guidelines"
                aria-selected="true">
                {{ __('admin.Guidelines') }}
              </button>
            </li>
            @endif
            <li class="nav-item" role="presentation">
              <button class="nav-link {{ !$hasGuidelines ? 'active' : '' }}" id="pills-form-data-tab" data-bs-toggle="pill"
                data-bs-target="#pills-form-data" type="button" role="tab" aria-controls="pills-form-data"
                aria-selected="{{ !$hasGuidelines ? 'true' : 'false' }}">
                {{ __('admin.Submissions')}}
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="pills-screening-criteria-tab" data-bs-toggle="pill"
                data-bs-target="#pills-screening-criteria" type="button" role="tab" aria-controls="pills-screening-criteria"
                aria-selected="false">
                {{ __('admin.Screening Criteria')}}
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="pills-final-comments-tab" data-bs-toggle="pill"
                data-bs-target="#pills-final-comments" type="button" role="tab" aria-controls="pills-final-comments"
                aria-selected="false">
                {{ __('admin.Final Comments')}}
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="pills-view-evaluation-tab" data-bs-toggle="pill"
                data-bs-target="#pills-view-evaluation" type="button" role="tab" aria-controls="pills-view-evaluation"
                aria-selected="false">
                {{ __('admin.View Evaluation')}}
              </button>
            </li>
          </ul>
          <div class="tab-content shadow-none p-2" id="pills-tabContent">
            @if($hasGuidelines)
            <div class="tab-pane fade show active" id="pills-guidelines" role="tabpanel" aria-labelledby="pills-guidelines-tab">
              <div class="row mb-6">
                <div class="col-12">
                  <h5 class="mb-4">{{ __('admin.Screening Guidelines') }}</h5>
                  <div class="mb-4">
                    {!! $evaluationForm->screening_guidelines !!}
                  </div>
                  @if(!empty($evaluationForm->screening_guidelines_file))
                  <div class="mb-4">
                    <iframe src="{{ url($evaluationForm->screening_guidelines_file) }}" style="width: 100%; height: 500px; border: none;"></iframe>
                  </div>
                  @endif
                  <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" id="continue-btn">
                      {{ __('admin.Continue') }} <i class="ti ti-arrow-right ms-2"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            @endif
            <div class="tab-pane fade {{ !$hasGuidelines ? 'show active' : '' }}" id="pills-form-data" role="tabpanel" aria-labelledby="pills-form-data-tab">
              <div class="row mb-6">
                  <div class="col-12">
                    <h5 class="mb-0">
                      {{  __('admin.Submissions') . ' ' .  __('cycles.for') }}  {{ trim( $formTitle ) ?  ' | ' . trim( $formTitle ) : '' }}
                    </h5>
                  </div>
                  <div class="col-12 my-6">
                    <div class="container-fluid px-0">
                      @if( $formData->count() > 0 )
                        <div class="accordion" id="formSubmissionAccordion">
                          @foreach( $formData as $index => $dataEntry )
                            <div class="accordion-item">
                              <h2 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                  {{ $dataEntry->question }}
                                </button>
                              </h2>
                              <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#formSubmissionAccordion">
                                <div class="accordion-body pt-4">
                                  @if( $dataEntry->field_type == 'file' )
                                    @if( $dataEntry->file_url )
                                      <a target="_blank" href="{{ $dataEntry->file_url }}"> {{ __('admin.Download File') }} </a>
                                    @else
                                      --
                                    @endif
                                  @else
                                    {{ $dataEntry->answer }}
                                  @endif
                                  <div class="mt-5">
                                    <div class="form-group">
                                      <label for="formComment_{{ $dataEntry->id }}" class="form-label">{{ __('admin.Comment') }}</label>
                                      <textarea class="form-control resize-none" id="formComment_{{ $dataEntry->id }}" name="formComment_{{ $dataEntry->id }}" rows="6"
                                        placeholder="{{ __('admin.Enter your comment here...') }}">{{ $dataEntry?->comment->comment ?? '' }}</textarea>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      @else
                        <div class="text-center">
                          {{ __('cycles.no_submissions_yet') }}
                        </div>
                      @endif
                    </div>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('pills-screening-criteria-tab').click()">
                      {{ __('admin.Continue') }} <i class="ti ti-arrow-right ms-2"></i>
                    </button>
                  </div>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-screening-criteria" role="tabpanel"
              aria-labelledby="pills-screening-criteria-tab">
              <!-- Content for Screening Criteria -->
              <div class="steps">
                <div class="row">
                  <div class="col-12">
                    <h5 class="mb-0">{{ __('admin.Screening Criteria')}}</h5>
                  </div>
    
                  <!-- Default Wizard -->
                  <div class="col-12 mb-6">
                    <div class="bs-stepper wizard-numbered mt-2 shadow-none p-0">
                      <div class="bs-stepper-header px-0 overflow-auto d-flex flex-nowrap">
                        @foreach ($evaluationForm->categories as $key => $criteria)
                        <div class="step flex-shrink-0" data-target="#_{{ $key }}">
                          <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">{{ $loop->iteration }}</span>
                            <span class="bs-stepper-label">
                              <span class="bs-stepper-title">{{ $criteria->name }}</span>
                              <span class="bs-stepper-subtitle">{{ __('admin.Evaluate') }}</span>
                            </span>
                          </button>
                        </div>
                        @if (!$loop->last)
                        <div class="line flex-shrink-0">
                          <i class="ti ti-chevron-right"></i>
                        </div>
                        @endif
                        @endforeach
                      </div>
                      <div class="bs-stepper-content px-0">
                        @foreach ($evaluationForm->categories as $key => $criteria)
                          <div id="_{{ $key }}" class="content">
                            <div class="content-header mb-4">
                              <h6 class="mb-0">{{ $criteria->name }}</h6>
                              <small>{{ __('admin.Provide your evaluation for') }} {{ $criteria->name }}.</small>
                            </div>
                            <div class="subcriteria-container">
                              <div class="nav-align-top">
                                <!-- Mobile/Responsive Dropdown for Sub-Criteria -->
                                <div class="subcriteria-dropdown d-block d-md-none">
                                  <label class="form-label">{{ __('admin.Select Sub-Criteria') }}</label>
                                  <select class="form-select subcriteria-select" id="subcriteria-select-{{ $key }}" data-criteria-key="{{ $key }}">
                                    @foreach( $criteria->subCriteria as $sub_key => $subCriteria )
                                      <option value="#tab{{ $key . '_' . $sub_key }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $subCriteria->name }}
                                      </option>
                                    @endforeach
                                  </select>
                                </div>
                                
                                <!-- Desktop Tabs for Sub-Criteria -->
                                <ul class="nav nav-tabs nav-fill subcriteria-tabs mb-6" role="tablist">
                                  @foreach( $criteria->subCriteria as $sub_key => $subCriteria )
                                  <li class="nav-item">
                                    <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                      role="tab" data-bs-toggle="tab" 
                                      data-bs-target="#tab{{ $key . '_' . $sub_key }}" 
                                      aria-controls="tab{{ $key . '_' . $sub_key }}" 
                                      aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                      <i class="ti ti-checkbox-circle me-1"></i>
                                      {{ $subCriteria->name }}
                                    </button>
                                  </li>
                                  @endforeach
                                </ul>
                                
                                <div class="tab-content p-0 shadow-none">
                                  @foreach( $criteria->subCriteria as $sub_key => $subCriteria )
                                  <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                    id="tab{{ $key . '_' . $sub_key }}" role="tabpanel">
                                    <div class="card shadow-sm border">
                                      <div class="card-body">
                                        <div class="row">
                                          <div class="col-12">
                                            <div class="form-group mb-6">
                                              <label for="score_{{ $subCriteria->id }}" class="form-label">{{ __('admin.Score') }}</label>
                                              <select class="form-select score-select mb-1 criteria-select" id="score_{{ $subCriteria->id }}" name="score_{{ $subCriteria->id }}" data-subcriteria-id="{{ $subCriteria->id }}">
                                                <option value="">{{ __('admin.Select score') }}</option>
                                                <option value="1" {{ $subCriteria?->result?->score == 1 ? 'selected' : '' }}>1 - {{ __('admin.score_1') }}</option>
                                                <option value="2" {{ $subCriteria?->result?->score == 2 ? 'selected' : '' }}>2 - {{ __('admin.score_2') }}</option>
                                                <option value="3" {{ $subCriteria?->result?->score == 3 ? 'selected' : '' }}>3 - {{ __('admin.score_3') }}</option>
                                                <option value="4" {{ $subCriteria?->result?->score == 4 ? 'selected' : '' }}>4 - {{ __('admin.score_4') }}</option>
                                                <option value="5" {{ $subCriteria?->result?->score == 5 ? 'selected' : '' }}>5 - {{ __('admin.score_5') }}</option>
                                                <option value="6" {{ $subCriteria?->result?->score == 6 ? 'selected' : '' }}>6 - {{ __('admin.score_6') }}</option>
                                                <option value="7" {{ $subCriteria?->result?->score == 7 ? 'selected' : '' }}>7 - {{ __('admin.score_7') }}</option>
                                                <option value="8" {{ $subCriteria?->result?->score == 8 ? 'selected' : '' }}>8 - {{ __('admin.score_8') }}</option>
                                                <option value="9" {{ $subCriteria?->result?->score == 9 ? 'selected' : '' }}>9 - {{ __('admin.score_9') }}</option>
                                                <option value="10" {{ $subCriteria?->result?->score == 10 ? 'selected' : '' }}>10 - {{ __('admin.score_10') }}</option>
                                              </select>
                                              <small class="text-muted">
                                                {{ $l == 'en' ? "'Score 1 = Lowest, 10 = Highest'" : "'1 = الأدنى، 10 = الأعلى'" }}
                                              </small>
                                              <div class="selected-score-badge" id="badge_score_{{ $subCriteria->id }}">
                                                @if($subCriteria?->result?->score)
                                                  @php
                                                    $score = $subCriteria->result->score;
                                                    $badgeClass = $score <= 3 ? 'bg-label-danger' : ($score <= 5 ? 'bg-label-warning' : ($score <= 7 ? 'bg-label-info' : ($score <= 9 ? 'bg-label-primary' : 'bg-label-success')));
                                                  @endphp
                                                  <span class="p-2 fw-semibold badge {{ $badgeClass }}">
                                                    {{ $subCriteria->result->score }} - {{ __('admin.score_' . $subCriteria->result->score) }}
                                                  </span>
                                                @endif
                                              </div>
                                            </div>
                                          </div>
                                          <div class="col-12">
                                            <div class="form-group">
                                              <label for="comment_{{ $subCriteria->id }}" class="form-label">{{ __('admin.Comment') }}</label>
                                              <textarea class="form-control" id="comment_{{ $subCriteria->id }}" name="comment_{{ $subCriteria->id }}" rows="3"
                                                placeholder="{{ __('admin.Enter your comment here...') }}">{{ $subCriteria?->result?->comment ?? '' }}</textarea>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  @endforeach
                                </div>
                              </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between mt-12">
                              <button type="button" class="btn btn-label-secondary btn-prev" {{ $loop->first ? 'disabled' : '' }}>
                                <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} ti-xs me-sm-2 me-0"></i>
                                <span class="align-middle d-sm-inline-block d-none">{{ __('admin.Previous') }}</span>
                              </button>

                              <button type="button" class="btn btn-primary next-btn">
                                <span class="align-middle d-sm-inline-block d-none me-sm-2">{{ __('admin.Next') }}</span>
                                <i class="ti ti-arrow-right ti-xs"></i>
                              </button>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  <!-- /Default Wizard -->
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-final-comments" role="tabpanel" aria-labelledby="pills-final-comments-tab">
              <div class="form-group mb-5">
                <label for="is_approved" class="form-label">{{ __('admin.Your Recommendation')}}</label>
                <select class="form-select" name="is_approved" id="is_approved">
                  <option value="" {{ is_null( $evaluationForm->is_approved ) ? 'selected' : '' }}>{{ __('admin.Not Decided yet') }}</option>
                  <option value="1" {{ ( $evaluationForm->is_approved === 1 || $evaluationForm->is_approved === true ) ? 'selected' : '' }}>{{ __('admin.Decision Approved') }}</option>
                  <option value="0" {{ ( $evaluationForm->is_approved === 0 || $evaluationForm->is_approved === false ) ? 'selected' : '' }}>{{ __('admin.Decision Disapproved') }}</option>
                </select>
              </div>
              <div class="form-group">
                <label for="final_comments" class="form-label">{{ __('admin.Final Comments')}}</label>
                <textarea
                  oninput="this.value = this.value.replace(/^\s+/g, ''); if (this.value.trim() !== '') { this.classList.remove('is-invalid'); }"
                  class="form-control"
                  id="final_comments"
                  name="final_comments"
                  rows="16"
                  placeholder="{{ __('admin.Enter your comments here...')}}"
                  >{{ $evaluationForm->final_comment ?? '' }}</textarea>
              </div>
              <div class="d-flex justify-content-end mt-12">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('pills-view-evaluation-tab').click()">
                  {{ __('admin.Continue') }} <i class="ti ti-arrow-right ms-2"></i>
                </button>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-view-evaluation" role="tabpanel" aria-labelledby="pills-view-evaluation-tab">
              <div class="row mb-6">
                @foreach( $evaluationForm->categories as $key => $criteria )
                <div class="col-6 col-md-3 pb-3">
                  <div id="category-{{ $criteria->id }}" class="d-flex flex-column h-100 border border-dark rounded px-3 py-2">
                    <h6 class="mb-1 text-secondary fw-semibold">{{ $criteria->name }}</h6>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                      <span class="fs-5 fw-bold mb-0 text-dark percentage">
                        {{ number_format( ($criteria->subCriteria->sum(function($subCriteria) { return $subCriteria->result->score; }) / ($criteria->subCriteria->count() * 10)) * 100, 2) }}
                      </span>
                      <span class="fs-5 fw-bold text-primary mb-0">%</span>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
              
              <div class="row">
                @foreach( $evaluationForm->categories as $key => $criteria )
                <div class="col-12 mb-5 border-bottom pb-4">
                  <h5 class="text-primary fw-semibold">{{ $criteria->name . ' ' . __('admin.Comments')}}</h5>
                  @foreach( $criteria->subCriteria as $index => $subCriteria )
                  <div class="d-flex flex-column mb-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-primary rounded-pill">{{ $index + 1 }}</span>
                      <span class="fs-5 mb-0 text-dark">{{ $subCriteria->name }}</span>
                    </div>
                    <small id="comment_summary_{{ $criteria->id }}_{{ $subCriteria->id }}" class="text-light line-clamp-3 mt-2 w-75">{{ $subCriteria->result->comment }}</small>
                  </div>
                  @endforeach
                </div>
                @endforeach
              </div>
              <div class="col-12">
                <h5 class="text-primary fw-semibold mb-2">{{ __('admin.Final Comments')}}</h5>
                <small id="final_comments_summary" class="text-light line-clamp-3 mt-2 w-75">{{ $evaluationForm->final_comment }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@else
<div class="screening-gavel screening-criteria-warning" role="button"
  title="{{ __('admin.criteria_not_configured_title') }}">
  <i class="ti ti-alert-triangle fs-2 text-warning"></i>
</div>
@endif

@push('scripts')
  <script>
    var categories = @json( $evaluationForm->categories ?? [] );

    // Warning icon (shown when the evaluation form isn't ready): explain that
    // the criteria haven't been configured yet, so evaluation can't be done.
    $(document).on('click', '.screening-criteria-warning', function () {
      if (typeof window.releaseWarningAlert === 'function') {
        window.releaseWarningAlert({
          title: '{{ __('admin.criteria_not_configured_title') }}',
          message: '{{ __('admin.criteria_not_configured_message') }}',
        });
      }
    });

    $(document).on('click', '#pills-view-evaluation-tab', function() {
      $.each(categories, function(index, category) {
        var totalScore = 0;
        $.each(category.subCriteria, function(index, subCriteria) {
          totalScore += parseInt( $("#score_" + subCriteria.id).val() );
          $("#comment_summary_" + category.id + "_" + subCriteria.id).html( $("#comment_" + subCriteria.id).val() );
        });
        var percentage = (totalScore / (category.subCriteria.length * 10)) * 100;
        $("#category-" + category.id ).find('.percentage').html( percentage.toFixed(2) );
      });
      $("#final_comments_summary").html( $("#final_comments").val() );
    });
    
    document.addEventListener('DOMContentLoaded', function() {
      // Function to get Bootstrap badge class based on score
      function getBadgeClass(score) {
        if (score <= 3) return 'bg-label-danger';
        if (score <= 5) return 'bg-label-warning';
        if (score <= 7) return 'bg-label-info';
        if (score <= 9) return 'bg-label-primary';
        return 'bg-label-success';
      }

      // Handle score selection changes
      document.querySelectorAll('.score-select').forEach(function(select) {
        select.addEventListener('change', function() {
          var score = this.value;
          var subcriteriaId = this.getAttribute('data-subcriteria-id');
          var badgeContainer = document.getElementById('badge_score_' + subcriteriaId);

          if (score && score !== '') {
            // Show the badge with selected score
            var badgeClass = getBadgeClass(parseInt(score));
            badgeContainer.innerHTML = '<span class="p-2 fw-semibold badge ' + badgeClass + '">' + this.options[this.selectedIndex].text + '</span>';
            badgeContainer.style.display = 'block';
          } else {
            // Hide the badge if no score selected
            badgeContainer.style.display = 'none';
            badgeContainer.innerHTML = '';
          }
        });

        // Trigger change event on page load for pre-selected values
        if (select.value && select.value !== '') {
          select.dispatchEvent(new Event('change'));
        }
      });
    });

   $('#continue-btn').on('click', function () {
      $('.nav-pills .active')
        .parent()
        .next('li')
        .find('button[data-bs-toggle="pill"]')
        .trigger('click');
    });

    $('.next-btn').on('click', function () {
      const $currentContent = $(this).closest('.content');
      const $nextTab = $currentContent
        .find('.subcriteria-tabs .nav-link.active')
        .parent()
        .next('li')
        .find('button[data-bs-toggle="tab"]');

      // If this is the last tab, stop here
      if (!$nextTab.length) {
        console.log('Already on last tab');
        moveToNextStep(); // Move to next stepper content
        return;
      }

      $nextTab.trigger('click');
    });

    function moveToNextStep() {
      const $currentStep = $('.bs-stepper-header .step.active');
      const $nextStep = $currentStep.nextAll('.step').first();

      if (!$nextStep.length) {
        console.log('Already on last step');

        // Show final comments tab
        const $finalTab = $('#pills-final-comments-tab');
        if ($finalTab.length) {
          $finalTab.tab('show'); // Works reliably with Bootstrap tabs
        }

        return;
      }

      // Move stepper
      $nextStep.find('.step-trigger').trigger('click');

      // Get the related content pane
      const target = $nextStep.data('target'); // e.g. "#_2"

      setTimeout(() => {
        const $content = $(target);
        const $firstTab = $content.find('.nav-tabs .nav-link').first();

        if ($firstTab.length) {
          new bootstrap.Tab($firstTab[0]).show();
        }
      }, 0);
    }
  </script>
@endpush
