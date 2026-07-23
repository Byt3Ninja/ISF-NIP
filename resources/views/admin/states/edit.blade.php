@extends('layouts.admin')
@section('title', __('admin.edit_state'))
@section('index_url', route('state-management.index', $state->country_id))
@section('subTitle', __('admin.edit_state'))
@section('content')
<div class="">
  <div class="">
    <div class="content-body">
      <section id="basic-input">
        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('country-management.index') }}">{{ __('admin.countries') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('state-management.index', $state->country_id) }}">{{ $l == 'ar' ? $state->country->name_ar : $state->country->name_en }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.edit_state') }}</li>
          </ol>
        </nav>
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title fw-bold pb-3 border-bottom">{{ __('admin.edit_state') }}</h5>
              </div>
              <div class="card-content">
                <div class="card-body">
                  <form action="{{ route('state-management.update', $state->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.name_en') }} *</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $state->name_en) }}" required>
                        @error('name_en') <div class="text-danger small">{{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.name_ar') }} *</label>
                        <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $state->name_ar) }}" dir="rtl" required>
                        @error('name_ar') <div class="text-danger small">{{ $message }}</div> @enderror
                      </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                      <a href="{{ route('state-management.index', $state->country_id) }}" class="btn btn-label-secondary me-2">{{ __('admin.Cancel') }}</a>
                      <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
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
