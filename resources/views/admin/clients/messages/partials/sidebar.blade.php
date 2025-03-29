@php
  $folders = [
    ['target' => 'inbox', 'icon' => 'ti-inbox', 'label' => 'Inbox', 'badge' => ['count' => $receivedMessagesCount, 'class' => 'bg-label-primary']],
    ['target' => 'sent', 'icon' => 'ti-send', 'label' => 'Sent'],
    // ['target' => 'draft', 'icon' => 'ti-edit', 'label' => 'Draft', 'badge' => ['count' => 2, 'class' => 'bg-label-warning']],
    // ['target' => 'draft', 'icon' => 'ti-edit', 'label' => 'Draft', 'badge' => ['count' => 2, 'class' => 'bg-label-warning']],
    // ['target' => 'starred', 'icon' => 'ti-star', 'label' => 'Starred'],
    // ['target' => 'spam', 'icon' => 'ti-alert-octagon', 'label' => 'Spam', 'badge' => ['count' => 4, 'class' => 'bg-label-danger']],
    // ['target' => 'trash', 'icon' => 'ti-trash', 'label' => 'Trash']
  ];

  $labels = [
    ['target' => 'work', 'color' => 'bg-success', 'label' => 'Personal'],
    ['target' => 'company', 'color' => 'bg-primary', 'label' => 'Company'],
    ['target' => 'important', 'color' => 'bg-warning', 'label' => 'Important'],
    ['target' => 'private', 'color' => 'bg-danger', 'label' => 'Private']
  ];
@endphp

<!-- Messages Sidebar -->
<div class="col app-email-sidebar border-end flex-grow-0" id="app-email-sidebar">
  <div class="btn-compost-wrapper d-grid">
    <button class="btn btn-primary btn-compose" data-bs-toggle="modal" data-bs-target="#emailComposeSidebar"
      id="emailComposeSidebarLabel">
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
            <span class="align-middle ms-2">{{ __('admin.'. $folder['label']) }}</span>
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
