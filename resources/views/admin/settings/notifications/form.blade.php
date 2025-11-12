<form action="{{ route('settings.notification_update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Email Channel -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">
                                <i class="fas fa-envelope text-primary"></i> Email Notifications
                            </h6>
                            <small class="text-muted">
                                Enable or disable email notifications globally
                            </small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="emailSwitch"
                                name="enable_email" value="1"
                                {{ $settings && $settings->enable_email ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Channel -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">
                                <i class="fab fa-whatsapp text-success"></i> WhatsApp Notifications
                            </h6>
                            <small class="text-muted">
                                Enable or disable WhatsApp notifications globally
                            </small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="whatsappSwitch"
                                name="enable_whatsapp" value="1"
                                {{ $settings && $settings->enable_whatsapp ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pusher Channel -->
        <div class="col-md-6 mb-4">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">
                                <i class="fas fa-broadcast-tower text-info"></i> Push Notifications
                            </h6>
                            <small class="text-muted">
                                Enable or disable push notifications (Pusher) globally
                            </small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pusherSwitch"
                                name="enable_pusher" value="1"
                                {{ $settings && $settings->enable_pusher ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4" role="alert">
        <i class="fas fa-info-circle"></i>
        <strong>Note:</strong> These settings control which notification channels are available globally. Users can still choose to disable specific channels in their personal settings, but cannot enable channels that are disabled here.
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </div>
</form>

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
