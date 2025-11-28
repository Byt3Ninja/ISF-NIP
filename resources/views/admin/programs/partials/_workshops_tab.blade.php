<div>
  <!-- Filters for this specific report -->
  <div class="bg-label-light p-4 rounded-3">
    <h5 class="fw-semibold"><i class="fas fa-filter"></i>
      {{ __('admin.Filter Options (Workshops)') }}</h5>
    <form class="row g-3 align-items-end workshops-filter-form" method="GET"
      action="{{ route('programs.report',$cycle->id) }}">
      @if (count($trainerNames) > 0)
      <div class="col-md-3"><label for="wsFilterTrainer" class="form-label">{{ __('admin.Trainer') }}</label><select
          class="form-select select2" id="wsFilterTrainer" name="trainer">
          <option selected value="">{{ __('admin.All') }}</option>
          @foreach ($trainerNames as $trainer)
          <option value="{{ $trainer }}" {{ request('trainer')==$trainer ? 'selected' : '' }}>{{ $trainer }}</option>
          @endforeach
        </select></div>
      @endif
      <div class="col-md-3"><label for="wsFilterWorkshop" class="form-label">{{ __('admin.Workshop Name') }}</label>
        <input type="text" class="form-control" id="wsFilterWorkshop" name="workshop_name">
      </div>
      <div class="col-md-3"><label for="wsFilterType" class="form-label">{{ __('admin.Workshop Type') }}</label>
        <select class="form-select select2" id="wsFilterType" name="type">
          <option selected value="">{{ __('admin.All') }}</option>
          <option value="online" {{ request('type')=='online' ? 'selected' : '' }}>{{ __('admin.Live Training') }}
          </option>
          <option value="offline" {{ request('type')=='offline' ? 'selected' : '' }}>{{ __('admin.In-Person') }}
          </option>
          <option value="sessions" {{ request('type')=='sessions' ? 'selected' : '' }}>{{ __('admin.Online') }}</option>
        </select>
      </div>
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
  <!-- Content from workshops report -->
  <!-- KPIs Row -->
  <div class="row justify-content-center mb-4 mt-4">
    <div class="col-lg-2 col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6"><i class="fas fa-chalkboard-user fs-3 mb-3 text-primary"></i>
        <div class="display-6 fw-bold text-dark-sm">{{ $totalWorkshops }}</div>
        <div class="text-lg fw-semibold text-dark text-nowrap">{{ __('admin.Total Workshops') }}</div>
      </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6"><i class="fas fa-clock fs-3 mb-3 text-success"></i>
        <div class="display-6 fw-bold text-dark-sm">+{{ $totalSessionHours }}</div>
        <div class="text-lg fw-semibold text-dark text-nowrap">{{ __('admin.Total Hours') }}</div>
      </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6"><i class="fas fa-video fs-3 mb-3 text-warning"></i>
        <div class="display-6 fw-bold text-dark-sm">{{ $zoomWorkshops }}</div>
        <div class="text-lg fw-semibold text-dark text-nowrap">{{ __('admin.Live Workshops') }}</div>
      </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6"><i class="fas fa-users fs-3 mb-3 text-info"></i>
        <div class="display-6 fw-bold text-dark-sm">{{ $inPersonWorkshops }}</div>
        <div class="text-lg fw-semibold text-dark text-nowrap">{{ __('admin.In-Person Workshops') }}</div>
      </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6"><i class="fas fa-file-upload fs-3 mb-3 text-success"></i>
        <div class="display-6 fw-bold text-dark-sm">{{ $uploadedMaterials }}</div>
        <div class="text-lg fw-semibold text-dark text-nowrap">{{ __('admin.Uploaded Materials') }}</div>
      </div>
    </div>

  </div>
  <div class="row mt-2">
    <div class="col-lg-7">
      <!-- Workshop Details Table -->
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-semibold border-bottom border-2 border-primary"><i
              class="fas fa-list-check text-primary me-2"></i> {{ __('admin.Workshop Details') }}</h4>
        </div>
        <div class="table-responsive">
          <!-- Copied Workshop Table 1 Structure Here -->
          <table id="workshopTable" class="table table-hover align-middle workshop-details-table">

            <thead>
              <tr>
                <th class="text-center">{{ __('admin.Workshop Name') }}</th>
                <th class="text-center">{{ __('admin.Trainer') }}</th>
                <th class="text-center">{{ __('admin.Participants') }}</th>
                <th class="text-center">{{ __('admin.type') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($workshops as $ws)
              <tr>
                <td class="text-center">{{ $ws->title }}</td>
                <td class="text-center">{{ $ws->trainer_name ?? '-' }}</td>
                <td>@if($ws->type == 'sessions' && $ws->reports->count() > 0)
                  {{ $ws->reports->count() ?? '-' }}
                  @else
                  {{ '-' }}
                  @endif
                </td>
                 <td class="text-center">
                  <span class="badge 
                  {{ $ws->type === 'sessions' ? 'bg-secondary' : 
                  ($ws->type === 'online' ? 'bg-info' : 'bg-label-warning text-dark') }}">
                     {{ $ws->type === 'sessions' ? __('admin.Live Training') : 
                   ($ws->type === 'online' ? __('admin.Online') : __('admin.In-Person')) }}
                 </span>
              </td>

              </tr>

              @endforeach

            </tbody>
          </table>
        </div>
      </div>
      <!-- Company Performance Table -->
      <div>
        <h4 class="fw-semibold border-bottom border-2 border-primary"><i
            class="fas fa-graduation-cap text-primary me-2"></i> {{ __('admin.Company Performance') }}</h4>
        <div class="table-responsive">
          <!-- Copied Workshop Table 2 Structure Here -->
          <table class="table table-hover align-middle workshop-company-perf-table">
            <thead>
              <tr>
                <th scope="col">{{ __('admin.company name') }}</th>
                <th scope="col">{{ __('admin.Score') }}</th>
                <th scope="col">{{ __('admin.Status') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($quizzesResults as $result)
              <tr>
                <td>{{ $result->{'c_company_name_'.$l} ?? '-' }}</td>
                <td> 
                  <span
											class="fw-bold fs-6 {{ $result->qr_result_score >= 50 ? 'text-success' : 'text-danger' }}">
											{{ $result->qr_result_score }}%
									</span></td>
                <td>
                  {!! $result->qr_result_status == 1
                    ? '<span class="badge bg-success rounded-pill">' . __('admin.Completed') . '</span>'
                     : '<span class="badge bg-danger rounded-pill">' . __('admin.Pending') . '</span>'
                 !!}
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <!-- Chart 1: Workshop Type Distribution -->
      <div class="mb-4">
        <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-chart-pie text-primary me-2"></i>
          {{ __('admin.Workshop Type Distribution') }}</h4>
        <div class="chart-container">
          <canvas id="workshopTypeChart"></canvas>
        </div>
      </div>
      <div>
        <h4 class="fw-semibold border-bottom border-2 border-primary">
          <i class="fas fa-poll text-primary me-2"></i> {{ __('admin.Survey Results') }}
        </h4>

        @if ($surveyResponses->isEmpty())
        <p class="text-muted text-center p-3 border rounded">
          <i class="fas fa-info-circle me-1"></i> {{ __('admin.No survey responses found') }}
        </p>
        @else
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th>{{ __('admin.User') }}</th>
                <th>{{ __('admin.Responses') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($surveyResponses as $response)
              <tr>
                <td>{{ $response->user->first_name_en . $response->user->last_name_en ?? '-' }}</td>
                <td>
                  @php
                  $decodedResponses = json_decode($response->responses, true);
                  @endphp

                  @if (is_array($decodedResponses))
                  <ul>
                    @foreach ($decodedResponses as $responseItem)
                    <li>
                      <strong>{{ $responseItem['question'] }}:</strong>
                      @php
                      $selectedAnswer = collect($responseItem['answers'])->firstWhere('status', true);
                      @endphp
                      @if ($selectedAnswer)
                      {{ $selectedAnswer['text'] }}
                      @endif
                    </li>
                    @endforeach
                  </ul>
                  @else
                  <span class="text-muted">{{ '-' }}</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>

    </div>
  </div>
</div>