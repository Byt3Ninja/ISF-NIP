<div class="card">
  <div class="card-header border-bottom mb-6">
    <h5 class="card-title mb-0">
      {{ $l == 'en' ? 'Former Submissions Requests' : 'طلبات تقديم الشركات' }}
    </h5>
  </div>
  <div class="card-body">
    <form action="/dashboard/hackathon/details/{{ $id }}" method="GET">
      <input type="hidden" name="tab" value="submissions-tab">
      <div class="d-flex align-items-center gap-4">
        <div class="media align-items-center col-md-3">
          <select class="form-select select2 " name="step_id" data-placeholder="{{ __('cycles.Select Step') }}" data-allow-clear="true">
            <option value="" disabled>{{ __('cycles.Select Step') }}</option>
            <option value="all" {{ request('step_id') === null || request('step_id') === '' || request('step_id') === 'all' ? 'selected' : '' }}>{{ __('cycles.AllـSteps') }}</option>
            <option value="0" {{ request('step_id') === '0' ? 'selected' : '' }}>{{ __('cycles.Disapprove') }}</option>
            @foreach ($steps as $step)
            <option value="{{ $step->id }}" {{ $step->id == request('step_id') ? 'selected' : '' }} >{{ $step->title }}
            </option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-filter me-2"></i> {{ __('cycles.Apply_Filters') }}
        </button>
      </div>
    </form>
  </div>
  <table class="table table-custom">
    <thead class="table-light">
      <tr>
        <th scope="col">{{ $l == 'en' ? 'Submitter' : 'المستخدم' }}</th>
        <th scope="col">{{ $l == 'en' ? 'Email' : 'البريد الاليكترونى' }}</th>
        <th scope="col">{{ $l == 'en' ? 'Date of Submission' : 'تاريخ التقديم' }}</th>
        <th scope="col">{{ $l == 'en' ? 'View Details' : 'عرض التفاصيل' }}</th>
        <th scope="col">{{ $l == 'en' ? 'Actions' : 'الإجراءات' }}</th>
      </tr>
    </thead>
    <tbody>
      @foreach( collect($data)->sortByDesc('submissionDate') as $submission )

      <tr>
        <td>{{ $submission->user_name }}</td>
        <td>{{ $submission->email }}</td>
        <td>{{ $submission->submissionDate }}</td>
        <td>
          <a target="_blank" title="view"
            href="/dashboard/hackathon/viewSubmission/{{ $submission->step_id }}/{{ $submission->user_id }}"
            class="btn btn-label-primary btn-sm">
            {{ $l == 'en' ? 'View' : 'عرض' }}
          </a>
        </td>
        <td>
          <div class="d-flex align-items-center gap-1">
            <select id="cstep" class="select form-control"
              onchange="changestep({{ $submission->company_id }},this.options[this.selectedIndex].value)">
              <option value="0">{{ __('cycles.Disapprove') }}</option>
              @foreach (\App\Step::where('cycle_id', $submission->cycle_id)->orderBy('arr')->get() as $step)
              <option value="{{ $step->id }}" {{ $step->id == $submission->company_step ? 'selected' : '' }}>
                {{ $step->title }}</option>
              @endforeach
            </select>
          </div>
        </td>
      </tr>

      @endforeach
    </tbody>
  </table>
</div>
