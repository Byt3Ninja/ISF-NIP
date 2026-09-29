@extends('layouts.admin')

@php
$tabs = $_permissions->groupBy('group')->map(function ($groupPermissions, $group) {
  return [
    'target' => Str::slug($group),
    'label' => $group,
  ];
})->values()->toArray();
@endphp

@section('content')
<!-- Content wrapper -->
<div class="">
  <!-- Content -->
  <div class="">
    @if(session()->has('success'))
      <x-alert type="success" message=" {{ __('admin.' . session()->get('success')) }} {{ Session::forget('success') }}" />
    @elseif(session()->has('msg'))
      <x-alert type="danger" message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" />
    @endif
    <!-- Validation messages -->
    @if($errors->any())
      @foreach($errors->all() as $error)
        <x-alert type="danger" message="{{ $error }}" />
      @endforeach
    @endif

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
        <form class="p-0 w-100" method="POST" action="{{ route('roles.update', [$role->id]) }}"
          enctype="multipart/form-data">
          @method('PUT')
          @csrf
          <div class="row mb-6 border-bottom pb-3 px-5">
            <div class="col-md-6 mb-3">
              <label for="name" class="form-label fs-6 mb-1 fw-semibold">{{ __('admin.Title') }}</label>
              <input type="text" class="form-control" id="name" name="name" required value="{{ $role->name }}" placeholder="{{ __('admin.Title') }}" />
            </div>

            <div class="col-12 col-md-6 form-group mb-6">
              <label class="form-label fs-6 mb-1 fw-semibold" for="default_for">{{ __('admin.roleType') }}</label>
              <select name="default_for" id="default_for" class="form-select border-light light-shadow">
                <option value="admin" {{ $role->default_for == 'admin' ? 'selected' : '' }}>{{ __('admin.roleAdmin') }} </option>
                <option value="editor" {{ $role->default_for == 'editor' ? 'selected' : '' }}>{{ __('admin.roleEditor') }}</option>
                <option value="coordinator" {{ $role->default_for == 'coordinator' ? 'selected' : '' }}>{{ __('admin.roleCoordinator') }}</option>
                <option value="page" {{ $role->default_for == 'page' ? 'selected' : '' }}>{{ __('admin.rolePageManager') }}</option>
                <option value="manager" {{ $role->default_for == 'manager' ? 'selected' : '' }}>{{ __('admin.roleManager') }}</option>
                <option value="mentor" {{ $role->default_for == 'mentor' ? 'selected' : '' }}>{{ __('admin.roleMentor') }}</option>
                <option value="judger" {{ $role->default_for == 'judger' ? 'selected' : '' }}>{{ __('admin.roleJudger') }}</option>
                <option value="registered" {{ $role->default_for == 'registered' ? 'selected' : '' }}>{{ __('admin.roleRegistered') }}</option>
                <option value="pages" {{ $role->default_for == 'pages' ? 'selected' : '' }}>{{ __('admin.rolePagesEditor') }}</option>
                <option value="organizationmanager" {{ $role->default_for == 'organizationmanager' ? 'selected' : '' }}>{{ __('admin.roleOrganizationManager') }}</option>
                <option value="subadmin" {{ $role->default_for == 'subadmin' ? 'selected' : '' }}>{{ __('admin.Sub-Admin') }}</option>
                <option value="serviceProvider" {{ $role->default_for == 'serviceProvider' ? 'selected' : '' }}>{{ __('admin.roleServiceProvider') }}</option>
                <option value="other" {{ $role->default_for == 'other' ? 'selected' : '' }}>{{ __('admin.roleOther') }}</option>
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
                      data-label="{{ Str::slug($tab['label'], '-') }}">
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
                        <div class="col-12 col-md-6">
                          <h5 class="card-title mb-0 fw-semibold pt-2 mb-3 mb-md-0">{{ $tab['label'] }}</h5>
                        </div>
                        <div class="col-12 col-md-4 d-flex justify-content-end align-items-center">
                          <!-- Search Input -->
                          <div class="position-relative w-100">
                            <input type="text" class="form-control permission-search" 
                              placeholder="{{ __('admin.Search_Permissions') }}..." />
                            <span class="position-absolute end-0 top-50 translate-middle-y search-icon mx-2">
                              <i class="ti ti-search text-muted"></i>
                            </span>
                          </div>
                          <!-- / Search Input -->
                        </div>
                        <div class="col-12 col-md-2 d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
                          <!-- Select All -->
                          <div class="form-check">
                            <input 
                              type="checkbox" 
                              class="form-check-input select-all-row"
                              id="select-all-{{ strtolower($tab['target']) }}"
                              onchange="this.checked ? this.nextElementSibling.innerText = '{{ __('admin.Selected') }}' : this.nextElementSibling.innerText = '{{ __('admin.Select') }}'"
                            >
                            <label class="form-check-label" for="select-all-{{ strtolower($tab['target']) }}">{{
                              __('admin.Select_All') }}
                            </label>
                          </div>
                          <span class="position-absolute end-0 top-50 translate-middle-y search-icon mx-2">
                            <i class="ti ti-check text-muted"></i>
                          </span>
                          <!-- / Select All -->
                        </div>
                      </div>
                    </div>
                    <div class="p-0 w-100">
                      <div class="permission-list w-100">
                        @foreach( $_permissions->where('group', $tab['label']) as $permission )
                        <div class="card permission-card card-body border mb-3">
                          <li class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div class="d-flex flex-column">
                              <h5 class="mb-0">{{ $permission->description }}</h5>
                              <span>{{ $permission->name }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-3">
                              <div class="form-check d-flex align-items-center gap-2 w-px-100 h-px-40 mt-4 mt-md-0">
                                <input 
                                  class="form-check-input" 
                                  type="checkbox" 
                                  name="permissions[]" 
                                  value="{{ $permission->name }}" 
                                  id="permission-{{ $permission->name }}"
                                  {{ $permission->checked ? 'checked' : '' }} 
                                  onchange="this.checked ? this.nextElementSibling.innerText = '{{ __('admin.Selected') }}' : this.nextElementSibling.innerText = '{{ __('admin.Select') }}'"
                                />
                                <label class="form-check-label" for="permission-{{ $permission->name }}">
                                  {{ $permission->checked ? __('admin.Selected') : __('admin.Select') }}
                                </label>
                              </div>
                              <a 
                                href="/dashboard/permissions/{{ $permission->id }}/edit"
                                class="btn btn-sm btn-icon rounded-circle btn-label-secondary">
                                <i class="ti ti-edit"></i>
                              </a>
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

  // Search across all permission cards in all tabs
  $('.permission-search').on('keyup', function () {
    const query = $(this).val().toLowerCase();
    const tabPane = $(this).closest('.tab-pane');
    tabPane.find(".permission-card").each(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
    });
  });

  // Select all checkboxes in a group
  $('.select-all-row').on('change', function () {
    const groupId = $(this).attr('id').replace('select-all-', '');
    const isChecked = $(this).is(':checked');
    $(`#navs-left-${groupId} .permission-list input[type="checkbox"]`).prop('checked', isChecked);
  });
</script>
@stop