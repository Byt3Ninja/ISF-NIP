<style>
  .nav-pills .nav-link { padding: 0.4rem 0.55rem !important; }
  .nav-pills .nav-link:not(.active) { background-color: white; }
  .nav-item button span { font-size: 0.67rem; }
  #modal-content { background-color: var(--bs-white); }
  @media (min-width: 768px) {
    #modal-content { background-color: #ebebed; }
    .nav-pills .nav-link { padding: 0.35rem 1.1rem !important; }
    .nav-item button span { font-size: 0.80rem; }
  }
  @media (max-width: 767.98px) {
    .modal-dialog { max-width: 100%; margin: 0; }
    .modal-content { min-height: 100vh; border-radius: 0; }
  }
</style>

<div class="modal fade" id="availabilty-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content rounded p-0 p-md-4" id="modal-content">
      @include('partials.modal.modal-header', ['title' => __('admin.'.'Add_Availabilty_Slots' )])
      <form method="post" action="/dashboard/mentors/modify" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <div class="rounded bg-white border-0 border-md-1 border-secondary p-4">
          <h5 class="border-bottom pb-3" id="availability-title">{{ __('dashboard.Select weekly days and times') }}</h5>
          <input type="hidden" name="type" id="type" value="Weekly">
          <input type="hidden" name="rtype" value="0" id="rtype">
          <input type="hidden" name="id" value="0" id="sid">
          <div id="" class="mb-3">
            <div id="days-container">
              @foreach(['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day)
                <div class="day-section mb-3 border-bottom pb-3 mb-6">
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="mb-0">{{ __('dashboard.' . $day) }}:</h6>
                    <button id="add-slot" type="button" class="btn btn-label-secondary btn-sm">
                      {{ __('admin.Add_Slot') }}
                      <i class="ti ti-plus ms-2"></i>
                    </button>
                  </div>
                  <div class="periods-container bg-white w-100" data-day="{{ $day }}" style="display: none;">
                    <div id="{{ $day }}-period-row" class="w-100 period-row d-flex align-items-center mb-2 my-2 rounded">
                      <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center gap-4 w-75">
                          <div class="row w-100">
                            <div class="col-md-6">
                              <select class=" form-select w-100" id="{{$day}}-from" type="time" name="periods[{{ $day }}][from][]" data-placeholder="{{ __('admin.From') }}">
                                <option value="">{{ __('admin.From') }}</option>
                                @for ($i = 0; $i < 24; $i++)
                                  @for ($j = 0; $j < 60; $j += 30)
                                    @php
                                      $hour = $i % 12 == 0 ? 12 : $i % 12;
                                      $period = $i < 12 ? 'AM' : 'PM';
                                    @endphp
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                                      {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }} {{ $period }}
                                    </option>
                                  @endfor
                                @endfor
                              </select>
                            </div>
                            <div class="col-md-6">
                              <select class=" form-select w-100" id="{{$day}}-to" type="time" name="periods[{{ $day }}][to][]" data-placeholder="{{ __('admin.To') }}">
                                <option value="">{{ __('admin.To') }}</option>
                                @for ($i = 0; $i < 24; $i++)
                                  @for ($j = 0; $j < 60; $j += 30)
                                    @php
                                      $hour = $i % 12 == 0 ? 12 : $i % 12;
                                      $period = $i < 12 ? 'AM' : 'PM';
                                    @endphp
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                                      {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }} {{ $period }}
                                    </option>
                                  @endfor
                                @endfor
                              </select>
                            </div>
                          </div>
                        </div>
                        <div class="d-flex align-items-center gap-4 w-25">
                          <span class="total-time fw-bold text-dark text-nowrap me-2 mb-2 mb-md-0">0 {{ __('admin.Min') }}</span>
                          <div class="d-flex flex-nowrap">
                            <button 
                              type="button" 
                              class="btn btn-xs rounded-circle btn-icon me-2 add-period"
                              bs-toggle="tooltip"
                              title="{{ __('admin.Add_Period') }}"
                            >
                              <i class="ti ti-plus text-success"></i>
                            </button>
                            <div id="remove-period">
                              <button 
                                type="button" 
                                class="btn btn-xs rounded-circle btn-icon remove-period"
                                bs-toggle="tooltip"
                                title="{{ __('admin.Remove_Period') }}"
                              >
                              <i class="ti ti-trash text-danger"></i>
                              </button>
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
          <div class="mb-3">
            <label for="notes" class="form-label fs-6 fw-semibold mb-1">{{ __('dashboard.Add a Note') }}:</label>
            <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="{{ __('dashboard.Write your note here') }}."></textarea>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3 mt-4 p-4 p-md-0">
          <button type="reset" class="btn btn-outline-secondary w-px-100" data-bs-dismiss="modal">
            {{ __('cycles.Cancel') }}
            <i class="ti ti-circle-x ms-2"></i>
          </button>
          <button type="submit" class="btn btn-primary w-px-100">
            {{ __('cycles.Save') }}
            <i class="ti ti-circle-check ms-2"></i>
          </button>
        </div> 
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    // Function to calculate the total time for a period row
    function calculateTotalTime($periodRow) {
      var fromTime = $periodRow.find('select[name$="[from][]"]').val();
      var toTime = $periodRow.find('select[name$="[to][]"]').val();
      if (fromTime && toTime) {
        var from = new Date('1970-01-01T' + fromTime + 'Z');
        var to = new Date('1970-01-01T' + toTime + 'Z');
        var diff = (to - from) / 60000; // difference in minutes
        $periodRow.find('.total-time').text(diff + ' {{__('admin.Min')}}');
      } else {
        $periodRow.find('.total-time').text('0 {{__('admin.Min')}}');
      }
    }

    // Function to check if any input has changed
    function checkFormChanges() {
      var formChanged = false;
      $('form input, form textarea, form select').each(function() {
        if ($(this).data('initial-value') !== $(this).val()) {
          formChanged = true;
          return false; // break the loop
        }
      });
      $('form button[type="submit"]').prop('disabled', !formChanged);
    }

    // Store initial values of inputs
    $('form input, form textarea, form select').each(function() {
      $(this).data('initial-value', $(this).val());
    });

    // Toggle the periods container
    $(document).on('click', '#add-slot', function() {
      var $day = $(this).closest('.day-section').find('.periods-container');
      var dayName = $day.data('day');
      if ($day.is(':visible')) {
        checkFormChanges();
        $day.hide();
        $(this).html('{{ __('admin.Add_Slot') }} <i class="ti ti-plus ti-13px ms-2"></i>');
        if ($day.find('.period-row').length > 0) {
          $day.find('.period-row').remove();
        }
      } else {
        $day.show();
        $(this).html('{{ __('admin.Remove_Slot') }} <i class="ti ti-x ti-13px ms-2"></i>');
        if ($day.find('.period-row').length === 0) {
          var newPeriodRow = `
            <div id="${dayName}-period-row" class="w-100 period-row d-flex align-items-center mb-2 my-2 rounded">
              <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-4 w-75">
                  <div class="row w-100">
                    <div class="col-md-6">
                      <select class=" form-select w-100" id="${dayName}-from" type="time" name="periods[${dayName}][from][]" required data-placeholder="{{ __('admin.From') }}">
                        <option value="">{{ __('admin.From') }}</option>
                        @for ($i = 0; $i < 24; $i++)
                          @for ($j = 0; $j < 60; $j += 30)
                            @php
                              $hour = $i % 12 == 0 ? 12 : $i % 12;
                              $period = $i < 12 ? 'AM' : 'PM';
                            @endphp
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                              {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }} {{ $period }}
                            </option>
                          @endfor
                        @endfor
                      </select>
                    </div>
                    <div class="col-md-6">
                      <select class=" form-select w-100" id="${dayName}-to" type="time" name="periods[${dayName}][to][]" required data-placeholder="{{ __('admin.To') }}">
                        <option value="">{{ __('admin.To') }}</option>
                        @for ($i = 0; $i < 24; $i++)
                          @for ($j = 0; $j < 60; $j += 30)
                            @php
                              $hour = $i % 12 == 0 ? 12 : $i % 12;
                              $period = $i < 12 ? 'AM' : 'PM';
                            @endphp
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                              {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }} {{ $period }}
                            </option>
                          @endfor
                        @endfor
                      </select>
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-4 w-25">
                  <span class="total-time fw-bold text-dark text-nowrap me-2 mb-2 mb-md-0">0 {{ __('admin.Min') }}</span>
                  <div class="d-flex flex-nowrap">
                    <button 
                      type="button" 
                      class="btn btn-xs rounded-circle btn-icon me-2 add-period"
                      bs-toggle="tooltip"
                      title="{{ __('admin.Add_Period') }}"
                    >
                      <i class="ti ti-plus text-success"></i>
                    </button>
                    <div id="remove-period">
                      <button 
                        type="button" 
                        class="btn btn-xs rounded-circle btn-icon remove-period"
                        bs-toggle="tooltip"
                        title="{{ __('admin.Remove_Period') }}"
                      >
                      <i class="ti ti-trash text-danger"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>`;
          $day.append(newPeriodRow);
        }
      }
    });

    // Add a new period row
    $(document).on('click', '.add-period', function() {
      var $templateRow = $(this).closest('.periods-container').find('.period-row').first().clone();
      $templateRow.find('select').val('');
      $templateRow.find('.total-time').text('0 {{__('admin.Min')}}');
      $(this).closest('.periods-container').append($templateRow);
      calculateTotalTime($templateRow); // Ensure total time is calculated for the new period
      checkFormChanges();
    });

    // Remove the period row
    $(document).on('click', '.remove-period', function() {
      $(this).closest('.period-row').remove();
      checkFormChanges();
    });

    // Calculate total time for each period row when the time is changed
    $(document).on('change input blur', 'select, textarea', function() {
      var $periodRow = $(this).closest('.period-row');
      calculateTotalTime($periodRow);
      
      // Validate that both "from" and "to" times are required if one is changed
      var fromTime = $periodRow.find('select[name$="[from][]"]').val();
      var toTime = $periodRow.find('select[name$="[to][]"]').val();
      if (fromTime) {
        $periodRow.find('select[name$="[to][]"]').prop('required', true);
      } else {
        $periodRow.find('select[name$="[to][]"]').prop('required', false);
      }
      if (toTime) {
        $periodRow.find('select[name$="[from][]"]').prop('required', true);
      } else {
        $periodRow.find('select[name$="[from][]"]').prop('required', false);
      }
      checkFormChanges();
    });

    // Initial check to disable the submit button if no changes
    checkFormChanges();
  });
</script>
