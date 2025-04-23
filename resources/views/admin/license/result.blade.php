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

        <h4>{{ __('admin.licenseDetails') }}</h4>
        <ul>
            @if(isset($license))
            <li><strong>{{ __('admin.ID') }}:</strong> {{ $license['id'] }}</li>
            <li><strong>{{ __('admin.Key') }}:</strong> {{ $license['attributes']['key'] }}</li>
            <li><strong>{{ __('admin.Name') }}:</strong> {{ $license['attributes']['name'] ?? 'N/A' }}</li>
            <li><strong>{{ __('admin.Status') }}:</strong> {{ $license['attributes']['status'] }}</li>
            <li><strong>{{ __('admin.Created') }}:</strong> {{ \Carbon\Carbon::parse($license['attributes']['created'])->translatedFormat('Y-m-d H:i') }}</li>
            <li><strong>{{ __('admin.Expiry') }}:</strong> {{ $license['attributes']['expiry'] ? \Carbon\Carbon::parse($license['attributes']['expiry'])->translatedFormat('Y-m-d H:i') : 'N/A' }}</li>
            <li><strong>{{ __('admin.Max Machines') }}:</strong> {{ $license['attributes']['maxMachines'] ?? 'N/A' }}</li>
            <li><strong>{{ __('admin.Uses Left') }}:</strong> {{ $license['attributes']['maxMachines'] - $license['attributes']['uses'] }}</li>
            @endif
        </ul>
     
            <div class="mt-3">
                <a href="{{ $success ? route('core.dashboard_view') : route('license.activate_view_form') }}" class="btn {{ $success ? 'btn-success' : 'btn-primary' }}">
                    {{ $success ? __('admin.Go To Dashboard') : __('admin.Try Again') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
