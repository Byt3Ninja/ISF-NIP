@extends('layouts.admin')

@section('style')
  <style>
    .remove-tag:hover {
      color: #ff3e1d;
    }
  </style>
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-body">
        
        <!-- Notifications -->
        <section id="notifications">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        </section>

        <!-- Cycle Selection -->
        <section id="cycle-selection">
          <div class="card-header mb-4">
            <h4 class="mb-1">{{ __('cycles.manage_mentors') }}</h4>
            <p class="mb-0">{{ __('cycles.manage_mentor_description1') }}</p>
            <p class="mb-0">{{ __('cycles.manage_mentor_description2') }}</p>
          </div>
          <div class="card mb-6">
            <div class="card-body overflow-x-hidden">
              <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between align-items-md-center">
                <div class="form-group d-flex flex-column flex-lg-row align-items-lg-center gap-2 mb-6 mb-lg-0 me-lg-4">
                  <label class="form-label fs-6">{{ __('cycles.Select_Cycle') }}:</label>
                  <select id="cstep" class="select2 form-select" style="width: 350px;"
                    onchange="window.open('/dashboard/mentorship/assign/'+this.options[this.selectedIndex].value,'_self');">
                    @foreach ($cycles as $cycle)
                      <option value="{{ $cycle->id }}" {{ $cycle->id == $id ? 'selected' : '' }}>
                        {{ $cycle->title }} ({{ $cycle->start->format('d-m-Y') }} - {{ $cycle->end->format('d-m-Y') }})
                      </option>
                    @endforeach
                    @if (!$id)
                      <option> {{ __('cycles.No records saved') }} </option>
                    @endif
                  </select>
                </div>
                <div class="d-flex flex-wrap flex-lg-nowrap align-items-center gap-3">
                  <a class="btn btn-label-primary" href="/dashboard/cycles/{{ $id }}">
                    <i class="ti ti-components me-2"></i> {{ __('cycles.Manage Cycle') }}
                  </a>
                  <a class="btn btn-primary" href="/dashboard/mentorship/view/{{ $id }}">
                    <i class="ti ti-settings me-2"></i> {{ __('cycles.Manage sessions') }}
                  </a>
                  <a class="btn btn-label-secondary" href="/dashboard/mentorship/{{ $id }}/surveys">
                    <i class="ti ti-question-mark me-2"></i> {{ __('cycles.Manage Surveys') }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Main Content -->
        <section>
          <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-lg-center border-bottom">
              <h5 class="card-title mb-6 mb-lg-0">
                {{ __('cycles.assign_mentors_to') }}
                <span class="fw-bold ms-1">
                  {{ $cycles->where('id', request('id'))->pluck('title')->implode(', ') }}
                </span>
              </h5>
              
              <!-- PHP Logic for Filters -->
              @php
                $specializations = [];
                // Reusing your existing logic efficiently
                $TMentors = App\User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('roles.default_for', 'mentor')
                        ->select('users.*')->get();
                foreach ($TMentors as $mentor) {
                        $spec = $mentor->bio->{'specialization_' . $l} ?? null;
                        if ($spec && !in_array($spec, $specializations)) $specializations[] = $spec;
                }
                $specializations = array_unique($specializations);
              @endphp
              <!-- Filters Section -->
              <div class="row g-3">
                <div class="col-md-4">
                  <select id="specializationFilter" class="form-select">
                    <option value="">{{ __('cycles.Specialization') }} ({{ __('admin.All') }})</option>
                    @foreach ($specializations as $spec)
                      <option value="{{ $spec }}" {{ request('specialization') == $spec ? 'selected' : '' }}>{{ $spec }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <select id="ratingFilter" class="form-select">
                    <option value="0">{{ __('admin.Rating') }} ({{ __('admin.All') }})</option>
                    @for($i=1; $i<=5; $i++)
                      <option value="{{ $i }}" {{ request('rate') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                  </select>
                </div>
                <div class="col-md-4">
                    <input type="text" id="clientSearchInput" class="form-control" placeholder="{{ (__('admin.Search')) }}...">
                </div>
              </div>
            </div>

            <form action="/dashboard/mentorship/assign/{{ $id }}" method="POST" id="mainForm">
              {!! csrf_field() !!}
              <input type="hidden" name="cycle" value="{{ $id }}">
              <input type="hidden" id="selected-mentors" name="selected_mentors" value="">

              <!-- SELECTED MENTORS BAR -->
              <div class="card shadow-none border-bottom bg-light-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold"><i class="ti ti-tags me-1"></i> {{ __('cycles.mentors_selected') }}: <span id="selected-count">0</span></h6>
                        <button type="button" class="btn btn-sm btn-label-danger d-none" id="clearAllBtn">{{ __('cycles.clear_all') }}</button>
                    </div>
                    <div id="selected-mentors-container" class="d-flex flex-wrap gap-2" style="width: 90%;">
                        <span class="text-muted fst-italic" id="no-selection-msg">{{ __('cycles.no_mentors_selected') }}.</span>
                    </div>
                </div>
              </div>

              <!-- TOOLBAR & PAGINATION CONTROLS -->
              <div class="card shadow-none border-bottom mb-6">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-8">
                      <div class="form-check mb-0">
                          <input class="form-check-input" type="checkbox" id="checkAllVisible">
                          <label class="form-check-label fw-bold" for="checkAllVisible">
                              {{ __('cycles.Select All') }}
                          </label>
                      </div>
                      <!-- Save Button -->
                     <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ti ti-device-floppy me-2"></i> {{ __('cycles.Save') }}
                     </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-nowrap">{{ __('admin.show') }}:</label>
                        <select id="rowsPerPage" class="form-select form-select-sm" style="width: 70px;">
                            <option value="8">8</option>
                            <option value="12" selected>12</option>
                            <option value="24">24</option>
                            <option value="48">48</option>
                        </select>
                        <span id="pagination-info" class="text-muted small text-nowrap ms-2"></span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card shadow-none border-none">
                <div class="card-body">
              <!-- MENTORS CARD GRID -->
              <div class="row g-4 mb-4" id="mentors-grid">
                @foreach ($mentors as $mentor)
                  <div class="col-lg-4 col-md-6 mentor-item-col"
                       data-name="{{ strtolower($mentor->name) }} {{ strtolower($mentor->title) }}"
                       data-specialization="{{ $mentor->bio->{'specialization_' . $l} ?? '' }}"
                       data-rating="{{ number_format($mentor->mrating(), 1) }}">
                        
                        <div class="card h-100 position-relative shadow-xs border" id="card-{{ $mentor->id }}">
                            <div class="card-body d-flex flex-column position-relative">
                                <div class="position-absolute top-0 start-0 ps-2 pt-1">
                                  <div class="form-check mt-1">
                                    <input class="form-check-input mentor-checkbox"
                                        type="checkbox"
                                        value="{{ $mentor->id }}"
                                        id="checkbox{{ $mentor->id }}"
                                        data-name="{{ $mentor->name }}"
                                        {{ in_array($mentor->id, $cList) ? 'checked' : '' }}>
                                  </div>
                                </div>
                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <!-- Left side: Avatar + Name/Title block -->
                                    <div class="d-flex align-items-center gap-3">
                                        <img 
                                          src="{{ $mentor->image_url ?? '/icons/account.svg' }}" 
                                          alt="{{ $mentor->name }}" 
                                          class="rounded-circle w-px-52 h-px-52 flex-shrink-0 border"
                                          onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                                        />

                                        <div>
                                            <h6 class="mb-0 card-title fw-semibold text-truncate" title="{{ $mentor->name }}">{{ $mentor->name }}</h6>
                                            <p class="text-muted small mb-0 text-truncate">{{ $mentor->title }}</p>
                                        </div>
                                    </div>

                                    <!-- Right side: LinkedIn icon + Checkbox -->
                                    @if ($mentor->bio->linkedin ?? 0)
                                        <a href="{{ $mentor->bio->linkedin }}" target="_blank" class="btn btn-icon btn-label-primary w-px-30 h-px-30">
                                            <i class="fa-brands fa-linkedin-in"></i>
                                        </a>
                                    @endif
                                </div>

                                <!-- Bio -->
                                <p class="line-clamp-3 small mb-4">
                                    {{ $mentor->bio->{'bio_' . $l} ?? __('admin.No bio has been added yet') }}
                                </p>

                                <!-- Footer -->
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <!-- Left side: Specialization badge -->
                                    @if (isset($mentor->bio->{'specialization_' . $l}))
                                      <span class="badge bg-label-secondary">{{ $mentor->bio->{'specialization_' . $l} ?? '' }}</span>
                                    @endif

                                    <!-- Right side: Rating badge -->
                                    <span class="badge bg-light-subtle border d-flex align-items-center gap-2">
                                        <i class="ti ti-star-filled ti-16px text-warning"></i>
                                        <span class="text-dark fw-semibold">{{ number_format($mentor->mrating(), 1) }}</span>
                                    </span>
                                </div>
                            </div>
                            
                        </div>
                      </div>
                    @endforeach
                    
                    <!-- No Results Message -->
                    <div id="no-results" class="col-12 text-center py-5" style="display: none;">
                        <i class="ti ti-search fs-1 text-muted mb-2"></i>
                        <p class="text-muted">{{ __('cycles.no_mentors_found_matched_your_criteria') }}</p>
                    </div>
                  </div>
    
                  <!-- Pagination Footer -->
                  <div class="d-flex justify-content-between align-items-center border-top pt-3">
                     <button type="button" class="btn btn-outline-secondary" id="prevPage" disabled>
                         <i class="ti ti-chevron-{{ $l == 'en' ? 'left' : 'right' }}"></i> {{ __('admin.Previous') }}
                     </button>
    
                     <button type="button" class="btn btn-outline-primary" id="nextPage">
                         {{ __('admin.Next') }} <i class="ti ti-chevron-{{ $l == 'en' ? 'right' : 'left' }}"></i>
                     </button>
                  </div>
                </div>
              </div>

            </form>
          </div>
        </section>
      </div>
    </div>
  </div>
@stop

@section('pagescripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- State Management ---
        let selectedMentors = []; // Stores IDs: ['1', '50', '23']
        let mentorNames = {};     // Stores Map: {'1': 'John Doe'}
        let initialMentors = [];  // Stores initial mentor selections for change detection
        let hasChanges = false;   // Track if changes have been made
        const saveButton = document.querySelector('button[type="submit"]');
        
        // --- DOM Elements ---
        const allMentorCols = Array.from(document.querySelectorAll('.mentor-item-col'));
        const searchInput = document.getElementById('clientSearchInput');
        const rowsPerPageSelect = document.getElementById('rowsPerPage');
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        const paginationInfo = document.getElementById('pagination-info');
        const checkAllVisible = document.getElementById('checkAllVisible');
        const hiddenInput = document.getElementById('selected-mentors');
        const selectedContainer = document.getElementById('selected-mentors-container');
        const selectedCountSpan = document.getElementById('selected-count');
        const noSelectionMsg = document.getElementById('no-selection-msg');
        const clearAllBtn = document.getElementById('clearAllBtn');
        const noResultsDiv = document.getElementById('no-results');
        const specializationFilter = document.getElementById('specializationFilter');
        const ratingFilter = document.getElementById('ratingFilter');

        // --- Initialization ---
        let currentPage = 1;
        let rowsPerPage = parseInt(rowsPerPageSelect.value);
        let visibleItems = allMentorCols; // Currently filtered items

        // Initialize state from server-rendered checkboxes
        document.querySelectorAll('.mentor-checkbox:checked').forEach(cb => {
            addSelection(cb.value, cb.dataset.name);
        });

        // Store initial state for change detection
        initialMentors = [...selectedMentors];

        // Disable save button initially
        saveButton.disabled = true;

        updateUI();

        // --- Event Listeners ---

        // 1. Pagination Change
        rowsPerPageSelect.addEventListener('change', (e) => {
            rowsPerPage = parseInt(e.target.value);
            currentPage = 1;
            renderGrid();
        });

        // 2. Search Filter
        searchInput.addEventListener('keyup', (e) => {
            applyFilters();
        });

        // 3. Navigation
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderGrid();
            }
        });

        nextBtn.addEventListener('click', () => {
            const maxPage = Math.ceil(visibleItems.length / rowsPerPage);
            if (currentPage < maxPage) {
                currentPage++;
                renderGrid();
            }
        });

        // 4. Card Selection (Delegation)
        // Note: The click on the card triggers the label, which triggers the checkbox.
        // We listen to the checkbox change.
        document.getElementById('mentors-grid').addEventListener('change', function(e) {
            if (e.target.classList.contains('mentor-checkbox')) {
                const id = e.target.value;
                const name = e.target.dataset.name;
                
                if (e.target.checked) {
                    addSelection(id, name);
                } else {
                    removeSelection(id);
                }
                updateUI();
            }
        });

        // 5. Select All Visible
        checkAllVisible.addEventListener('change', function() {
            const isChecked = this.checked;
            
            // Get currently displayed items (based on filter/search)
            // Note: Decide if "Select All" means *current page* or *all filtered results*.
            // Implementation: All filtered results to be more useful.
            
            visibleItems.forEach(col => {
                const checkbox = col.querySelector('.mentor-checkbox');
                checkbox.checked = isChecked;
                
                // Trigger visual update for the card
                toggleCardVisual(checkbox);

                if (isChecked) {
                    addSelection(checkbox.value, checkbox.dataset.name);
                } else {
                    removeSelection(checkbox.value);
                }
            });
            updateUI();
        });

        // 6. Clear All
        clearAllBtn.addEventListener('click', function() {
            selectedMentors = [];
            mentorNames = {};

            // Uncheck all checkboxes in DOM
            document.querySelectorAll('.mentor-checkbox').forEach(cb => {
                cb.checked = false;
                toggleCardVisual(cb);
            });

            checkAllVisible.checked = false;
            updateUI();
        });

        // 7. Specialization Filter
        specializationFilter.addEventListener('change', function() {
            applyFilters();
        });

        // 8. Rating Filter
        ratingFilter.addEventListener('change', function() {
            applyFilters();
        });

        // --- Core Functions ---

        function checkForChanges() {
            // Compare current selections with initial state
            const currentSorted = [...selectedMentors].sort();
            const initialSorted = [...initialMentors].sort();

            // Check if arrays are different
            const hasChanges = currentSorted.length !== initialSorted.length ||
                              currentSorted.some((val, index) => val !== initialSorted[index]);

            saveButton.disabled = !hasChanges;
            return hasChanges;
        }

        function addSelection(id, name) {
            if (!selectedMentors.includes(id)) {
                selectedMentors.push(id);
                mentorNames[id] = name;
            }
            // Ensure DOM is synced (for items not currently in view if manipulated externally)
            const cb = document.getElementById('checkbox' + id);
            if(cb) {
                cb.checked = true;
                toggleCardVisual(cb);
            }
            checkForChanges();
        }

        function removeSelection(id) {
            selectedMentors = selectedMentors.filter(item => item !== id);
            delete mentorNames[id];

            const cb = document.getElementById('checkbox' + id);
            if(cb) {
                cb.checked = false;
                toggleCardVisual(cb);
            }
            checkForChanges();
        }

        function toggleCardVisual(checkbox) {
            const card = document.getElementById('card-' + checkbox.value);
            if (checkbox.checked) {
                card.classList.add('border-primary');
            } else {
                card.classList.remove('border-primary');
            }
        }

        function updateUI() {
            // 1. Update Hidden Input
            hiddenInput.value = selectedMentors.join(',');

            // 2. Update Count
            selectedCountSpan.textContent = selectedMentors.length;

            // 3. Render Tags
            selectedContainer.innerHTML = '';
            if (selectedMentors.length === 0) {
                selectedContainer.appendChild(noSelectionMsg);
                clearAllBtn.classList.add('d-none');
            } else {
                clearAllBtn.classList.remove('d-none');
                selectedMentors.forEach(id => {
                    const tag = document.createElement('span');
                    tag.className = 'badge bg-label-primary selected-tag small d-flex align-items-center';
                    tag.innerHTML = `
                        ${mentorNames[id]}
                        <i class="ti ti-x remove-tag cursor-pointer ms-2" data-id="${id}"></i>
                    `;
                    // Remove handler
                    tag.querySelector('.remove-tag').addEventListener('click', (e) => {
                        e.stopPropagation(); // prevent bubbling
                        removeSelection(e.target.dataset.id);
                        updateUI();
                    });
                    selectedContainer.appendChild(tag);
                });
            }

            // Check for changes and update save button state
            checkForChanges();
        }

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedSpecialization = specializationFilter.value;
            const selectedRating = parseFloat(ratingFilter.value);

            visibleItems = allMentorCols.filter(col => {
                // Check search term
                const matchesSearch = col.dataset.name.includes(searchTerm);

                // Check specialization
                const matchesSpecialization = !selectedSpecialization || col.dataset.specialization === selectedSpecialization;

                // Check rating
                const mentorRating = parseFloat(col.dataset.rating);
                const matchesRating = selectedRating === 0 || (
                    mentorRating >= selectedRating - 0.5 && mentorRating < selectedRating + 0.5
                );

                return matchesSearch && matchesSpecialization && matchesRating;
            });

            currentPage = 1;
            if(visibleItems.length === 0) {
                noResultsDiv.style.display = 'block';
            } else {
                noResultsDiv.style.display = 'none';
            }
            renderGrid();
            // Reset "Select All" check if filters change
            checkAllVisible.checked = false;
        }

        function renderGrid() {
            // Hide all first
            allMentorCols.forEach(col => col.style.display = 'none');

            // Calculate range
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageItems = visibleItems.slice(start, end);

            // Show specific items
            pageItems.forEach(col => col.style.display = 'block');

            // Update Pagination Text
            const total = visibleItems.length;
            const displayEnd = Math.min(end, total);
            const displayStart = total === 0 ? 0 : start + 1;

            paginationInfo.textContent = `@lang('admin.showing') ${displayStart}-${displayEnd} @lang('admin.of') ${total}`;

            // Update Button States
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage >= Math.ceil(total / rowsPerPage) || total === 0;
        }

        // Initial Render
        renderGrid();
    });
  </script>
@stop
