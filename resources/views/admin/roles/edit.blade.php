@extends('layouts.admin')
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
                <h4 class="card-title border-bottom pb-3">{{ __('admin.EditPermission') }}</h4>
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
                      <div class="col-md-6 mb-3">
                        <label for="default_for" class="form-label fs-6 mb-1 fw-semibold">{{ __('admin.roleType') }}</label>
                        <select name="default_for" id="default_for" class="form-select">
                          <option value="admin" {{ $role->default_for == 'admin' ? 'selected' : '' }}>{{ __('admin.roleAdmin') }}</option>
                          <option value="editor" {{ $role->default_for == 'editor' ? 'selected' : '' }}>{{ __('admin.roleEditor') }}</option>
                        </select>
                      </div>
                      <div class="col-12 col-md-6 form-group mb-6">
                        <label class="fs-5 fw-semibold mb-2" for="default_for">{{ __('admin.roleType') }}</label>
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
                        <h5 class="mb-6 mb-md-0">{{ __('admin.Role_Permissions') }}</h5>
                        <div class="d-flex align-items-center gap-6">
                          <div class="form-group">
                            <input type="text" class="form-control" id="search-permissions" placeholder="{{ __('admin.Search_Permissions') }}">
                          </div>
                          <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="select-all">
                            <label class="form-check-label" for="select-all">{{ __('admin.Select_All') }}</label>
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
                        <div class="row my-6 permission-group" data-module="{{ strtolower($module) }}">
                          <div class="col-12">
                            <div class="border-bottom">
                              <h6>{{ str_replace('_', ' ', $module) }}</h6>
                              <div class="d-flex flex-wrap align-items-center gap-6">
                                @foreach($actions as $action => $id)
                                  <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="permission_{{ $id }}" name="permissions[]" value="{{ $id }}" {{ $role->permissions->contains($id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission_{{ $id }}">{{ str_replace('_', ' ', $action ? $action : $module) }}</label>
                                  </div>
                                @endforeach
                              </div>
                            </div>
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

  document.getElementById('select-all').addEventListener('change', function() {
    var isChecked = this.checked;
    var checkboxes = document.querySelectorAll('.permission-group .form-check-input');
    checkboxes.forEach(function(checkbox) {
      checkbox.checked = isChecked;
    });
    document.querySelector('label[for="select-all"]').textContent = isChecked ? '{{ __('admin.Unselect_All') }}' : '{{ __('admin.Select_All') }}';
  });
</script>
@stop
