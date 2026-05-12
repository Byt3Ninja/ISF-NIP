@extends('layouts.admin')

@section('style')
  <link 
      rel="stylesheet" 
      type="text/css"
      href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css"
    />
	<style>
    .training-image {
      border-radius: 50%;
      border: 3px solid #ccc;
      padding: 0.2rem;
    }
  
    .w-15 {
      width: 15%;
    }
  
    .custom-tag {
      padding-block: 0.125rem;
      padding-inline: 0.5rem;
      cursor: pointer;
    }
  </style>
@endsection

@section('content')
	<div class="">
		<div class="">
			<div class="content-overlay"></div>
			{{-- @dd($quizzes) --}}
			@if ($quizzes->isNotEmpty())
			<div class="content-header row">
				@if( session()->has('msg'))
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-{{ session()->get('class') ?? 'danger' }} mb-2" role="alert">
                <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                <div class="d-flex align-items-center">
                  <i class="ti ti-error"></i>
                  <span> {{ session()->get('msg') }} {{ Session::forget('msg') }} </span>
                </div>
              </div>
            </div>
          </div>
				@endif
			</div>
			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="organizations-list-table">
						<div class="card">
              <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('admin.My Quizzes') }}</h5>
              </div>
							<div class="card-content">
								<div class="card-body">
									<div class="table-responsive">
										<table id="organizations-list-datatable" class="table">
											<thead>
												<tr class="table-light">
													<th> {{ __('admin.quizTitle') }} </th>
													<th> {{ __('admin.quizNumOfQuestions') }} </th>
													<th> {{ __('admin.quizStartsAt') }} </th>
													<th> {{ __('admin.quizEndsAt') }} </th>
													<th> {{ __('admin.quizStatus') }} </th>
													<th> {{ __('admin.TimeLimit') }} </th>
													<th> {{ __('admin.quizAttempts') }} </th>
													<th> {{ __('admin.passingCriteria') }} </th>
												</tr>
											</thead>
											<tbody>
												@foreach( $quizzes ?? collect([]) as $quiz )
													<tr>
														<td>
															@if( $_user->ableTo('quizzes.view_overview_mine', $permissions) )
															<a href="{{ route('quizzes.view_overview_mine', ['id' => $quiz->training_id, 'quiz_id' => $quiz->id]) }}"> {{ $quiz->title }} </a>
															@else
															{{ $quiz->title }}
															@endif
														</td>
														<td> {{ $quiz->questions->count() }} </td>
														<td> {{ $quiz->start->format('jS M. Y h:i A') }} </td>
														<td> {{ $quiz->end->format('jS M. Y h:i A') }} </td>
														<td>
															@php $attempts = $quiz->myAttempts; @endphp
															@if( $attempts->where('score', '>=', $quiz->passing_criteria)->count() >= 1 )
																<span class="badge bg-label-success"> {{ __('admin.quizPassed') }} </span>
															@elseif( now()->format('Y-m-d H:i:s') > $quiz->end->format('Y-m-d H:i:s') && $attempts->count() == 0 )
																<span class="badge bg-label-danger"> {{ __('admin.quizNotTaken') }} </span>
															@elseif( $attempts->where('score', '<', $quiz->passing_criteria)->where('status', true)->count() >= $quiz->attempts )
																<span class="badge bg-label-danger"> {{ __('admin.quizFailed') }} </span>
															@elseif( now()->format('Y-m-d H:i:s') > $quiz->end->format('Y-m-d H:i:s') )
																<span class="badge bg-label-danger"> {{ __('admin.quizFailed') }} </span>
															@elseif( now()->format('Y-m-d H:i:s') < $quiz->start->format('Y-m-d H:i:s') )
																<span class="badge bg-secondary"> {{ __('admin.quizNotStartedYet') }} </span>
															@elseif( $quiz->in_progress == true )
																<span class="badge bg-label-primary"> {{ __('admin.quizInProgress') }} </span>
															@else
																<span class="badge bg-label-primary"> {{ __('admin.quizAvailable') }} </span>
															@endif
														</td>
														<td> {{ $quiz->time_limit > 60 ? intval($quiz->time_limit / 60) . ' ' . __('admin.hours') . ' ' . ($quiz->time_limit % 60 > 0 ? intval($quiz->time_limit % 60) . ' ' . __('admin.minutes') : '')  : $quiz->time_limit . ' ' . __('admin.minutes') }} </td>
														<td> {{ $quiz->myAttempts->count() }} / {{ $quiz->attempts }} </td>
														<td> {{ $quiz->passing_criteria . '%' }} </td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>
			@else
			<section class="row">
				<div class="col-xl-12 col-md-12 col-sm-12">
				  <div class="card px-4">
					<div class="card-header border-bottom mb-4">
					  <h4 class="card-title">{{ __('cycles.nosubscripe') }}</h4>
					</div>
					<div class="card-body">
					  {{ __('admin.No Quizzes added yet') }}, <a href="/dashboard">{{ __('cycles.Back') }}</a>.
					</div>
				  </div>
				</div>
			  </section>
			@endif
		</div>
	</div>
@endsection

@section('pagescripts')
	<script src="/app-assets/js/scripts/pages/page-organizations.js"></script>
	<script src="/app-assets/js/scripts/modal/components-modal.js"></script>
@endsection
