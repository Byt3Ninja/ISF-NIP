@extends('layouts.admin')

@section('title', __('License Activation'))

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5>{{ __('admin.Activate License') }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('license.activate') }}">
                @csrf
                <div class="mb-3">
                    <label for="license_key" class="form-label">{{ __('admin.License Key') }}</label>
                    <input type="text" name="license_key" class="form-control text-uppercase" placeholder="{{ __('admin.enter_License_Key') }}" required>
                    <small class="form-text text-muted">{{ __('admin.License Key Format') }}: XXXX-XXXX-XXXX-XXXX</small>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('admin.Activate License') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
