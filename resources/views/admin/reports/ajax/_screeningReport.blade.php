<div class="col-12">
<div class="d-flex flex-wrap justify-content-center gap-6 mb-8">
	@foreach ($kpiData as $key => $item)
	<div>
		<button 
		type="button"
		class="card card-border-shadow-{{ $item['color'] }} h-100 text-dark screening-kpi-filter-card btn p-0 border-0"
		@if($key == 0)
			data-filter-type="judges"
			onclick="window.location.href='{{ route('users.judger') }}'"
			title="{{ __('cycles.Go to judges page') }}"
		@else
			title="{{ __('cycles.Filter by') . ' ' . __('admin.' . $item['label']) }}"
			data-filter-type="{{ $key == 1 ? 'total' : ($key == 2 ? 'not-assigned' : ($key == 3 ? 'not-evaluated' : ( $key == 4 ? 'draft' : 'published' ))) }}"
			onclick="filterByScreeningKPI(this)"
		@endif
		>
		<div class="card-body py-2" style="display: grid; place-items: center;">
			<div class="d-flex align-items-center mb-2">
			<div class="avatar me-4">
				<span class="avatar-initial rounded bg-label-{{ $item['color'] }}">
				<i class="ti ti-{{ $item['icon'] }} ti-20px"></i>
				</span>
			</div>
			<h5 class="mb-0">{{ $key == 0 ? $judgers : $item['value'] }}</h5>
			</div>
			<small class="text-center">{{ __('admin.' . $item['label']) }}</small>
		</div>
		</button>
	</div>
	@endforeach
</div>
</div>

<!-- Companies Results Section -->
<div class="row">
	<div class="col-12">
		<div class="card border">
			<div class="card-header overflow-hidden">
				<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-6">
					<div>
						<h5 class="card-title mb-1">
						{{ __('cycles.Screening results') }}
						@if(request('program_id') && trim( strtolower( request('program_id') ) ) != 'all')
							<span class="badge bg-label-primary ms-2">
							@php
								$selectedProgram = $cycles->firstWhere('id', request('program_id'));
								echo $selectedProgram ? $selectedProgram->title : '';
							@endphp
							</span>
						@endif
						</h5>
						@if(request('program_id'))
						<p class="text-muted mb-0 small">{{ $l == 'en' ? 'Viewing screening results for selected program' : 'عرض نتائج الفحص للبرنامج المحدد' }}</p>
						@endif
					</div>
					<div class="d-flex flex-wrap gap-2 align-items-center">            
						<!-- Search Input -->
						<div class="search-wrapper position-relative">
						<div class="position-relative">
							<input
							type="text"
							id="screening-company-search"
							class="form-control"
							placeholder="{{ __('cycles.Search by company name, user name, or category...') }}"
							style="padding-left: 2rem; padding-right: 2rem; min-width: 300px;"
							>
							<i class="ti ti-search ti-16px position-absolute top-50 translate-middle-y mx-2 text-muted" style="left: 0;"></i>
							<button id="screening-clear-search" class="btn btn-sm position-absolute top-50 translate-middle-y d-none" style="right: 5px; border: none; background: none; padding: 2px;">
							<i class="ti ti-x ti-14px text-muted"></i>
							</button>
						</div>
						</div>

						<!-- Export Buttons -->
						<div class="btn-group">
							<button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="ti ti-file-export me-2"></i>{{ __('settings.Export') }}
							</button>
							<ul class="dropdown-menu">
								@foreach(['csv', 'excel'] as $key => $option)
									<li>
										<a class="dropdown-item text-uppercase cursor-pointer" id="screening-export-{{$option}}">
											{{$option}}
										</a>
									</li>
								@endforeach
							</ul>
						</div>
					</div>
				</div>

				<!-- Enhanced Filter Section -->
				<div class="row g-2 align-items-center mb-4 pb-3 border-bottom">
					<!-- Program Filter -->
					<div class="col-lg-2 col-md-4 col-sm-6">
						<select id="screening-cycle-filter" class="form-select">
							<option value="">{{ __('cycles.All_Cycles') }}</option>
							@foreach ($cycles as $cycle)
							<option value="{{ $cycle->id }}" {{ request('program_id') == $cycle->id ? 'selected' : '' }}>
								{{ $cycle->title }}
							</option>
							@endforeach
						</select>
					</div>

					<!-- Step Filter -->
					<div class="col-lg-2 col-md-4 col-sm-6">
						<select id="screening-step-filter" class="form-select">
							<option value="">{{ __('cycles.AllـSteps') }}</option>
							@foreach ($steps as $step)
								<option value="{{ $step->id }}">{{ $step->title }}</option>
							@endforeach
						</select>
					</div>

					<!-- Judge Filter -->
					<div class="col-lg-2 col-md-4 col-sm-6">
						<select id="screening-judge-filter" class="form-select">
							<option value="">{{ __('cycles.All Judges') }}</option>
							@foreach ($judges->unique('id') as $judge)
								<option value="{{ $judge->id }}">{{ Str::limit($judge->name, 25) }}</option>
							@endforeach
						</select>
					</div>

					<!-- Score Range Filter -->
					<div class="col-lg-2 col-md-4 col-sm-6">
						<select id="screening-score-filter" class="form-select">
							<option value="">{{ __('cycles.All Scores') }}</option>
							<option value="excellent">{{ __('cycles.Excellent') }} (75-100%)</option>
							<option value="good">{{ __('cycles.Good') }} (50-74%)</option>
							<option value="average">{{ __('cycles.Average') }} (25-49%)</option>
							<option value="poor">{{ __('cycles.Poor') }} (0-24%)</option>
						</select>
					</div>

					<!-- Status Filter -->
					<div class="col-lg-2 col-md-4 col-sm-6">
						<select id="screening-status-filter" class="form-select">
							<option value="">{{ __('cycles.All Status') }}</option>
							<option value="not-assigned">{{ __('admin.Not Assigned') }}</option>
							<option value="published">{{ __('cycles.Published') }}</option>
							<option value="draft">{{ __('admin.In Progress') }}</option>
							<option value="not-evaluated">{{ __('cycles.Not Evaluated') }}</option>
						</select>
					</div>

					<!-- Clear Filters Button -->
					<div class="col-lg-2 col-md-4 col-sm-6">
						<button id="screening-clear-filters" class="btn btn-outline-secondary d-none">
							<i class="ti ti-x me-1"></i>
							{{ __('cycles.Clear') }}
						</button>
					</div>
				</div>
			</div>
			<div class="card-body">
				@if($startups->count() == 0)
					<!-- No Startups Available -->
					<div class="col-12">
						<div class="text-center py-5">
							<p class="text-muted">{{ __('cycles.No_Startups_Screening') }}</p>
						</div>
					</div>
				@else
					<!-- Company Cards Grid -->
					<div id="screening-companies-grid" class="row g-4">
						@foreach ($startups as $company)
							@php
							// Get the evaluation status from judges
							$evaluationStatuses = [];
							$hasPublished = false;
							$hasDraft = false;
							$hasNotEvaluated = false;
							
							foreach ($judges as $judge) {
								// Check if the judge has evaluated this company
								$scoped_company = collect($judge->companies)->where('id', $company->id)->first();
								if ($scoped_company) {
								$status = strtolower($scoped_company->status ?? 'not-evaluated');
								$evaluationStatuses[] = $status;
								
								if ($status === 'published') $hasPublished = true;
								elseif ($status === 'draft') $hasDraft = true;
								else $hasNotEvaluated = true;
								}
							}
							
							// Determine primary status for filtering - use the actual company status from database
							$primaryStatus = strtolower(str_replace(' ', '-', $company->status ?? 'not-evaluated'));
							// Create searchable category string
							$categories = is_string($company->business_categories) ? json_decode($company->business_categories, true) : $company->business_categories;
							$categoryString = is_array($categories) ? implode(' ', array_filter($categories ?? [])) : ($company->business_categories ?? '');
							$categoryString = str_replace(['/', ','], ' ', $categoryString ?? '');
							
							// Calculate average score from judge evaluations
							$totalScore = 0;
							$publishedEvaluations = 0;
							$averageScore = $company?->score ?? 0;
							
							// Calculate average score for display (published only)
							foreach ($judges as $judge) {
								$scoped_company = collect($judge->companies)->where('id', $company->id)->first();
								if ($scoped_company && strtolower($scoped_company->status) === 'published' && isset($scoped_company->score)) {
								$totalScore += (float) $scoped_company->score;
								$publishedEvaluations++;
								}
							}
							
							// if ($publishedEvaluations > 0) {
							//  $averageScore = round($totalScore / $publishedEvaluations, 1);
							// }
							
							// Calculate filter score (includes published AND draft with scores)
							$filterTotalScore = 0;
							$filterEvaluationsCount = 0;
							$filterScore = 0;
							
							foreach ($judges as $judge) {
								$scoped_company = collect($judge->companies)->where('id', $company->id)->first();
								if ($scoped_company && isset($scoped_company->score) && $scoped_company->score !== null && 
									(strtolower($scoped_company->status) === 'published' || strtolower($scoped_company->status) === 'draft')) {
								$filterTotalScore += (float) $scoped_company->score;
								$filterEvaluationsCount++;
								}
							}
							
							if ($filterEvaluationsCount > 0) {
								$filterScore = round($filterTotalScore / $filterEvaluationsCount, 1);
							}
							
							// Get judge IDs for this company
							$companyJudgeIds = [];
							// foreach ($judges as $judge) {
							//   // Check if the judge has evaluated this company
							//   $scoped_company = collect($judge->companies)->where('id', $company->id)->first();
							//   if ($scoped_company) {
							//     $companyJudgeIds[] = $judge->id;
							//   }
							// }
							$companyJudgeIds = $company->judges->pluck('id')->toArray();
							$scoped_judges = $judges->filter(function($judge) use ($company) {
								return collect($judge->companies)->where('id', $company->id)->isNotEmpty();
							})->values();
							
							// Ensure data consistency
							$evaluationStatuses = array_filter($evaluationStatuses);
							@endphp
							
							<div class="col-xl-4 col-lg-6 col-md-12 screening-company-card" 
								data-company-name="{{ strtolower($company->name ?? '') }}"
								data-user-name="{{ strtolower($company?->user?->name ?? '') }}"
								data-category="{{ strtolower($categoryString ?? '') }}"
								data-score="{{ $averageScore ?? 0 }}"
								data-status="{{ $primaryStatus ?? 'not-evaluated' }}"
								data-evaluation-statuses="{{ implode(',', array_filter($evaluationStatuses)) }}"
								data-judge-ids="{{ implode(',', array_filter($companyJudgeIds)) }}"
								data-cycle-id="{{ $company->company_cycle_id }}"
								data-step-id="{{ $company->evaluation_step_id ?? $company->company_step_id }}">
								<div class="card border rounded-3 h-100">
								<!-- Company Header -->
								<div class="card-header bg-transparent border-0 pb-2">
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											@if( $_user->ableTo('company.profile_view', $permissions) ) <a href="/dashboard/companyProfile/{{ $company->id }}" class="text-decoration-none"> @endif
												<div class="company-avatar me-3">
													<img
														src="{{ $company->logo_url }}"
														alt="logo"
														class="rounded-3 shadow-sm w-px-50 h-px-50"
														height="50"
														width="50"
													/>
												</div>
											@if( $_user->ableTo('company.profile_view', $permissions) ) </a> @endif
											<div>
												@if( $_user->ableTo('company.profile_view', $permissions) ) <a href="/dashboard/companyProfile/{{ $company->id }}" class="text-decoration-none"> @endif
													<h6 class="mb-1 fw-bold text-dark">{{ Str::limit($company->name, 20) }}</h6>
												@if( $_user->ableTo('company.profile_view', $permissions) ) </a> @endif
												<small class="text-muted line-clamp-1">
													@php
														$categories = is_string($company->business_categories) ? json_decode($company->business_categories, true) : $company->business_categories;
														$categoryList = is_array($categories) ? implode(', ', array_filter($categories)) : ($company->business_categories ?? '');
														$categoryList = str_replace('/', ', ', $categoryList);
													@endphp
													{{ $categoryList }}
												</small>
											</div>
										</div>
										<!-- Average Score Badge with better styling -->
										<div class="text-end">
											<div class="ms-3">
												<div
													class="circular-progress position-relative d-inline-flex align-items-center justify-content-center"
													style="width: 44px; height: 44px"
												>
													<div
														class="progress-circle position-absolute w-100 h-100"
														style="
															background: conic-gradient(
															{{ $averageScore >= 75 ? 'var(--bs-success)' : ($averageScore >= 50 ? 'var(--bs-info)' : ($averageScore >= 25 ? 'var(--bs-warning)' : 'var(--bs-danger)')) }}
															{{ $averageScore * 3.6 }}deg,
															#e9ecef {{ $averageScore * 3.6 }}deg
															);
															border-radius: 50%;
														"
													></div>
													<span class="fw-bold text-dark position-relative" style="font-size: 0.875rem"> {{ number_format($averageScore, 1) }} % </span>
												</div>
												<div class="small text-muted text-nowrap">{{ __('cycles.Avg Score') }}</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Company Body -->
								<div class="card-body px-3">
								<!-- User Information -->
									<div class="user-info bg-label-light border rounded-3 p-2 mb-4">
										<div class="d-flex align-items-center">
											@if( $_user->ableTo('company.profile_view', $permissions) ) <a href="/dashboard/companyProfile/{{ $company->id }}" class="text-decoration-none"> @endif
												<img
													src="{{ $company?->user?->image_url ?? '/icons/account.svg' }}"
													alt="img"
													class="rounded-circle me-3 shadow-sm"
													height="40"
													width="40"
												/>
											@if( $_user->ableTo('company.profile_view', $permissions) )</a> @endif
											<div class="flex-grow-1">
												@if( $_user->ableTo('company.profile_view', $permissions) ) <a href="/dashboard/companyProfile/{{ $company->id }}" class="text-decoration-none"> @endif
													<h6 class="mb-0 fw-semibold text-dark">{{ $company?->user?->name }}</h6>
													<small class="text-muted">{{ $company?->user?->title ?: __('cycles.No title') }}</small>
												@if( $_user->ableTo('company.profile_view', $permissions) ) </a> @endif
											</div>
										</div>
									</div>

									<!-- Program and Step Information -->
									<div class="mt-2 mb-4">
										<div class="d-flex flex-wrap gap-1">
											@php
												$cycleForChip = ($company->stepdata && $company->stepdata->cycle) ? $company->stepdata->cycle : ($company->cycledata ?? null);
												$programUrl = ($cycleForChip && isset($_user) && $_user->ableTo('programs.view', $permissions ?? []))
													? url('dashboard/programlist/show/'.$cycleForChip->id) : null;
											@endphp
											@if($cycleForChip)
												<{{ $programUrl ? 'a' : 'small' }} @if($programUrl) href="{{ $programUrl }}" @endif
															class="badge bg-label-info text-info rounded-pill px-2 py-1 small text-decoration-none"
															title="{{ __('cycles.Program') }}: {{ $cycleForChip->title }}"
															data-bs-toggle="tooltip" data-bs-placement="top">
													<i class="ti ti-components ti-16px me-1"></i>
													{{ Str::limit($cycleForChip->title, 23) }}
												</{{ $programUrl ? 'a' : 'small' }}>
											@endif
											@if($company->stepInfo)
												@php
													// Screening report: green when the startup is currently on an
													// initial screening step (stype 2), red otherwise. Every step type
													// links to its own manage page, each gated by that page's own
													// permission. The {id} each route expects differs by type — screening,
													// training and final take the STEP id; the custom form takes the
													// step's BUILDER FORM id; mentorship assignment takes the CYCLE id —
													// so passing the wrong id would 404 (GV-3841). Types with no manage
													// page fall through to a plain non-clickable badge.
													$stepInfo = $company->stepInfo;
													$stype = (int) $stepInfo->stype;
													$onStage = $stype === 2;
													$stepChipClass = $onStage ? 'bg-label-success text-success' : 'bg-label-danger text-danger';
													$stepUrl = stepManageUrl($stepInfo, $_user ?? null, $permissions ?? []);
												@endphp
												<{{ $stepUrl ? 'a' : 'small' }} @if($stepUrl) href="{{ $stepUrl }}" @endif
															class="badge {{ $stepChipClass }} rounded-pill px-2 py-1 small text-decoration-none"
															title="{{ __('cycles.Step') }}: {{ $stepInfo->title }}"
															data-bs-toggle="tooltip" data-bs-placement="top">
													<i class="ti ti-step-into ti-16px me-1"></i>
													{{ Str::limit($stepInfo->title, 18) }}
												</{{ $stepUrl ? 'a' : 'small' }}>
											@endif
										</div>
									</div>

								<!-- Judges Evaluation -->
								<div class="judges-evaluation">
									<h6 class="fw-semibold mb-3 text-dark d-flex align-items-center">
									<i class="ti ti-users me-2 text-primary"></i>
									<span>{{ __('cycles.Judges Evaluation') }}</span>
									<span class="badge bg-label-primary bg-opacity-10 text-primary ms-2 rounded-pill">
										{{ $company->judges->count() }}
									</span>
									</h6>
									<div class="judges-list">
									@foreach ($company->judges->take(3) as $judge)
										@php($scoped_company = collect($judge->companies)->where('id', $company->id)->first())
										@if( $scoped_company ) {{-- Check if judge has evaluated this company --}}
										<div class="judge-evaluation-item bg-transparent rounded-3 border px-2 py-1 mb-2">
											<div class="d-flex align-items-center justify-content-between">
											<div class="d-flex align-items-center">
												<div class="status-icon me-2">
												@if( $scoped_company->status == 'Published' )
													<i class="ti ti-circle-check text-success fs-5" title="Published"></i>
												@elseif ($scoped_company->status == 'Draft')
													<i class="ti ti-clock text-warning fs-5" title="Draft"></i>
												@else
													<i class="ti ti-circle-x text-danger fs-5" title="Not Evaluated"></i>
												@endif
												</div>
												<div>
												<span class="fw-medium text-dark">{{ Str::limit($judge->name, 15) }}</span>
												<div class="small text-muted">{{ __('cycles.' . $scoped_company->status) }}</div>
												</div>
											</div>
											<div class="evaluation-score text-end">
												<div class="score-badge badge {{ $scoped_company->score >= 75 ? 'border-success bg-label-success' : ($scoped_company->score >= 50 ? 'border-info bg-label-info' : ($scoped_company->score >= 25 ? 'border-warning bg-label-warning' : 'border-danger bg-label-danger')) }} px-2 py-1 border">
												{{ $scoped_company->score }}%
												</div>
											</div>
											</div>
										</div>
										@endif
									@endforeach
									@if( $company->judges->count() > 3 )
										<div class="text-center">
										<small class="text-muted">+{{ $company->judges->count() - 3 }} {{ __('cycles.more judges') }}</small>
										</div>
									@endif
									</div>
								</div>
								</div>

								<!-- Company Footer -->
								<div class="card-footer bg-transparent border-0 pt-0">
								<div class="d-grid gap-2">
									<button
									class="screening-show-evaluation btn btn-primary btn-sm rounded-pill shadow-sm"
									data-company="{{ $company->id }}"
									data-step="{{ $company->evaluation_step_id ?? $company->company_step_id }}"
									>
									<i class="ti ti-eye me-2"></i>
									{{ __('cycles.View Complete Evaluation') }}
									</button>
								</div>
								</div>
							</div>
							</div>
						@endforeach
					</div>

					<!-- No Results State (Hidden by default, shown via JavaScript when filters do not match) -->
					<div id="screening-no-results-state" class="d-none">
						<div class="text-center py-5">
							<p class="text-muted">{{ __('cycles.No_Startups_Match_Filters') }}</p>
						</div>
					</div>
				@endif
			</div>
		</div>
	</div>
</div>

<!-- Evaluation Modal -->
<div class="modal fade" id="screeningEvaluationModal" tabindex="-1" aria-labelledby="screeningEvaluationModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title fw-bold" id="screeningEvaluationModalLabel">
					<i class="ti ti-clipboard-check me-2"></i>
					{{ __('cycles.Company Evaluation Details') }}
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="screeningEvaluationModalBody">
				<!-- Content will be loaded dynamically -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
					<i class="ti ti-x me-2"></i>
					{{ __('cycles.Close') }}
				</button>
			</div>
		</div>
	</div>
</div>

<script>
	// Global variables for filtering (Regular Screening - namespaced to avoid conflicts)
	// Only declare if not already declared to avoid redeclaration errors
	if (typeof screeningCompanyCards === 'undefined') {
		var screeningCompanyCards = [];
	}
	if (typeof screeningCurrentFilters === 'undefined') {
		var screeningCurrentFilters = {
			search: '',
			cycle: '',
			step: '',
			judge: '',
			score: '',
			status: ''
		};
	}

	$(document).ready(function() {
		// Only initialize if not already initialized
		if (screeningCompanyCards.length === 0) {
			// Initialize company cards array
			screeningCompanyCards = $('.screening-company-card').toArray();
			
			// Initialize search and filter functionality
			if (typeof initializeScreeningSearchAndFilters === 'function') {
				initializeScreeningSearchAndFilters();
			}
			
			// Hide no-results state initially (will be shown by updateDisplay if needed)
			$('#screening-no-results-state').addClass('d-none');
			
			// Set initial filter indicators
			if (typeof updateScreeningFilterIndicators === 'function') {
				updateScreeningFilterIndicators();
			}
			
			// Enable debug mode by setting: window.debugFilters = true in browser console
			if (window.debugFilters) {
				console.log('Regular Screening filtration system initialized with', screeningCompanyCards.length, 'companies');
				
				// Show status distribution
				const statusCounts = {};
				screeningCompanyCards.forEach(card => {
					const status = $(card).data('status') || 'undefined';
					statusCounts[status] = (statusCounts[status] || 0) + 1;
				});
				console.log('Status distribution:', statusCounts);
			}
		}

		// Add debug function to window for troubleshooting (only if not already defined)
		if (typeof window.debugScreeningFilters === 'undefined') {
			window.debugScreeningFilters = function() {
				console.log('=== Screening Filter Debug Info ===');
				console.log('Total company cards:', screeningCompanyCards.length);
				console.log('Current filters:', screeningCurrentFilters);
				console.log('Visible cards:', $('.screening-company-card:visible').length);
				
				// Sample data from first few cards
				screeningCompanyCards.slice(0, 5).forEach((card, index) => {
					const $card = $(card);
					console.log(`Card ${index + 1} data:`, {
						companyName: $card.data('company-name'),
						userName: $card.data('user-name'),
						category: $card.data('category'),
						score: $card.data('score'),
						status: $card.data('status'),
						statusRaw: $card.attr('data-status'),
						evaluationStatuses: $card.data('evaluation-statuses'),
						evaluationStatusesRaw: $card.attr('data-evaluation-statuses'),
						judgeIds: $card.data('judge-ids'),
						cycleId: $card.data('cycle-id'),
						stepId: $card.data('step-id')
					});
				});
				
				// Show unique status values
				const allStatuses = screeningCompanyCards.map(card => $(card).data('status')).filter(s => s);
				const uniqueStatuses = [...new Set(allStatuses)];
				console.log('Unique status values found:', uniqueStatuses);
			};
		}
	});

	// Define function only if it doesn't exist
	if (typeof initializeScreeningSearchAndFilters === 'undefined') {
		window.initializeScreeningSearchAndFilters = function() {
			// Search input handler with debounce
			let searchTimeout;
			$('#screening-company-search').on('input', function() {
				clearTimeout(searchTimeout);
				const searchValue = $(this).val().trim();
				
				// Show/hide clear search button
				if (searchValue) {
					$('#screening-clear-search').removeClass('d-none');
				} else {
					$('#screening-clear-search').addClass('d-none');
				}
				
				searchTimeout = setTimeout(() => {
					screeningCurrentFilters.search = searchValue.toLowerCase();
					applyScreeningFilters();
				}, 300);
			});

			// Filter change handlers
			$('#screening-cycle-filter, #screening-step-filter, #screening-judge-filter, #screening-score-filter, #screening-status-filter').on('change', function() {
				const filterId = $(this).attr('id');
				const filterType = filterId.replace('screening-', '').replace('-filter', '');
				screeningCurrentFilters[filterType] = $(this).val() || '';
				applyScreeningFilters();
			});

			// Clear filters button
			$('#screening-clear-filters, #screening-clear-search').on('click', function() {
				clearAllScreeningFilters();
			});
		};
	}

	if (typeof applyScreeningFilters === 'undefined') {
		window.applyScreeningFilters = function() {
			let filteredCards = [...screeningCompanyCards];
			
			// Debug logging (can be removed in production)
			if (window.debugFilters) {
				console.log('Applying screening filters:', screeningCurrentFilters);
				console.log('Total company cards:', screeningCompanyCards.length);
			}

			// Apply search filter
			if (screeningCurrentFilters.search) {
				filteredCards = filteredCards.filter(card => {
					const $card = $(card);
					const companyName = String($card.data('company-name') || '').toLowerCase();
					const userName = String($card.data('user-name') || '').toLowerCase();
					const category = String($card.data('category') || '').toLowerCase();
					const searchTerm = screeningCurrentFilters.search.toLowerCase();
					
					return companyName.includes(searchTerm) || 
									userName.includes(searchTerm) || 
									category.includes(searchTerm);
				});
				if (window.debugFilters) console.log('After search filter:', filteredCards.length);
			}

			// Apply cycle filter
			if (screeningCurrentFilters.cycle) {
				filteredCards = filteredCards.filter(card => {
					const $card = $(card);
					const cycleId = String($card.data('cycle-id') || '');
					return cycleId === screeningCurrentFilters.cycle && cycleId !== '';
				});
				if (window.debugFilters) console.log('After cycle filter:', filteredCards.length);
			}

			// Apply step filter
			if (screeningCurrentFilters.step) {
				filteredCards = filteredCards.filter(card => {
					const $card = $(card);
					const stepId = String($card.data('step-id') || '');
					return stepId === screeningCurrentFilters.step && stepId !== '';
				});
				if (window.debugFilters) console.log('After step filter:', filteredCards.length);
			}

			// Apply judge filter
			if (screeningCurrentFilters.judge) {
				filteredCards = filteredCards.filter(card => {
					const $card = $(card);
					const judgeIdsString = String($card.data('judge-ids') || '');
					if (!judgeIdsString || judgeIdsString.trim() === '') return false;
					const judgeIds = judgeIdsString.split(',').map(id => id.trim()).filter(id => id !== '');
					return judgeIds.includes(String(screeningCurrentFilters.judge));
				});
				if (window.debugFilters) console.log('After judge filter:', filteredCards.length);
			}

			// Apply score filter
			if (screeningCurrentFilters.score) {
				filteredCards = filteredCards.filter(card => {
					const scoreValue = $(card).data('score');
					const score = parseFloat(scoreValue);
					
					// Handle NaN and undefined scores
					if (isNaN(score) || score === null || score === undefined) {
						return screeningCurrentFilters.score === 'poor';
					}
					
					switch (screeningCurrentFilters.score) {
						case 'excellent': return score >= 75 && score <= 100;
						case 'good': return score >= 50 && score < 75;
						case 'average': return score >= 25 && score < 50;
						case 'poor': return score >= 0 && score < 25;
						default: return true;
					}
				});
				if (window.debugFilters) console.log('After score filter:', filteredCards.length);
			}

			// Apply status filter - based purely on company status
			if (screeningCurrentFilters.status) {
				filteredCards = filteredCards.filter(card => {
					const $card = $(card);
					const companyStatus = String($card.data('status') || 'not-evaluated').toLowerCase();
					
					if (window.debugFilters) {
						console.log('Filtering card with company status:', companyStatus, 'filter:', screeningCurrentFilters.status);
					}
					
					return companyStatus === screeningCurrentFilters.status;
				});
				if (window.debugFilters) console.log('After status filter:', filteredCards.length);
			}

			if (window.debugFilters) console.log('Screening filtered results:', filteredCards.length);
			
			// Update display
			updateScreeningDisplay(filteredCards);
			
			// Show/hide clear filters button and update filter indicators
			const hasActiveFilters = screeningCurrentFilters.search || screeningCurrentFilters.cycle || screeningCurrentFilters.step || screeningCurrentFilters.judge || screeningCurrentFilters.score || screeningCurrentFilters.status;
			if (hasActiveFilters) {
				$('#screening-clear-filters').removeClass('d-none');
			} else {
				$('#screening-clear-filters').addClass('d-none');
			}
			
			// Update filter indicators
			updateScreeningFilterIndicators();
		};
	}

	if (typeof updateScreeningDisplay === 'undefined') {
		window.updateScreeningDisplay = function(filteredCards) {
			const $grid = $('#screening-companies-grid');
			const $noResults = $('#screening-no-results-state');
			
			// Hide all cards first
			$('.screening-company-card').hide();
			
			if (filteredCards.length > 0) {
				// Show filtered cards
				filteredCards.forEach(card => {
					$(card).show();
				});
				
				// Hide no results state
				$noResults.addClass('d-none');
			} else {
				// Check if we have any companies at all and if any filters are active
				const totalCompanies = screeningCompanyCards.length;
				const hasActiveFilters = screeningCurrentFilters.search || screeningCurrentFilters.cycle || screeningCurrentFilters.step || screeningCurrentFilters.judge || screeningCurrentFilters.score || screeningCurrentFilters.status;
				
				if (totalCompanies > 0 && hasActiveFilters) {
					// Companies exist but filters don't match - show filter no-result
					$noResults.removeClass('d-none');
				} else {
					// No companies exist or no filters active - hide filter no-result (server-side message will show)
					$noResults.addClass('d-none');
				}
			}
		};
	}

	if (typeof updateScreeningFilterIndicators === 'undefined') {
		window.updateScreeningFilterIndicators = function() {
			// Add visual indicators for active filters
			$('#screening-cycle-filter, #screening-step-filter, #screening-judge-filter, #screening-score-filter, #screening-status-filter').each(function() {
				const $select = $(this);
				const hasValue = $select.val() && $select.val() !== '';
				
				if (hasValue) {
					$select.addClass('border-primary');
				} else {
					$select.removeClass('border-primary');
				}
			});
			
			// Handle search input
			const $search = $('#screening-company-search');
			if (screeningCurrentFilters.search) {
				$search.addClass('border-primary');
			} else {
				$search.removeClass('border-primary');
			}
		};
	}	if (typeof clearAllScreeningFilters === 'undefined') {
		window.clearAllScreeningFilters = function() {
			// Reset form inputs
			$('#screening-company-search').val('');
			$('#screening-cycle-filter, #screening-step-filter, #screening-judge-filter, #screening-score-filter, #screening-status-filter').val('');
			
			// Hide clear search button
			$('#screening-clear-search').addClass('d-none');
			
			// Reset current filters
			screeningCurrentFilters = {
				search: '',
				cycle: '',
				step: '',
				judge: '',
				score: '',
				status: ''
			};
			
			// Apply filters (will show all cards and hide no-results state)
			applyScreeningFilters();
		};
	}

	// Show Company Evaluation Modal (only bind if not already bound)
	if (!$(document).data('screening-evaluation-handler-bound')) {
		$(document).data('screening-evaluation-handler-bound', true);
		$(document).on('click', '.screening-show-evaluation', function(e) {
		e.preventDefault();
		const company = parseInt($(this).data('company'));
		const step = parseInt($(this).data('step'));
		
		// Show modal
		$('#screeningEvaluationModal').modal('show');
		
		// Reset modal body
		$('#screeningEvaluationModalBody').html(`
			<div class="text-center p-5">
				<div class="spinner-border text-primary" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
				<p class="mt-3 text-muted">Loading evaluation details...</p>
			</div>
		`);
		
		$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
		$.ajax({
			type: 'POST',
			url: '/dashboard/screening/evaluation/results',
			data: {
				company: company,
				step: step,
			},
			success: function(response) {
				buildScreeningEvaluationModal(response);
			}, 
			error: function (error) { 
				$('#screeningEvaluationModalBody').html(`
					<div class="alert alert-danger m-4">
						<i class="ti ti-alert-circle me-2"></i>
						Error loading evaluation details. Please try again.
					</div>
				`);
			}
		});
		});
	}

	if (typeof buildScreeningEvaluationModal === 'undefined') {
		window.buildScreeningEvaluationModal = function(data) {
		const totalScore = (data.total * 100).toFixed(1);
		
		let modalContent = `
			<div class="px-4 py-3">
				<!-- Overall Score Header -->
				<div class="row mb-5">
					<div class="col-12">
						<div class="card bg-gradient-primary text-white border-0 shadow-sm">
							<div class="card-body text-center py-4">
								<div class="d-flex align-items-center justify-content-center mb-4">
									<div class="circular-progress-large position-relative d-inline-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
										<div class="progress-circle position-absolute w-100 h-100" style="
											background: conic-gradient(
												${totalScore >= 75 ? 'var(--bs-primary)' : (totalScore >= 50 ? 'var(--bs-info)' : (totalScore >= 25 ? 'var(--bs-warning)' : 'var(--bs-danger)'))}
												${totalScore * 3.6}deg,
												rgba(255,255,255,0.3) ${totalScore * 3.6}deg
											);
											border-radius: 50%;
										"></div>
										<span class="fw-bold text-white position-relative fs-3">${totalScore}%</span>
									</div>
								</div>
								<h4 class="mb-2">{{ __('cycles.Overall Evaluation Score') }}</h4>
								<p class="mb-0 opacity-90">{{ __('cycles.Based on all judge evaluations') }}</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Judges Summary -->
				<div class="row mb-5">
					<div class="col-12">
						<h5 class="fw-bold mb-4 d-flex align-items-center">
							<i class="ti ti-users text-primary me-2"></i>
							{{ __('cycles.Judges Summary') }}
						</h5>
						<div class="row g-4">`;

			// Add judges summary cards
			data.judges.forEach(judge => {
				const judgeScore = (judge.evaluation.value * 100).toFixed(1);
				const statusClass = judge.evaluation.status === 'Published' ? 'success' : judge.evaluation.status === 'Draft' ? 'warning' : 'danger';
				const statusIcon = judge.evaluation.status === 'Published' ? 'circle-check' : judge.evaluation.status === 'Draft' ? 'clock' : 'circle-x';
				
				modalContent += `<div class="col-md-4">
					<div class="card border shadow-sm h-100">
						<div class="card-body text-center py-4">
							<img src="${judge.image_url}" alt="${judge.name}" class="rounded-circle mb-3" width="60" height="60">
							<h6 class="fw-semibold mb-2">${judge.name}</h6>
							<div class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} mb-3 px-3 py-2">
								<i class="ti ti-${statusIcon} me-1"></i>
								${judge.evaluation.status}
							</div>
							<div class="h3 mb-0 ${judge.evaluation.status === 'Not Evaluated' ? 'text-muted' : 'text-primary'}">${judge.evaluation.status === 'Not Evaluated' ? '-' : judgeScore + '%'}</div>
						</div>
					</div>
				</div>`;
			});

		modalContent += `
						</div>
					</div>
				</div>

				<!-- Detailed Criteria Breakdown -->
				<div class="row mb-5">
					<div class="col-12">
						<h5 class="fw-bold mb-4 d-flex align-items-center">
							<i class="ti ti-list-details text-primary me-2"></i>
							{{ __('cycles.Detailed Criteria Breakdown') }}
						</h5>`;

		// Add criteria details
		data.criteria.forEach(criterion => {
			const criterionScore = (criterion.value * 100).toFixed(1);
			modalContent += `
						<div class="card mb-4 shadow-sm">
							<div class="card-header bg-light py-3">
								<div class="d-flex align-items-center justify-content-between">
									<h6 class="mb-0 fw-bold text-dark">${criterion.name}</h6>
									<span class="fw-bold text-primary fs-5">${criterionScore} %</span>
								</div>
							</div>
							<div class="card-body p-4">`;

			// Add sub-criteria
			criterion.details.forEach(subCriterion => {
				const subScore = (subCriterion.value * 100).toFixed(1);
				modalContent += `
								<div class="border rounded-3 p-4 mb-4 bg-light bg-opacity-50">
									<div class="d-flex align-items-center justify-content-between mb-4">
										<h6 class="mb-0 fw-semibold text-dark">${subCriterion.name}</h6>
										<span class="fw-bold text-primary fs-6">${subScore} %</span>
									</div>
									
									<!-- Judge Details for this sub-criterion -->
									<div class="row g-3">`;

				subCriterion.details.forEach(judgeDetail => {
					const judgeScore = (judgeDetail.value * 100).toFixed(1);
					const statusClass = judgeDetail.status === 'Published' ? 'success' : judgeDetail.status === 'Draft' ? 'warning' : 'danger';
					
					modalContent += `<div class="col-md-6">
						<div class="card border-0 bg-white shadow-sm">
							<div class="card-body p-4">
								<div class="d-flex align-items-center justify-content-between mb-3">
									<div class="d-flex align-items-center">
										<img src="${data.judges.find(j => j.id === judgeDetail.judger.id)?.image_url || '/icons/account.svg'}" 
													alt="${judgeDetail.judger.name}" class="rounded-circle me-3" width="32" height="32">
										<div>
											<div class="fw-medium text-dark">${judgeDetail.judger.name}</div>
											<span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} small mt-1">${judgeDetail.status}</span>
										</div>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-3">
									<span class="text-muted">Score: ${judgeDetail.given_score}/${judgeDetail.max}</span>
									<span class="fw-bold text-primary">${judgeScore}%</span>
								</div>
								${judgeDetail.comment ? `<div class="border-top pt-3"><small class="text-muted fst-italic">"${judgeDetail.comment}"</small></div>` : ''}
							</div>
						</div>
					</div>`;
				});

				modalContent += `
									</div>
								</div>`;
			});

			modalContent += `
							</div>
						</div>`;
		});

		modalContent += `
					</div>
				</div>

				<!-- Individual Judge Evaluations -->
				<div class="row">
					<div class="col-12">
						<h5 class="fw-bold mb-4 d-flex align-items-center">
							<i class="ti ti-user-check text-primary me-2"></i>
							{{ __('cycles.Individual Judge Evaluations') }}
						</h5>
						<div class="accordion" id="judgeAccordion">`;

		// Add individual judge evaluations
		data.judges.forEach((judge, index) => {
			const judgeScore = (judge.evaluation.value * 100).toFixed(1);
			const statusClass = judge.evaluation.status === 'Published' ? 'success' : judge.evaluation.status === 'Draft' ? 'warning' : 'danger';
			
			modalContent += `
							<div class="accordion-item border shadow-sm mb-3">
								<h2 class="accordion-header" id="heading${judge.id}">
									<button class="accordion-button ${index === 0 ? '' : 'collapsed'} py-4" type="button" data-bs-toggle="collapse" 
													data-bs-target="#collapse${judge.id}" aria-expanded="${index === 0 ? 'true' : 'false'}" aria-controls="collapse${judge.id}">
										<div class="d-flex align-items-center w-100">
											<img src="${judge.image_url}" alt="${judge.name}" class="rounded-circle me-4" width="40" height="40">
											<div class="flex-grow-1">
												<div class="fw-bold text-dark">${judge.name}</div>
												<div class="small text-muted mt-1">${judge.email}</div>
											</div>
											<div class="me-4">
												<span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} px-3 py-2">${judge.evaluation.status}</span>
											</div>
											<div class="text-end me-4">
												<div class="fw-bold fs-5 ${judge.evaluation.status === 'Not Evaluated' ? 'text-muted' : 'text-primary'}">${judge.evaluation.status === 'Not Evaluated' ? '-' : judgeScore + '%'}</div>
											</div>
										</div>
									</button>
								</h2>
								<div id="collapse${judge.id}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
											aria-labelledby="heading${judge.id}" data-bs-parent="#judgeAccordion">
									<div class="accordion-body p-4">`;

			if (judge.evaluation.status === 'Not Evaluated')
			{
				modalContent += `
										<div class="text-center text-muted py-5">
											<i class="ti ti-clock fs-1 mb-4 opacity-50"></i>
											<h6 class="text-muted">{{ __('cycles.This judge has not evaluated this company yet.') }}</h6>
										</div>`;
			}
			else
			{
				// Add judge's detailed evaluation
				judge.evaluation.details.forEach(judgeCtiterion => {
					if (judgeCtiterion.details.length > 0) {
						modalContent += `
										<div class="mb-5">
											<h6 class="fw-bold mb-4 text-dark">${judgeCtiterion.name}</h6>
											<div class="row g-4">`;

						judgeCtiterion.details.forEach(detail => {
							const detailScore = (detail.score / detail.max * 100).toFixed(1);
							modalContent += `
												<div class="col-md-6">
													<div class="card border shadow-sm">
														<div class="card-body p-4">
															<div class="d-flex justify-content-between align-items-start mb-3">
																<h6 class="mb-0 text-dark">${detail.name}</h6>
																<span class="badge bg-label-primary text-primary px-3 py-2">${detail.score}/${detail.max}</span>
															</div>
															<div class="mb-3">
																<div class="progress" style="height: 8px;">
																	<div class="progress-bar bg-primary" role="progressbar" style="width: ${detailScore}%" aria-valuenow="${detailScore}" aria-valuemin="0" aria-valuemax="100"></div>
																</div>
															</div>
															<div class="border-top pt-3">
																${detail.comment ? `<small class="text-muted fst-italic">"${detail.comment}"</small>` : '<small class="text-muted">No comment provided</small>'}
															</div>
														</div>
													</div>
												</div>`;
						});

						modalContent += `
											</div>
										</div>`;
					}
				});

				if (judge.evaluation.comment) {
					modalContent += `
										<div class="mt-5 pt-4 border-top">
											<h6 class="fw-bold mb-3">{{ __('cycles.Overall Comment') }}</h6>
											<div class="bg-light rounded-3 p-4">
												<p class="mb-0 text-dark fst-italic">"${judge.evaluation.comment}"</p>
											</div>
										</div>`;
				}
			}

			modalContent += `
									</div>
								</div>
							</div>`;
		});

		modalContent += `
						</div>
					</div>
				</div>
			</div>`;

		$('#screeningEvaluationModalBody').html(modalContent);
		};
	}

	// KPI Card filtering functionality for Regular Screening
	if (typeof window.filterByScreeningKPI === 'undefined') {
		window.filterByScreeningKPI = function(cardElement) {
		const filterType = $(cardElement).data('filter-type');
		
		// Clear all current filters first
		clearAllScreeningFilters();
		
		console.log(filterType);
		// Apply the specific filter based on KPI card clicked
		switch(filterType) {
			case 'total':
				// Show all - filters are already cleared
				break;
			case 'not-assigned':
				screeningCurrentFilters.status = 'not-assigned';
				$('#screening-status-filter').val('not-assigned');
				break;
			case 'not-evaluated':
				screeningCurrentFilters.status = 'not-evaluated';
				$('#screening-status-filter').val('not-evaluated');
				break;
			case 'draft':
				screeningCurrentFilters.status = 'draft';
				$('#screening-status-filter').val('draft');
				break;
			case 'published':
				screeningCurrentFilters.status = 'published';
				$('#screening-status-filter').val('published');
				break;
		}
		
		// Apply the filter and update display
		applyScreeningFilters();
		
		// Add visual feedback to the clicked card (only regular screening cards)
		$('.screening-kpi-filter-card').removeClass('border-primary');
		$(cardElement).addClass('border-primary');
		
		// Remove the border after a few seconds
		setTimeout(() => {
			$(cardElement).removeClass('border-primary');
		}, 3000);
		
		// Scroll to results section smoothly
		$('#screening-companies-grid').get(0).scrollIntoView({ behavior: 'smooth', block: 'start' });
		};
	}

	// Default function for backward compatibility
	if (typeof window.filterByKPI === 'undefined') {
		window.filterByKPI = window.filterByScreeningKPI;
	}

	// Export functionality (only bind if not already bound)
	if (!$(document).data('screening-export-handler-bound')) {
		$(document).data('screening-export-handler-bound', true);
		$('#screening-export-csv, #screening-export-excel').on('click', function() {
			const format = $(this).attr('id') === 'screening-export-csv' ? 'csv' : 'excel';
			const button = $(this);
			const originalText = button.html();
			
			// Show loading state
			button.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i>{{ __("cycles.Exporting...") }}');
			
			// Prepare export data with current filters
			const exportData = {
				format: format,
				filters: {
					search: screeningCurrentFilters.search || '',
					cycle: screeningCurrentFilters.cycle || '',
					step: screeningCurrentFilters.step || '',
					judge: screeningCurrentFilters.judge || '',
					score: screeningCurrentFilters.score || '',
					status: screeningCurrentFilters.status || ''
				},
				_token: $('meta[name="csrf-token"]').attr('content')
			};
			
			// Create a form and submit it to trigger download
			const form = $('<form>', {
				method: 'POST',
				action: '{{ route("reports.export.screening") }}',
				style: 'display: none;'
			});
			
			// Add all data as hidden inputs
			Object.keys(exportData).forEach(key => {
				if (key === 'filters') {
					Object.keys(exportData.filters).forEach(filterKey => {
						form.append($('<input>', {
							type: 'hidden',
							name: `filters[${filterKey}]`,
							value: exportData.filters[filterKey]
						}));
					});
				} else {
					form.append($('<input>', {
						type: 'hidden',
						name: key,
						value: exportData[key]
					}));
				}
			});
			
			$('body').append(form);
			form.submit();
			form.remove();
			
			// Reset button state after a short delay
			setTimeout(() => {
				button.prop('disabled', false).html(originalText);
			}, 2000);
		});
	}
	// Update program filter and reload the page with the selected program
	function updateProgramFilter(programId) {
		const url = new URL(window.location.href);
		if (programId) {
			url.searchParams.set('program_id', programId);
		} else {
			url.searchParams.delete('program_id');
		}
		// Also clear any step filter when changing programs
		url.searchParams.delete('step_id');
		window.location.href = url.toString();
	}
</script>