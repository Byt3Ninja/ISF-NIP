<!-- Task List Table -->
    <div class="card">
      <div class="card-header border-bottom">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('admin.tasks_management') }}</h5>
          <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="ti ti-plus"></i>
            <span class="d-none d-md-inline ms-md-2">{{ __('admin.add_new_task') }}</span>
          </button>
        </div>
      </div>
      <div class="card-datatable table-responsive">
        <table class="datatables-tasks table">
          <thead class="table-light">
            <tr>
              <th></th>
              <th class="text-nowrap">{{ __('admin.task') }}</th>
              <th class="text-nowrap">{{ __('admin.Program') }}</th>
              <th class="text-nowrap">{{ __('admin.assignees') }}</th>
              <th class="text-nowrap">{{ __('admin.Priority') }}</th>
              <th class="text-nowrap">{{ __('admin.Status') }}</th>
              <th class="text-nowrap">{{ __('admin.Due_Date') }}</th>
              <th class="text-nowrap">{{ __('admin.Actions') }}</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
    <!--/ Task List Table -->