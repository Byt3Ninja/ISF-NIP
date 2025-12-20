@php
  // Show the canvas if the query parameter 'screening' is set to 'true'
  $showCanvas = request()->query('screening') === 'true';
  $hasGuidelines = !empty($evaluationForm->screening_guidelines) || !empty($evaluationForm->screening_guidelines_file);
@endphp

<style>
  .subcriteria-container .nav-tabs .nav-link {
    padding: 0.75rem 1rem;
    border-radius: 0.375rem 0.375rem 0 0;
    transition: all 0.2s ease-in-out;
  }
  
  .subcriteria-container .nav-tabs .nav-link:not(.active):hover {
    background-color: rgba(67, 89, 113, 0.04);
  }
  
  .subcriteria-container .tab-content .card {
    border-top-left-radius: 0;
    box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.15);
    margin-bottom: 1.5rem;
  }
  
  /* Styles for responsive sub-criteria display */
  .subcriteria-container .nav-tabs {
    flex-wrap: nowrap;
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(67, 89, 113, 0.2) transparent;
  }
  
  .subcriteria-container .nav-tabs::-webkit-scrollbar {
    height: 5px;
  }
  
  .subcriteria-container .nav-tabs::-webkit-scrollbar-thumb {
    background: rgba(67, 89, 113, 0.2);
    border-radius: 10px;
  }
  
  .subcriteria-container .nav-tabs::-webkit-scrollbar-track {
    background: transparent;
  }
  
  .subcriteria-container .nav-tabs .nav-item {
    min-width: 150px;
    flex-shrink: 0;
  }
  
  .subcriteria-container .nav-tabs .nav-link {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  /* Dropdown for mobile and many criteria */
  .subcriteria-dropdown {
    display: none;
    margin-bottom: 1rem;
  }
  
  /* For many sub-criteria, style the tabs better */
  .subcriteria-container.many-criteria .nav-tabs .nav-item {
    min-width: 120px;
  }
  
  /* Horizontal scrolling indicators */
  .subcriteria-container .nav-tabs.has-more-right::after {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 30px;
    background: linear-gradient(to right, rgba(255,255,255,0), rgba(255,255,255,0.9));
    pointer-events: none;
    z-index: 1;
  }
  
  .subcriteria-container .nav-tabs.has-more-left::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 30px;
    background: linear-gradient(to left, rgba(255,255,255,0), rgba(255,255,255,0.9));
    pointer-events: none;
    z-index: 1;
  }
  
  /* Responsive adjustments */
  @media (max-width: 992px) {
    .subcriteria-dropdown {
      display: block;
    }
    .subcriteria-container .subcriteria-tabs {
      display: none;
    }
  }
  
  @media (max-width: 768px) {
    .subcriteria-container .tab-content .card .card-body {
      padding: 1rem;
    }
  }
  
  /* When there are 5 or more sub-criteria */
  @media (min-width: 993px) {
    .subcriteria-container.many-criteria .nav-tabs {
      position: relative;
      padding: 0 15px;
    }
  }

</style>

@if( !empty( $evaluationForm ) && $evaluationForm->status == 'Published' )
<div class="screening-gavel" data-bs-target="#rightModalCanvas" data-bs-toggle="offcanvas" aria-controls="rightModalCanvas"
  onclick="document.querySelector('.template-customizer-open-btn').style.display = 'none';">
  <img src="/icons/not-started.svg" />
</div>


<div class="offcanvas offcanvas-end {{ $showCanvas ? 'show' : '' }}" tabindex="-1" id="rightModalCanvas"
  aria-labelledby="rightModalCanvasLabel" style="width: 50vw;">
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
              <button class="nav-link" id="pills-form-data-tab" data-bs-toggle="pill"
                data-bs-target="#pills-form-data" type="button" role="tab" aria-controls="pills-form-data"
                aria-selected="false">
                {{ __('admin.Submissions')}}
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link {{ !$hasGuidelines ? 'active' : '' }}" id="pills-screening-criteria-tab" data-bs-toggle="pill"
                data-bs-target="#pills-screening-criteria" type="button" role="tab" aria-controls="pills-screening-criteria"
                aria-selected="{{ !$hasGuidelines ? 'true' : 'false' }}">
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
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('pills-form-data-tab').click()">
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
                                    <a target="_blank" href="{{ URL::to('/'. $dataEntry->answer) }}"> {{ __('admin.Download File') }} </a>
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
                                <div class="subcriteria-dropdown">
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
                                <ul class="nav nav-tabs nav-fill subcriteria-tabs" role="tablist">
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
                                    <div class="card">
                                      <div class="card-body">
                                        <div class="row">
                                          <div class="col-12 col-md-6">
                                            <div class="form-group mb-4">
                                              <label for="score_{{ $subCriteria->id }}" class="form-label">{{ __('admin.Score') }}</label>
                                              <select class="form-select score-select mb-1" id="score_{{ $subCriteria->id }}" name="score_{{ $subCriteria->id }}">
                                                <option value="">{{ __('admin.Select score') }}</option>
                                                <option value="1" {{ $subCriteria?->result?->score == 1 ? 'selected' : '' }}>1</option>
                                                <option value="2" {{ $subCriteria?->result?->score == 2 ? 'selected' : '' }}>2</option>
                                                <option value="3" {{ $subCriteria?->result?->score == 3 ? 'selected' : '' }}>3</option>
                                                <option value="4" {{ $subCriteria?->result?->score == 4 ? 'selected' : '' }}>4</option>
                                                <option value="5" {{ $subCriteria?->result?->score == 5 ? 'selected' : '' }}>5</option>
                                              </select>
                                              <small class="text-muted">
                                                {{ $l == 'en' ? "'Score 1 = Lowest, 5 = Highest'" : "'1 = الأدنى، 5 = الأعلى'" }}
                                              </small>
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
                                <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                <span class="align-middle d-sm-inline-block d-none">{{ __('admin.Previous') }}</span>
                              </button>
                              <button type="button" class="btn btn-primary btn-next" {{ $loop->last ? 'disabled' : '' }}>
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
                <label for="is_approved" class="form-label">{{ __('admin.Your Decision')}}</label>
                <select class="form-select" name="is_approved" id="is_approved">
                  <option value="" {{ is_null( $evaluationForm->is_approved ) ? 'selected' : '' }}>{{ __('admin.Not Decided yet') }}</option>
                  <option value="1" {{ ( $evaluationForm->is_approved === 1 || $evaluationForm->is_approved === true ) ? 'selected' : '' }}>{{ __('admin.Decision Approved') }}</option>
                  <option value="0" {{ ( $evaluationForm->is_approved === 0 || $evaluationForm->is_approved === false ) ? 'selected' : '' }}>{{ __('admin.Decision Disapproved') }}</option>
                </select>
              </div>
              <div class="form-group">
                <label for="final_comments" class="form-label">{{ __('admin.Final Comments')}}</label>
                <textarea 
                  oninput="this.value = this.value.replace(/^\s+/g, '')"
                  class="form-control" 
                  id="final_comments" 
                  name="final_comments" 
                  rows="16"
                  placeholder="{{ __('admin.Enter your comments here...')}}"
                  >{{ $evaluationForm->final_comment ?? '' }}</textarea>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-view-evaluation" role="tabpanel" aria-labelledby="pills-view-evaluation-tab">
              <div class="row mb-6">
                @foreach( $evaluationForm->categories as $key => $criteria )
                <div class="col-3 pb-3">
                  <div class="border border-dark rounded px-3 py-2">
                    <h6 class="mb-0 text-secondary fw-semibold">{{ $criteria->name }}</h6>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                      <span class="fs-5 fw-bold mb-0 text-dark">
                        {{ number_format( ($criteria->subCriteria->sum(function($subCriteria) { return $subCriteria->result->score; }) / ($criteria->subCriteria->count() * 5)) * 100, 2) }}
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
                    <small class="text-light line-clamp-3 mt-2 w-75">{{ $subCriteria->result->comment }}</small>
                  </div>
                  @endforeach
                </div>
                @endforeach
              </div>
              <div class="col-12">
                <h5 class="text-primary fw-semibold mb-2">{{ __('admin.Final Comments')}}</h5>
                <small class="text-light line-clamp-3 mt-2 w-75">{{ $evaluationForm->final_comment }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endif
