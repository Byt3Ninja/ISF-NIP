@extends('layouts.admin')

@php
  $tabs = [
    ['title' => __('admin.training_sessions'), 'url' => 'dashboard/training/sessions', 'icon' => 'book'],
    ['title' => __('admin.training_courses'),  'url' => 'dashboard/training/all',      'icon' => 'certificate'],
    ['title' => __('admin.training_results'),  'url' => 'dashboard/training/results',  'icon' => 'report-analytics'],
  ];
@endphp

@section('content')
<div>
  {{-- Alert --}}
  @if (session()->has('message'))
    <x-alert
      type="{{ session()->get('class') ?? 'danger' }}"
      message="{{ __(session('message')) }}"
    />
  @endif

  <x-navigation-pills :tabs="$tabs" />

  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ __('admin.training_courses') }}</h5>
        <div class="d-flex align-items-center gap-3">
          @if ($_user->hasAnyRole(['organizationmanager','mentor','admin','subadmin']) || $_user->ableToAny(['trainings.create','trainings.store']))
            <x-export-dropdown callback="exportCourses" />
            <a href="{{ route('trainings.create') }}" class="btn btn-primary">
              <i class="ti ti-plus me-2"></i>{{ __('admin.Add_New') }}
            </a>
          @endif
        </div>
      </div>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table" id="tblCourses">
        <thead class="table-light">
          <tr>
            <th>{{ __('cycles.Title') }}</th>
            <th>{{ __('cycles.Added By') }}</th>
            <th>{{ __('admin.quizStatus') }}</th>
            <th>{{ __('admin.Scope') }}</th>
            <th>{{ __('cycles.Tags') }}</th>
            <th>{{ __('admin.Categories') }}</th>
            <th>{{ __('admin.Programs') }}</th>
            <th>{{ __('cycles.Created at') }}</th>
            <th>{{ __('cycles.Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($courses as $course)
            @php
              // Parse tags — handles both plain strings and JSON-encoded arrays
              $tagsList = [];
              foreach ($course->tags ?? [] as $tag) {
                $name = $tag->name ?? '';
                if ($name && (str_starts_with($name, '[') || str_starts_with($name, '{'))) {
                  $decoded = json_decode($name, true);
                  if (is_array($decoded)) {
                    foreach ($decoded as $item) {
                      if (is_array($item) && isset($item['value'])) {
                        $tagsList[] = trim($item['value']);
                      }
                    }
                  }
                } elseif ($name) {
                  $tagsList[] = trim($name);
                }
              }
              $tagsList = array_values(array_filter($tagsList));

              $categories     = $course->categories;
              $categoriesText = $categories->pluck('title')->unique()->values()->implode(', ');
            @endphp
            <tr id="course-row-{{ $course->id }}">
              {{-- Title --}}
              <td>
                <a href="{{ route('trainings.course_view', $course->id) }}" class="text-dark">
                  {{ Str::limit($course->title, 50) }}
                </a>
              </td>

              {{-- Added By --}}
              <td>
                <div class="d-flex align-items-center gap-2">
                  @if ($course->user && $course->user->img_url)
                    <img src="{{ asset('191014/' . $course->user->img_url) }}" alt="avatar" class="rounded-circle" width="30" height="30">
                  @else
                  <img src="/icons/account.svg" alt="avatar" class="rounded-circle" width="30" height="30">
                  @endif
                  <span>
                    @if ($course->user)
                      {{ $course->user->{"first_name_{$l}"} }} {{ $course->user->{"last_name_{$l}"} }}
                    @else
                      <span class="text-muted">{{ __('admin.Unknown') }}</span>
                    @endif
                  </span>
                </div>
              </td>

              {{-- Status --}}
              <td data-status="{{ $course->Status == 0 ? 'draft' : 'published' }}">
                @if ($course->Status == 0)
                  <span class="badge bg-label-secondary">{{ __('admin.Draft') }}</span>
                @else
                  <span class="badge bg-label-success">{{ __('admin.Published') }}</span>
                @endif
              </td>

              {{-- Scope --}}
              <td>
                @if ($course->is_public)
                  <span class="badge bg-label-primary">{{ __('admin.Public') }}</span>
                @elseif ($course->organization)
                  <span class="badge bg-label-info">{{ $course->organization->{"name_{$l}"} }}</span>
                @else
                  <span class="badge bg-label-secondary">{{ __('admin.Unassigned') }}</span>
                @endif
              </td>

              {{-- Tags --}}
              <td style="max-width:140px; white-space:normal;">
                <small class="text-body">
                  {{ count($tagsList) ? implode(', ', $tagsList) : '–' }}
                </small>
              </td>

              {{-- Categories --}}
              <td>
                <div class="text-wrap" style="width: 200px">
                  <small class="text-body">{{ $categoriesText ?: '–' }}</small>
                </div>
              </td>

              {{-- Contents count --}}
              <td>
                <div class="text-center">
                  {{ $course->contents_count ?? $course->contents->count() }}
                </div>
              </td>

              {{-- Created At --}}
              <td>
                <div class="small text-nowrap">
                  {{ $course->created_at->format('Y-m-d') }}
                </div>
              </td>

              {{-- Actions --}}
              <td>
                <div class="d-flex align-items-center">
                  <a href="/dashboard/training/viewCourse/{{ $course->id }}"
                     class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                     data-bs-toggle="tooltip" title="{{ __('admin.View') }}">
                    <i class="ti ti-eye ti-md"></i>
                  </a>
                  @if ($_user->hasAnyRole(['organizationmanager','mentor','admin','subadmin']) || $_user->ableToAny(['trainings.edit','trainings.update']))
                    <a href="/dashboard/training/EditTR/{{ $course->id }}"
                       class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                       data-bs-toggle="tooltip" title="{{ __('admin.Edit') }}">
                      <i class="ti ti-pencil ti-md"></i>
                    </a>
                    <button onclick="deleteCourse({{ $course->id }})"
                            class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                            data-bs-toggle="tooltip" title="{{ __('admin.Delete') }}">
                      <i class="ti ti-trash ti-md"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@stop

@section('pagescripts')
<script>
$(function () {
  'use strict';

  const CSRF = $('meta[name="csrf-token"]').attr('content');

  /* ================================================================
   * DataTable
   * ============================================================== */
  const table = $('#tblCourses').DataTable({
    ...getSharedDataTableConfig({ title: 'Courses', descIndex: 7, actionsIndex: 8 }),
    responsive: false,
    dom:
      '<"card-header border-bottom d-flex flex-wrap flex-lg-nowrap align-items-center gap-2 py-3 px-4"' +
        '<"flex-shrink-0"l>' +
        '<"d-flex flex-wrap flex-lg-nowrap align-items-center gap-2 flex-grow-1"' +
          '<"dt-filter dt-filter--status">' +
          '<"dt-filter dt-filter--scope">' +
          '<"dt-filter dt-filter--tag">' +
          '<"dt-filter dt-filter--category">' +
          '<"ms-lg-auto"f>' +
        '>' +
      '>' +
      '<"table-responsive"t>' +
      '<"card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-4"' +
        '<"dt-info"i>' +
        '<"dt-paging"p>' +
      '>',
    initComplete() {
      buildStatusFilter(this.api());
      buildScopeFilter(this.api());
      buildTagFilter(this.api());
      buildCategoryFilter(this.api());
      $('.dataTables_filter .form-control').removeClass('form-control-sm').css('width', '');
      $('.dataTables_length .form-select').removeClass('form-select-sm').css('width', 'auto');

      // Initialize Select2 for filter selects
      setTimeout(function() {
        $('#tblCourses_wrapper .filter-select2, #tblCourses_wrapper .dataTables_length select').each(function () {
          const $this = $(this);
          if (!$this.hasClass('select2-hidden-accessible')) {
            const isLength = $this.closest('.dataTables_length').length > 0;
            const $parent = isLength ? $this.parent() : ($this.closest('.dt-filter').length ? $this.closest('.dt-filter') : $('body'));
            $this.css('visibility', 'hidden');
            $this.select2({
              dropdownParent: $parent,
              width: 'resolve',
              minimumResultsForSearch: Infinity,
              allowClear: false
            });
            $this.next('.select2-container').css({
              'min-width': isLength ? '70px' : '150px',
              'max-width': isLength ? '90px' : '200px'
            });
            setTimeout(function() { $this.css('visibility', 'visible'); }, 0);
          }
        });
      }, 200);
    },
  });

  /* ── Status filter ───────────────────────────────────────────── */
  function buildStatusFilter(api) {
    const $select = buildSelect('status-filter', "{{ $l === 'ar' ? 'كل الحالات' : 'All Statuses' }}", 'dt-filter--status');
    $select.append(`<option value="draft">{{ __('admin.Draft') }}</option>`);
    $select.append(`<option value="published">{{ __('admin.Published') }}</option>`);

    $select.on('change', function () {
      const val = this.value;
      $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tblCourses' || !val) return true;
        const status = $(api.row(dataIndex).node()).find('[data-status]').data('status');
        return status === val;
      });
      table.draw();
      $.fn.dataTable.ext.search.pop();
    });
  }

  /* ── Scope filter ────────────────────────────────────────────── */
  function buildScopeFilter(api) {
    const seen = new Set();
    api.column(3).data().each(function (html) {
      const div = document.createElement('div');
      div.innerHTML = html;
      const text = div.textContent.replace(/\s+/g, ' ').trim();
      if (text) seen.add(text);
    });
    const $select = buildSelect('scope-filter', "{{ $l === 'ar' ? 'كل النطاقات' : 'All Scopes' }}", 'dt-filter--scope');
    [...seen].sort().forEach(v => $select.append(`<option value="${v}">${v}</option>`));
    $select.on('change', function () {
      const val = this.value;
      table.column(3).search(val, false, false).draw();
    });
  }

  /* ── Tag filter ─────────────────────────────────────────────── */
  function buildTagFilter(api) {
    const seen = new Set();
    api.column(4).data().each(function (html) {
      const div = document.createElement('div');
      div.innerHTML = html;
      const text = div.textContent.replace(/\s+/g, ' ').trim();
      if (text && text !== '–') {
        text.split(',').forEach(t => { const v = t.trim(); if (v) seen.add(v); });
      }
    });
    const $select = buildSelect('tag-filter', "{{ $l === 'ar' ? 'كل الوسوم' : 'All Tags' }}", 'dt-filter--tag');
    [...seen].sort().forEach(v => $select.append(`<option value="${v}">${v}</option>`));
    $select.on('change', function () {
      table.column(4).search(this.value ? this.value : '', true, false).draw();
    });
  }

  /* ── Category filter ─────────────────────────────────────────── */
  function buildCategoryFilter(api) {
    const seen = new Set();
    api.column(5).data().each(function (html) {
      const div = document.createElement('div');
      div.innerHTML = html;
      const text = div.textContent.replace(/\s+/g, ' ').trim();
      if (text && text !== '–') {
        text.split(',').forEach(t => { const v = t.trim(); if (v) seen.add(v); });
      }
    });
    const $select = buildSelect('category-filter', "{{ $l === 'ar' ? 'كل الفئات' : 'All Categories' }}", 'dt-filter--category');
    [...seen].sort().forEach(v => $select.append(`<option value="${v}">${v}</option>`));
    $select.on('change', function () {
      table.column(5).search(this.value ? this.value : '', true, false).draw();
    });
  }

  function buildSelect(id, placeholder, containerClass) {
    return $(
      `<select id="${id}" class="form-select filter-select2">` +
      `<option value="">${placeholder}</option></select>`
    ).appendTo(`#tblCourses_wrapper .${containerClass}`);
  }

  /* ── Export ──────────────────────────────────────────────────── */
  window.exportCourses = function (format) {
    try {
      table.button(`.buttons-${format}`).trigger();
    } catch (err) {
      console.error('Export error:', err);
      releaseAlert('danger', { message: 'Failed to export. Please try again.' });
    }
  };

  /* ================================================================
   * Delete Course
   * ============================================================== */
  window.deleteCourse = function (id) {
    releaseAlert('warning', {
      hideCancel: true,
      title:        '{{ __('admin.SureDelete') }}',
      message:      '{{ __('admin.Recover') }}',
      confirmText:  '{{ __('admin.DYes') }}',
      cancelText:   '{{ __('admin.No') }}',
      confirmColor: 'danger',
      onConfirm() {
        $.post(`/dashboard/training/delete/${id}`, { _token: CSRF })
          .done(function (response) {
            if (response.state === 'forbidden') {
              releaseAlert('danger', { message: '{{ __('admin.dont_have_permission') }}' });
              return;
            }

            const rowEl = document.getElementById(`course-row-${id}`);
            const row = table.row(rowEl);

            if (row.node()) {
              row.remove().draw(false);
            } else {
              table.draw(false);
            }

            releaseAlert('success', { message: '{{ __('admin.Deleted') }}!' });
          })
          .fail(function (xhr, status, error) {
            console.error('Delete failed:', error);
            releaseAlert('danger', { message: '{{ __('admin.unexpected_error') }}' });
          });
      },
    });
  };
});
</script>
@stop