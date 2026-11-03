@php
  $tabs = [
    ['id' => 'program_info', 'label' => 'Program Info', 'active' => true],
    ['id' => 'program_guideline', 'label' => 'Program Guideline', 'active' => false],
    ['id' => 'financial_policy', 'label' => 'Financial Policy', 'active' => false],
    ['id' => 'legal_policy', 'label' => 'Legal Policy', 'active' => false],
    ['id' => 'mentorship_request', 'label' => 'Mentorship Request', 'active' => false],
  ];
@endphp

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

<div class="modal fade" id="{{$mode}}-program-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content rounded p-0 p-md-4" id="modal-content">
      @include('partials.modal.modal-header', ['title' => __('cycles.' . $mode . '_program')])
      <form action="{{ route('cycles.modify') }}" method="POST" enctype="multipart/form-data">
        @csrf 
        <input type="hidden" name="tab_id" value="{{ \App\Enums\CycleTabAction::CREATE_CYCLE }}">
        <div class="rounded bg-white border-0 border-md-1 border-secondary py-4 px-2">
          <ul
            class="nav nav-pills flex-nowrap justify-content-center py-1 bg-label-light rounded-pill gap-1 gap-md-2"
            style="margin-bottom: -10px;"
            id="pills-tab-{{ $mode }}"
            role="tablist"
          >
            @foreach ($tabs as $tab)
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link rounded-pill shadow-sm {{ $tab['active'] ? 'active' : '' }}"
                  id="pills-{{ $tab['id'] }}-tab-{{ $mode }}"
                  data-bs-toggle="pill"
                  data-bs-target="#pills-{{ $tab['id'] }}-{{ $mode }}"
                  type="button"
                  role="tab"
                  aria-controls="pills-{{ $tab['id'] }}-{{ $mode }}"
                  aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
                >
                  <span class="fw-semibold">{{ $tab['label'] }}</span>
                </button>
              </li>
            @endforeach
          </ul>
          <hr />
          <div class="tab-content shadow-none p-4" id="pills-tabContent-{{ $mode }}">
            @foreach ($tabs as $tab)
              <div
                class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}"
                id="pills-{{ $tab['id'] }}-{{ $mode }}"
                role="tabpanel"
                aria-labelledby="pills-{{ $tab['id'] }}-tab-{{ $mode }}"
              >
                @include('admin.programs.partials._edit_' . $tab['id'], ['mode' => $mode])
              </div>
            @endforeach
          </div>            
        </div>
        <div class="d-flex align-items-center justify-content-end gap-3 mt-4 p-4 p-md-0">
          <button type="submit" class="btn btn-primary w-px-100">
            {{ __('cycles.Save') }}
            <i class="ti ti-circle-check ms-2"></i>
          </button>
          <button type="button" class="btn btn-outline-secondary w-px-100" data-bs-dismiss="modal">
            {{ __('cycles.Cancel') }}
            <i class="ti ti-circle-x ms-2"></i>
          </button>
        </div> 
      </form>
    </div>
  </div>
</div> 