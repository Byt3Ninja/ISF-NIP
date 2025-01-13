@php
  $tabs = [
    ['id' => 'program_info', 'label' => 'Program Info', 'active' => true],
    ['id' => 'program_guideline', 'label' => 'Program Guideline', 'active' => false],
    ['id' => 'financial_policy', 'label' => 'Financial Policy', 'active' => false],
    ['id' => 'legal_policy', 'label' => 'Legal Policy', 'active' => false],
  ];
@endphp

<style>
  button span { font-size: 0.60rem; }
  .nav-pills .nav-link:not(.active) { background-color: white; }
  .rounded-pill { padding: 0.5rem 0.75rem !important; }
  @media (min-width: 768px) {
    button span { font-size: 1rem; }
    .rounded-pill { padding: 0.5rem 1.5rem !important; }
  }
  @media (max-width: 767.98px) {
    .modal-dialog { max-width: 100%; margin: 0; }
    .modal-content { min-height: 100vh; border-radius: 0; }
  }
</style>

<div class="modal fade" id="{{$mode}}-program-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="padding: 1rem !important;">
      @include('partials.modal.modal-header')
      <div class="modal-body mt-3" style="padding: 0 !important;">
        <ul
          class="nav nav-pills border-bottom pb-3 mb-3 flex-nowrap flex-md-wrap justify-content-center"
          id="pills-tab"
          role="tablist"
        >
          @foreach ($tabs as $tab)
            <li class="nav-item me-1 me-md-3" role="presentation">
              <button
                class="nav-link rounded-pill shadow-sm {{ $tab['active'] ? 'active' : '' }}"
                id="pills-{{ $tab['id'] }}-tab"
                data-bs-toggle="pill"
                data-bs-target="#pills-{{ $tab['id'] }}"
                type="button"
                role="tab"
                aria-controls="pills-{{ $tab['id'] }}"
                aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
              >
                <span>{{ $tab['label'] }}</span>
              </button>
            </li>
          @endforeach
        </ul>
        <div class="tab-content shadow-none p-0 p-md-4" id="pills-tabContent">
          @foreach ($tabs as $tab)
            <div
              class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}"
              id="pills-{{ $tab['id'] }}"
              role="tabpanel"
              aria-labelledby="pills-{{ $tab['id'] }}-tab"
            >
              @include('admin.programs.partials._edit_' . $tab['id'], ['mode' => $mode])
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
