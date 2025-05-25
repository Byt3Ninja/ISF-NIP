@php
  // Show the canvas if the query parameter 'screening' is set to 'true'
  $showCanvas = request()->query('screening') === 'true';
@endphp

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
          @if( $evaluationForm->isPublished == true )
          <button onclick="event.preventDefault();" class="btn btn-primary disabled" disabled>
            <i class="ti ti-check me-2"></i> Published
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
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="pills-screening-criteria-tab" data-bs-toggle="pill"
                data-bs-target="#pills-screening-criteria" type="button" role="tab" aria-controls="pills-screening-criteria"
                aria-selected="true">
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
            <div class="tab-pane fade show active" id="pills-screening-criteria" role="tabpanel"
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
                            <div class="accordion" id="criteria_{{ $key }}">
                              @foreach( $criteria->subCriteria as $sub_key => $subCriteria )
                              <div class="accordion-item">
                                <h2 class="accordion-header">
                                  <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $key . '_' . $sub_key }}" aria-expanded="true" aria-controls="collapse{{ $key . '_' . $sub_key }}">
                                    {{ $subCriteria->name }}
                                  </button>
                                </h2>
                                <div id="collapse{{ $key . '_' . $sub_key }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#criteria_{{ $key }}">
                                  <div class="accordion-body">
                                    <div class="form-group mb-4">
                                      <label for="score_{{ $subCriteria->id }}" class="form-label">{{ __('admin.Score') }}</label>
                                      <select class="form-select" id="score_{{ $subCriteria->id }}" name="score_{{ $subCriteria->id }}">
                                        <option value="">{{ __('admin.Select score') }}</option>
                                        @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}" {{ $subCriteria?->result?->score == $i ? 'selected' : '' }}>{{ $i }}</option> @endfor
                                      </select>
                                    </div>
                                    <div class="form-group">
                                      <label for="comment_{{ $subCriteria->id }}" class="form-label">{{ __('admin.Comment') }}</label>
                                      <textarea class="form-control" id="comment_{{ $subCriteria->id }}" name="comment_{{ $subCriteria->id }}" rows="3"
                                        placeholder="{{ __('admin.Enter your comment here...') }}">{{ $subCriteria?->result?->comment ?? '' }}</textarea>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              @endforeach
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
                <div class="col-3">
                  <div class="border border-dark rounded px-3 py-2">
                    <h6 class="mb-0 text-secondary fw-semibold">{{ __('admin.Screening')}}</h6>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                      <span class="fs-5 fw-bold mb-0 text-dark">24</span>
                      <span class="fs-5 fw-bold text-primary mb-0">{{ __('admin.Point')}}</span>
                    </div>
                  </div>
                </div>
                <div class="col-3">
                  <div class="border border-dark rounded px-3 py-2">
                    <h6 class="mb-0 text-secondary fw-semibold">{{ __('admin.Screening')}}</h6>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                      <span class="fs-5 fw-bold mb-0 text-dark">24</span>
                      <span class="fs-5 fw-bold text-primary mb-0">{{ __('admin.Point')}}</span>
                    </div>
                  </div>
                </div>
                <div class="col-3">
                  <div class="border border-dark rounded px-3 py-2">
                    <h6 class="mb-0 text-secondary fw-semibold">{{ __('admin.Screening')}}</h6>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                      <span class="fs-5 fw-bold mb-0 text-dark">24</span>
                      <span class="fs-5 fw-bold text-primary mb-0">{{ __('admin.Point')}}</span>
                    </div>
                  </div>
                </div>
                <div class="col-3">
                  <div class="border border-dark rounded px-3 py-2">
                    <h6 class="mb-0 text-secondary fw-semibold">{{ __('admin.Screening')}}</h6>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                      <span class="fs-5 fw-bold mb-0 text-dark">24</span>
                      <span class="fs-5 fw-bold text-primary mb-0">{{ __('admin.Point')}}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-12 mb-5 border-bottom pb-4">
                  <h5 class="text-primary fw-semibold">{{ __('admin.All Comments')}}</h5>
                  <div class="d-flex flex-column mb-4">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-primary rounded-pill">1</span>
                      <span class="fs-5 mb-0 text-dark">Market Fit</span>
                    </div>
                    <small class="text-light line-clamp-3 mt-2 w-75">
                      Lorem, ipsum dolor sit amet consectetur adipisicing elit.
                      At expedita ab dolore odit et iure aperiam, quibusdam dolorum
                    </small>
                  </div>
                  <div class="d-flex flex-column">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-primary rounded-pill">2</span>
                      <span class="fs-5 mb-0 text-dark">Financial Viability</span>
                    </div>
                    <small class="text-light line-clamp-3 mt-2 w-75">
                      Lorem, ipsum dolor sit amet consectetur adipisicing elit.
                      At expedita ab dolore odit et iure aperiam, quibusdam dolorum
                    </small>
                  </div>
                </div>
              </div>
              <div class="col-12 mb-5">
                <h5 class="text-primary fw-semibold">{{ __('admin.All Notes')}}</h5>
                <div class="d-flex flex-column mb-4">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill">1</span>
                    <span class="fs-5 mb-0 text-dark">Market Fit</span>
                  </div>
                  <small class="text-light line-clamp-3 mt-2 w-75">
                    Lorem, ipsum dolor sit amet consectetur adipisicing elit.
                    At expedita ab dolore odit et iure aperiam, quibusdam dolorum
                  </small>
                </div>
                <div class="d-flex flex-column">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill">2</span>
                    <span class="fs-5 mb-0 text-dark">Financial Viability</span>
                  </div>
                  <small class="text-light line-clamp-3 mt-2 w-75">
                    Lorem, ipsum dolor sit amet consectetur adipisicing elit.
                    At expedita ab dolore odit et iure aperiam, quibusdam dolorum
                  </small>
                </div>
              </div>
              <div class="col-12">
                <h5 class="text-primary fw-semibold mb-2">{{ __('admin.Final Comments')}}</h5>
                <small class="text-light line-clamp-3 mt-2 w-75">
                  Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                  Ad minus, optio repellat odit pariatur accusantium, quas,
                  natus tenetur delectus voluptas tempora sint corporis. Autem omnis
                  maxime cumque blanditiis labore perferendis.
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endif
