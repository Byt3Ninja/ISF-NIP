<div>
    <!-- Filters for this specific report -->
    <div class="bg-label-light p-4 rounded-3">
        <h5 class="fw-semibold"><i class="fas fa-filter"></i> {{ __('admin.Filter Options (Final)') }}</h5>
        <form class="row g-2 align-items-end final-eval-filter-form" method="GET"
            action="{{ route('programs.report', $cycle->id) }}">
            <div class="col-md-4">
                <label for="feFilterStatus" class="form-label">{{ __('admin.Status') }}</label>
                <select class="form-select select2" id="feFilterStatus" name="status">
                    <option value="">{{ __('admin.All') }}</option>
                    <option value="Highest Rated" {{ request('status')=='Highest Rated' ? 'selected' : '' }}>{{
                        __('admin.Highest Rated') }}</option>
                    <option value="Good Performance" {{ request('status')=='Good Performance' ? 'selected' : '' }}>{{
                        __('admin.Good Performance') }}</option>
                    <option value="Needs Improvement" {{ request('status')=='Needs Improvement' ? 'selected' : '' }}>{{
                        __('admin.Needs Improvement') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="feFilterMinScore" class="form-label">{{ __('admin.Minimum Score') }}</label>
                <input type="number" class="form-control" id="feFilterMinScore" name="min_score" min="0" max="100"
                    step="5" value="{{ request('min_score') }}" placeholder="e.g., 70">
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search me-2"></i> {{ __('admin.Search') }}</button>
                    <a href="{{ route('programs.report', $cycle->id) }}" class="btn btn-outline-secondary flex-fill"><i class="fas fa-undo me-2"></i> {{ __('admin.Reset') }}</a>
                </div>
            </div>
        </form>
    </div>
    <!-- Content from final evaluation report -->
    <div class="row mt-4">
        <div class="col-lg-6 border-0 border-lg-1 border-end pe-lg-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                        class="fas fa-clipboard-list me-2 text-primary"></i> {{ __('admin.Final Company Evaluations') }}
                </h4>
            </div>
            <div class="table-responsive">
                <table id="finalEvalTable" class="table table-striped table-hover align-middle final-eval-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('admin.Rank') }}</th>
                            <th scope="col">{{ __('admin.company_name') }}</th>
                            <th scope="col">{{ __('admin.Total Score') }}</th>
                            <th scope="col">{{ __('admin.Judge Comments (Summary)') }}</th>
                            <th scope="col">{{ __('admin.Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach( $publishedCompanies as $index => $eval )
                        <tr>
                            <td class="text-center rank-col">
                                {{ $index + 1 }}
                                @if ($index == 0)
                                <i class="fas fa-medal text-gold"></i>
                                @elseif ($index == 1)
                                <i class="fas fa-award text-silver"></i>
                                @elseif ($index == 2)
                                <i class="fas fa-bronze text-bronze"></i>
                                @endif
                            </td>
                            <td class="text-end">{{ $eval['name'] }}</td>
                            <td class="text-center score-col">{{ $eval['score'] }} / 100</td>
                            <td class="text-end comments-col">
                                @foreach( $eval['company_judges'] as $judge )
                                    @if( !empty( $judge->comments ) ) <div>{{ $judge->comments }}</div> @endif
                                @endforeach
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge bg-label-{{ $eval['status'] == 'Highest Rated' ? 'success' : ($eval['status'] == 'Good Performance' ? 'primary' : 'danger') }} status-badge">
                                    {{ __($eval['status']) }}
                                </span>
                            </td>
                        </tr>
                        {{--  @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ __('admin.No evaluations found') }}</td>
                        </tr>  --}}
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Chart: Score Distribution -->
            <div class="mt-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                        class="fas fa-chart-bar text-primary me-2"></i> {{ __('admin.Score Distribution') }}</h4>
                <div class="chart-container">
                    <canvas id="finalEvalScoreDistributionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 ps-lg-4">
            <!-- Evaluator Status -->
            <div class="mb-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                        class="fas fa-user-check text-primary me-2"></i> {{ __('admin.Evaluator Status') }}</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('admin.Evaluator Name') }}</th>
                                <th>{{ __('admin.Total Evaluations') }}</th>
                                <th>{{ __('admin.Published') }}</th>
                                <th>{{ __('admin.Draft') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluatorStatus as $evaluator)
                            <tr>
                                <td>{{ $evaluator['evaluator_name'] }}</td>
                                <td>{{ $evaluator['total_evaluations'] }}</td>
                                <td>{{ $evaluator['published_evaluations'] }}</td>
                                <td>{{ $evaluator['draft_evaluations'] }}</td>
                            </tr>
                            @endforeach
                          
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Results Summary -->
            <div>
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                        class="fas fa-bullseye text-primary me-2"></i> {{ __('admin.Final Results Summary') }}</h4>
                <h6 class="mb-2 fw-bold text-success">{{ __('admin.Highest Rated') }}:</h6>
                <ul class="summary-list" id="feTopCompaniesList">
                    @foreach ($publishedCompanies->where('status', 'Highest Rated')->take(3) as $eval)
                        <li><i class="fas fa-star"></i> {{ $eval['name'] }} <small>({{ $eval['score'] }}/100)</small></li>
                    @endforeach
                    @if ($publishedCompanies->where('status', 'Highest Rated')->isEmpty())
                        <li>{{ __('admin.No companies found') }}</li>
                    @endif
                </ul>
                <h6 class="mt-4 mb-2 fw-bold text-danger">{{ __('admin.Needs Improvement') }}:</h6>
                <ul class="summary-list" id="feImprovementCompaniesList">
                    @foreach ($publishedCompanies->where('status', 'Needs Improvement') as $eval)
                        <li><i class="fas fa-wrench"></i> {{ $eval['name'] }} <small>({{ $eval['score'] }}/100)</small></li>
                    @endforeach
                        @if ($publishedCompanies->where('status', 'Needs Improvement')->isEmpty())
                        <li>{{ __('admin.No companies found') }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>