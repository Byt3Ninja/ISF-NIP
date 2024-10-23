@extends('layouts.admin')
@section('title', __('admin.Roles'))
@section('index_url', route('roles.index'))
@section('subTitle', __('admin.EditRole'))

@section('style')
  <style>
    .light-shadow {
      box-shadow: 0px 2px 0px 0px #F2F1F3;
    }
  </style>
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-body">
      <!-- BuilderForm start -->
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
                    <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
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
                    <div class="row">
                      <div class="col-12 col-md-6 form-group mb-6">
                        <label class="fs-5 fw-semibold mb-2" for="name">{{ __('admin.Title') }}</label>
                        <input 
                          type="text" 
                          class="form-control border-light light-shadow" 
                          id="name" 
                          name="name" 
                          required
                          placeholder="{{ __('admin.Title') }}" 
                          value="{{ $role->name }}"
                        >
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="fs-5 fw-semibold mb-6" for="permissions">
                        {{ __('admin.Permissions') }}
                      </label>
                      <div class="row">
                        @foreach($permissions as $id => $permission)
                        <div class="col-6 col-md-4 mb-2 border-bottom">
                          <div class="form-check">
                            <input 
                              type="checkbox" 
                              class="form-check-input" 
                              id="permission_{{ $id }}" 
                              name="permissions[]" 
                              value="{{ $id }}" 
                              {{ (in_array($id, old('permissions', [])) || $role->permissions->contains($id)) ? 'checked' : '' }}
                            />
                            <label class="form-check-label text-capitalize" for="permission_{{ $id }}">
                              {{ str_replace('_', ' ', $permission) }}
                            </label>
                          </div>
                        </div>
                        @endforeach
                      </div>
                      @if($errors->has('permissions'))
                      <div class="invalid-feedback">
                        {{ $errors->first('permissions') }}
                      </div>
                      @endif
                    </div>
                    <div class="d-flex justify-content-end mt-6">
                      <button type="submit" class="btn btn-primary">
                        <i class="ti ti-pencil me-2"></i>
                        {{ __('admin.Update') }}
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- BuilderForm end -->
    </div>
  </div>
</div>
<!-- END: Content-->
@stop
