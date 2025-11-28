<!-- Forms Table -->
<div class="tab-pane fade" id="forms-pane" role="tabpanel">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center border-bottom">
      <h5 class="card-title mb-0">{{ __('forms.forms_list') }}</h5>
      <a href="/dashboard/show-builder" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> {{ __('forms.add_new_form') }}
      </a>
    </div>
    <div class="card-datatable table-responsive">
      <table class="table datatables-basic" id="formsTable">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">{{ __('forms.form_id') }}</th>
            <th style="width:110px;">{{ __('forms.form_name') }}</th>
            <th>{{ __('forms.assigned_organization') }}</th>
            <th>{{ __('forms.submissions') }}</th>
            <th style="width:140px;">{{ __('forms.last_modified') }}</th>
            <th style="width:140px;">{{ __('forms.created_date') }}</th>
            <th style="width:110px; text-align:center;">{{ __('forms.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @if(!empty($data) && count($data))
            @foreach($data as $form)
              @php
                $form_id = $form->form_id ?? ($form->id ?? '');
                $title = $form->form_name ?? $form->name ?? '—';
                $assignedOrg = $form->assigned_organization ?? ($form->organization_name ?? '—');
                $submissions = $form->submissions ?? 0;
                $updated = (isset($form->last_modified) && $form->last_modified != 'null') ? \Carbon\Carbon::parse($form->last_modified)->format('Y-m-d') : '—';
                $created = (isset($form->created_date) && $form->created_date != 'null') ? \Carbon\Carbon::parse($form->created_date)->format('Y-m-d') : '—';
                $org_id = $form->org_id ?? '';
              @endphp
              <tr data-org="{{ $assignedOrg }}">
                <td>{{ $form_id }}</td>
                <td>{{ $title }}</td>
                <td>{{ $assignedOrg }}</td>
                <td>{{ $submissions }}</td>
                <td>{{ $updated }}</td>
                <td>{{ $created }}</td>
                <td class="table-actions text-center">
                  <a href="/dashboard/show-form/{{ $form_id }}" class="btn btn-sm btn-label-primary" title="{{ __('forms.search') }}">
                    <i class="ti ti-eye ti-sm"></i>
                  </a>
                  <a href="/dashboard/show-builder/{{ $form_id }}{{ $org_id ? '?org_id=' . $org_id : '' }}"
                     class="btn btn-sm btn-label-info" title="Edit">
                    <i class="ti ti-edit ti-sm"></i>
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
