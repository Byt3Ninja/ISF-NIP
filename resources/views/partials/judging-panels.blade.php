<div class="row">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center border-bottom mb-6">
      <div>
        <h5 class="mb-1">{{ __('admin.screening.add_judging_panels') }}</h5>
        <p class="text-muted">{{ __('admin.screening.add_panels_description') }}</p>
      </div>
      <button type="button" class="btn btn-outline-primary" id="addPanelBtn">
        <i class="ti ti-plus"></i>
        <span class="d-none d-md-inline ms-2">{{ __('admin.screening.add_panel') }}</span>
      </button>
    </div>
    @php
      $judges = [
        ['value' => 'John Doe'],
        ['value' => 'Jane Smith'],
        ['value' => 'Michael Johnson'],
        ['value' => 'Emily Davis'],
        ['value' => 'David Wilson'],
        ['value' => 'Sarah Brown'],
        ['value' => 'Robert Miller'],
        ['value' => 'Lisa Garcia']
      ];

      $companies = [
        ['value' => 'Google'],
        ['value' => 'Microsoft'],
        ['value' => 'Apple'],
        ['value' => 'Amazon'],
        ['value' => 'Facebook'],
        ['value' => 'Tesla'],
        ['value' => 'Netflix'],
        ['value' => 'Uber']
      ];

      $judgingPanels = [
        ['judges' => ['John Doe', 'Jane Smith'], 'companies' => ['Google'], 'session' => ['date' => '2025-12-25', 'start_time' => '10:00', 'end_time' => '11:00', 'meeting_link' => 'https://meet.google.com/abc']],
        ['judges' => ['Michael Johnson'], 'companies' => ['Microsoft', 'Apple'], 'session' => ['date' => '2025-12-26', 'start_time' => '14:00', 'end_time' => '15:30', 'meeting_link' => 'https://zoom.us/j/123']],
        ['judges' => ['Emily Davis', 'David Wilson'], 'companies' => ['Amazon', 'Facebook']],
        ['judges' => ['Sarah Brown'], 'companies' => ['Tesla'], 'session' => ['date' => '2025-12-27', 'start_time' => '09:00', 'end_time' => '10:00', 'meeting_link' => 'https://teams.microsoft.com/l/meetup-join/xyz']],
        ['judges' => ['Robert Miller', 'Lisa Garcia'], 'companies' => ['Netflix', 'Uber']]
      ];
    @endphp
    <div class="accordion" id="panels-container">
      @foreach($judgingPanels as $index => $panel)
        <div class="accordion-item border">
          <h2 class="accordion-header d-flex align-items-center py-3">
            <button
              class="accordion-button flex-grow-1 {{ $index === 0 ? '' : 'collapsed' }}"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#panel-{{ $index + 1 }}"
              aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
            >
              <i class="ti ti-user-check me-2"></i>
              {{ __('admin.screening.panel') }} #{{ $index + 1 }}
            </button>
            @if($index > 0)
              <button
                type="button"
                class="btn btn-icon btn-sm btn-outline-danger ms-2 me-4 remove-panel"
              >
                <i class="ti ti-trash"></i>
              </button>
            @endif
          </h2>

          <div
            id="panel-{{ $index + 1 }}"
            class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
            data-bs-parent="#panels-container"
          >
            <div class="accordion-body mt-3">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">{{ __('admin.Judgers') }}</label>
                  <input
                    type="text"
                    class="form-control tagify-judges"
                    placeholder="Select judges (type to search)"
                    value="{{ implode(',', $panel['judges']) }}"
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">{{ __('admin.Startups') }}</label>
                  <input
                    type="text"
                    class="form-control tagify-companies"
                    placeholder="Select companies assigned to this panel"
                    value="{{ implode(',', $panel['companies']) }}"
                  />
                </div>
              </div>

              <hr class="my-4">

              <!-- Session Schedule Section -->
              <div class="row mb-3">
                <div class="col-12">
                  <div class="d-flex align-items-center mb-3">
                    <div class="form-check form-switch me-3">
                      <input
                        class="form-check-input session-toggle"
                        type="checkbox"
                        id="session-toggle-{{ $index + 1 }}"
                        {{ isset($panel['session']) || str_contains($_SERVER['REQUEST_URI'], 'final') ? 'checked' : '' }}
                      />
                      <label class="form-check-label fw-semibold" for="session-toggle-{{ $index + 1 }}">
                        {{ __('admin.screening.schedule_session') }}
                      </label>
                    </div>
                  </div>
                  <div class="session-inputs border rounded p-4" id="session-inputs-{{ $index + 1 }}" style="display: {{ isset($panel['session']) ? 'block' : 'none' }};">
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('admin.Session_date') }}</label>
                        <input type="text" class="form-control session-date" name="panels[{{ $index }}][session_date]" value="{{ $panel['session']['date'] ?? '' }}">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('admin.Meeting Link') }}</label>
                        <input type="url" class="form-control session-meeting-link" name="panels[{{ $index }}][meeting_link]" placeholder="https://" value="{{ $panel['session']['meeting_link'] ?? '' }}">
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">{{ __('admin.Start Time') }}</label>
                        <input type="text" class="form-control session-start-time" name="panels[{{ $index }}][start_time]" value="{{ $panel['session']['start_time'] ?? '' }}">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">{{ __('admin.End Time') }}</label>
                        <input type="text" class="form-control session-end-time" name="panels[{{ $index }}][end_time]" value="{{ $panel['session']['end_time'] ?? '' }}">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

@push('scripts')
<script>
  let panelIndex = 5;

  const allJudges = @json($judges);
  const allCompanies = @json($companies);

  function initTagify(container) {
    $(container).find('.tagify-judges').each(function() {
      if (!this.tagify) {
        new Tagify(this, {
          whitelist: allJudges,
          enforceWhitelist: true,
          dropdown: {
            enabled: 0,
            closeOnSelect: false
          }
        });
      }
    });

    $(container).find('.tagify-companies').each(function() {
      if (!this.tagify) {
        new Tagify(this, {
          whitelist: allCompanies,
          enforceWhitelist: true,
          dropdown: {
            enabled: 0,
            closeOnSelect: false
          }
        });
      }
    });
  }

  function initFlatpickr(container) {
    $(container).find('.session-date').each(function() {
      if (!$(this).data('flatpickr')) {
        flatpickr(this, { dateFormat: 'Y-m-d' });
      }
    });

    $(container).find('.session-start-time, .session-end-time').each(function() {
      if (!$(this).data('flatpickr')) {
        flatpickr(this, { enableTime: true, noCalendar: true, dateFormat: 'H:i' });
      }
    });
  }

  // Initialize first panel
  initTagify(document);
  initFlatpickr(document);

  // Handle session toggle
  $(document).on('change', '.session-toggle', function() {
    const panelId = $(this).attr('id').split('-').pop();
    const inputsDiv = $(`#session-inputs-${panelId}`);
    if ($(this).is(':checked')) {
      inputsDiv.show();
    } else {
      inputsDiv.hide();
    }
  });

  $('#addPanelBtn').on('click', function() {
    panelIndex++;

    const accordion = $('#panels-container');
    const panelId = `panel-${panelIndex}`;

    // Clone the first panel
    const originalPanel = $('.accordion-item').first();
    const clonedPanel = originalPanel.clone(true);

    // Update the cloned panel
    clonedPanel.find('.accordion-button').removeClass('show').addClass('collapsed').attr('data-bs-target', `#${panelId}`).html(`<i class="ti ti-user-check me-2"></i> Panel #${panelIndex}`);
    clonedPanel.find('.accordion-collapse').removeClass('show').attr('id', panelId);

    // Update session toggle and inputs IDs
    clonedPanel.find('.session-toggle').attr('id', `session-toggle-${panelIndex}`);
    clonedPanel.find('.session-inputs').attr('id', `session-inputs-${panelIndex}`);

    // Update input names for session fields
    clonedPanel.find('[name*="panels["]').each(function() {
      const name = $(this).attr('name');
      const newName = name.replace(/panels\[\d+\]/, `panels[${panelIndex - 1}]`);
      $(this).attr('name', newName);
    });

    clonedPanel.find('.accordion-body input').val(''); // Clear inputs
    clonedPanel.find('.session-toggle').prop('checked', false); // Uncheck toggle
    clonedPanel.find('.session-inputs').hide(); // Hide session inputs

    // Add remove button to cloned panels
    const removeBtn = $('<button type="button" class="btn btn-sm btn-outline-danger ms-2 me-4 remove-panel"><i class="ti ti-trash"></i></button>');
    clonedPanel.find('.accordion-header').append(removeBtn);

    // Destroy existing Tagify instances in clone to reinitialize
    clonedPanel.find('input').each(function() {
      if (this.tagify) {
        this.tagify.destroy();
      }
    });

    accordion.append(clonedPanel);
    initTagify(clonedPanel[0]);
    initFlatpickr(clonedPanel[0]);
  });

  $(document).on('click', '.remove-panel', async function() {
    const item = $(this).closest('.accordion-item');
    const inputs = item.find('input');
    let hasData = false;
    inputs.each(function() {
      if ($(this).val().trim() !== '') {
        hasData = true;
        return false; // break
      }
    });

    if (hasData) {
      const result = await Swal.fire({
        title: '@lang("admin.SureDelete")',
        text: '@lang("admin.Recover")',
        icon: 'warning',
        showCancelButton: true,
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary',
        },
        buttonsStyling: false,
        cancelButtonText: '@lang("admin.Cancel")',
        confirmButtonText: '@lang("admin.DYes")',
      });
      if (!result.isConfirmed) {
        return;
      }
    }

    inputs.each(function() {
      if (this.tagify) {
        this.tagify.destroy();
      }
    });

    item.remove();

    // Reset index if only first panel left
    if ($('.accordion-item').length === 1) {
      panelIndex = 1;
    }
  });
</script>
@endpush
