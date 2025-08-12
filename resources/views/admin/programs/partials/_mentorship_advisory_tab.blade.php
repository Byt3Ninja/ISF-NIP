<div>
  <!-- Filters for this specific report -->
  <div class="bg-label-light p-4 rounded-3">
    <h5 class="fw-semibold"><i class="fas fa-filter"></i>
      {{ __('admin.Filter Options (Mentorship)') }}</h5>
    <form class="row g-3 align-items-end mentorship-filter-form" method="GET"
      action="{{ route('programs.report',$cycle->id) }}">
      <div class="col-md-3"><label for="mentorFilterMentor" class="form-label">{{ __('admin.Mentor') }}</label>
        <select class="form-select select2" id="mentorFilterMentor" name="mentor">
          <option value="">{{ __('admin.All') }}</option>
          @foreach($mentors as $mentor)
          <option value="{{ $mentor->id }}" {{ request('mentor')==$mentor->id ? 'selected' : '' }}>{{ $mentor->name }}
          </option>
          @endforeach
        </select>
      </div>
      @if (count($companies) > 0)
      <div class="col-md-3"><label for="mentorFilterCompany" class="form-label">{{ __('admin.Company') }}</label>
        <select class="form-select select2" id="mentorFilterCompany" name="company">
          <option value="">{{ __('admin.All') }}</option>
          @foreach($companies as $company)
          <option value="{{ $company->id }}" {{ request('company')==$company->id ? 'selected' : '' }}>{{
            $company->{'name_'.app()->getLocale()} }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="col-md-3"><label for="mentorFilterDateStart" class="form-label">{{ __('admin.From Date')
          }}</label><input type="date" class="form-control" id="mentorFilterDateStart" name="zoom_date"></div>
      <div class="col-md-3">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill"><i
              class="fas fa-search me-2"></i> {{ __('admin.Search') }}</button>
          <a href="{{ route('programs.report', $cycle->id) }}" class="btn btn-outline-secondary flex-fill"><i
              class="fas fa-undo me-2"></i> {{ __('admin.Reset') }}</a>
        </div>
      </div>
    </form>
  </div>
  <!-- Content from mentorship report -->
  <!-- KPIs Row -->
  <div class="row justify-content-center mb-4 mt-4">
    <div class="col-lg-3 col-md-6">
      <div class="card card-body text-center mb-6"> <i class="fas fa-users-gear fs-3 mb-3 text-primary"></i>
        <div class="display-6 fw-bold text-dark-sm">{{ $mentorshipRequests->count() }}</div>
        <div class="text-lg fw-semibold text-dark">{{ __('admin.Total Sessions') }}</div>
      </div>
    </div>
    @php
    $totalMinutes = $mentorshipRequests->sum('period');
    $totalHours = $totalMinutes / 60;
    @endphp

    <div class="col-lg-3 col-md-6">
      <div class="card card-body text-center mb-6"> <i class="fas fa-clock fs-3 mb-3 text-success"></i>
        <div class="display-6 fw-bold text-dark-sm"> +{{ number_format($totalHours, 1) }} </div>
        <div class="text-lg fw-semibold text-dark">{{ __('admin.Total Hours') }}</div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="card card-body text-center mb-6"> <i class="fas fa-star fs-3 mb-3 text-warning"></i>
        <div class="display-6 fw-bold text-dark-sm">{{ number_format($mentorshipRequests->avg('rating')) }}</div>
        <div class="text-lg fw-semibold text-dark">{{ __('admin.Average Rating') }}</div>
      </div>
    </div>
  </div>
  <div class="row mt-2">
    <div class="col-lg-7">
      <!-- Table 1: Mentorship Details per Company -->
      @php
      $groupedByCompany = $mentorshipRequests->groupBy('user_id');
      @endphp

      <div class="mb-4">
        <h4 class="fw-semibold border-bottom border-2 border-primary"><i
            class="fas fa-building-user text-primary me-2"></i>
          {{ __('admin.Mentorship Details by Company') }}
        </h4>
        <div class="table-responsive">
          <!-- Copied Mentorship Table 1 Structure Here -->
          <table class="table table-hover align-middle mentorship-company-table">
            <thead>
              <tr>
                <th scope="col">{{ __('admin.company name') }}</th>
                <th scope="col">{{ __('admin.Mentor') }}</th>
                <th scope="col">{{ __('admin.Sessions') }}</th>
                <th scope="col">{{ __('admin.Hours') }}</th>
                <th scope="col">{{ __('admin.Mentor Rating') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($groupedByCompany as $userId => $requests)
              @php
              $company = $requests->first()->user->company;
              $mentor = $requests->first()->mentor;
              $totalSessions = $requests->count();
              $totalMinutes = $requests->sum('period');
              $totalHours = $totalMinutes / 60;
              $avgRating = $requests->first()->rating;
              @endphp
              <tr>
                <td class="text-end">{{ $company?->{'name_' . app()->getLocale()} }}</td>
                <td>{{ $mentor->first_name_en . ' ' . $mentor->last_name_en ?? 'N/A' }}</td>
                <td>{{ $totalSessions }}</td>
                <td>{{ number_format($totalHours, 1) }}</td>
                <td>
                  <span class="badge bg-label-{{ $avgRating >= 4.5 ? 'success' : 'primary' }}">
                    {{ number_format($avgRating, 1) }}/5
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <!-- Table 2: Mentor Performance -->
      @php
      $groupedByMentor = $mentorshipRequests->groupBy('mentor_id');
      @endphp

      <div>
        <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-user-tie text-primary me-2"></i>
          {{ __('admin.Mentor Performance') }}</h4>
        <div class="table-responsive">
          <!-- Copied Mentorship Table 2 Structure Here -->
          <table class="table table-hover align-middle mentorship-mentor-table">
            <thead>
              <tr>
                <th scope="col">{{ __('admin.Mentor') }}</th>
                <th scope="col">{{ __('admin.Total Sessions') }}</th>
                <th scope="col">{{ __('admin.Total Hours') }}</th>
                <th scope="col">{{ __('admin.Average Rating') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($groupedByMentor as $mentorId => $requests)
              @php
              $mentor = $requests->first()->mentor;
              $totalSessions = $requests->count();
              $totalMinutes = $requests->sum('period');
              $totalHours = $totalMinutes / 60;
              $avgRating = $requests->whereNotNull('rating')->avg('rating');
              @endphp
              <tr>
                <td class="text-end">{{ $mentor->name ?? 'N/A' }}</td>
                <td>{{ $totalSessions }}</td>
                <td>{{ $totalHours }}</td>
                <td>
                  <span class="badge bg-label-{{ $avgRating >= 4.5 ? 'success' : 'primary' }}">
                    {{ number_format($avgRating, 1) }}/5
                  </span>
                </td>
              </tr>
              @endforeach

            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <!-- Chart 1: Session Distribution -->
      <div class="mb-4">
        <h4 class="fw-semibold border-bottom border-2 border-primary">
          <i class="fas fa-chart-pie text-primary me-2"></i> {{ __('admin.Session Distribution') }}
        </h4>
        <div class="chart-container">
          <canvas id="mentorSessionsChart"></canvas>
        </div>
      </div>
      <!-- Chart 2: Mentor Average Rating -->
      <div>
        <h4 class="fw-semibold border-bottom border-2 border-primary"><i
            class="fas fa-chart-bar text-primary me-2"></i>{{ __('admin.Mentor Average Ratings') }}</h4>
        <div class="chart-container">
          <canvas id="mentorRatingChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>