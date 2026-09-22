@extends('layouts.admin')

@section('style')
  <style>
    .remove-tag:hover {
      color: #ff3e1d;
    }
    .cursor-pointer {
      cursor: pointer;
    }
    .cursor-pointer:hover {
      opacity: 0.85;
    }
  </style>
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
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
        <section id="cycle-selection" class="mb-4">
          <div class="card">
            <div class="card-body">

              {{-- Header text --}}
              <div class="row mb-3">
                <div class="col-12">
                  <h4 class="mb-1">{{ __('cycles.manage_mentors') }}</h4>
                  <p class="mb-0 text-muted">{{ __('cycles.manage_mentor_description1') }}</p>
                  <p class="mb-0 text-muted">{{ __('cycles.manage_mentor_description2') }}</p>
                </div>
              </div>

              {{-- Cycle dropdown + action buttons --}}
              <div class="row g-3 align-items-end">

                {{-- Dropdown --}}
                <div class="col-12 col-lg-5">
                  <label class="form-label" for="cstep">{{ __('cycles.Select_Cycle') }}</label>
                  <select id="cstep" class="form-select select2"
                    onchange="window.open('/dashboard/mentorship/assign/'+this.value,'_self');">
                    @foreach ($cycles as $cycle)
                      <option value="{{ $cycle->id }}" @selected($cycle->id == $id)>
                        {{ $cycle->title }}
                       ({{ $cycle->start?->format('d-m-Y') ?? '-' }} - {{ $cycle->end?->format('d-m-Y') ?? '-' }})
                       — {{ optional($cycle->organization)->name }}
                      </option>
                    @endforeach
                    @unless ($id)
                      <option>{{ __('cycles.No records saved') }}</option>
                    @endunless
                  </select>
                </div>

                {{-- Action buttons --}}
                <div class="col-12 col-lg-7">
                  <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a class="btn btn-primary" href="/dashboard/cycles/{{ $id }}">
                      <i class="ti ti-adjustments me-2"></i>{{ __('cycles.Manage Cycle') }}
                    </a>
                    <a class="btn btn-label-primary" href="/dashboard/mentorship/view/{{ $id }}">
                      <i class="ti ti-settings me-2"></i>{{ __('cycles.Manage sessions') }}
                    </a>
                    <a class="btn btn-outline-primary" href="/dashboard/mentorship/{{ $id }}/surveys">
                      <i class="ti ti-question-mark me-2"></i>{{ __('cycles.Manage Surveys') }}
                    </a>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </section>

        <!-- Main Content -->
        <section>
          <div class="card">
            <div class="card-header d-flex flex-column flex-md-row flex-wrap justify-content-between align-items-lg-center border-bottom">
              <div class="d-flex flex-wrap align-items-center justify-content-between w-100 mb-8">
                <h5 class="card-title mb-6 mb-lg-0">
                  {{ __('cycles.assign_mentors_to') }}
                  <span class="fw-bold ms-1">
                    {{ $cycles->where('id', request('id'))->pluck('title')->implode(', ') }}
                  </span>
                </h5>
                @if ($_user->hasAnyRole('admin'))
                <div class="mb-6 mb-lg-0">
                  <a class="btn btn-primary" href="{{ route('users.create', ['role' => 'mentor']) }}" target="_blank" rel="noopener">
                    <i class="ti ti-user-plus me-2"></i>{{ __('admin.add_new_mentor') }}
                  </a>
                </div>
                @endif
              </div>
              
              <!-- PHP Logic for Filters -->
              @php
                $scopeOptions = [
                  ['key' => 'public', 'label' => __('admin.Public'), 'hint' => __('admin.public_scope_hint')],
                ];

                $specializations = [];
                foreach ($mentors as $mentor) {
                    $spec = $mentor->bio->{'specialization_' . $l} ?? null;
                    if ($spec && !in_array($spec, $specializations)) $specializations[] = $spec;
                }
                sort($specializations);
              @endphp
              <!-- Filters Section -->
             
              <div class="w-100">
                <div class="row g-3">
                  <div class="col-md-2">
                    @php
                      $organization_q = App\Organization::where('is_draft', 0);
                      //$organization = auth()->user()->hasRole('admin') ? $organization_q->get() : $organization_q->where('id', auth()->user()->organization_id)->get();
                      if ($_user->hasRole('admin')) {
                          $organization = $organization_q->get();
                      } elseif ($_user->hasRole('subadmin')) {
                          $organization = $organization_q
                              ->whereIn('id', $_user->organizations_ids)
                              ->get();
                      } elseif ($_user->hasRole('OrganizationManager')) {
                          $organization = $organization_q
                              ->where('id', $_user->organization_id)
                              ->get();
                      }else {
                          $organization = collect(); 
                      }
                      if($id > 0) {
                          $cycle = App\Cycle::find($id);
                          $organization = $cycle->organization()->where('is_draft', 0)->get();
                      }
                    @endphp
                    <select id="scopeFilter" class="form-select">
                      <option value="">{{ __('admin.Scope') }} ({{ __('admin.All') }})</option>
                      @foreach ($scopeOptions as $option)
                        <option value="{{ $option['key'] }}">{{ $option['label'] }} ({{ $option['hint'] }})</option>
                      @endforeach
                      @foreach ($organization as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <select id="specializationFilter" class="form-select">
                      <option value="">{{ __('cycles.Expertise') }} ({{ __('admin.All') }})</option>
                        @php
                          $expertises = App\Expertise::where('parent_id', null)->get();
                        @endphp
                        @foreach ($expertises as $expertise_group)
                          <optgroup label="{{ $expertise_group->title }}">
                            @foreach ($expertise_group->children as $expertise)
                              <option value="{{ $expertise->id }}"> {{ $expertise->title }} </option>
                            @endforeach
                          </optgroup>
                        @endforeach
                    </select>
                  </div>
                  <div class="col-md-2">
                    <select id="ratingFilter" class="form-select">
                      <option value="0">{{ __('admin.Rating') }} ({{ __('admin.All') }})</option>
                      @for($i=1; $i<=5; $i++)
                        <option value="{{ $i }}">{{ $i }} {{ $i == 1 ? __('admin.star') : __('admin.star') }} {{ $i == 5 ? '' : '& ' .  __('admin.up') }}</option>
                      @endfor
                    </select>
                  </div>
                  <div class="col-md-3">
                      <input type="search" id="clientSearchInput" class="form-control" placeholder="{{ __('admin.Search') }}...">
                  </div>
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
                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div class="d-flex align-items-center gap-8 mb-6 mb-md-0">
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
                  @php
                    // Get mentor's expertise IDs from the mentor_expertise relationship
                    $mentorExpertiseIds = $mentor->mentorExpertises->pluck('id')->toArray();
                    
                    // Get organization names for this mentor
                    $mentorOrgs = [];
                    if ($mentor->scope === 'organization' && !empty($mentor->scope_org_ids)) {
                        $orgIds = json_decode($mentor->scope_org_ids, true) ?? [];
                        if (!empty($orgIds)) {
                            $organizations = App\Organization::whereIn('id', $orgIds)
                                ->where('is_draft', 0)
                                ->get();
                            
                            foreach ($organizations as $org) {
                                // Try localized name first, fallback to non-localized
                                $mentorOrgs[] = $org->{'name_' . $l} ?? $org->name ?? '';
                            }
                        }
                    }
                  @endphp
                  <div class="col-lg-4 col-md-6 mentor-item-col"
                        data-name="{{ strtolower($mentor->name) }} {{ strtolower($mentor->title) }}"
                        data-specialization="{{ $mentor->bio->{'specialization_' . $l} ?? '' }}"
                        data-expertise-ids="{{ json_encode($mentorExpertiseIds) }}"
                        data-scope="{{ strtolower($mentor->scope) }}"
                        data-scope-org-ids="{{ $mentor->scope_org_ids }}"
                        data-rating="{{ number_format($mentor->mrating(), 1) }}"
                    >
                        
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
                                    <a 
                                        @if ($_user->ableTo('mentors.profile_view', $permissions))
                                            href="/dashboard/show/UserProfile/{{ $mentor->id }}" 
                                        @else
                                            href="javascript:void(0)"
                                        @endif
                                        class="d-flex align-items-center gap-3"
                                    >
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
                                    </a>

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
                                <div class="d-flex justify-content-between align-items-center mt-auto flex-wrap gap-2">
                                    <!-- Left side: Specialization badge -->
                                    @if (isset($mentor->bio->{'specialization_' . $l}))
                                      <span class="badge bg-label-secondary">{{ $mentor->bio->{'specialization_' . $l} ?? '' }}</span>
                                    @endif

                                    <!-- Right side: Rating badge and Organization/Public badges -->
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-light-subtle border d-flex align-items-center gap-2">
                                            <i class="ti ti-star-filled ti-16px text-warning"></i>
                                            <span class="text-dark fw-semibold">{{ number_format($mentor->mrating(), 1) }}</span>
                                        </span>
                                        
                                        @if (!empty($mentorOrgs))
                                            @php
                                                $visibleOrgs = array_slice($mentorOrgs, 0, 2);
                                                $remainingCount = count($mentorOrgs) - 2;
                                            @endphp
                                            
                                            @foreach ($visibleOrgs as $orgName)
                                                <span class="badge bg-label-info badge-sm">
                                                    <i class="ti ti-building ti-xs me-1"></i>{{ $orgName }}
                                                </span>
                                            @endforeach
                                            
                                            @if ($remainingCount > 0)
                                                <span class="badge bg-label-info badge-sm cursor-pointer" 
                                                      data-bs-toggle="modal" 
                                                      data-bs-target="#orgsModal{{ $mentor->id }}">
                                                    <i class="ti ti-dots ti-xs me-1"></i>+{{ $remainingCount }} {{ __('admin.more') }}
                                                </span>
                                            @endif
                                        @elseif ($mentor->scope === 'public')
                                            <span class="badge bg-label-success badge-sm">
                                                <i class="ti ti-world ti-xs me-1"></i>{{ __('admin.Public') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        
                        <!-- Organizations Modal -->
                        @if (!empty($mentorOrgs) && count($mentorOrgs) > 2)
                            <div class="modal fade" id="orgsModal{{ $mentor->id }}" tabindex="-1" aria-labelledby="orgsModalLabel{{ $mentor->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="orgsModalLabel{{ $mentor->id }}">
                                                <i class="ti ti-building me-2"></i>{{ __('admin.Organizations') }} - {{ $mentor->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush">
                                                @foreach ($mentorOrgs as $orgName)
                                                    <li class="list-group-item d-flex align-items-center">
                                                        <i class="ti ti-building text-info me-2"></i>
                                                        <span>{{ $orgName }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
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
        const scopeFilter = document.getElementById('scopeFilter');

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

        // 9. Scope Filter
        scopeFilter.addEventListener('change', function() {
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
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedSpecialization = specializationFilter.value;
            const selectedRating = parseInt(ratingFilter.value);
            const selectedScope = scopeFilter.value; // '', 'public', or org ID string

            visibleItems = allMentorCols.filter(col => {
                // Search: name + title
                const matchesSearch = !searchTerm || col.dataset.name.includes(searchTerm);

                // Specialization - now check by expertise IDs
                let matchesSpecialization = true;
                if (selectedSpecialization) {
                    try {
                        const expertiseIds = JSON.parse(col.dataset.expertiseIds || '[]');
                        // Convert both to strings for comparison
                        matchesSpecialization = expertiseIds.map(String).includes(String(selectedSpecialization));
                    } catch (e) {
                        matchesSpecialization = false;
                    }
                }

                // Rating: use Math.round to match display
                let matchesRating = true;

                if (selectedRating > 0) {
                      const mentorRating = parseFloat(col.dataset.rating) || 0;
                      matchesRating = mentorRating >= selectedRating;
                  }

                // Scope - fixed to use lowercase and proper comparison
                let matchesScope = true;
                if (selectedScope) {
                    const mentorScope = col.dataset.scope; // already lowercase from backend
                    if (selectedScope === 'public') {
                        matchesScope = mentorScope === 'public';
                    } else {
                        // selectedScope is an org ID — check if mentor's org_ids includes it
                        try {
                            const orgIds = JSON.parse(col.dataset.scopeOrgIds || '[]');
                            // Convert both to integers for comparison
                            matchesScope = (mentorScope === 'organization') && 
                                          orgIds.map(Number).includes(Number(selectedScope));
                        } catch (e) {
                            matchesScope = false;
                        }
                    }
                }

                return matchesSearch && matchesSpecialization && matchesRating && matchesScope;
            });

            currentPage = 1;
            noResultsDiv.style.display = visibleItems.length === 0 ? 'block' : 'none';
            renderGrid();
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
