@extends('layouts.admin')

@section('style')
<link rel="stylesheet" type="text/css"
	href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
@stop

@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">

		{{-- <div class="row mb-6">
			<div class="col-12 col-md-8">
				<h4 class="fw-bold mb-2">{{ $training->title }}</h4>
				<p class="card-text text-muted line-clamp-2 text-truncate">{{ $training->dsc }}</p>
			</div>
		</div> --}}

		@if(session()->has('msg'))
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-3 shadow-sm"
						role="alert">
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						<div class="d-flex align-items-center">
							<i class="ti ti-alert-triangle me-3 ti-md"></i>
							<span> {{ session()->get('msg') }} {{ Session::forget('msg') }} </span>
						</div>
					</div>
				</div>
			</div>
		@endif

		

		<div class="card">
			<div class="card-header">
				<h5 class="card-title border-bottom pb-2">
					<i class="ti text-primary ti-report-analytics ti-lg"></i>
					{{ __('admin.quizResults') }}
				</h5>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-hover align-middle">
						<thead class="table-light">
							<tr>
								<th class="text-center" style="width: 5%;"> # </th>
                                <th class="text-center" style="width: 10%;"> {{ __('admin.Name') }} </th>
                                <th class="text-center" style="width: 10%;"> {{ __('admin.Company') }} </th>
                                <th class="text-center" style="width: 10%;"> {{ __('admin.User') }} </th>
								<th class="text-center" style="width: 10%;"> {{ __('admin.quizScore') }} </th>
								<th class="text-center" style="width: 12%;"> {{ __('admin.quizStatus') }} </th>
								<th> {{ __('admin.quizStartedAt') }} </th>
								<th class="text-center"> {{ __('admin.quizTimeTook') }} </th>
                                <th class="text-center"> {{ __('admin.Training') }} </th>
                                <th class="text-center"> {{ __('admin.Organization') }} </th>
								<th class="text-center" style="width: 18%;"> {{ __('admin.Actions') }} </th>
							</tr>
						</thead>
						<tbody>
							
								@foreach($quizzesResults as $index => $quizzesResult)
								<tr>
									<td class="text-center fw-bold"> {{ $index + 1 }} </td>
                                    <td class="text-center">{{ $quizzesResult->q_quiz_title}}</td>
                                    <td class="text-center">
                                        	<a class="text-dark d-flex align-items-center gap-2"
												href="/dashboard/companyProfile/{{ $quizzesResult->c_company_id }}">
												<img src="{{ $quizzesResult->c_company_logo }}" alt="avatar"
													class="rounded-circle" height="35" width="35" style="border: 2px solid #ddd;"
													onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"
													>
												<span class="fw-medium"> {{ $quizzesResult?->{'c_company_name_' . $l} }}</span>
											</a>
                                    </td>
                                    <td class="text-center">
                                        <a class="text-dark d-flex align-items-center gap-2"
												href="/dashboard/Profile/{{ $quizzesResult->u_user_id }}">
												<img src="{{ $quizzesResult->u_user_img_url }}" alt="avatar"
													class="rounded-circle" height="35" width="35" style="border: 2px solid #ddd;"
													onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"
													>
												<span class="fw-medium"> {{ $quizzesResult->{'u_user_first_name_' . $l} . ' ' . $quizzesResult->{'u_user_last_name_' . $l} }}</span>
											</a>
                                    </td>
									<td class="text-center">
										<span
											class="fw-bold fs-6 {{ $quizzesResult->qr_result_score >= 50 ? 'text-success' : 'text-danger' }}">
											{{ $quizzesResult->qr_result_score }}%
										</span>
									</td>
									<td class="text-center">{!! $quizzesResult->qr_result_status == 1 ? '<span class="badge bg-success rounded-pill">Completed</span>' : '<span class="badge bg-danger rounded-pill">Pending</span>' !!}</td>
									<td>{{ $quizzesResult->qr_result_started_at }}</td>
									<td class="text-center">{{ $quizzesResult->qr_result_ended_at }}</td>
                                    <td class="text-center">{{ $quizzesResult->t_training_title }}</td>
                                    <td class="text-center">{{ $quizzesResult->{'o_org_name_' . $l} }}</td>
									
									<td class="text-center">
										<a href="quizResults/{{ $quizzesResult->qr_result_id }}"
											class="btn btn-label-primary btn-sm rounded-pill shadow-sm">
											<i class="ti ti-eye me-1"></i>
											{{ __('admin.quizViewResults') }}
										</a>
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
@stop
@section('pagescripts')
<script type="text/javascript">


		
</script>
@stop