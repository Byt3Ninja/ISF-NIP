@extends('layouts.admin')

@section('title', __('admin.License Activation Result'))

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h5>{{ __('admin.License Activation Result') }}</h5>
        </div>
        <div class="card-body">
            <div class="alert {{ $success ? 'alert-success' : 'alert-danger' }}">
                {{ $message }}
            </div>

            <div class="mt-3">
                <a href="{{ $success ? route('dashboard') : route('license.activate-form') }}" class="btn {{ $success ? 'btn-success' : 'btn-primary' }}">
                    {{ $success ? __('admin.Go To Dashboard') : __('admin.Try Again') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
