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
                      <tr>
                        <td class="text-center">{{ $c++ }}</td>
                        <td class="text-center">
                          <div class="d-flex align-items-center">
                            <div class="media-left mr-50">
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
                            <div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ShowSession{{ $session->id }}" >{{__('admin.Show')}}</div>
                            

                        </td>
                      </tr>
                       <!-- Show Customer Modal -->
                    <div class="modal fade" id="ShowSession{{ $session->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                        <div class="modal-content" dir="ltr">
                            <div class="modal-body">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="text-center mb-6">
                                    <h4 class="mb-2">{{__('admin.Survey_Report')}} ( {{ $session->zoom_date }} )</h4>
                                </div>
  
  
                                @php
                            //   $surveyScope = \App\SurveyScopes::whereIn('type', ['mentor', 'both'])->get();
                              $available_surveys = \App\SurveyResponses::where('user_id', $session->mentor_id)->where('triggered_at', $session->id)->where('triggered_by', 'session')->get();
                              @endphp
                              
                              @if($available_surveys->isNotEmpty())
                              @foreach($available_surveys as $scope)
                             
                              @php
                              $surveyorrate = $scope->responses ? json_decode($scope->responses) : null;
                              @endphp
                            
                              @if($surveyorrate)
                              
                              @foreach($surveyorrate as $question_index => $item)
                              <div class="d-flex align-items-center gap-3 pb-2 border-bottom">
                                <span class="badge bg-primary rounded-circle d-flex justify-content-center align-items-center"
                                    style="width: 30px; height: 30px;">
                                    {{ $question_index + 1 }}
                                </span>
                                <h5 class="fw-semibold mb-0">{{ $item->question }}</h5>
                            </div>
                                  {{-- <p>Question ID: {{ $item->id }} <br></p> --}}
                                  

                                  <div class="my-3">
                                    @if( $item->type == 'stars' )
								@php
									$answers = $item->answer;
									
								@endphp
								<div class="h-auto d-flex justify-content-center align-items-center py-4" style="width: fit-content;">
									<div class='stars form-control bg-label-secondary d-flex justify-content-center align-items-center' id="review_stars_{{$question_index}}">
										@for ($i = 5; $i > 0; $i--)
                                        
											<label 
												class="star star-{{ $i }}" 
												for="star-{{ $question_index }}-{{ $i }}"
											></label>
										@endfor
										<span class='float-end reviewFaceFeedBack text-nowrap ms-3'></span>
									</div>
								</div>
								@elseif( $item->type == 'radio' )
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                @php
                                                $answers = is_string($item->answers) ? json_decode($item->answers) : $item->answers;
                                                @endphp
                                                @if($answers)
                                                @foreach ($answers as $key => $answers2)
                                                @if($key == $item->answer)
                                                {{ $answers2->text }}
                                                @endif
                                                @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        @elseif( $item->type == 'answers' )
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    {{ $item->answer }}
                                                </div>
                                            </div>
                                        @endif
                                </div>

                              @endforeach
                              @endif
                              @endforeach
                              @else
                              <p> (ID: {{ $scope->id ?? 'N/A' }} ).</p>
                              @endif

                            </div>
                        </div>
                        </div>
                      </div>
                        <!--/ Show Customer Modal -->
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
  