@extends('layouts.admin')
@section('title', __('admin.Projects') )
@section('index_url', route('projects.index') )

@section('style')
<style>
  #basic-input {
    width: 70%;
    margin: 2rem auto 4rem auto;
  }

  @media (max-width: 768px) {
    #basic-input {
      width: 100%;
    }
  }
</style>
@stop


@section('content')
<!-- BEGIN: Content-->

<div class="">
  <div class="">
    <div class="content-body">
      <section id="basic-input">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title fw-bold pb-3 border-bottom">{{__('admin.edit_project')}}</h5>
              </div>
              <div class="card-content">
                <div class="card-body">
                  <form action="{{ route('projects.update', $project->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                      <div class="col-md-12">
                        <label class="fw-semibold mb-1 fs-6 form-label">{{ __('admin.Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $project->name) }}"
                          required>
                        @error('name')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-md-12 mt-3">
                        <label class="fw-semibold mb-1 fs-6 form-label">{{__('admin.Description')}}</label>
                        <textarea name="description" rows="5"
                          class="form-control">{{ $project->description ?? '' }}</textarea>
                      </div>
                    </div>
                    <div class="w-100 d-flex justify-content-end mt-6">
                      <button type="submit" class="btn btn-primary">
                        {{ __('admin.Save') }}
                        <i class="ti ti-circle-check ms-3"></i>
                      </button>
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

<!-- END: Content-->

{{-- @section('pagescripts')
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
@stop --}}