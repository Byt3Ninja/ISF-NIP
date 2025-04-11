@php
$criterias = [
'market-fit' => 'Market Fit',
'team-strenth' => 'Team Strength',
'financial-viability' => 'Financial Viability',
'sustainability' => 'Sustainability',
'innovation' => 'Innovation',
'scalability' => 'Scalability',
'social-impact' => 'Social Impact',
'technology' => 'Technology',
'business-model' => 'Business Model',
'customer-acquisition' => 'Customer Acquisition',
'competition' => 'Competition',
];
@endphp

<div class="screening-gavel">
  <div data-bs-target="#rightModalCanvas" data-bs-toggle="offcanvas" aria-controls="rightModalCanvas"
    onclick="document.querySelector('.template-customizer-open-btn').style.display = 'none';">
    <img src="/icons/not-started.svg" />
  </div>
</div>

<div class="offcanvas offcanvas-end fade show active" tabindex="-1" id="rightModalCanvas"
  aria-labelledby="rightModalCanvasLabel" style="width: 50vw;">
  <div class="offcanvas-body">
    <form>
      <div class="d-flex justify-content-between align-items-center mb-6">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        <div class="d-flex align-items-center gap-3">
          <button type="submit" class="btn btn-primary">
            {{ __('admin.Publish')}}
            <i class="ti ti-world ms-2"></i>
          </button>
          <button type="button" class="btn btn-outline-primary" data-bs-dismiss="offcanvas">
            {{ __('admin.Save')}}
            <i class="ti ti-circle-check ms-2"></i>
          </button>
        </div>
      </div>

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
                    @foreach ($criterias as $key => $label)
                    <div class="step flex-shrink-0" data-target="#{{ $key }}">
                      <button type="button" class="step-trigger">
                        <span class="bs-stepper-circle">{{ $loop->iteration }}</span>
                        <span class="bs-stepper-label">
                          <span class="bs-stepper-title">{{ $label }}</span>
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
                    @foreach ($criterias as $key => $label)
                    <div id="{{ $key }}" class="content">
                      <div class="content-header mb-4">
                        <h6 class="mb-0">{{ $label }}</h6>
                        <small>{{ __('admin.Provide your evaluation for') }} {{ $label }}.</small>
                      </div>
                      <div class="form-group mb-4">
                        <label for="{{ $key }}-score" class="form-label">{{ __('admin.Score') }}</label>
                        <select class="form-select" id="{{ $key }}-score" name="{{ $key }}_score">
                          <option value="">{{ __('admin.Select score') }}</option>
                          @for ($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="{{ $key }}-comment" class="form-label">{{ __('admin.Comment') }}</label>
                        <textarea class="form-control" id="{{ $key }}-comment" name="{{ $key }}_comment" rows="3"
                          placeholder="{{ __('admin.Enter your comment here...') }}"></textarea>
                      </div>
                      <div class="col-12 d-flex justify-content-between mt-12">
                        <button type="button" class="btn btn-label-secondary btn-prev" {{ $loop->first ? 'disabled' : ''
                          }}>
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
      </div>
      <div class="tab-pane fade" id="pills-final-comments" role="tabpanel" aria-labelledby="pills-final-comments-tab">
        <div class="form-group">
          <label for="final-comments" class="form-label">{{ __('admin.Final Comments')}}</label>
          <textarea class="form-control" id="final-comments" rows="16"
            placeholder="{{ __('admin.Enter your comments here...')}}"></textarea>
        </div>
      </div>
      <div class="tab-pane fade" id="pills-view-evaluation" role="tabpanel" aria-labelledby="pills-view-evaluation-tab">
        <!-- Content for View Evaluation -->
      </div>
    </form>
  </div>
</div>