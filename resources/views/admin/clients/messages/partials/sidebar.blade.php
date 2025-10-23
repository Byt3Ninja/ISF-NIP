@php
  $labels = [
    ['target' => 'work', 'color' => 'bg-success', 'label' => 'Personal'],
    ['target' => 'company', 'color' => 'bg-primary', 'label' => 'Company'],
    ['target' => 'important', 'color' => 'bg-warning', 'label' => 'Important'],
    ['target' => 'private', 'color' => 'bg-danger', 'label' => 'Private']
  ];
@endphp

<!-- Messages Sidebar -->
<div class="col app-email-sidebar border-end flex-grow-0" id="app-email-sidebar">
  <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
    <h6 class="mb-0">{{ __('admin.Messages') }}</h6>
    <button class="btn btn-icon btn-text-secondary rounded-pill d-lg-none" id="sidebar-close">
      <i class="ti ti-x ti-lg"></i>
    </button>
  </div>
  <div class="btn-compost-wrapper d-grid p-3">
    <button 
      class="btn btn-primary btn-compose" 
      data-bs-toggle="modal" 
      data-bs-target="#sendMessageModal"
      id="emailComposeSidebarLabel"
      {{-- onclick="document.getElementById('save-draft-button').classList.remove('d-none');" --}}
    >
      <i class="ti ti-plus me-2"></i>
      {{ __('admin.New Message') }}
    </button>
  </div>
  <!-- Messages Filters -->
  <div class="email-filters pt-4 pb-2">
    <!-- Messages Filters: Folder -->
    <ul class="email-filter-folders list-unstyled">
      @foreach ($folders as $folder)
        <li id="{{ $folder['target'] }}-tab" class="d-flex justify-content-between align-items-center mb-1 {{ $loop->first ? 'active' : '' }}" data-target="{{ $folder['target'] }}">
          <a href="javascript:void(0);" class="d-flex flex-wrap align-items-center">
            <i class="ti {{ $folder['icon'] }}"></i>
            <span class="align-middle ms-2">{{ __('messages.'. $folder['label']) }}</span>
          </a>
          @if (isset($folder['badge']))
            <div class="badge {{ $folder['badge']['class'] }} rounded-pill">{{ $folder['badge']['count'] }}</div>
          @endif
        </li>
      @endforeach
    </ul>
    <!-- Messages Filters: Labels -->
    {{-- <div class="email-filter-labels pt-4">
      <p class="small mx-6 text-muted text-uppercase">Labels</p>
      <ul class="list-unstyled mb-2">
        @foreach ($labels as $label)
          <li data-target="{{ $label['target'] }}">
            <a href="javascript:void(0);">
              <i class="badge badge-dot {{ $label['color'] }}"></i>
              <span class="align-middle ms-2">{{ $label['label'] }}</span>
            </a>
          </li>
        @endforeach
      </ul>
    </div> --}}
    <!--/ Messages Filters -->
  </div>
</div>
<!--/ Messages Sidebar -->
