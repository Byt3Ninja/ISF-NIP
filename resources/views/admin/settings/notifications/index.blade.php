@extends('layouts.dashboard')

@section('title', 'Notification Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-bell"></i> Notification Channels Settings
                    </h4>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        Configure which notification channels are enabled globally for all users and organizations.
                    </p>

                    <form action="{{ route('settings.notification_update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Email Channel -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-left-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <i class="fas fa-envelope text-primary"></i> Email Notifications
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    Enable or disable email notifications
                                                </p>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="emailSwitch"
                                                    name="enable_email"
                                                    {{ $settings && $settings->enable_email ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- WhatsApp Channel -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-left-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <i class="fab fa-whatsapp text-success"></i> WhatsApp Notifications
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    Enable or disable WhatsApp notifications
                                                </p>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="whatsappSwitch"
                                                    name="enable_whatsapp"
                                                    {{ $settings && $settings->enable_whatsapp ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pusher Channel -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-left-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <i class="fas fa-broadcast-tower text-info"></i> Push Notifications
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    Enable or disable push notifications (Pusher)
                                                </p>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="pusherSwitch"
                                                    name="enable_pusher"
                                                    {{ $settings && $settings->enable_pusher ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="{{ route('settings.system_general_view') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-info mt-4" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle"></i> Note
                </h5>
                <p class="mb-0">
                    These settings control which notification channels are available globally. Users can still choose to disable specific channels in their personal settings, but cannot enable channels that are disabled here.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #007bff;
    }

    .border-left-success {
        border-left: 4px solid #28a745;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>
@endsection
