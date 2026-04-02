@php
/**
 * STATIC DATA DEFINITIONS
 */
$founders = [
  [
    'id' => 1,
    'name' => 'Alex Rivera',
    'email' => 'alex@startup.io',
    'phone' => '+1 555 0123',
    'linkedin' => 'https://linkedin.com/in/alexrivera',
    'join_date' => '2024-01-10',
    'type' => 'Founder',
    'commitment' => 'Full-time',
  ],
];

$investors = [
    [
      'id' => 1,
      'name' => 'Blue Chip Ventures',
      'type' => 'Venture Capital',
    ],
  ];

$shareholders = [
    [
      'id' => 1,
      'name' => 'Jordan Smith',
      'role' => 'CTO',
      'percentage' => 12.5,
    ],
  ];

$tabs = [
  ['id' => 'founders', 'label' => 'Founders', 'icon' => 'users'],
  ['id' => 'investors', 'label' => 'investors', 'icon' => 'building-bank'],
  ['id' => 'shareholders', 'label' => 'shareholders', 'icon' => 'chart-pie'],
];
@endphp

<div class="container p-0">
  <!-- Tab Navigation -->
  <nav class="card shadow-xs px-4 py-3 mb-4">
    <ul class="nav nav-pills d-flex flex-column flex-md-row gap-2" id="teamTabs" role="tablist">
      @foreach ($tabs as $key => $tab)
        <li class="nav-item me-2" role="presentation">
          <button
            class="nav-link {{ $key == 0 ? 'active' : '' }}"
            id="{{ $tab['id'] }}-tab"
            data-bs-toggle="tab"
            data-bs-target="#{{ $tab['id'] }}-pane"
            type="button"
            role="tab"
            aria-controls="{{ $tab['id'] }}-pane"
            aria-selected="{{ $key == 0 ? 'true' : 'false' }}"
          >
            <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
            {{ __('admin.' . $tab['label']) }}
          </button>
        </li>
      @endforeach
    </ul>
  </nav>

  <!-- Tab Content -->
  <div class="tab-content shadow-xs bg-card" id="teamTabsContent">
    <!-- Founders Pane -->
    <div class="tab-pane fade show active" id="founders-pane" role="tabpanel">
      <div class="row align-items-center mb-8">
        <div class="col-md-4">
          <h5 class="mb-4 mb-md-0">{{ __('admin.Founders') }} (<span id="count-founders">{{count($founders)}}</span>)</h5>
        </div>
        <div class="col-md-5">
          <div class="input-group input-group-merge">
            <span class="input-group-text text-muted"><i class="ti ti-search"></i></span>
            <input type="text" class="form-control search-input" data-type="founder" placeholder="{{ __('admin.Search') }}..." />
          </div>
        </div>
        <div class="col-md-3 text-md-end mt-3 mt-md-0">
          <button class="btn btn-primary w-100 w-md-auto shadow-sm" onclick="openModal('founder')">
            <i class="ti ti-plus me-1"></i> {{ __('admin.Add') }} {{ __('admin.Founder') }}
          </button>
        </div>
      </div>
      <div class="row g-4" id="list-founders"></div>
    </div>

    <!-- Investors Pane -->
    <div class="tab-pane fade" id="investors-pane" role="tabpanel">
      <div class="row align-items-center mb-8">
        <div class="col-md-4">
          <h5 class="mb-4 mb-md-0">{{ __('admin.investors') }} (<span id="count-investors">{{count($investors)}}</span>)</h5>
        </div>
        <div class="col-md-5">
          <div class="input-group input-group-merge">
            <span class="input-group-text text-muted"><i class="ti ti-search"></i></span>
            <input type="text" class="form-control search-input" data-type="investor" placeholder="{{ __('admin.Search') }}..." />
          </div>
        </div>
        <div class="col-md-3 text-md-end mt-3 mt-md-0">
          <button class="btn btn-primary w-100 w-md-auto shadow-sm" onclick="openModal('investor')">
            <i class="ti ti-plus me-1"></i> {{ __('admin.Add') }} {{ __('admin.Investor') }}
          </button>
        </div>
      </div>
      <div class="row g-4" id="list-investors"></div>
    </div>

    <!-- Shareholders Pane -->
    <div class="tab-pane fade" id="shareholders-pane" role="tabpanel">
      <div class="row align-items-center mb-8">
        <div class="col-md-4">
          <h5 class="mb-4 mb-md-0">{{ __('admin.shareholders') }} (<span id="count-shareholders">{{count($shareholders)}}</span>)</h5>
        </div>
        <div class="col-md-5">
          <div class="input-group input-group-merge">
            <span class="input-group-text text-muted"><i class="ti ti-search"></i></span>
            <input type="text" class="form-control search-input" data-type="shareholder" placeholder="{{ __('admin.Search') }}..." />
          </div>
        </div>
        <div class="col-md-3 text-md-end mt-3 mt-md-0">
          <button class="btn btn-primary w-100 w-md-auto shadow-sm" onclick="openModal('shareholder')">
            <i class="ti ti-plus me-1"></i> {{ __('admin.Add') }} {{ __('admin.Shareholder') }}
          </button>
        </div>
      </div>
      <div class="row g-4" id="list-shareholders"></div>
    </div>
  </div>
</div>

<!-- ============================================
     MODALS
     ============================================ -->

<!-- Founder Modal -->
<div class="modal fade" id="modal-founder" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <form id="form-founder" class="modal-content" novalidate>
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title mb-0">{{ __('admin.Founder Details') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" />
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">{{ __('admin.Name') }} <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. john doe" maxlength="100" required />
            <div class="invalid-feedback" id="error-name">{{ __('admin.Required_Field') }}</div>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('admin.Email') }} <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="e.g. john@example.com" maxlength="254" required />
            <div class="invalid-feedback" id="error-email">{{ __('admin.please_enter_valid_email') }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('admin.Phone') }}</label>
            <input type="tel" name="phone" class="form-control" />
            <div class="invalid-feedback" id="error-phone">{{ __('admin.Phone must be a valid number') }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('admin.Type') }} <span class="text-danger">*</span></label>
            <select name="type" class="form-select" required>
              <option value="Founder">{{ __('admin.Founder') }}</option>
              <option value="Co-founder">{{ __('admin.cofounder') }}</option>
            </select>
            <div class="invalid-feedback" id="error-type">{{ __('admin.Required_Field') }}</div>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('admin.Linkedin_URL') }}</label>
            <input type="url" name="linkedin" class="form-control" placeholder="e.g. https://www.linkedin.com/in/username" maxlength="500" />
            <div class="invalid-feedback" id="error-linkedin">{{ __('admin.please_enter_valid_linkedin') }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('admin.Join_Date') }}</label>
            <input type="text" name="join_date" class="form-control" id="join_date" placeholder="YYYY-MM-DD" />
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('admin.commitment') }}</label>
            <select name="commitment" class="form-select">
              <option value="Full-time">{{ __('admin.full_time') }}</option>
              <option value="Part-time">{{ __('admin.part_time') }}</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
      </div>
    </form>
  </div>
</div>

<!-- Investor Modal -->
<div class="modal fade" id="modal-investor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="form-investor" class="modal-content" novalidate>
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title mb-0">{{ __('admin.investor_details') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" />
        <div class="mb-3">
          <label class="form-label">{{ __('admin.nvestor_name') }} <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required />
          <div class="invalid-feedback" id="error-name-investor">{{ __('admin.Required_Field') }}</div>
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('admin.investment_type') }}</label>
          <select name="type" class="form-select">
            <option value="Angel_Investor">{{ __('admin.Angel_Investor') }}</option>
            <option value="Angel_Group">{{ __('admin.Angel_Group') }}</option>
            <option value="Venture_Capital_Firm">{{ __('admin.Venture_Capital_Firm') }}</option>
            <option value="Accelerator">{{ __('admin.Accelerator') }}</option>
            <option value="Incubator">{{ __('admin.Incubator') }}</option>
            <option value="Corporate_Investor">{{ __('admin.Corporate_Investor') }}</option>
            <option value="Family_Office">{{ __('admin.Family_Office') }}</option>
            <option value="Family">{{ __('admin.Family') }}</option>
            <option value="Friends">{{ __('admin.Friends') }}</option>
          </select>
          <div class="invalid-feedback" id="error-type-investor">{{ __('admin.Required_Field') }}</div>
        </div>
      </div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
      </div>
    </form>
  </div>
</div>

<!-- Shareholder Modal -->
<div class="modal fade" id="modal-shareholder" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="form-shareholder" class="modal-content" novalidate>
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title mb-0">{{ __('admin.shareholder_details') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" />
        <div class="mb-3">
          <label class="form-label">{{ __('admin.Name') }} <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required />
          <div class="invalid-feedback" id="error-name-shareholder">{{ __('admin.Required_Field') }}</div>
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('admin.Role') }}</label>
          <input type="text" name="role" class="form-control" placeholder="e.g. CEO" />
          <div class="invalid-feedback" id="error-role">{{ __('admin.Required_Field') }}</div>
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('admin.ownership_percentage') }} (%)</label>
          <input type="number" name="percentage" class="form-control" step="0.1" min="0" max="100" placeholder="e.g. 50" />
          <div class="invalid-feedback" id="error-percentage">{{ __('admin.percentage_must_be_between_1_and_100') }}</div>
        </div>
      </div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  window.TEAM_INFO_CONFIG = {
    companyId: {{ isset($client) && $client->company ? $client->company->id : auth()->user()->id }},
    founders:     @json($founders),
    investors:    @json($investors),
    shareholders: @json($shareholders),
    routes: {
      add:    '{{ route('company.profile_teams_add',    ['id' => isset($client) && $client->company ? $client->company->id : auth()->user()->id]) }}',
      edit:   '{{ route('company.profile_teams_edit',   ['id' => isset($client) && $client->company ? $client->company->id : auth()->user()->id]) }}',
      delete: '{{ route('company.profile_teams_delete', ['id' => isset($client) && $client->company ? $client->company->id : auth()->user()->id]) }}',
    },
  };
</script>
<script src="/vuexy/assets/js/app-team-info.js"></script>
@endpush