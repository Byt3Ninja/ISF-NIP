@extends('layouts.admin')

@section('title', __('notification_triggers.edit_trigger'))

@section('content')
<div class="">
  <div class="">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0">{{ __('notification_triggers.edit_trigger') }}</h5>
            <div>
              <a href="{{ route('notification-triggers.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i> {{ __('notification_triggers.back_to_list') }}
              </a>
            </div>
          </div>

          <form action="{{ route('notification-triggers.update', $notificationTrigger) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label for="name" class="form-label">{{ __('notification_triggers.trigger_name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $notificationTrigger->name) }}" required>
                    <div class="form-text">{{ __('notification_triggers.name_help') }}</div>
                    @error('name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="title" class="form-label">{{ __('notification_triggers.display_title') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title', $notificationTrigger->title) }}" required>
                    <div class="form-text">{{ __('notification_triggers.title_help') }}</div>
                    @error('title')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="description" class="form-label">{{ __('notification_triggers.description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description', $notificationTrigger->description) }}</textarea>
                    <div class="form-text">{{ __('notification_triggers.description_help') }}</div>
                    @error('description')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="event" class="form-label">{{ __('notification_triggers.event') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('event') is-invalid @enderror" 
                            id="event" name="event" required>
                      <option value="">{{ __('notification_triggers.select_event') }}</option>
                      @foreach($availableEvents as $eventKey => $eventLabel)
                        <option value="{{ $eventKey }}" {{ old('event', $notificationTrigger->event) == $eventKey ? 'selected' : '' }}>
                          {{ $eventLabel }}
                        </option>
                      @endforeach
                    </select>
                    <div class="form-text">{{ __('notification_triggers.event_help') }}</div>
                    @error('event')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label for="template_id" class="form-label">{{ __('notification_triggers.template') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('template_id') is-invalid @enderror" 
                            id="template_id" name="template_id" required>
                      <option value="">{{ __('notification_triggers.select_template') }}</option>
                      @foreach($templates as $template)
                        <option value="{{ $template->id }}" {{ old('template_id', $notificationTrigger->template_id) == $template->id ? 'selected' : '' }}>
                          {{ $template->title }} ({{ ucfirst($template->channel) }})
                        </option>
                      @endforeach
                    </select>
                    <div class="form-text">{{ __('notification_triggers.template_help') }}</div>
                    @error('template_id')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="mb-3">
                    <label class="form-label">{{ __('notification_triggers.conditions') }} <small class="text-muted">({{ __('notification_triggers.optional') }})</small></label>
                    <div id="conditions-container">
                      @if(old('conditions') || $notificationTrigger->conditions)
                        @php
                          $conditions = old('conditions', $notificationTrigger->conditions ?? []);
                        @endphp
                        @foreach($conditions as $index => $condition)
                          <div class="condition-row mb-2">
                            <div class="row">
                              <div class="col-md-4">
                                <input type="text" class="form-control condition-field mb-3 mb-md-0" 
                                       placeholder="{{ __('notification_triggers.field_placeholder') }}" 
                                       name="conditions[{{ $index }}][field]" value="{{ $condition['field'] ?? '' }}">
                              </div>
                              <div class="col-md-2">
                                <select class="form-select condition-operator mb-3 mb-md-0" name="conditions[{{ $index }}][operator]">
                                  <option value="equals" {{ ($condition['operator'] ?? '') == 'equals' ? 'selected' : '' }}>{{ __('notification_triggers.equals') }}</option>
                                  <option value="in" {{ ($condition['operator'] ?? '') == 'in' ? 'selected' : '' }}>{{ __('notification_triggers.in_array') }}</option>
                                  <option value="not_equals" {{ ($condition['operator'] ?? '') == 'not_equals' ? 'selected' : '' }}>{{ __('notification_triggers.not_equals') }}</option>
                                </select>
                              </div>
                              <div class="col-md-4">
                                <input type="text" class="form-control condition-value mb-3 mb-md-0" 
                                       placeholder="{{ __('notification_triggers.value_placeholder') }}" 
                                       name="conditions[{{ $index }}][value]" value="{{ $condition['value'] ?? '' }}">
                              </div>
                              <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-condition">
                                  <i class="ti ti-minus"></i>
                                </button>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      @else
                        <div class="condition-row mb-2">
                          <div class="row">
                            <div class="col-md-4">
                              <input type="text" class="form-control condition-field mb-3 mb-md-0" 
                                     placeholder="{{ __('notification_triggers.field_placeholder') }}" name="conditions[0][field]">
                            </div>
                            <div class="col-md-2">
                              <select class="form-select condition-operator mb-3 mb-md-0" name="conditions[0][operator]">
                                <option value="equals">{{ __('notification_triggers.equals') }}</option>
                                <option value="in">{{ __('notification_triggers.in_array') }}</option>
                                <option value="not_equals">{{ __('notification_triggers.not_equals') }}</option>
                              </select>
                            </div>
                            <div class="col-md-4">
                              <input type="text" class="form-control condition-value mb-3 mb-md-0" 
                                     placeholder="{{ __('notification_triggers.value_placeholder') }}" name="conditions[0][value]">
                            </div>
                            <div class="col-md-2">
                              <button type="button" class="btn btn-outline-danger btn-sm remove-condition">
                                <i class="ti ti-minus"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      @endif
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-condition">
                      <i class="ti ti-plus me-2"></i> {{ __('notification_triggers.add_condition') }}
                    </button>
                    <div class="form-text">{{ __('notification_triggers.conditions_help') }}</div>
                  </div>

                  <div class="mb-3">
                    <label for="priority" class="form-label">{{ __('notification_triggers.priority') }}</label>
                    <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                           id="priority" name="priority" value="{{ old('priority', $notificationTrigger->priority) }}" min="0" max="100">
                    <div class="form-text">{{ __('notification_triggers.priority_help') }}</div>
                    @error('priority')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card border shadow-sm">
                    <div class="card-header">
                      <h5 class="card-title mb-0">{{ __('notification_triggers.recipients') }}</h5>
                    </div>
                    <div class="card-body">
                      <div id="recipients-container">
                        @if(old('recipient_rules') || $notificationTrigger->recipient_rules)
                          @php
                            $recipientRules = old('recipient_rules', $notificationTrigger->recipient_rules ?? []);
                          @endphp
                          @foreach($recipientRules as $index => $rule)
                            <div class="recipient-rule mb-3">
                              <div class="mb-3">
                                <label class="form-label">{{ __('notification_triggers.recipient_type') }}</label>
                                <select class="form-select recipient-type" name="recipient_rules[{{ $index }}][type]" required>
                                  <option value="">{{ __('notification_triggers.select_type') }}</option>
                                  @foreach($recipientRuleTypes as $typeKey => $typeLabel)
                                    <option value="{{ $typeKey }}" {{ ($rule['type'] ?? '') == $typeKey ? 'selected' : '' }}>{{ $typeLabel }}</option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="mb-3 recipient-value-group" style="{{ in_array($rule['type'] ?? '', ['role', 'email', 'custom']) ? '' : 'display: none;' }}">
                                <label class="form-label">{{ __('notification_triggers.value') }}</label>
                                <input type="text" class="form-control recipient-value" 
                                       name="recipient_rules[{{ $index }}][value]" 
                                       value="{{ $rule['value'] ?? '' }}"
                                       placeholder="{{ __('notification_triggers.enter_value') }}">
                                <div class="form-text recipient-help"></div>
                              </div>
                              <button type="button" class="btn btn-outline-danger btn-sm remove-recipient" style="{{ count(old('recipient_rules', $notificationTrigger->recipient_rules ?? [])) > 1 ? '' : 'display: none;' }}">
                                <i class="ti ti-minus me-2"></i> {{ __('notification_triggers.remove') }}
                              </button>
                            </div>
                          @endforeach
                        @else
                          <div class="recipient-rule mb-3">
                            <div class="mb-3">
                              <label class="form-label">{{ __('notification_triggers.recipient_type') }}</label>
                              <select class="form-select recipient-type" name="recipient_rules[0][type]" required>
                                <option value="">{{ __('notification_triggers.select_type') }}</option>
                                @foreach($recipientRuleTypes as $typeKey => $typeLabel)
                                  <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="mb-3 recipient-value-group" style="display: none;">
                              <label class="form-label">{{ __('notification_triggers.value') }}</label>
                              <input type="text" class="form-control recipient-value" 
                                     name="recipient_rules[0][value]" placeholder="{{ __('notification_triggers.enter_value') }}">
                              <div class="form-text recipient-help"></div>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-recipient" style="display: none;">
                              <i class="ti ti-minus me-2"></i> {{ __('notification_triggers.remove') }}
                            </button>
                          </div>
                        @endif
                      </div>
                      <button type="button" class="btn btn-outline-primary btn-sm" id="add-recipient">
                        <i class="ti ti-plus me-2"></i> {{ __('notification_triggers.add_recipient') }}
                      </button>
                    </div>
                  </div>

                  <div class="mt-3">
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input" id="is_active" 
                             name="is_active" value="1" {{ old('is_active', $notificationTrigger->is_active) ? 'checked' : '' }}>
                      <label class="form-check-label" for="is_active">{{ __('notification_triggers.active') }}</label>
                    </div>
                    <div class="form-text">{{ __('notification_triggers.active_help') }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary me-3">
                <i class="ti ti-device-floppy me-2"></i> {{ __('notification_triggers.update') }}
              </button>
              <a href="{{ route('notification-triggers.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-x me-2"></i> {{ __('notification_triggers.cancel') }}
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('pagescripts')
<script>
$(document).ready(function() {
    let recipientIndex = {{ count(old('recipient_rules', $notificationTrigger->recipient_rules ?? [])) }};
    let conditionIndex = {{ count(old('conditions', $notificationTrigger->conditions ?? [])) }};

    // Initialize form
    initializeForm();
    updateRemoveButtons();

    function initializeForm() {
        // Show/hide recipient value fields based on type
        $('.recipient-type').each(function() {
            toggleRecipientValue($(this));
        });
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
                $helpText.text('{{ __('notification_triggers.role_help') }}');
                $valueInput.attr('placeholder', 'Admin');
            } else if (type === 'email') {
                $helpText.text('{{ __('notification_triggers.email_help') }}');
                $valueInput.attr('placeholder', 'user@example.com');
            } else if (type === 'custom') {
                $helpText.text('{{ __('notification_triggers.custom_help') }}');
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
                <div class="mb-3">
                    <label class="form-label">{{ __('notification_triggers.recipient_type') }}</label>
                    <select class="form-select recipient-type" name="recipient_rules[${recipientIndex}][type]" required>
                        <option value="">{{ __('notification_triggers.select_type') }}</option>
                        @foreach($recipientRuleTypes as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 recipient-value-group" style="display: none;">
                    <label class="form-label">{{ __('notification_triggers.value') }}</label>
                    <input type="text" class="form-control recipient-value" 
                           name="recipient_rules[${recipientIndex}][value]" placeholder="{{ __('notification_triggers.enter_value') }}">
                    <div class="form-text recipient-help"></div>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-recipient">
                    <i class="ti ti-minus me-2"></i> {{ __('notification_triggers.remove') }}
                </button>
            </div>
        `;
        $('#recipients-container').append(recipientHtml);
        updateRemoveButtons();
        recipientIndex++;
    });

    // Remove recipient rule
    $(document).on('click', '.remove-recipient', function() {
        $(this).closest('.recipient-rule').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        const $recipients = $('.recipient-rule');
        $recipients.find('.remove-recipient').toggle($recipients.length > 1);
    }

    // Handle recipient type change
    $(document).on('change', '.recipient-type', function() {
        toggleRecipientValue($(this));
    });

    // Add condition
    $('#add-condition').click(function() {
        var conditionHtml = `
            <div class="condition-row mb-2" data-condition-index="${conditionIndex}">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control condition-field mb-3 mb-md-0" 
                               placeholder="{{ __('notification_triggers.field_placeholder') }}" name="conditions[${conditionIndex}][field]">
                        <small class="text-muted">{{ __('notification_triggers.available_fields') }}</small>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select condition-operator mb-3 mb-md-0" name="conditions[${conditionIndex}][operator]">
                            <option value="equals">{{ __('notification_triggers.equals') }}</option>
                            <option value="in">{{ __('notification_triggers.in_array') }}</option>
                            <option value="not_equals">{{ __('notification_triggers.not_equals') }}</option>
                            <option value="contains">{{ __('notification_triggers.contains') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control condition-value mb-3 mb-md-0" 
                               placeholder="{{ __('notification_triggers.value_placeholder') }}" name="conditions[${conditionIndex}][value]">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-condition">
                            <i class="ti ti-minus"></i>
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