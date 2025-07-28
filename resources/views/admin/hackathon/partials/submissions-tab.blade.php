<div class="card">

  <div class="card-body">
    <h5 class="card-title">
      {{ $l == 'en' ? 'Former Submissions Requests' : 'طلبات تقديم الشركات' }}
    </h5>
    <table class="table">
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
        @foreach ($data as $submission)

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
</div>