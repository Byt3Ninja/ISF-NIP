@extends('layouts.admin')

@php
  $tabs = $_permissions->groupBy('group')->map(function ($groupPermissions, $group) {
    return [
      'target' => Str::slug($group),
      'label' => $group,
    ];
  })->values()->toArray();
@endphp


@section('title', __('admin.Roles'))
@section('index_url', route('roles.index'))
@section('subTitle', __('admin.CreateRole'))
@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="card">
      <div class="card-header border-bottom mb-6">
        <div class="row d-flex justify-content-between align-items-center ps-0 ps-md-6">
          <div class="col-12 col-md-8">
            <h4 class="fw-bold mb-3 mb-md-0">{{ __('admin.CreateRole') }}</h4>
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
        @if(session()->has('success'))
        <div class="alert alert-success mb-2" role="alert">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          <div class="d-flex align-items-center">
            <i class="ti ti-error"></i>
            <span>
              {{ __('admin.' . session()->get('success')) }}
              {{ Session::forget('success') }}
            </span>
          </div>
        </div>
        @elseif(session()->has('msg'))
        <div class="alert alert-danger mb-2" role="alert">
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

        <form class="p-0 w-100" method="POST" action="{{ route('roles.store') }}" enctype="multipart/form-data">
          @csrf
          <div class="row mb-6 border-bottom pb-3">
            <div class="col-md-6 mb-3">
              <label for="name" class="form-label fs-6 mb-1 fw-semibold">{{ __('admin.Title') }}</label>
              <input type="text" class="form-control" id="name" name="name" required value="">
            </div>
            
            <div class="col-12 col-md-6 form-group mb-6">
              <label class="form-label fs-6 mb-1 fw-semibold" for="default_for">{{ __('admin.roleType') }}</label>
              <select name="default_for" id="default_for" class="form-select border-light light-shadow">
                <option value="admin">{{ __('admin.roleAdmin') }}</option>
                <option value="editor">{{ __('admin.roleEditor') }}</option>
                <option value="coordinator">{{ __('admin.roleCoordinator') }}</option>
                <option value="page">{{ __('admin.rolePageManager') }}</option>
                <option value="manager">{{ __('admin.roleManager') }}</option>
                <option value="mentor">{{ __('admin.roleMentor') }}</option>
                <option value="judger">{{ __('admin.roleJudger') }}</option>
                <option value="registered">{{ __('admin.roleRegistered') }}</option>
                <option value="pages">{{ __('admin.rolePagesEditor') }}</option>
                <option value="organizationmanager">{{ __('admin.roleOrganizationManager') }}</option>
                <option value="other">{{ __('admin.roleOther') }}</option>
              </select>
            </div>
          </div>
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
                          @foreach( $_permissions->where('group', $tab['label']) as $permission )
                          <div class="card card-body border mb-3">
                            <li class="d-flex justify-content-between align-items-center">
                              <div class="d-flex flex-column">
                                <h5 class="mb-0">{{ $permission->description }}</h5>
                                <span>{{ $permission->name }}</span>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission-{{ $permission->name }}">
                                <label class="form-check-label" for="permission-{{ $permission->name }}">
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
          <div class="d-flex justify-content-end align-items-center gap-6 mt-8">
            <button type="reset" class="btn btn-label-secondary">{{ __('admin.Reset') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('admin.Submit') }}</button>
          </div>
        </form>
      </div>
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
