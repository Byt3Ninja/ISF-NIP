@extends('layouts.admin')

@php
$permissions = [
[
'name' => 'users.list',
'description' => 'List Users',
'group' => 'User Management',
],
[
'name' => 'users.create',
'description' => 'Create User',
'group' => 'User Management',
],
[
'name' => 'users.update',
'description' => 'Update User',
'group' => 'User Management',
],
[
'name' => 'users.delete',
'description' => 'Delete User',
'group' => 'User Management',
],
[
'name' => 'permissions.list',
'description' => 'List Permissions',
'group' => 'Permissions',
],
[
'name' => 'permissions.create',
'description' => 'Create Permission',
'group' => 'Permissions',
],
[
'name' => 'permissions.update',
'description' => 'Update Permission',
'group' => 'Permissions',
],
[
'name' => 'permissions.delete',
'description' => 'Delete Permission',
'group' => 'Permissions',
],
[
'name' => 'dashboard.view',
'description' => 'View Dashboard',
'group' => 'Dashboard',
],
[
'name' => 'settings.view',
'description' => 'View Settings',
'group' => 'Settings',
],
[
'name' => 'settings.update',
'description' => 'Update Settings',
'group' => 'Settings',
],
[
'name' => 'reports.view',
'description' => 'View Reports',
'group' => 'Reports',
],
[
'name' => 'reports.export',
'description' => 'Export Reports',
'group' => 'Reports',
],
[
'name' => 'audit_logs.view',
'description' => 'View Audit Logs',
'group' => 'Audit Logs',
],
[
'name' => 'audit_logs.delete',
'description' => 'Delete Audit Logs',
'group' => 'Audit Logs',
],
[
'name' => 'notifications.manage',
'description' => 'Manage Notifications',
'group' => 'Notifications',
],
[
'name' => 'projects.view',
'description' => 'View Projects',
'group' => 'Projects',
],
[
'name' => 'projects.create',
'description' => 'Create Projects',
'group' => 'Projects',
],
[
'name' => 'projects.update',
'description' => 'Update Projects',
'group' => 'Projects',
],
[
'name' => 'projects.delete',
'description' => 'Delete Projects',
'group' => 'Projects',
],
[
'name' => 'tasks.view',
'description' => 'View Tasks',
'group' => 'Tasks',
],
[
'name' => 'tasks.create',
'description' => 'Create Tasks',
'group' => 'Tasks',
],
[
'name' => 'tasks.update',
'description' => 'Update Tasks',
'group' => 'Tasks',
],
[
'name' => 'tasks.delete',
'description' => 'Delete Tasks',
'group' => 'Tasks',
],
[
'name' => 'clients.view',
'description' => 'View Clients',
'group' => 'Clients',
],
[
'name' => 'clients.create',
'description' => 'Create Clients',
'group' => 'Clients',
],
[
'name' => 'clients.update',
'description' => 'Update Clients',
'group' => 'Clients',
],
[
'name' => 'clients.delete',
'description' => 'Delete Clients',
'group' => 'Clients',
],
];

$tabs = collect($permissions)->groupBy('group')->map(function ($groupPermissions, $group) {
return [
'target' => Str::slug($group),
'label' => $group,
];
})->values()->toArray();
@endphp

@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="card">
      <div class="card-header border-bottom mb-6">
        <div class="row d-flex justify-content-between align-items-center ps-0 ps-md-6">
          <div class="col-12 col-md-8">
            <h4 class="fw-bold mb-3 mb-md-0">{{ __('admin.EditRole') }}</h4>
          </div>
            <div class="col-12 col-md-4">
            <select class="form-select select2" id="group-select" data-placeholder="{{ __('admin.Search groups...') }}">
              <option value="" disabled selected>{{ __('admin.Search groups...') }}</option>
              @foreach ($tabs as $tab)
              <option value="{{ Str::slug($tab['label'], '-') }}">{{ $tab['label'] }}</option>
              @endforeach
            </select>
            </div>
        </div>
      </div>
      <div class="card-body">
        <!-- Tabs (mobile) -->
        <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none">
          <ul class="nav nav-pills flex-nowrap" role="tablist">
            @foreach ($tabs as $key => $tab)
            <li class="nav-item d-flex justify-content-center align-items-center">
              <a href="#navs-left-{{ $tab['target'] }}" class="nav-link {{ $key == 0 ? 'active' : '' }}"
                data-bs-toggle="tab" role="tab">
                {{-- <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i> --}}
                {{ $tab['label'] }}
              </a>
            </li>
            @endforeach
          </ul>
        </div>
        <!-- / Tabs (mobile) -->

        <div class="row">
          <div class="col-12 col-md-3 d-none d-md-block">
            <div class="w-100" style="height: 600px; overflow-y: auto;">
              <nav class="nav-align-left mb-6 p-4 bg-white w-100 bg-danger">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-fill border-none m-auto w-100" role="tablist" id="group-tabs">
                  @foreach ($tabs as $key => $tab)
                  <li 
                    class="nav-item bg-label-light mb-2 rounded overflow-x-hidden d-flex align-items-center group-tab"
                    data-label="{{ Str::slug($tab['label'], '-') }}"
                  >
                    <a href="#navs-left-{{ $tab['target'] }}" class="nav-link {{ $key == 0 ? 'active' : '' }} py-3"
                      data-bs-toggle="tab" role="tab">
                      {{-- <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i> --}}
                      {{ $tab['label'] }}
                    </a>
                  </li>
                  @endforeach
                </ul>
                <!-- / Tabs -->
              </nav>
            </div>
          </div>

          <div class="col-12 col-md-9">
            <!-- Tab Content On Tab Click -->
            <div class="tab-content p-0" id="tab-content">
              @foreach ($tabs as $index => $tab)
              <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }} mb-6"
                id="navs-left-{{ strtolower($tab['target']) }}">
                <div class="card shadow-none">
                  <div class="card-header ms-2 p-0 p-md-auto">
                    <div class="row pb-3 border-bottom mb-4">
                      <div class="col-12 col-md-8">
                        <h5 class="card-title mb-0 fw-semibold pt-2 mb-3 mb-md-0">{{ $tab['label'] }}</h5>
                      </div>
                      <div class="col-12 col-md-4">
                        <!-- Search Input -->
                        <div class="position-relative">
                          <input type="text" id="permission-search" class="form-control"
                            placeholder="{{ __('admin.Search_Permissions') }}..." />
                            <span class="position-absolute end-0 top-50 translate-middle-y search-icon mx-2">
                              <i class="ti ti-search text-muted"></i>
                            </span>
                          </div>
                        <!-- / Search Input -->
                      </div>
                    </div>
                  </div>
                  <div class="p-0 w-100">
                    <div class="permission-list w-100" >
                      @foreach (collect($permissions)->where('group', $tab['label']) as $permission)
                      <div class="card card-body border mb-3">
                        <li class="d-flex justify-content-between align-items-center">
                          <div class="d-flex flex-column">
                            <h5 class="mb-0">{{ $permission['description'] }}</h5>
                            <span>{{ $permission['name'] }}</span>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]"
                              value="{{ $permission['name'] }}" id="permission-{{ $permission['name'] }}">
                            <label class="form-check-label" for="permission-{{ $permission['name'] }}">
                              {{ __('admin.Select') }}
                            </label>
                          </div>
                        </li>
                      </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            <!-- / Tab Content On Tab Click -->
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->
</div>
@stop

@section('pagescripts')
<script>
  // Activate navigation tabs based on selected group
  const groupSelect = $('#group-select');
  groupSelect.on('change', function () {
    const selectedGroup = $(this).val().toLowerCase().trim();
    console.log('selectedGroup', selectedGroup);
    $('.group-tab').each(function () {
      const groupLabel = $(this).data('label').toLowerCase().trim();
      console.log('groupLabel', groupLabel);
      if (groupLabel === selectedGroup) {
        $(this).addClass('active');
        $(`#navs-left-${groupLabel}`).addClass('show active');
        $(this).find('a').addClass('active'); // Activate the nav item
      } else {
        $(this).removeClass('active');
        $(`#navs-left-${groupLabel}`).removeClass('show active');
        $(this).find('a').removeClass('active'); // Deactivate the nav item
      }
    });
  });

  $('#permission-search').on('input', function () {
    const query = $(this).val().toLowerCase();
    $('.permission-list li').each(function () {
      const text = $(this).text().toLowerCase();
      $(this).parent().toggle(text.includes(query));
    });
  });
</script>
@stop
{{-- @extends('layouts.admin')
@section('title', __('admin.Roles'))
@section('index_url', route('roles.index'))
@section('subTitle', __('admin.EditRole'))

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-body">
      <section id="basic-input">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header mb-6" style="height: 70px;">
                <h4 class="card-title border-bottom pb-3 fw-bold">{{ __('admin.EditPermission') }}</h4>
              </div>
              <div class="card-content">
                <div class="card-body">
                  @if(session()->has('msg'))
                  <div class="alert alert-warning alert-danger mb-2" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <div class="d-flex align-items-center">
                      <i class="ti ti-error"></i>
                      <span>
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
                      </span>
                    </div>
                  </div>
                  @endif
                  <!-- Validation messages -->
                  @if($errors->any())
                  <div class="alert alert-danger">
                    <ul>
                      @foreach($errors->all() as $error)
                      <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                  @endif
                  <form method="POST" action="{{ route('roles.update', [$role->id]) }}" enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="row mb-6">
                      <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fs-6 mb-1 fw-semibold">{{ __('admin.Title') }}</label>
                        <input type="text" class="form-control" id="name" name="name" required value="{{ $role->name }}">
                      </div>
                      
                      <div class="col-12 col-md-6 form-group mb-6">
                        <label class="form-label fs-6 mb-1 fw-semibold" for="default_for">{{ __('admin.roleType') }}</label>
                        <select name="default_for" id="default_for" class="form-select border-light light-shadow">
                          <option value="admin" {{ $role->default_for == 'admin' ? 'selected' : '' }}>{{ __('admin.roleAdmin') }}</option>
                          <option value="editor" {{ $role->default_for == 'editor' ? 'selected' : '' }}>{{ __('admin.roleEditor') }}</option>
                          <option value="coordinator" {{ $role->default_for == 'coordinator' ? 'selected' : '' }}>{{ __('admin.roleCoordinator') }}</option>
                          <option value="page" {{ $role->default_for == 'page' ? 'selected' : '' }}>{{ __('admin.rolePageManager') }}</option>
                          <option value="manager" {{ $role->default_for == 'manager' ? 'selected' : '' }}>{{ __('admin.roleManager') }}</option>
                          <option value="mentor" {{ $role->default_for == 'mentor' ? 'selected' : '' }}>{{ __('admin.roleMentor') }}</option>
                          <option value="judger" {{ $role->default_for == 'judger' ? 'selected' : '' }}>{{ __('admin.roleJudger') }}</option>
                          <option value="registered" {{ $role->default_for == 'registered' ? 'selected' : '' }}>{{ __('admin.roleRegistered') }}</option>
                          <option value="pages" {{ $role->default_for == 'pages' ? 'selected' : '' }}>{{ __('admin.rolePagesEditor') }}</option>
                          <option value="organizationmanager" {{ $role->default_for == 'organizationmanager' ? 'selected' : '' }}>{{ __('admin.roleOrganizationManager') }}</option>
                          <option value="other" {{ $role->default_for == 'other' ? 'selected' : '' }}>{{ __('admin.roleOther') }}</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-12 mb-6">
                      <div class="w-100 d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3">
                        <h5 class="mb-6 mb-md-0 fw-semibold">{{ __('admin.Role_Permissions') }}</h5>
                        <div class="d-flex align-items-center gap-6">
                          <div class="form-group">
                            <input type="text" class="form-control" id="search-permissions" placeholder="{{ __('admin.Search_Permissions') }}">
                          </div>
                        </div>
                      </div>
                      @php
                        $groupedPermissions = [];
                        foreach ($_permissions as $id => $permission) {
                          $parts = explode('.', $permission);
                          $module = ucfirst($parts[0]);
                          $action = ucfirst($parts[1] ?? '');
                          $groupedPermissions[$module][$action] = $id;
                        }
                      @endphp

                      @foreach($groupedPermissions as $module => $actions)
                        <div class="row permission-group mb-3 border-bottom pt-3 pb-2" data-module="{{ strtolower($module) }}">
                          <div class="col-12 d-flex align-items-center justify-content-between mb-4">
                            <h6 class="me-4 fw-semibold">{{ str_replace('_', ' ', $module) }}</h6>                     
                            <div class="form-check me-3">
                              <input type="checkbox" class="form-check-input select-all-row" id="select-all-{{ strtolower($module) }}">
                              <label class="form-check-label" for="select-all-{{ strtolower($module) }}">{{ __('admin.Select_All') }}</label>
                            </div>
                          </div>

                          <div class="col-12 d-flex flex-wrap align-items-center gap-3 pt-2">
                            @foreach($actions as $action => $id)
                              <div class="form-check">
                                <input type="checkbox" class="form-check-input permission-checkbox" id="permission_{{ $id }}" name="permissions[]" value="{{ $id }}" {{ $role->permissions->contains($id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="permission_{{ $id }}">{{ str_replace('_', ' ', $action ? $action : $module) }}</label>
                              </div>
                            @endforeach
                          </div>
                        </div>
                      @endforeach
                    </div>
                    
                    <div class="d-flex justify-content-end align-items-center gap-6 mt-8">
                      <button type="reset" class="btn btn-label-secondary">{{ __('admin.Reset') }}</button>
                      <button type="submit" class="btn btn-primary">{{ __('admin.Submit') }}</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  document.getElementById('search-permissions').addEventListener('input', function() {
    var searchValue = this.value.toLowerCase();
    document.querySelectorAll('.permission-group').forEach(function(group) {
      var moduleName = group.getAttribute('data-module');
      if (moduleName.includes(searchValue)) {
        group.style.display = '';
      } else {
        group.style.display = 'none';
      }
    });
  });

  document.querySelectorAll('.select-all-row').forEach(function(selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      var isChecked = this.checked;
      var module = this.id.replace('select-all-', '').replace(/_/g, '\\_');
      var checkboxes = document.querySelectorAll('.permission-group[data-module="' + module + '"] .permission-checkbox');
      checkboxes.forEach(function(checkbox) {
        checkbox.checked = isChecked;
      });
    });
  });

  // Ensure the "Select All" checkbox in each row updates correctly when individual checkboxes are changed
  document.querySelectorAll('.permission-checkbox').forEach(function(permissionCheckbox) {
    permissionCheckbox.addEventListener('change', function() {
      var module = this.closest('.permission-group').getAttribute('data-module');
      var allCheckboxes = document.querySelectorAll('.permission-group[data-module="' + module + '"] .permission-checkbox');
      var allChecked = Array.from(allCheckboxes).every(function(checkbox) {
        return checkbox.checked;
      });
      document.getElementById('select-all-' + module).checked = allChecked;
    });
  });
</script>
@stop
 --}}
