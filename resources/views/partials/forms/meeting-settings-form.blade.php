<form
  class="form d-flex flex-column h-100"
  action="{{ route('settings.system_meeting_integration_update') }}"
  method="POST"
  enctype="multipart/form-data"
  id="meetingIntegrationForm"
>
  {!! csrf_field() !!}
  @method('PUT')

  <input
    type="hidden"
    name="id"
    value="{{ $Title->id }}"
  />

  <div class="form-content flex-grow-1">

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
            />
          </div>
        </div>
      </div>
    </div>
    
    <div class="card border shadow-sm">
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
              value="1"
              {{ $Title->allow_offline_sessions == 1 ? 'checked' : '' }}
            />
          </div>
        </div>
        <div id="offline_sessions_fields" @if ($Title->allow_offline_sessions != 1) style="display: none;" @endif>
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
            <div class="input-group mb-1">
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
                class="btn btn-outline-primary" 
                id="openLocationModal"
              >
                <i class="ti ti-map-pin me-2"></i> {{ __('settings.Search_Location') }}
              </button>
              <button 
                type="button" 
                class="btn btn-outline-secondary" 
                id="clearLocation"
                {{ empty($Title->map_location) ? 'style=display:none;' : '' }}
              >
                <i class="ti ti-x"></i>
              </button>
            </div>
            <small class="text-muted">{{ __('settings.Map_Location_Description') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end mt-12">
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
document.addEventListener("DOMContentLoaded", () => {
    let selectedLocation = null;
    let fv;

    // Config
    const meetingSelect = document.getElementById("Meeting");
    const offlineCheckbox = document.getElementById("allow_offline_sessions");
    const offlineContainer = document.getElementById("offline_sessions_fields");
    const offlineFields = ["default_offline_session_location", "map_location"];
    const teamsFields = [
        "MICROSOFT_CLIENT_ID",
        "MICROSOFT_CLIENT_SECRET_VALUE",
        "MICROSOFT_TENANT_ID",
        "MICROSOFT_CLIENT_SECRET_ID",
        "MICROSOFT_APP_EMAIL",
        "MICROSOFT_REDIRECT_URI",
    ];
    const zoomFields = ["ZOOM_CLIENT_ID", "ZOOM_CLIENT_SECRET", "ZOOM_ACCOUNT_ID"];

    /* ---------------------------
       UI Toggling helpers
       --------------------------- */
    function toggleCredentialsUI() {
        const value = meetingSelect.value;
        document.getElementById("zoom_credentials").style.display = value === "zoom" ? "block" : "none";
        document.getElementById("teams_credentials").style.display = value === "teams" ? "block" : "none";
    }

    function toggleOfflineUI() {
        offlineContainer.style.display = offlineCheckbox.checked ? "block" : "none";
    }

    meetingSelect.addEventListener("change", toggleCredentialsUI);
    offlineCheckbox.addEventListener("change", toggleOfflineUI);
    toggleCredentialsUI();
    toggleOfflineUI();

    /* ---------------------------
       Location search & modal
       --------------------------- */
    async function searchLocation(query) {
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`);
            const data = await res.json();
            const list = document.getElementById("searchResultsList");
            list.innerHTML = "";

            if (!data || data.length === 0) {
                document.getElementById("searchResults").style.display = "none";
                alert("No results found");
                return;
            }

            document.getElementById("searchResults").style.display = "block";
            data.forEach(r => {
                const a = document.createElement("a");
                a.href = "#";
                a.className = "list-group-item list-group-item-action";
                a.innerHTML = `<div class="d-flex w-100 justify-content-between"><h6 class="mb-1">${r.display_name}</h6></div><small class="text-muted">${r.type}</small>`;
                a.addEventListener("click", (e) => {
                    e.preventDefault();
                    selectedLocation = { lat: parseFloat(r.lat), lng: parseFloat(r.lon), address: r.display_name };
                    document.getElementById("selectedLocationText").textContent = r.display_name;
                    document.getElementById("selectedLatitude").value = r.lat;
                    document.getElementById("selectedLongitude").value = r.lon;
                    document.getElementById("selectedAddress").value = r.display_name;
                    document.getElementById("searchResults").style.display = "none";
                });
                list.appendChild(a);
            });
        } catch (err) {
            console.error("Search error:", err);
            alert("Search error occurred");
        }
    }

    document.getElementById("openLocationModal").addEventListener("click", () => {
        const modal = new bootstrap.Modal(document.getElementById("locationModal"));
        modal.show();

        document.getElementById("locationSearch").value = "";
        document.getElementById("searchResults").style.display = "none";
        document.getElementById("selectedLocationText").textContent = "No location selected";
        selectedLocation = null;

        const existing = document.getElementById("map_location").value;
        if (existing) {
            try {
                const parsed = JSON.parse(existing);
                const addr = parsed.address || parsed.display_name || existing;
                document.getElementById("selectedLocationText").textContent = addr;
                document.getElementById("selectedLatitude").value = parsed.lat || "";
                document.getElementById("selectedLongitude").value = parsed.lng || parsed.lon || "";
                document.getElementById("selectedAddress").value = addr;
                selectedLocation = {
                    lat: parsed.lat ? parseFloat(parsed.lat) : null,
                    lng: parsed.lng ? parseFloat(parsed.lng) : parsed.lon ? parseFloat(parsed.lon) : null,
                    address: addr,
                };
            } catch (e) {
                document.getElementById("selectedLocationText").textContent = existing;
                document.getElementById("selectedAddress").value = existing;
                selectedLocation = { lat: null, lng: null, address: existing };
            }
        }
    });

    document.getElementById("searchLocationBtn").addEventListener("click", () => {
        const q = document.getElementById("locationSearch").value.trim();
        if (q) searchLocation(q);
    });
    document.getElementById("locationSearch").addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            const q = e.target.value.trim();
            if (q) searchLocation(q);
        }
    });

    document.getElementById("confirmLocation").addEventListener("click", () => {
        if (selectedLocation && selectedLocation.address) {
            document.getElementById("map_location").value = selectedLocation.address;
            document.getElementById("clearLocation").style.display = "inline-block";
            bootstrap.Modal.getInstance(document.getElementById("locationModal")).hide();
            if (fv) fv.revalidateField("map_location");
        } else {
            alert("Please select a location");
        }
    });

    document.getElementById("clearLocation").addEventListener("click", function () {
        document.getElementById("map_location").value = "";
        this.style.display = "none";
        if (fv) fv.revalidateField("map_location");
    });

    const mapLocationInput = document.getElementById("map_location");
    const clearLocationBtn = document.getElementById("clearLocation");
    function updateMapLocationState() {
        const v = mapLocationInput.value.trim();
        if (v) {
            clearLocationBtn.style.display = "inline-block";
            document.getElementById("selectedAddress").value = v;
            selectedLocation = { lat: null, lng: null, address: v };
        } else {
            clearLocationBtn.style.display = "none";
            document.getElementById("selectedAddress").value = "";
            selectedLocation = null;
        }
        if (fv) fv.revalidateField("map_location");
    }
    mapLocationInput.addEventListener("input", updateMapLocationState);
    updateMapLocationState();

    /* ---------------------------
       Helpers: validators enable/disable & reset
       --------------------------- */
    function enableFieldValidators(field, validators = ["notEmpty", "stringLength"]) {
        validators.forEach(v => fv.enableValidator(field, v));
    }
    function disableFieldValidatorsAndClear(field, validators = ["notEmpty", "stringLength"]) {
        validators.forEach(v => fv.disableValidator(field, v));
        fv.resetField(field, true);           // clear error UI & state
        fv.updateFieldStatus(field, "Valid"); // ensure FV treats it as valid for submit
    }

    /* ---------------------------
       FormValidation init
       --------------------------- */
    fv = FormValidation.formValidation(document.getElementById("meetingIntegrationForm"), {
        fields: {
            Meeting: {
                validators: {
                    notEmpty: { message: "Meeting platform is required" },
                    callback: {
                        message: "Meeting platform is required",
                        callback: input => input.value !== "0"
                    }
                }
            },
            MICROSOFT_CLIENT_ID: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 36, max: 36 }, regexp: { enabled: false, regexp: /^[0-9a-fA-F-]{36}$/ } } },
            MICROSOFT_CLIENT_SECRET_VALUE: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 20, max: 100 } } },
            MICROSOFT_TENANT_ID: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 36, max: 36 }, regexp: { enabled: false, regexp: /^[0-9a-fA-F-]{36}$/ } } },
            MICROSOFT_CLIENT_SECRET_ID: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 36, max: 36 }, regexp: { enabled: false, regexp: /^[0-9a-fA-F-]{36}$/ } } },
            MICROSOFT_APP_EMAIL: { validators: { notEmpty: { enabled: false }, emailAddress: { enabled: false }, stringLength: { enabled: false, min: 5, max: 255 } } },
            MICROSOFT_REDIRECT_URI: { validators: { notEmpty: { enabled: false }, uri: { enabled: false }, stringLength: { enabled: false, min: 10, max: 500 } } },
            ZOOM_CLIENT_ID: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 10, max: 100 } } },
            ZOOM_CLIENT_SECRET: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 10, max: 100 } } },
            ZOOM_ACCOUNT_ID: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 10, max: 100 } } },
            default_offline_session_location: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 3, max: 255 } } },
            map_location: { validators: { notEmpty: { enabled: false }, stringLength: { enabled: false, min: 10, max: 1000 } } }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: "", rowSelector: ".form-group" }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            autoFocus: new FormValidation.plugins.AutoFocus()
        }
    });

    /* ---------------------------
       Meeting change: enable/disable platform validators
       --------------------------- */
    meetingSelect.addEventListener("change", () => {
        const sel = meetingSelect.value;

        teamsFields.forEach(field => {
            if (sel === "teams") {
                enableFieldValidators(field);
                if (field === "MICROSOFT_APP_EMAIL") fv.enableValidator(field, "emailAddress");
                if (field === "MICROSOFT_REDIRECT_URI") fv.enableValidator(field, "uri");
                if (["MICROSOFT_CLIENT_ID", "MICROSOFT_TENANT_ID", "MICROSOFT_CLIENT_SECRET_ID"].includes(field)) fv.enableValidator(field, "regexp");
            } else {
                // disable and clear
                fv.disableValidator(field, "notEmpty");
                fv.disableValidator(field, "stringLength");
                fv.disableValidator(field, "emailAddress");
                fv.disableValidator(field, "uri");
                fv.disableValidator(field, "regexp");
                fv.resetField(field, true);
                fv.updateFieldStatus(field, "Valid");
            }
        });

        zoomFields.forEach(field => {
            if (sel === "zoom") {
                enableFieldValidators(field);
            } else {
                fv.disableValidator(field, "notEmpty");
                fv.disableValidator(field, "stringLength");
                fv.resetField(field, true);
                fv.updateFieldStatus(field, "Valid");
            }
        });

        fv.revalidateField("Meeting");
    });

    /* ---------------------------
       Offline checkbox: enable/disable offline validators (uses helpers)
       --------------------------- */
    offlineCheckbox.addEventListener("change", () => {
        if (offlineCheckbox.checked) {
            offlineFields.forEach(f => enableFieldValidators(f));
            offlineFields.forEach(f => fv.revalidateField(f));
        } else {
            offlineFields.forEach(f => disableFieldValidatorsAndClear(f));
        }
    });

    /* ---------------------------
       Initial state
       --------------------------- */
    meetingSelect.dispatchEvent(new Event("change"));

    if (offlineCheckbox.checked) {
        offlineFields.forEach(f => enableFieldValidators(f));
        offlineFields.forEach(f => fv.revalidateField(f));
    } else {
        offlineFields.forEach(f => disableFieldValidatorsAndClear(f));
    }
});
</script>
@endpush
