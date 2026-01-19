<form
  action="{{ route('settings.system_meeting_integration_update') }}"
  method="POST"
  enctype="multipart/form-data"
  id="meetingIntegrationForm"
>
  {!! csrf_field() !!}
  @method('PUT')

  <input type="hidden" name="id" value="{{ $Title->id }}"/>

  <div class="card border shadow-sm mb-6">
    <div class="card-header">
      <h6 class="card-title mb-0">{{ __('settings.meeting_integration') }}</h6>
      <span class="text-muted">{{ __('settings.meeting_integration_description') }}</span>
    </div>
    <div class="card-body">
      <!-- Meeting Type -->
      <div class="form-group mb-4">
        <label class="form-label">{{ __('settings.meeting_platform') }}</label>
        <select
          class="form-select Meeting"
          id="Meeting"
          name="Meeting"
          required
        >
          <option value="0">{{ __('settings.select_platform') }}</option>
          <option
            value="zoom"
            {{ strtolower($Title->Meeting) == 'zoom' ? 'selected' : '' }}
          >
            {{ __('admin.Zoom') }}
          </option>
          <option
            value="teams"
            {{ strtolower($Title->Meeting) == 'teams' ? 'selected' : '' }}
          >
            {{ __('admin.Teams') }}
          </option>
        </select>
      </div>

      <!-- Maximum Concurrent Online Sessions -->
      <div class="form-group mb-4">
        <label class="form-label" for="max_concurrent_sessions">
          {{ __('settings.max_concurrent_sessions') }}
        </label>
        <input
          id="max_concurrent_sessions"
          type="number"
          class="form-control"
          name="max_concurrent_sessions"
          value="{{ $Title->max_concurrent_sessions }}"
          placeholder="{{ __('settings.max_concurrent_sessions_placeholder') }}"
        />
        <small class="text-muted">{{ __('settings.max_concurrent_sessions_description') }}</small>
      </div>

      <!-- Teams Credentials -->
      <div
        id="teams_credentials"
        @if ($Title->Meeting != 'Teams') style="display: none;" @endif
      >
        <div class="form-group mb-4">
          <label class="form-label" for="MICROSOFT_CLIENT_ID">
            {{ __('settings.MICROSOFT_CLIENT_ID') }}
          </label>
          <input
            id="MICROSOFT_CLIENT_ID"
            type="text"
            class="form-control"
            name="MICROSOFT_CLIENT_ID"
            value="{{ $Title->MICROSOFT_CLIENT_ID }}"
            placeholder="{{ __('settings.MICROSOFT_CLIENT_ID_Placeholder') }}"
          />
        </div>

        <div class="form-group mb-4">
          <label class="form-label" for="MICROSOFT_CLIENT_SECRET_VALUE">
            {{ __('settings.MICROSOFT_CLIENT_SECRET_VALUE') }}
          </label>
          <input
            id="MICROSOFT_CLIENT_SECRET_VALUE"
            type="text"
            class="form-control"
            name="MICROSOFT_CLIENT_SECRET_VALUE"
            value="{{ $Title->MICROSOFT_CLIENT_SECRET_VALUE }}"
            placeholder="{{ __('settings.MICROSOFT_CLIENT_SECRET_VALUE_Placeholder') }}"
          />
        </div>

        <div class="form-group mb-4">
          <label class="form-label" for="MICROSOFT_TENANT_ID">
            {{ __('settings.MICROSOFT_TENANT_ID') }}
          </label>
          <input
            id="MICROSOFT_TENANT_ID"
            type="text"
            class="form-control"
            name="MICROSOFT_TENANT_ID"
            value="{{ $Title->MICROSOFT_TENANT_ID }}"
            placeholder="{{ __('settings.MICROSOFT_TENANT_ID_Placeholder') }}"
          />
        </div>

        <div class="form-group mb-4">
          <label class="form-label" for="MICROSOFT_CLIENT_SECRET_ID">
            {{ __('settings.MICROSOFT_CLIENT_SECRET_ID') }}
          </label>
          <input
            id="MICROSOFT_CLIENT_SECRET_ID"
            type="text"
            class="form-control"
            name="MICROSOFT_CLIENT_SECRET_ID"
            value="{{ $Title->MICROSOFT_CLIENT_SECRET_ID }}"
            placeholder="{{ __('settings.MICROSOFT_CLIENT_SECRET_ID_Placeholder') }}"
          />
        </div>

        <div class="form-group mb-4">
          <label class="form-label" for="MICROSOFT_APP_EMAIL">
            {{ __('settings.MICROSOFT_APP_EMAIL') }}
          </label>
          <input
            id="MICROSOFT_APP_EMAIL"
            type="text"
            class="form-control"
            name="MICROSOFT_APP_EMAIL"
            value="{{ $Title->MICROSOFT_APP_EMAIL }}"
            placeholder="{{ __('settings.MICROSOFT_APP_EMAIL_Placeholder') }}"
          />
        </div>

        <div class="form-group mb-4">
          <label class="form-label" for="MICROSOFT_REDIRECT_URI">
            {{ __('settings.MICROSOFT_REDIRECT_URI') }}
          </label>
          <input
            id="MICROSOFT_REDIRECT_URI"
            type="text"
            class="form-control"
            name="MICROSOFT_REDIRECT_URI"
            value="{{ $Title->MICROSOFT_REDIRECT_URI }}"
            placeholder="{{ __('settings.MICROSOFT_REDIRECT_URI_Placeholder') }}"
          />
        </div>
      </div>

      <!-- Zoom Credentials -->
      <div
        id="zoom_credentials"
        @if ($Title->Meeting != 'Zoom') style="display: none;" @endif
      >
        <div class="form-group mb-4">
          <label class="form-label" for="ZOOM_CLIENT_ID">
            {{ __('settings.ZOOM_CLIENT_ID') }}
          </label>
          <input
            id="ZOOM_CLIENT_ID"
            type="text"
            class="form-control"
            name="ZOOM_CLIENT_ID"
            value="{{ $Title->ZOOM_CLIENT_ID }}"
            placeholder="{{ __('settings.ZOOM_CLIENT_ID_Placeholder') }}"
          />
        </div>

        <div class="form-group mb-4">
          <label class="form-label" for="ZOOM_CLIENT_SECRET">
            {{ __('settings.ZOOM_CLIENT_SECRET') }}
          </label>
          <input
            id="ZOOM_CLIENT_SECRET"
            type="text"
            class="form-control"
            name="ZOOM_CLIENT_SECRET"
            value="{{ $Title->ZOOM_CLIENT_SECRET }}"
            placeholder="{{ __('settings.ZOOM_CLIENT_SECRET_Placeholder') }}"
          />
        </div>

        <div class="form-group mb-4">
          <label class="form-label" for="ZOOM_ACCOUNT_ID">
            {{ __('settings.ZOOM_ACCOUNT_ID') }}
          </label>
          <input
            id="ZOOM_ACCOUNT_ID"
            type="text"
            class="form-control"
            name="ZOOM_ACCOUNT_ID"
            value="{{ $Title->ZOOM_ACCOUNT_ID }}"
            placeholder="{{ __('settings.ZOOM_ACCOUNT_ID_Placeholder') }}"
          />
        </div>
      </div>
    </div>
  </div>
  
  <div class="card border shadow-sm mb-6">
    <div class="card-header">
      <h6 class="card-title mb-0">{{ __('settings.Offline_Sessions') }}</h6>
      <span class="text-muted">{{ __('settings.Offline_Sessions_Description') }}</span>
    </div>
    <div class="card-body">
      <div class="form-group d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex flex-column">
          <label class="form-label" for="allow_offline_sessions">
            {{ __('settings.Allow_Offline_Sessions') }}
          </label>
          <span class="text-muted">{{ __('settings.Allow_Offline_Sessions_Description') }}</span>
        </div>
        <div class="form-check form-switch">
          <input
            class="form-check-input w-px-40 h-px-20"
            id="allow_offline_sessions"
            type="checkbox"
            name="allow_offline_sessions"
            value="{{ $Title->allow_offline_sessions ? $Title->allow_offline_sessions : '1' }}"
            {{ $Title->allow_offline_sessions == 1 ? 'checked' : '' }}
          />
        </div>
      </div>
      {{-- <div id="offline_sessions_fields" @if ($Title->allow_offline_sessions != 1) style="display: none;" @endif>
        <div class="form-group mb-4">
          <label class="form-label" for="default_offline_session_location">
            {{ __('settings.Default_Offline_Session_Location') }}
          </label>
          <input
            id="default_offline_session_location"
            type="text"
            class="form-control mb-1"
            name="default_offline_session_location"
            value="{{ $Title->default_offline_session_location }}"
            placeholder="{{ __('settings.Default_Offline_Session_Location_Placeholder') }}"
          />
          <small class="text-muted">{{ __('settings.Default_Offline_Session_Location_Description') }}</small>
        </div>
        <div class="form-group mb-4">
          <label class="form-label" for="map_location">
            {{ __('settings.Map_Location') }}
          </label>
          <div class="d-flex align-items-center gap-2 mb-1">
            <input
              id="map_location"
              type="text"
              class="form-control"
              name="map_location"
              value="{{ $Title->map_location }}"
              placeholder="{{ __('settings.Map_Location_Placeholder') }}"
            />
            <button
              type="button"
              class="btn btn-label-primary text-nowrap flex-shrink-0"
              id="openLocationModal"
            >
              <i class="ti ti-map-pin me-2"></i> {{ __('settings.Search_Location') }}
            </button>
            <button
              type="button"
              class="btn btn-icon btn-label-secondary"
              id="clearLocation"
              {{ empty($Title->map_location) ? 'style=display:none;' : '' }}
            >
              <i class="ti ti-x"></i>
            </button>
          </div>
          <small class="text-muted">{{ __('settings.Map_Location_Description') }}</small>
        </div>
      </div> --}}
    </div>
  </div>
  
  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-primary">
      {{ __('settings.Submit') }}
    </button>
  </div>
</form>

<!-- Location Search Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center" id="locationModalLabel">
         <i class="ti ti-map-pin me-2"></i> {{ __('settings.Search_Location') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <div class="input-group">
            <input 
              type="text" 
              class="form-control" 
              id="locationSearch" 
              placeholder="{{ __('settings.Search_Location_Placeholder') }}"
            />
            <button class="btn btn-primary" type="button" id="searchLocationBtn">
              <i class="ti ti-search me-2"></i> {{ __('settings.Search') }}
            </button>
          </div>
        </div>
        <div id="searchResults" class="mb-3" style="display: none;">
          <div class="list-group" id="searchResultsList"></div>
        </div>
        <div class="mt-3">
          <p class="mb-1"><strong>{{ __('settings.Selected_Location') }}:</strong></p>
          <p id="selectedLocationText" class="text-muted">{{ __('settings.No_Location_Selected') }}</p>
          <input type="hidden" id="selectedLatitude" />
          <input type="hidden" id="selectedLongitude" />
          <input type="hidden" id="selectedAddress" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('settings.Cancel') }}</button>
        <button type="button" class="btn btn-primary" id="confirmLocation">{{ __('settings.Confirm_Location') }}</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
var settingsTranslations = {!! json_encode(__('settings')) !!};
$(document).ready(function () {
  let selectedLocation = null;

  const $meetingForm = $("#meetingIntegrationForm");
  const $meetingSelect = $("#Meeting");
  const $offlineCheckbox = $("#allow_offline_sessions");
  const $offlineContainer = $("#offline_sessions_fields");

  /* ---------------------------
    UI Toggles
  --------------------------- */
  function toggleCredentialsUI() {
    const val = $meetingSelect.val();
    $("#zoom_credentials").toggle(val === "zoom");
    $("#teams_credentials").toggle(val === "teams");
  }

  function toggleOfflineUI() {
    $offlineContainer.toggle($offlineCheckbox.is(":checked"));
  }

  $meetingSelect.on("change", toggleCredentialsUI);
  $offlineCheckbox.on("change", toggleOfflineUI);

  toggleCredentialsUI();
  toggleOfflineUI();

  /* ---------------------------
    Search Location
  --------------------------- */
  async function searchLocation(query) {
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`
      );
      const data = await res.json();

      const $results = $("#searchResults");
      const $list = $("#searchResultsList").empty();

      if (!data.length) {
        $results.hide();
        alert(settingsTranslations.validation_no_results_found_alert);
        return;
      }

      $results.show();
      data.forEach(r => {
        $("<a href='#' class='list-group-item list-group-item-action'></a>")
          .html(`
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">${r.display_name}</h6>
            </div>
            <small class="text-muted">${r.type}</small>
          `)
          .on("click", function (e) {
            e.preventDefault();
            selectedLocation = {
              lat: parseFloat(r.lat),
              lng: parseFloat(r.lon),
              address: r.display_name
            };

            $("#selectedLocationText").text(r.display_name);
            $("#selectedLatitude").val(r.lat);
            $("#selectedLongitude").val(r.lon);
            $("#selectedAddress").val(r.display_name);
            $results.hide();
          })
          .appendTo($list);
      });

    } catch (err) {
      console.error("Search error:", err);
      alert(settingsTranslations.validation_search_error_alert);
    }
  }

  /* ---------------------------
    Modal Handling
  --------------------------- */
  $("#openLocationModal").on("click", function () {
    const modal = new bootstrap.Modal("#locationModal");
    modal.show();

    $("#locationSearch").val("");
    $("#searchResults").hide();
    $("#selectedLocationText").text(settingsTranslations.No_Location_Selected);
    selectedLocation = null;

    // Load existing location if exists
    const existing = $("#map_location").val();
    if (existing) {
      try {
        const p = JSON.parse(existing);
        const addr = p.address || p.display_name || existing;

        $("#selectedLocationText").text(addr);
        $("#selectedLatitude").val(p.lat || "");
        $("#selectedLongitude").val(p.lng || p.lon || "");
        $("#selectedAddress").val(addr);

        selectedLocation = {
          lat: p.lat ? parseFloat(p.lat) : null,
          lng: p.lng ? parseFloat(p.lng) : p.lon ? parseFloat(p.lon) : null,
          address: addr
        };
      } catch {
        $("#selectedLocationText").text(existing);
        $("#selectedAddress").val(existing);
        selectedLocation = { lat: null, lng: null, address: existing };
      }
    }
  });

  $("#searchLocationBtn").on("click", function () {
    const q = $("#locationSearch").val().trim();
    if (q) searchLocation(q);
  });

  $("#locationSearch").on("keypress", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      const q = $(this).val().trim();
      if (q) searchLocation(q);
    }
  });

  $("#confirmLocation").on("click", function () {
    if (selectedLocation?.address) {
      $("#map_location").val(selectedLocation.address);
      $("#clearLocation").show();
      bootstrap.Modal.getInstance($("#locationModal")[0]).hide();
    } else {
      alert(settingsTranslations.validation_select_location_alert);
    }
  });

  $("#clearLocation").on("click", function () {
    $("#map_location").val("");
    $(this).hide();
  });

  /* ---------------------------
    Sync map_location state
  --------------------------- */
  const $mapInput = $("#map_location");
  const $clearBtn = $("#clearLocation");

  function updateMapLocationState() {
    if ($mapInput) {
      const v = $mapInput.val() ? $mapInput.val().trim() : '';
      if (v) {
        $clearBtn.show();
        $("#selectedAddress").val(v);
        selectedLocation = { lat: null, lng: null, address: v };
      } else {
        $clearBtn.hide();
        $("#selectedAddress").val("");
        selectedLocation = null;
      }
    }
  }

  $mapInput?.on("input", updateMapLocationState);
  updateMapLocationState();

  /* ---------------------------
    Form Validation
  --------------------------- */
  const validationConfig = {
    fields: {
      /* ------------------------------
          Meeting Platform
      ------------------------------ */
      Meeting: {
        validators: {
          notEmpty: {
            message: settingsTranslations.validation_select_meeting_platform
          }
        }
      },
      max_concurrent_sessions: {
        validators: {
          integer: {
            message: settingsTranslations.validation_max_concurrent_sessions_integer
          }
        }
      },

      /* ------------------------------
          Zoom Fields
      ------------------------------ */
      ZOOM_CLIENT_ID: {
        validators: {
          callback: {
            message: settingsTranslations.validation_zoom_client_id_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "zoom") return true;
              return input.value.trim().length >= 5;
            }
          }
        }
      },
      ZOOM_CLIENT_SECRET: {
        validators: {
          callback: {
            message: settingsTranslations.validation_zoom_client_secret_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "zoom") return true;
              return input.value.trim().length >= 5;
            }
          }
        }
      },
      ZOOM_ACCOUNT_ID: {
        validators: {
          callback: {
            message: settingsTranslations.validation_zoom_account_id_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "zoom") return true;
              return input.value.trim().length >= 5;
            }
          }
        }
      },

      /* ------------------------------
          Teams Fields
      ------------------------------ */
      MICROSOFT_CLIENT_ID: {
        validators: {
          callback: {
            message: settingsTranslations.validation_microsoft_client_id_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "teams") return true;
              return input.value.trim().length >= 5;
            }
          }
        }
      },
      MICROSOFT_CLIENT_SECRET_VALUE: {
        validators: {
          callback: {
            message: settingsTranslations.validation_microsoft_secret_value_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "teams") return true;
              return input.value.trim().length >= 5;
            }
          }
        }
      },
      MICROSOFT_TENANT_ID: {
        validators: {
          callback: {
            message: settingsTranslations.validation_microsoft_tenant_id_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "teams") return true;
              return input.value.trim().length >= 5;
            }
          }
        }
      },
      MICROSOFT_CLIENT_SECRET_ID: {
        validators: {
          callback: {
            message: settingsTranslations.validation_microsoft_client_secret_id_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "teams") return true;
              return input.value.trim().length >= 5;
            }
          }
        }
      },
      MICROSOFT_APP_EMAIL: {
        validators: {
          callback: {
            message: settingsTranslations.validation_microsoft_app_email_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "teams") return true;
              return input.value.trim().length >= 5;
            }
          }
        }
      },
      MICROSOFT_REDIRECT_URI: {
        validators: {
          callback: {
            message: settingsTranslations.validation_microsoft_redirect_uri_required,
            callback: function (input) {
              const meet = $("#Meeting").val();
              if (meet !== "teams") return true;
              return input.value.trim().length >= 10;
            }
          }
        }
      },

      /* ------------------------------
          Offline Fields
      ------------------------------ */
      default_offline_session_location: {
        validators: {
          stringLength: {
            min: 3,
            max: 255,
            message: settingsTranslations.validation_default_offline_location_length,
          },
          callback: {
            message: settingsTranslations.validation_default_offline_location_required,
            callback: function (input) {
              const ok = $("#allow_offline_sessions").is(":checked");
              if (!ok) return true;
              return input.value.trim().length >= 3;
            }
          }
        }
      },
      map_location: {
        validators: {
          stringLength: {
            min: 3,
            max: 255,
            message: settingsTranslations.validation_map_location_length,
          },
        }
      }
    },

    plugins: {
      /* Bootstrap integration */
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        rowSelector: ".form-group",
        eleValidClass: '',
      }),

      /* Submit handler */
      submitButton: new FormValidation.plugins.SubmitButton(),
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus(),

      /* Trigger validator on input/blur/change */
      trigger: new FormValidation.plugins.Trigger(),
    }
  };

  const fv = FormValidation.formValidation($meetingForm[0], validationConfig);

  /* ---------------------------------
      LIVE updating of dynamic rules
  ---------------------------------- */
  $("#Meeting, #allow_offline_sessions").on("change", function () {
    fv.revalidateField("ZOOM_CLIENT_ID");
    fv.revalidateField("ZOOM_CLIENT_SECRET");
    fv.revalidateField("ZOOM_ACCOUNT_ID");

    fv.revalidateField("MICROSOFT_CLIENT_ID");
    fv.revalidateField("MICROSOFT_CLIENT_SECRET_VALUE");
    fv.revalidateField("MICROSOFT_TENANT_ID");
    fv.revalidateField("MICROSOFT_CLIENT_SECRET_ID");
    fv.revalidateField("MICROSOFT_APP_EMAIL");
    fv.revalidateField("MICROSOFT_REDIRECT_URI");

    fv.revalidateField("default_offline_session_location");
    fv.revalidateField("map_location");
  });
});
</script>
@endpush
