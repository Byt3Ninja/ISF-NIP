@extends('layouts.admin')

@section('title', 'Create Notification Trigger')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Create Notification Trigger</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.notification-triggers.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Triggers
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.notification-triggers.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Trigger Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    <small class="form-text text-muted">Unique identifier for this trigger (e.g., "user_created_admin")</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="title">Display Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" required>
                                    <small class="form-text text-muted">Human-readable title for this trigger</small>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    <small class="form-text text-muted">Optional description of when this trigger should fire</small>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Event Selection -->
                                <div class="form-group">
                                    <label for="event">Event <span class="text-danger">*</span></label>
                                    <select class="form-control @error('event') is-invalid @enderror" 
                                            id="event" name="event" required>
                                        <option value="">Select Event</option>
                                        @foreach($availableEvents as $eventKey => $eventLabel)
                                            <option value="{{ $eventKey }}" {{ old('event') == $eventKey ? 'selected' : '' }}>
                                                {{ $eventLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">The system event that will trigger this notification</small>
                                    @error('event')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Template Selection -->
                                <div class="form-group">
                                    <label for="template_id">Notification Template <span class="text-danger">*</span></label>
                                    <select class="form-control @error('template_id') is-invalid @enderror" 
                                            id="template_id" name="template_id" required>
                                        <option value="">Select Template</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                                {{ $template->title }} ({{ ucfirst($template->channel) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">The notification template to send when this trigger fires</small>
                                    @error('template_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Conditions -->
                                <div class="form-group">
                                    <label>Conditions <small class="text-muted">(Optional)</small></label>
                                    <div id="conditions-container">
                                        <div class="condition-row mb-2">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control condition-field" 
                                                           placeholder="Field (e.g., role, status)" name="conditions[field][]">
                                                </div>
                                                <div class="col-md-2">
                                                    <select class="form-control condition-operator" name="conditions[operator][]">
                                                        <option value="equals">Equals</option>
                                                        <option value="in">In Array</option>
                                                        <option value="not_equals">Not Equals</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control condition-value" 
                                                           placeholder="Value (e.g., Admin, active)" name="conditions[value][]">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-condition">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-condition">
                                        <i class="fas fa-plus"></i> Add Condition
                                    </button>
                                    <small class="form-text text-muted">Additional conditions that must be met for this trigger to fire</small>
                                </div>

                                <!-- Priority -->
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                           id="priority" name="priority" value="{{ old('priority', 0) }}" min="0" max="100">
                                    <small class="form-text text-muted">Higher priority triggers fire first (0-100)</small>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Recipient Rules -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Recipients</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="recipients-container">
                                            <div class="recipient-rule mb-3">
                                                <div class="form-group">
                                                    <label>Recipient Type</label>
                                                    <select class="form-control recipient-type" name="recipient_rules[0][type]" required>
                                                        <option value="">Select Type</option>
                                                        @foreach($recipientRuleTypes as $typeKey => $typeLabel)
                                                            <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group recipient-value-group" style="display: none;">
                                                    <label>Value</label>
                                                    <input type="text" class="form-control recipient-value" 
                                                           name="recipient_rules[0][value]" placeholder="Enter value">
                                                    <small class="form-text text-muted recipient-help"></small>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-recipient">
                                                    <i class="fas fa-minus"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-recipient">
                                            <i class="fas fa-plus"></i> Add Recipient
                                        </button>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="form-group mt-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                    <small class="form-text text-muted">Only active triggers will fire automatically</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Trigger
                        </button>
                        <a href="{{ route('admin.notification-triggers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let recipientIndex = 1;
    let conditionIndex = 1;

    // Initialize form
    initializeForm();

    function initializeForm() {
        // Show/hide recipient value fields based on type
        $('.recipient-type').each(function() {
            toggleRecipientValue($(this));
        });
        
        // Update recipient help text
        updateRecipientHelp();
    }

    // Helper: toggle recipient value visibility and help text for a given select
    function toggleRecipientValue($select) {
        const type = $select.val();
        const $rule = $select.closest('.recipient-rule');
        const $valueGroup = $rule.find('.recipient-value-group');
        const $valueInput = $rule.find('.recipient-value');
        const $helpText = $rule.find('.recipient-help');

        if (type === 'role' || type === 'email' || type === 'custom') {
            $valueGroup.show();
            $valueInput.attr('required', true);

            if (type === 'role') {
                $helpText.text('Enter role name (e.g., Admin, organizationManager)');
                $valueInput.attr('placeholder', 'Admin');
            } else if (type === 'email') {
                $helpText.text('Enter email address');
                $valueInput.attr('placeholder', 'user@example.com');
            } else if (type === 'custom') {
                $helpText.text('Enter field name from event data (e.g., user_id, manager_id)');
                $valueInput.attr('placeholder', 'user_id');
            }
        } else {
            $valueGroup.hide();
            $valueInput.removeAttr('required');
            $helpText.text('');
            $valueInput.attr('placeholder', '');
        }
    }

    // Add recipient rule
    $('#add-recipient').click(function() {
        const recipientHtml = `
            <div class="recipient-rule mb-3">
                <div class="form-group">
                    <label>Recipient Type</label>
                    <select class="form-control recipient-type" name="recipient_rules[${recipientIndex}][type]" required>
                        <option value="">Select Type</option>
                        @foreach($recipientRuleTypes as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group recipient-value-group" style="display: none;">
                    <label>Value</label>
                    <input type="text" class="form-control recipient-value" 
                           name="recipient_rules[${recipientIndex}][value]" placeholder="Enter value">
                    <small class="form-text text-muted recipient-help"></small>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-recipient">
                    <i class="fas fa-minus"></i> Remove
                </button>
            </div>
        `;
        $('#recipients-container').append(recipientHtml);
        recipientIndex++;
    });

    // Remove recipient rule
    $(document).on('click', '.remove-recipient', function() {
        if ($('.recipient-rule').length > 1) {
            $(this).closest('.recipient-rule').remove();
        }
    });

    // Handle recipient type change
    $(document).on('change', '.recipient-type', function() {
        toggleRecipientValue($(this));
        updateRecipientHelp && updateRecipientHelp();
    });

    // Add condition
    $('#add-condition').click(function() {
        var conditionHtml = `
            <div class="condition-row mb-2" data-condition-index="${conditionIndex}">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control condition-field" 
                               placeholder="Field (e.g., role, status)" name="conditions[${conditionIndex}][field]">
                        <small class="text-muted">Available: role, status, email, name</small>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control condition-operator" name="conditions[${conditionIndex}][operator]">
                            <option value="equals">Equals</option>
                            <option value="in">In Array</option>
                            <option value="not_equals">Not Equals</option>
                            <option value="contains">Contains</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control condition-value" 
                               placeholder="Value (e.g., Admin, active)" name="conditions[${conditionIndex}][value]">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-condition">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#conditions-container').append(conditionHtml);
        conditionIndex++;
    });

    // Remove condition
    $(document).on('click', '.remove-condition', function() {
        $(this).closest('.condition-row').remove();
    });

    // Load sample conditions when event changes
    $('#event').change(function() {
        const event = $(this).val();
        if (event) {
            loadSampleConditions(event);
        }
    });

    function loadSampleConditions(event) {
        $.get(`/admin/notification-triggers/sample-conditions?event=${event}`)
            .done(function(conditions) {
                // You could auto-populate sample conditions here if desired
                console.log('Sample conditions for', event, ':', conditions);
            });
    }
});
</script>
@endsection
