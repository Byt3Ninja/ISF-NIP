<div class="col-12">
    <div class="row g-6">
      <!-- Card Border Shadow -->
      <a href="/dashboard/users#search=Mentor" class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-primary h-100">
          <div class="card-body" style="display: grid; place-items: center;">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti ti-school ti-28px"></i>
                </span>
              </div>
              <h4 class="mb-0">{{ $Total_Survey }}</h4>
            </div>
            <p class="mb-1">{{ __('admin.Total_Servey') }}</p>
          </div>
        </div>
      </a>
      <a href="/dashboard/users#search=Mentor" class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-secondary h-100">
          <div class="card-body" style="display: grid; place-items: center;">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-secondary">
                  <i class="ti ti-search ti-28px"></i>
                </span>
              </div>
              <h4 class="mb-0">{{ $Survey_Done }}</h4>
            </div>
            <p class="mb-1">{{ __('admin.Done') }}</p>
          </div>
        </div>
      </a>
      <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-warning h-100">
          <div class="card-body" style="display: grid; place-items: center;">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti ti-calendar-event ti-28px"></i>
                </span>
              </div>
              <h4 class="mb-0">{{ $Survey_NotDone }} </h4>
            </div>
            <p class="mb-1">{{ __('admin.NotDone') }}</p>
          </div>
        </div>
      </a>
      
      
    </div>
  </div>
  
  <div class="col-12 mt-10">
    <div class="row">
      <div class="col-md-12">
        <div>
          <h4 class="card-title mb-4">
            <i class="ti ti-chart-donut ti-28px"></i>
            <span class="mx-2">{{ __('admin.Mentor_Sessions_survey') }}:</span>
          </h4>
          <div class="card-body">
            <div class="card">
              <div class="table-responsive">
                <table class="table" id="userstable">
                  <thead class="bg-label-secondary">
                    <tr>
                      <th class="text-center">#</th>
                      <th class="text-center">{{ __('admin.Mentor') }}</th>
                      <th class="text-center">{{ __('admin.Startup') }}</th>
                      <th class="text-center">{{ __('admin.Prorgam_name') }}</th>
                      <th class="text-center">{{ __('admin.Orgranization') }}</th>
                      <th class="text-center">{{ __('admin.Session_date') }}</th>
                      <th class="text-center">{{ __('admin.Survey_Report') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                        $c = 1;
                    @endphp
                    @foreach ($mentorRequests as $session)
                    @php
                              $available_surv = \App\SurveyResponses::where('user_id', $session->mentor_id)->where('triggered_at', $session->id)->where('triggered_by', 'session')->first();
                              @endphp
                      <tr>
                        <td class="text-center">{{ $c++ }}</td>
                        <td class="text-center">
                          <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 text-nowrap">
                              <img 
                                src="{{ $session->mentor->image_url }}" alt="avatar" class="rounded-circle"
                                height="40" width="40" 
                              />
                              {{ $session->mentor->name }}
                            </div>
                          </div>
                        </td>
                        <td class="text-center"><div class="d-flex align-items-center">
                            <div class="media-left mr-50">
                              <img 
                                src="{{ $session->user->company->logo }}" alt="avatar" class="rounded-circle"
                                height="40" width="40" 
                              />
                              {{ $session->user->company->{'name_' . $l} }}
                              
                            </div>
                          </div></td>
                        <td class="text-center">  {{ $session->cycle->title }}
                            
                        </td>
                        <td class="text-center">
                          {{ $session->orgnization->{'name_' . $l} }}
                        </td>
                        <td class="text-center">
                          {{ $session->zoom_date }}
                        </td>
                        
                        <td class="text-center">
                            
                            @if(isset($available_surv) && $available_surv->responses && count($available_surv->responses) > 0)
    <button data-id="{{ $available_surv->id }}" class="btn btn-sm btn-label-success open-responses"> {{ __('admin.answered') }} </button>
@elseif(isset($available_surv))
    <button class="btn btn-sm btn-label-danger"> {{ __('admin.notAnswered') }} </button>
@endif

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
    </div>
  </div>

  <div class="modal fade" id="questionsModal" tabindex="-1" role="dialog" aria-labelledby="modalScrollableTitle" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalScrollableTitle"> {{ __('admin.surveyAnswersDetails') }} </h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-8 col-lg-8 col-sm-8">
							<div class="table-responsive">
								<table class="table table-bordered table-hover">
									<thead>
										<tr>
											<th> {{__('admin.question')}} </th>
											<th class="w-25 text-center px-0"> {{__('admin.question_Type')}} </th>
										</tr>
									</thead>
									<tbody id="question-picker-tbody"></tbody>
								</table>
							</div>
						</div>
						<div class="col-md-4 col-lg-4 col-sm-4">
							<div class="py-1 px-2" id="modelPreviewArea">
								<div class="text-center"> {{ __('admin.previewAreaPlaceholder') }} </div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-md btn-dark" data-bs-dismiss="modal">
					<span class="d-none d-sm-block">{{__('messages.Close')}}</span>
				</button>
			</div>
		</div>
	</div>
</div>
  
