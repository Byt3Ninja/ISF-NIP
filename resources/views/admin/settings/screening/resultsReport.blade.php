@extends('layouts.admin')

@section('content')
  @php
    // Filter over the full evaluated set (same source as the card list below),
    // not just startups currently at the step — otherwise filtering by judge or
    // result silently drops startups that have advanced past this screening step.
    $companies = $_companies;

    if (request('startup_id') != 0) {
      $companies = $companies->where('id', request('startup_id'));
    }

    if (request('judge_id') != 0) {
      $judgeId = request('judge_id');
      $formId = $form->id ?? null;
      $judges = collect();
      $judges = $companies
        ->map(function ($company) use ($formId) {
          return \App\User::whereIn('id', $company->judges($company->id, $formId))->get();
        })
        ->flatten();
    }

    if (request('result_id') != 0) {
      $companyModel = new \App\Company();
      $comparison = request('result2_id'); // نوع المقارنة (==, <, >, <=, >=)
      $threshold = request('result_id'); // القيمة التي تتم المقارنة معها

      $filteredCompanies = $companies
        ->filter(function ($company) use ($form, $companyModel, $comparison, $threshold) {
          $weight = $companyModel->companyWeight([$company->id], $form->id ?? '', 0);

          // تطبيق المقارنة بناءً على نوعها
          return match ($comparison) {
            '==' => $weight == $threshold,
            '>' => $weight > $threshold,
            '<' => $weight < $threshold,
            '>=' => $weight >= $threshold,
            '<=' => $weight <= $threshold,
            default => false, // في حالة لم تكن المقارنة صحيحة، لا تُرجع أي شركة
          };
        });

      $companies = $filteredCompanies;
    } else {
      $companies = $companies->values();
    }
    $types = [
      'onetoall' => 'Assign One to all users',
      'grouptoall' => 'Assign Group to all users',
      'grouptogroup' => 'Assign Group to group of users',
    ];
   
  @endphp

  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-body">
        @if ($cstep)
          <!-- Enhanced Page Header -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                  <!-- Main Header Section -->
                  <div class="d-flex flex-wrap align-items-start justify-content-between gap-4 mb-4">
                    <div class="page-title-wrapper flex-grow-1">
                      <div class="d-flex align-items-center mb-3">
                        <div
                          class="icon-wrapper bg-label-primary bg-opacity-10 rounded-3 p-3 me-4 d-flex align-items-center justify-content-center"
                          style="width: 60px; height: 60px"
                        >
                          <i class="ti ti-clipboard-check text-primary fs-3"></i>
                        </div>
                        <div>
                          <h4 class="page-title mb-1 fw-bold text-dark d-flex align-items-center">
                            {{ __('cycles.Screening results') }}
                          </h4>
                          <p class="page-subtitle mb-0 text-muted line-clamp-1 fs-6" style="max-width: 600px;">
                            {{ __('cycles.Manage and review evaluation results for') }} {{ $cstep->title }}
                          </p>
                        </div>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    {{-- <div class="page-actions d-flex flex-wrap gap-2">
                      <a
                        class="btn btn-primary rounded-pill px-4 py-2 shadow-sm d-flex align-items-center"
                        href="/dashboard/cycles/{{ $cstep->cycle_id }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('cycles.Export') }}"
                      >
                        <i class="fa-solid fa-file-export me-2"></i>
                        <span class="d-none d-sm-inline">{{ __('cycles.Export') }}</span>
                      </a>
                    </div> --}}
                  </div>

                  <!-- Enhanced Screening Selection Section -->
                  
                </div>
              </div>
            </div>
          </div>

          <!-- Alerts Section -->
          @if (session()->has('message'))
            <div class="row mb-4">
              <div class="col-12">
                <div class="alert alert-success alert-dismissible rounded-4 shadow-sm border-0" role="alert">
                  <div class="d-flex align-items-center">
                    <i class="ti ti-check-circle me-3 fs-4"></i>
                    <div>
                      <strong>Success!</strong>
                      {{ __('admin.' . session()->get('message')) }}
                    </div>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              </div>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="row mb-4">
              <div class="col-12">
                <div class="alert alert-danger alert-dismissible rounded-4 shadow-sm border-0" role="alert">
                  <div class="d-flex align-items-center">
                    <i class="ti ti-alert-circle me-3 fs-4"></i>
                    <div>
                      <strong>Error!</strong>
                      {{ __('admin.' . session()->get('message2')) }}
                    </div>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              </div>
            </div>
          @endif

          @if ($_companies->count() == 0)
            <input type="hidden" class="stepsExcel" value="0" />
          @else
            <input type="hidden" class="stepsExcel" value="{{ request()->route('id') }}" />
          @endif

          <!-- Companies Results Section -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                      <h5 class="card-title mb-1 fw-bold text-dark d-flex align-items-center">
                        <i class="ti ti-building-bank me-2 text-primary"></i>
                        {{ __('cycles.Companies Evaluation Results') }}
                      </h5>
                      <p class="text-muted mb-0">{{ __('cycles.Review and manage company evaluations') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                      <div class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                        <i class="ti ti-check-circle me-1"></i>
                        <span id="results-count">{{ $_companies->count() }}</span> {{ __('cycles.Results') }}
                      </div>
                    </div>
                  </div>

                  <!-- Enhanced Search and Filter Section -->
                  <div class="search-filter-section mt-4 p-4 bg-label-light bg-opacity-50 rounded-3 border">
                    <div class="row g-3">
                      <!-- Search Input -->
                      <div class="col-lg-3 col-md-6">
                        <div class="search-wrapper position-relative">
                          <label class="form-label fw-semibold mb-2 text-dark">
                            <i class="ti ti-search me-1 text-primary"></i>
                            {{ __('cycles.Search Companies') }}
                          </label>
                          <div class="position-relative">
                            <input
                              type="text"
                              id="company-search"
                              class="form-control"
                              placeholder="{{ __('cycles.Search by company name, user name, or category...') }}"
                              style="padding-left: 2.5rem;"
                            >
                             <i class="ti ti-search position-absolute top-50 translate-middle-y mx-2 text-muted"></i>
                          </div>
                        </div>
                      </div>

                      <!-- Judge Filter -->
                      <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold mb-2 text-dark">
                          <i class="ti ti-user-check me-1 text-info"></i>
                          {{ __('cycles.Judge') }}
                        </label>
                        <select id="judge-filter" class="form-select">
                          <option value="">{{ __('cycles.All Judges') }}</option>
                          @foreach ($judges->unique('id') as $judge)
                            <option value="{{ $judge->id }}">{{ Str::limit($judge->name, 25) }}</option>
                          @endforeach
                        </select>
                      </div>

                      <!-- Score Range Filter -->
                      <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold mb-2 text-dark">
                          <i class="ti ti-chart-line me-1 text-success"></i>
                          {{ __('cycles.Score Range') }}
                        </label>
                        <select id="score-filter" class="form-select">
                          <option value="">{{ __('cycles.All Scores') }}</option>
                          <option value="excellent">{{ __('cycles.Excellent') }} (75-100%)</option>
                          <option value="good">{{ __('cycles.Good') }} (50-74%)</option>
                          <option value="average">{{ __('cycles.Average') }} (25-49%)</option>
                          <option value="poor">{{ __('cycles.Poor') }} (0-24%)</option>
                        </select>
                      </div>

                      <!-- Status Filter -->
                      <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold mb-2 text-dark">
                          <i class="ti ti-flag me-1 text-warning"></i>
                          {{ __('cycles.Evaluation Status') }}
                        </label>
                        <select id="status-filter" class="form-select">
                          <option value="">{{ __('cycles.All Status') }}</option>
                          <option value="published" @if(request('status') == 'published') selected @endif >{{ __('cycles.Published') }}</option>
                          <option value="draft" @if(request('status') == 'draft') selected @endif >{{ __('cycles.Draft') }}</option>
                          <option value="not-evaluated" @if(request('status') == 'not-evaluated') selected @endif >{{ __('cycles.Not Evaluated') }}</option>
                          {{-- <option value="mixed">{{ __('cycles.Mixed Status') }}</option> --}}
                        </select>
                      </div>
                    </div>

                    <!-- Second Row for Sort -->
                    <div class="row g-3 mt-2">
                      <!-- Sort Options -->
                      <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold mb-2 text-dark">
                          <i class="ti ti-sort-ascending me-1 text-info"></i>
                          {{ __('cycles.Sort By') }}
                        </label>
                        <select id="sort-filter" class="form-select">
                          <option value="name-asc">{{ __('cycles.Name A-Z') }}</option>
                          <option value="name-desc">{{ __('cycles.Name Z-A') }}</option>
                          <option value="score-desc">{{ __('cycles.Score High-Low') }}</option>
                          <option value="score-asc">{{ __('cycles.Score Low-High') }}</option>
                          <option value="approval-desc">{{ __('cycles.Approval % High-Low') }}</option>
                          <option value="approval-asc">{{ __('cycles.Approval % Low-High') }}</option>
                        </select>
                      </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="filter-actions mt-3 d-flex flex-wrap gap-2 align-items-center">
                      <button id="clear-filters" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="ti ti-x me-1"></i>
                        {{ __('cycles.Clear Filters') }}
                      </button>
                      <div class="filter-summary ms-auto">
                        <small class="text-muted">
                          <span id="active-filters-text">{{ __('cycles.No active filters') }}</span>
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <!-- Company Cards Grid -->
                  <div id="companies-grid" class="row g-4">
                    @foreach ($_companies as $company)
                      @php
                        // Get the evaluation status from judges
                        $evaluationStatuses = [];
                        $hasPublished = false;
                        $hasDraft = false;
                        $hasNotEvaluated = false;
                        
                        foreach ($judges as $judge) {
                          if (in_array($company->id, $judge->startups->toArray())) {
                            $scoped_company = collect($judge->companies)->where('id', $company->id)->first();
                            if ($scoped_company) {
                              $status = strtolower($scoped_company->status ?? 'not-evaluated');
                              $evaluationStatuses[] = $status;
                              
                              if ($status === 'published') $hasPublished = true;
                              elseif ($status === 'draft') $hasDraft = true;
                              else $hasNotEvaluated = true;
                            }
                          }
                        }
                        
                        // Determine primary status for filtering
                        $primaryStatus = 'not-evaluated';
                        if ($hasPublished && !$hasDraft && !$hasNotEvaluated) {
                          $primaryStatus = 'published';
                        } elseif ($hasDraft) {
                          $primaryStatus = 'draft';
                        } elseif ($hasPublished && $hasNotEvaluated) {
                          $primaryStatus = 'mixed'; // Has both published and not evaluated
                        }
                        
                        // Create searchable category string
                        $categories = is_string($company->business_categories) ? json_decode($company->business_categories, true) : $company->business_categories;
                        $categoryString = is_array($categories) ? implode(' ', $categories) : $company->business_categories;
                        $categoryString = str_replace(['/', ','], ' ', $categoryString);
                        
                        // Calculate average score from judge evaluations
                        $totalScore = 0;
                        $publishedEvaluations = 0;
                        $averageScore = 0;
                        
                        // Calculate average score for display (published only)
                        foreach ($judges as $judge) {
                          if (in_array($company->id, $judge->startups->toArray())) {
                            $scoped_company = collect($judge->companies)->where('id', $company->id)->first();
                            if ($scoped_company && strtolower($scoped_company->status) === 'published' && isset($scoped_company->score)) {
                              $totalScore += (float) $scoped_company->score;
                              $publishedEvaluations++;
                            }
                          }
                        }
                        
                        if ($publishedEvaluations > 0) {
                          $averageScore = round($totalScore / $publishedEvaluations, 1);
                        }
                        
                        // Calculate filter score (includes published AND draft with scores)
                        $filterTotalScore = 0;
                        $filterEvaluationsCount = 0;
                        $filterScore = 0;
                        
                        foreach ($judges as $judge) {
                          if (in_array($company->id, $judge->startups->toArray())) {
                            $scoped_company = collect($judge->companies)->where('id', $company->id)->first();
                            if ($scoped_company && isset($scoped_company->score) && $scoped_company->score !== null && 
                                (strtolower($scoped_company->status) === 'published' || strtolower($scoped_company->status) === 'draft')) {
                              $filterTotalScore += (float) $scoped_company->score;
                              $filterEvaluationsCount++;
                            }
                          }
                        }
                        
                        if ($filterEvaluationsCount > 0) {
                          $filterScore = round($filterTotalScore / $filterEvaluationsCount, 1);
                        }
                        
                        // Get judge IDs for this company and collect approval data
                        $companyJudgeIds = [];
                        $companyApprovals = [];
                        foreach ($judges as $judge) {
                          if (in_array($company->id, $judge->startups->toArray())) {
                            $companyJudgeIds[] = $judge->id;
                            $scoped_company_approval = collect($judge->companies)->where('id', $company->id)->first();
                            if ($scoped_company_approval) {
                              $companyApprovals[] = $scoped_company_approval->approval ?? 'pending';
                            } else {
                              $companyApprovals[] = 'pending';
                            }
                          }
                        }

                        // Approval statistics
                        $approvalStatsCollection = collect($companyApprovals);
                        $totalJudges = $approvalStatsCollection->count();
                        $approvedCount = $approvalStatsCollection->filter(fn($a) => $a === 'approved')->count();
                        $disapprovedCount = $approvalStatsCollection->filter(fn($a) => $a === 'disapproved')->count();
                        $totalDecisions = $approvalStatsCollection->filter(fn($a) => $a !== 'pending')->count();
                        $approvalPct = $totalDecisions > 0 ? round(($approvedCount / $totalDecisions) * 100) : 0;
                        $disapprovalPct = $totalDecisions > 0 ? round(($disapprovedCount / $totalDecisions) * 100) : 0;
                      @endphp
                      
                      <div class="col-xl-4 col-lg-6 col-md-12 company-card"
                           data-company-name="{{ strtolower($company->name) }}"
                           data-user-name="{{ strtolower($company->user->name) }}"
                           data-category="{{ strtolower($categoryString) }}"
                           data-score="{{ $filterScore }}"
                           data-approval-pct="{{ $approvalPct }}"
                           data-status="{{ $primaryStatus }}"
                           data-evaluation-statuses="{{ implode(',', $evaluationStatuses) }}"
                           data-judge-ids="{{ implode(',', $companyJudgeIds) }}">
                        <div class="card border rounded-3 h-100">
                          <!-- Company Header -->
                          <div class="card-header bg-transparent border-0 pb-2">
                            <div class="d-flex align-items-center justify-content-between">
                              <div class="d-flex align-items-center">
                                @if( $_user->ableTo('company.profile_view', $permissions) ) <a href="/dashboard/companyProfile/{{ $company->id }}" class="text-decoration-none"> @endif
                                  <div class="company-avatar me-3">
                                    <img
                                      src="{{ $company->logo_url }}"
                                      alt="Company Logo"
                                      class="rounded-3 shadow-sm"
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
                                      $categoryList = is_array($categories) ? implode(', ', $categories) : $company->business_categories;
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
                                          {{ $averageScore >= 75 ? '#28a745' : ($averageScore >= 50 ? '#17a2b8' : ($averageScore >= 25 ? '#ffc107' : '#dc3545')) }}
                                            {{ $averageScore * 3.6 }}deg,
                                          #e9ecef {{ $averageScore * 3.6 }}deg
                                        );
                                        border-radius: 50%;
                                      "
                                    ></div>
                                    <span class="fw-bold text-dark position-relative" style="font-size: 0.875rem">
                                      {{ $averageScore }} %
                                    </span>
                                  </div>
                                  <div class="small text-muted text-nowrap">{{ __('cycles.Avg Score') }}</div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- Company Body -->
                          <div class="card-body">
                            <!-- User Information -->
                            <div class="user-info bg-label-light border rounded-3 p-2 mb-4">
                              <div class="d-flex align-items-center">
                                @if( $_user->ableTo('users.edit', $permissions) ) <a href="/dashboard/users/{{ $company->user->id }}/edit" class="text-decoration-none"> @endif
                                  <img
                                    src="{{ $company->user->image_url ?? '/icons/account.svg' }}"
                                    alt="img"
                                    class="rounded-circle me-3 shadow-sm"
                                    height="40"
                                    width="40"
                                  />
                                @if( $_user->ableTo('users.edit', $permissions) ) </a> @endif
                                <div class="flex-grow-1">
                                  @if( $_user->ableTo('users.edit', $permissions) ) <a href="/dashboard/users/{{ $company->user->id }}/edit" class="text-decoration-none"> @endif
                                    <h6 class="mb-0 fw-semibold text-dark">{{ $company->user->name }}</h6>
                                    <small class="text-muted">{{ $company->user->title ?: __('cycles.No title') }}</small>
                                  @if( $_user->ableTo('users.edit', $permissions) ) </a> @endif
                                </div>
                              </div>
                            </div>

                            <!-- Approval Statistics -->
                            <div class="approval-stats d-flex align-items-center gap-2 mb-4 px-1">
                              <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                  <small class="fw-semibold text-success"><i class="ti ti-circle-check me-1"></i>{{ __('cycles.Approved') }}</small>
                                  <small class="fw-bold text-success">{{ $approvedCount }}/{{ $totalJudges }} ({{ $approvalPct }}%)</small>
                                </div>
                                <div class="progress mb-2" style="height: 6px;">
                                  <div class="progress-bar bg-success" style="width: {{ $approvalPct }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                  <small class="fw-semibold text-danger"><i class="ti ti-circle-x me-1"></i>{{ __('cycles.Disapproved') }}</small>
                                  <small class="fw-bold text-danger">{{ $disapprovedCount }}/{{ $totalJudges }} ({{ $disapprovalPct }}%)</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                  <div class="progress-bar bg-danger" style="width: {{ $disapprovalPct }}%"></div>
                                </div>
                              </div>
                            </div>

                            <!-- Judges Evaluation -->
                            <div class="judges-evaluation">
                              <h6 class="fw-semibold mb-3 text-dark d-flex align-items-center">
                                <i class="ti ti-users me-2 text-primary"></i>
                                <span>{{ __('cycles.Judges Evaluation') }}</span>
                                <span class="badge bg-label-primary bg-opacity-10 text-primary ms-2 rounded-pill">
                                  {{ $judges->count() }}
                                </span>
                              </h6>
                              <div class="judges-list">
                                @foreach ($judges->take(3) as $judge)
                                  @php($scoped_company = collect($judge->companies)->where('id', $company->id)->first())
                                  @if (in_array($company->id, $judge->startups->toArray()))
                                    <div class="judge-evaluation-item bg-transparent rounded-3 border px-2 py-1 mb-2">
                                      <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                          <div class="status-icon me-2">
                                            @if ($scoped_company->status == 'Published')
                                              <i class="ti ti-circle-check text-success fs-5" title="Published"></i>
                                            @elseif ($scoped_company->status == 'Draft')
                                              <i class="ti ti-clock text-warning fs-5" title="Draft"></i>
                                            @elseif ($scoped_company->status == 'Not Evaluated')
                                              <i class="ti ti-circle-x text-danger fs-5" title="Not Evaluated"></i>
                                            @endif
                                          </div>
                                          <div>
                                            <span class="fw-medium text-dark">{{ Str::limit($judge->name, 15) }}</span>
                                            <div class="small text-muted">{{ __('cycles.' . $scoped_company->status) }}</div>
                                          </div>
                                        </div>
                                        <div class="evaluation-score text-end">
                                          {{-- @if ($scoped_company->status === 'Not Evaluated')
                                            <small class="text-muted fst-italic">{{ __('cycles.Pending') }}</small>
                                          @else --}}
                                            <div
                                              class="score-badge badge {{ $scoped_company->score >= 75 ? 'border-success bg-label-success' : ($scoped_company->score >= 50 ? 'border-info bg-label-info' : ($scoped_company->score >= 25 ? 'border-warning bg-label-warning' : 'border-danger bg-label-danger')) }} px-2 py-1 border"
                                            >
                                              {{ $scoped_company->score }}%
                                            </div>
                                          {{-- @endif --}}
                                        </div>
                                      </div>
                                    </div>
                                  @endif
                                @endforeach
                                @if ($judges->count() > 3)
                                  <div class="text-center">
                                    <small class="text-muted">+{{ $judges->count() - 3 }} {{ __('cycles.more judges') }}</small>
                                  </div>
                                @endif
                              </div>
                            </div>
                          </div>

                          <!-- Company Footer -->
                          <div class="card-footer bg-transparent border-0 pt-0">
                            <div class="d-grid gap-2">
                              <button
                                class="show-company-evaluation btn btn-primary btn-sm rounded-pill shadow-sm"
                                data-company="{{ $company->id }}"
                                data-step="{{ $cstep->id }}"
                              >
                                <i class="ti ti-eye me-2"></i>
                                {{ __('cycles.View Complete Evaluation') }}
                              </button>
                              <select
                                class="form-select form-select-sm rounded-pill shadow-sm px-4"
                                onchange="changestep({{ $company->id }}, this.value)"
                              >
                                <option value="0">{{ __('cycles.Disapprove') }}</option>
                                @foreach ($_steps->where('cycle_id', $company->cycle)->sortBy('arr')->values() as $step)
                                  <option value="{{ $step->id }}" {{ $step->id == $company->step ? 'selected' : '' }}>
                                    {{ $step->title }}
                                  </option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>

                  <!-- No Results State -->
                  <div id="no-results-state" class="no-results-state text-center py-5" style="display: none;">
                    <div class="empty-state-icon mb-3">
                      <i class="ti ti-search-off text-muted" style="font-size: 4rem"></i>
                    </div>
                    <h5 class="text-muted mb-2">{{ __('cycles.No Companies Found') }}</h5>
                    <p class="text-muted mb-3">{{ __('cycles.No companies match your search criteria. Try adjusting your filters.') }}</p>
                    <button id="clear-search" class="btn btn-outline-primary rounded-pill">
                      <i class="ti ti-x me-2"></i>
                      {{ __('cycles.Clear Search') }}
                    </button>
                  </div>

                  <!-- Empty State -->
                  @if ($_companies->isEmpty())
                    <div class="empty-state text-center py-5">
                      <div class="empty-state-icon mb-3">
                        <i class="ti ti-clipboard-off text-muted" style="font-size: 4rem"></i>
                      </div>
                      <h5 class="text-muted mb-2">{{ __('cycles.No Companies Found') }}</h5>
                      <p class="text-muted mb-3">{{ __('cycles.No companies match your current filter criteria.') }}</p>
                      <button class="btn btn-outline-primary rounded-pill" onclick="location.reload()">
                        <i class="ti ti-refresh me-2"></i>
                        {{ __('cycles.Refresh Results') }}
                      </button>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @else
          <!-- Error State -->
          <div class="row">
            <div class="col-12">
              <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                  <div class="error-state-icon mb-4">
                    <i class="ti ti-alert-triangle text-warning" style="font-size: 4rem;"></i>
                  </div>
                  <h5 class="card-title mb-3">{{ __('admin.No_results_found') }}</h5>
                  <p class="text-muted mb-4">
                    {{ __('cycles.No records screening from') }}
                  </p>
                  <a href="/dashboard/cycles" class="btn btn-primary rounded-pill px-4">
                    <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
                    {{ __('cycles.Cycles') }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
  <!-- END: Content-->

  <!-- Evaluation Modal -->
  <div class="modal fade" id="evaluationModal" tabindex="-1" aria-labelledby="evaluationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="evaluationModalLabel">
            <i class="ti ti-clipboard-check me-2"></i>
            {{ __('cycles.Company Evaluation Details') }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="evaluationModalBody">
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
@stop

  @section('pagescripts')
    <script>
      // Global variables for filtering
      let allCompanyCards = [];
      let currentFilters = {
        search: '',
        judge: '',
        score: '',
        status: '',
        sort: 'name-asc'
      };

      $(document).ready(function() {
        // Initialize company cards array
        allCompanyCards = $('.company-card').toArray();
        
        // Initialize search and filter functionality
        initializeSearchAndFilters();
      });

      function initializeSearchAndFilters() {
        // Search input handler with debounce
        let searchTimeout;
        $('#company-search').on('input', function() {
          clearTimeout(searchTimeout);
          searchTimeout = setTimeout(() => {
            currentFilters.search = $(this).val().toLowerCase().trim();
            applyFilters();
          }, 300);
        });

        // Filter change handlers
        $('#judge-filter, #score-filter, #status-filter, #sort-filter').on('change', function() {
          const filterId = $(this).attr('id');
          const filterType = filterId.replace('-filter', '');
          currentFilters[filterType] = $(this).val();
          applyFilters();
        });

        // Clear filters button
        $('#clear-filters, #clear-search').on('click', function() {
          clearAllFilters();
        });
      }

      function applyFilters() {
        let filteredCards = [...allCompanyCards];

        // Apply search filter
        if (currentFilters.search) {
          filteredCards = filteredCards.filter(card => {
            const $card = $(card);
            const companyName = $card.data('company-name') || '';
            const userName = $card.data('user-name') || '';
            const category = $card.data('category') || '';
            
            return companyName.includes(currentFilters.search) || 
                   userName.includes(currentFilters.search) || 
                   category.includes(currentFilters.search);
          });
        }

        // Apply judge filter
        if (currentFilters.judge) {
          filteredCards = filteredCards.filter(card => {
            const $card = $(card);
            const judgeIds = ($card.data('judge-ids') || '').toString().split(',');
            return judgeIds.includes(currentFilters.judge);
          });
        }

        // Apply score filter
        if (currentFilters.score) {
          filteredCards = filteredCards.filter(card => {
            const scoreValue = $(card).data('score');
            const score = parseFloat(scoreValue);
            
            // Skip cards with no valid score data
            if (isNaN(score) || score === 0) {
              return false;
            }
            
            switch (currentFilters.score) {
              case 'excellent': return score >= 75;
              case 'good': return score >= 50 && score < 75;
              case 'average': return score >= 25 && score < 50;
              case 'poor': return score > 0 && score < 25;
              default: return true;
            }
          });
        }

        // Apply status filter
        if (currentFilters.status) {
          filteredCards = filteredCards.filter(card => {
            const $card = $(card);
            const primaryStatus = $card.data('status') || 'not-evaluated';
            const evaluationStatuses = ($card.data('evaluation-statuses') || '').split(',').filter(status => status);
            
            switch (currentFilters.status) {
              case 'published':
                // Show companies where ALL evaluations are published (primaryStatus === 'published')
                // OR companies with at least some published evaluations (for mixed cases)
                return primaryStatus === 'published' || evaluationStatuses.includes('published');
              case 'draft':
                return primaryStatus === 'draft' || evaluationStatuses.includes('draft');
              case 'not-evaluated':
                return primaryStatus === 'not-evaluated' || 
                       (evaluationStatuses.length === 0) || 
                       evaluationStatuses.every(status => status === 'not-evaluated' || status === '');
              case 'mixed':
                return primaryStatus === 'mixed';
              default:
                return true;
            }
          });
        }

        // Apply sorting
        filteredCards.sort((a, b) => {
          const $cardA = $(a);
          const $cardB = $(b);
          
          switch (currentFilters.sort) {
            case 'name-asc':
              return ($cardA.data('company-name') || '').localeCompare($cardB.data('company-name') || '');
            case 'name-desc':
              return ($cardB.data('company-name') || '').localeCompare($cardA.data('company-name') || '');
            case 'score-desc':
              return (parseFloat($cardB.data('score')) || 0) - (parseFloat($cardA.data('score')) || 0);
            case 'score-asc':
              return (parseFloat($cardA.data('score')) || 0) - (parseFloat($cardB.data('score')) || 0);
            case 'approval-desc':
              return (parseFloat($cardB.data('approval-pct')) || 0) - (parseFloat($cardA.data('approval-pct')) || 0);
            case 'approval-asc':
              return (parseFloat($cardA.data('approval-pct')) || 0) - (parseFloat($cardB.data('approval-pct')) || 0);
            default:
              return 0;
          }
        });

        // Update display
        updateDisplay(filteredCards);
        updateFilterSummary();
      }

      function updateDisplay(filteredCards) {
        const $grid = $('#companies-grid');
        const $noResults = $('#no-results-state');
        
        // Hide all cards first
        $('.company-card').hide();
        
        if (filteredCards.length > 0) {
          // Show filtered cards
          filteredCards.forEach(card => {
            $(card).show();
          });
          
          // Reorder cards in DOM
          $grid.append(filteredCards);
          
          // Hide no results state
          $noResults.hide();
        } else {
          // Show no results state
          $noResults.show();
        }

        // Update results count
        $('#results-count').text(filteredCards.length);
      }

      function updateFilterSummary() {
        const activeFilters = [];
        
        if (currentFilters.search) {
          activeFilters.push(`{{ __('cycles.search') }}: "${currentFilters.search}"`);
        }
        if (currentFilters.judge) {
          const judgeSelect = $('#judge-filter');
          const judgeText = judgeSelect.find('option:selected').text();
          activeFilters.push(`{{ __('cycles.Judge') }}: ${judgeText}`);
        }
        if (currentFilters.score) {
          const scoreLabels = {
            'excellent': '{{ __('cycles.Excellent') }}',
            'good': '{{ __('cycles.Good') }}',
            'average': '{{ __('cycles.Average') }}',
            'poor': '{{ __('cycles.Poor') }}'
          };
          activeFilters.push(`{{ __('cycles.Score') }}: ${scoreLabels[currentFilters.score]}`);
        }
        if (currentFilters.status) {
          const statusLabels = {
            'published': '{{ __('cycles.Published') }}',
            'draft': '{{ __('cycles.Draft') }}',
            'not-evaluated': '{{ __('cycles.Not Evaluated') }}',
            'mixed': '{{ __('cycles.Mixed Status') }}'
          };
          
          activeFilters.push(`{{ __('cycles.Status') }}: ${statusLabels[currentFilters.status]}`);
        }

        const $filterText = $('#active-filters-text');
        if (activeFilters.length > 0) {
          $filterText.html(`<strong>{{ __('cycles.Active filters') }}:</strong> ${activeFilters.join(', ')}`);
          $('#clear-filters').removeClass('d-none');
        } else {
          $filterText.text('{{ __('cycles.No active filters') }}');
          $('#clear-filters').addClass('d-none');
        }
      }

      function clearAllFilters() {
        // Reset form inputs
        $('#company-search').val('');
        $('#judge-filter, #score-filter, #status-filter').val('');
        $('#sort-filter').val('name-asc');
        
        // Reset current filters
        currentFilters = {
          search: '',
          judge: '',
          score: '',
          status: '',
          sort: 'name-asc'
        };
        
        // Apply filters (will show all cards)
        applyFilters();
      }

      // Original changestep function
      function changestep(company, step) {
        if (confirm("{{ __('cycles.sure change step') }}")) {
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
          });
          $.ajax({
            type: 'POST',
            url: '/dashboard/screening/changestep',
            data: {
              company: company,
              step: step,
            },
            success: function (response) {
              console.log(response);
              location.href = '/dashboard/screening/results/{{ $id }}';
            },
            error: function (error) {
              console.log(error);
              alert('Error Occurred, arrange failed');
            },
          });
        } else {
          location.href = '/dashboard/screening/results/{{ $id }}';
        }
      }

      // Show Company Evaluation Modal
    $(document).on('click', '.show-company-evaluation', function(e) {
      e.preventDefault();
      const company = parseInt($(this).data('company'));
      const step = parseInt($(this).data('step'));
      
      // Show modal
      $('#evaluationModal').modal('show');
      
      // Reset modal body
      $('#evaluationModalBody').html(`
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
          buildEvaluationModal(response);
        }, 
        error: function (error) { 
          $('#evaluationModalBody').html(`
            <div class="alert alert-danger m-4">
              <i class="ti ti-alert-circle me-2"></i>
              Error loading evaluation details. Please try again.
            </div>
          `);
        }
      });
    });

    function buildEvaluationModal(data) {
      const totalScore = (data.total * 100).toFixed(1);

      let modalContent = `
        <style>
          .judge-card-ribbon { position: relative; overflow: hidden; }
          .approval-ribbon {
            position: absolute; top: 2px; right: -8px;
            width: 180px; padding: 6px 0;
            transform: translate(50px, 18px) rotate(45deg);
            font-size: 10px; font-weight: 600;
            text-align: center; white-space: nowrap;
            text-transform: uppercase; letter-spacing: 0.5px;
            z-index: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.15);
          }
          .approval-ribbon.ribbon-approved { background: #28a745; color: #fff; }
          .approval-ribbon.ribbon-disapproved { background: #dc3545; color: #fff; }
          .approval-ribbon.ribbon-pending { background: #ffc107; color: #212529; }
        </style>
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
                          ${totalScore >= 75 ? '#28a745' : (totalScore >= 50 ? '#17a2b8' : (totalScore >= 25 ? '#ffc107' : '#dc3545'))}
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
        const statusClass = judge.evaluation.status === 'Published' ? 'success' :
                           judge.evaluation.status === 'Draft' ? 'warning' : 'danger';
        const statusIcon = judge.evaluation.status === 'Published' ? 'circle-check' :
                          judge.evaluation.status === 'Draft' ? 'clock' : 'circle-x';
        const approvalLabel = judge.evaluation.approval === 'approved' ? '{{ __("cycles.Approved") }}' :
                          judge.evaluation.approval === 'disapproved' ? '{{ __("cycles.Disapproved") }}' : '{{ __("cycles.Pending") }}';
        const ribbonClass = judge.evaluation.approval === 'approved' ? 'ribbon-approved' :
                          judge.evaluation.approval === 'disapproved' ? 'ribbon-disapproved' : 'ribbon-pending';

        modalContent += `
                <div class="col-md-4">
                  <div class="card border shadow-sm h-100 judge-card-ribbon">
                    <span class="approval-ribbon ${ribbonClass}">${approvalLabel}</span>
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
            const statusClass = judgeDetail.status === 'Published' ? 'success' :
                               judgeDetail.status === 'Draft' ? 'warning' : 'danger';

            modalContent += `
                      <div class="col-md-6">
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
        const statusClass = judge.evaluation.status === 'Published' ? 'success' :
                           judge.evaluation.status === 'Draft' ? 'warning' : 'danger';
        const approvalBadgeClass = judge.evaluation.approval === 'approved' ? 'success' :
                           judge.evaluation.approval === 'disapproved' ? 'danger' : 'warning';
        const approvalBadgeIcon = judge.evaluation.approval === 'approved' ? 'circle-check' :
                           judge.evaluation.approval === 'disapproved' ? 'times-circle' : 'clock';
        const approvalLabel = judge.evaluation.approval === 'approved' ? '{{ __("cycles.Approved") }}' :
                           judge.evaluation.approval === 'disapproved' ? '{{ __("cycles.Disapproved") }}' : '{{ __("cycles.Pending") }}';

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
                        <div class="me-2">
                          <span class="badge bg-${approvalBadgeClass} bg-opacity-10 text-${approvalBadgeClass} px-3 py-2"><i class="far fa-fw fa-sm fa-${approvalBadgeIcon} me-1"></i>${approvalLabel}</span>
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

        if (judge.evaluation.status === 'Not Evaluated') {
          modalContent += `
                      <div class="text-center text-muted py-5">
                        <i class="ti ti-clock fs-1 mb-4 opacity-50"></i>
                        <h6 class="text-muted">{{ __('cycles.This judge has not evaluated this company yet.') }}</h6>
                      </div>`;
        } else {
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

          // Form submission comments
          if (judge.form_comments && judge.form_comments.length > 0) {
            modalContent += `
                      <div class="mt-5 pt-4 border-top">
                        <h6 class="fw-bold mb-3">
                          <i class="ti ti-message-dots text-primary me-2"></i>
                          {{ __('cycles.Submission Comments') }}
                        </h6>
                        <div class="list-group list-group-flush">`;
            judge.form_comments.forEach(fc => {
              modalContent += `
                          <div class="list-group-item border-start border-primary border-3 mb-2 rounded bg-light">
                            <div class="fw-semibold text-dark mb-1">${fc.question}</div>
                            <div class="text-muted fst-italic">"${fc.comment}"</div>
                          </div>`;
            });
            modalContent += `
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

      $('#evaluationModalBody').html(modalContent);
    }
    </script>
  @stop
