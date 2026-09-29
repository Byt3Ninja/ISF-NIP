@extends('layouts.admin')
@section('title', __('admin.add_country'))
@section('index_url', route('country-management.index'))
@section('subTitle', __('admin.add_country'))
@section('content')
<div class="">
  <div class="">
    <div class="content-body">
      <section id="basic-input">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title fw-bold pb-3 border-bottom">{{ __('admin.add_country') }}</h5>
              </div>
              <div class="card-content">
                <div class="card-body">
                  <form action="{{ route('country-management.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.name_en') }} *</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" required>
                        @error('name_en') <div class="text-danger small">{{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.name_ar') }} *</label>
                        <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar') }}" dir="rtl" required>
                        @error('name_ar') <div class="text-danger small">{{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.iso3') }}</label>
                        <input type="text" name="iso3" class="form-control" value="{{ old('iso3') }}" maxlength="3">
                        @error('iso3') <div class="text-danger small">{{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.phone_code') }}</label>
                        <input type="text" name="phone_code" class="form-control" value="{{ old('phone_code') }}" maxlength="10">
                        @error('phone_code') <div class="text-danger small">{{ $message }}</div> @enderror
                      </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                      <a href="{{ route('country-management.index') }}" class="btn btn-label-secondary me-2">{{ __('admin.Cancel') }}</a>
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
