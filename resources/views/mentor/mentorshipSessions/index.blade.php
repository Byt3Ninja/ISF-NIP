@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if(session()->has('message') || session()->has('msg'))
          <div class="alert alert-success alert-dismissible mb-2" role="alert">
            {{-- <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button> --}}
            <div class="d-flex align-items-center">
              <i class="ti ti-check"></i>
              <span>
                {{ __('admin.' . session()->get('message', session()->get('msg'))) }}
              </span>
            </div>
          </div>
          @endif

          @if(session()->has('message2')|| !empty($errors->all()))
          <div class="alert alert-danger alert-dismissible mb-2" role="alert">
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <div class="d-flex align-items-center">
              <i class="ti ti-error"></i>
              <span>
                {{ __('admin.'.session()->get('message2')) }}
                @forelse($errors->all() as $message)

                {{ $message }}
                @empty
                @endforelse

              </span>
            </div>
          </div>
          @endif
        </section>
        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('admin.tasks') }}</h5>
              </div>
              <div class="card-datatable table-responsive datatable-basic">
                <table class="table mb-0 mt-3" id="userstable">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center">#</th>
                      <th class="text-center">{{__('cycles.Company')}}</th>
                      <th class="text-center">{{__('cycles.User/Title')}}</th>
                      <th class="text-center">{{__('cycles.Rate/Comment')}}</th>
                      <th class="text-center">{{__('cycles.Date/Time')}}</th>
                      <th class="text-center">{{__('mentor.mentorshipTasks.completedTasks')}}</th>
                      <th class="text-center">{{__('mentor.mentorshipTasks.nonCompletedTasks')}}</th>
                      @if( $_user->ableTo('mentorshipTasks.index', $permissions) )<th class="text-center">{{__('mentor.mentorshipTasks.all')}}</th>@endif
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($sessions as $session)
                          
                    <tr>
                      <td class="text-center">{{ $loop->iteration }}</td>
                      <td>
                        @if( $_user->ableTo('company.profile_view', $permissions) )
                        <a 
                          class="d-flex align-items-center gap-2" 
                          href="/dashboard/companyProfile/{{$session->user->company->id}}"
                        >
                          <img 
                            src="{{
                              $session->user->company->logo_url ?? '/images/logo-placeholder.png'
                            }}" 
                            alt="avatar" 
                            class="rounded-circle w-px-40 h-px-40 border border-light"
                            onerror="this.src='/images/logo-placeholder.png';"
                            onerror="if(this.src!='/images/logo-placeholder.png'){this.onerror=null;this.src='/images/logo-placeholder.png';}"
                          >
                          <small class="text-dark">{{$session->user->company->name }}</small>
                        @endif
                      </td>
                      <td>
                        <a 
                          href="@role('Mentor') /dashboard/show/UserProfile/{{$session->user->id}} @else /dashboard/show/myProfile @endrole" 
                          class="d-flex align-items-center gap-2"
                        >
                          <img 
                            src="{{$session->user->image_url ?? '/icons/account.svg'}}" alt="avatar" 
                            class="rounded-circle w-px-40 h-px-40 border border-light"
                            onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                          />
                          <div>
                            <div class="small mb-0 text-dark fw-semibold">{{$session->user->name }} </div>
                            <div class="small text-muted">{{$session->user->title }}</div>
                          </div>
                        </a>
                      </td>

                      <td class="text-center">
                        <small>
                            @if( Auth::user()->hasRole('Mentor') )
                                @php
                                $available_surv = \App\SurveyResponses::where('user_id', $session->mentor_id)
                                    ->where('triggered_at', $session->id)
                                    ->where('triggered_by', 'session')
                                    ->first();
                                @endphp
                                @if(isset($available_surv) && $available_surv->responses && count($available_surv->responses) > 0)
                                    <button data-id="{{ $available_surv->id }}" class="btn btn-sm btn-label-success open-responses"> {{ __('admin.answered') }} </button>
                                @elseif(isset($available_surv))
                                    <a href="{{ route('surveys.take', ['id' => $available_surv->id]) }}" class="btn btn-sm btn-label-dark rounded w-100">{{ __('dashboard.startSurvey') }}</a>
                                @endif
                            @elseif( Auth::user()->hasRole('Registered') )
                                @php
                                $available_surv = \App\SurveyResponses::where('user_id', Auth::id())
                                    ->where('triggered_at', $session->id)
                                    ->where('triggered_by', 'session')
                                    ->first();
                                @endphp
                                @if(isset($available_surv) && $available_surv->responses && count($available_surv->responses) > 0)
                                    <button data-id="{{ $available_surv->id }}" class="btn btn-sm btn-label-success open-responses"> {{ __('admin.answered') }} </button>
                                @elseif(isset($available_surv))
                                    <a href="{{ route('surveys.take', ['id' => $available_surv->id]) }}" class="btn btn-sm btn-label-dark rounded w-100">{{ __('dashboard.startSurvey') }}</a>
                                @endif
                            @endif
                        </small>
                    </td>
                    
                      <td>
                        <div class="d-flex flex-column gap-0 justify-content-center align-items-center">
                          <small class="text-nowrap text-capitalize">{{$session->zoom_date}}</small>
                          <small class="text-nowrap">
                            @if($session->period)
                              ({{ $session->period }} {{ $l == 'en' ? 'Min' : 'دقيقة' }})
                            @endif
                          </small>
                        </div>
                      </td>

                      <td class="text-center"> {{
                        $session->mentorshipTasks()->where('status','completed')->whereNotNull('title')->count() }}
                      </td>
                      <td class="text-center"> {{
                        $session->mentorshipTasks()->where('status','pending')->whereNotNull('title')->count() }}</td>
                      @if( $_user->ableTo('mentorshipTasks.index', $permissions) )
                      <td class="text-center">

                        <a href="{{ route('mentorshipTasks.index',['mentorship_id'=>$session->id]) }}"
                          title="{{ __('mentor.mentorshipTasks.tasks') }}"
                          class="btn btn-sm {{ $session->mentorshipTasks()->whereNull('title')->count()?'btn-label-warning':'btn-primary'  }}">
                          <i class="ti ti-eye me-1"></i>
                          {{ $l == 'en' ? 'View' : 'عرض' }}</a>
                          
                        </td>
                        @endif
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>
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
  <!-- END: Content-->
@stop

@section('pagescripts')
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script>
    $(document).ready(function() {
      $('.table').DataTable({
          "responsive": true, "searching": true, "lengthChange": true, "paging": true, "bInfo": false,
          "columnDefs": [
              {"orderable": false, "targets":  [5]},
          ],
          @if ($l=='ar')
          "language": {
              "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
              "sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
              "sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
              "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
              "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
              "sInfoPostFix":  "", "sSearch":       "ابحث:", "sUrl":          "",
              "oPaginate": {"sFirst":    "الأول", "sPrevious": "السابق", "sNext":     "التالي", "sLast":     "الأخير"},
              "oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}}
          @endif
      });
  });

  
  var responses = @json($available_surveys)

		$(document).on('click', '.open-responses', function() {
			var id = $(this).data('id');
			var responses = @json($available_surveys);
			var response = responses.filter(function(oneResponse) {
				return oneResponse.id == id;
			});
			var responses = response[0].responses;
			var html = '';
			responses.forEach(function(oneResponse) {
				html += '<tr data-id="' + id + '" data-qid="' + oneResponse.id + '" class="cursor-pointer open-question-details">';
				html += '<td>' + oneResponse.question + '</td>';
				html += '<td class="text-center">' + ( oneResponse.type == 'radio' ? "{{ __('admin.surveyTypeRadioButtons') }}" : ( oneResponse.type == 'answers' ? "{{ __('admin.surveyQuestionAndAnswer') }}" : "{{ __('admin.surveyTypeStarRating') }}" ) ) + '</td>';
				html += '</tr>';
			});
			$("#modelPreviewArea").html('<div class="text-center"> {{ __("admin.previewAreaPlaceholder") }} </div>');
			$('#question-picker-tbody').html(html);
			$('#questionsModal').modal('show');
		});

		$(document).on('click', '.open-question-details', function() {
			var id = $(this).data('id');
			var question_id = $(this).data('qid');
			var responses = @json($available_surveys);
			var response = responses.filter(function(oneResponse) {
				return oneResponse.id == id;
			});
			var responses = response[0].responses;
			var question = responses.filter(function(oneResponse) {
				return oneResponse.id == question_id;
			});
			var question = question[0];
			var html = '';
			if( question.type == 'radio' )
			{
				question.answers.forEach( function(item, key) {
					var count = key + 1;
					var checked = parseInt( question.answer ) == key ? 'btn-outline-success' : 'btn-outline-secondary';
					html += '<div class="custom-py-1"> <button class="w-100 text-left btn ' + checked + '">' + count + '. ' + item.text + '</button> </div>';
				});
			}
			else if( question.type == 'answers' )
			{
				question.answers.forEach( function(item, key) {
					var count = key + 1;
					var checked = parseInt( question.answer ) == key ? 'btn-outline-success' : 'btn-outline-secondary';
					html += '<div class="custom-py-1"> '+ question.answer +' </div>';
				});
			}
			else if( question.type == 'stars' )
			{
				var answer = parseInt( question.answer );
				html += '<ul class="custom-star-rating text-center p-0 my-1">';
					for( var i = 0; i < question.answers.length; i++ )
					{
						if( i < answer )
						{
							html += '<li><i class="fas fa-star text-gold"></i> </li>';
						}
						else
						{
							html += '<li><i class="far fa-star text-gold"></i> </li>';
						}
					}
				html += '</ul>';
			}
			$("#modelPreviewArea").html(html);
		});

  </script>
@stop