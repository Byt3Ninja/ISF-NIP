{{--
  Export Dropdown Component
  ─────────────────────────
  Usage:
    <x-export-dropdown callback="exportCourses" />

  Props:
    $callback — JS function name that receives the format string. Required.
    $label    — button label. Defaults to admin.Export translation.

  The component renders unique IDs based on the callback name to avoid
  conflicts when used multiple times on the same page.
--}}

@props([
  'callback' => null,
  'label'    => null,
])

@php $uid = Str::camel($callback); @endphp

<div class="dropdown">
  <button
    class="btn btn-label-secondary dropdown-toggle"
    type="button"
    data-bs-toggle="dropdown"
    aria-haspopup="true"
    aria-expanded="false"
  >
    <i class="ti ti-file-export me-2"></i>{{ $label ?? __('admin.Export') }}
  </button>
  <ul class="dropdown-menu">
    <li>
      <a class="dropdown-item" href="javascript:void(0)" @if ($uid) id="exportCsv-{{ $uid }}" @else id="exportCsv" @endif>
        <i class="ti ti-file-type-csv me-2"></i>CSV
      </a>
    </li>
    <li>
      <a class="dropdown-item" href="javascript:void(0)" @if ($uid) id="exportExcel-{{ $uid }}" @else id="exportExcel" @endif>
        <i class="ti ti-file-type-xls me-2"></i>Excel
      </a>
    </li>
  </ul>
</div>

@if ($callback)
<script>
  document.getElementById('exportCsv-{{ $uid }}').addEventListener('click', function () {
    {{ $callback }}('csv');
  });
  document.getElementById('exportExcel-{{ $uid }}').addEventListener('click', function () {
    {{ $callback }}('excel');
  });
</script>
@endif