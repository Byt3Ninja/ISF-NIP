@extends('layouts.admin')

@section('title', __('notification_triggers.notification_triggers'))

@section('content')
<div class="">
  <div class="">

    <!-- Navigation Tabs -->
    <nav class="nav-align-top mb-6">
      @php
        $tabs = [
          ['title' => __('notification_templates.notification_templates'), 'url' => 'dashboard/admin/notification-templates', 'icon' => 'mail'],
          ['title' => __('notification_triggers.notification_triggers'), 'url' => 'dashboard/admin/notification-triggers', 'icon' => 'mail-check'],
        ]
      @endphp
      <ul class="nav nav-pills">
        @foreach($tabs as $tab)
          <li class="nav-item">
            <a href="/{{ $tab['url'] }}" class="nav-link {{ Request::is($tab['url'] . '*') ? 'active' : '' }}">
              <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
              {{ $tab['title'] }}
            </a>
          </li>
        @endforeach
      </ul>
    </nav>
    <!--/ Navigation Tabs -->
    
    <div class="card">
      <div class="card-header border-bottom">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
          <h5 class="card-title mb-3 mb-md-0">
            {{ __('notification_triggers.notification_triggers') }}
          </h5>
          <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i> {{ __('notification_triggers.export') }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
              </ul>
            </div>
            <a href="{{ route('notification-triggers.create') }}" class="btn btn-primary">
              <i class="ti ti-plus me-1"></i> {{ __('notification_triggers.create_trigger') }}
            </a>
          </div>
        </div>
      </div>
      
      <!-- Datatable filter configuration fields -->
      <div class="row" id="datatable-filters">
        <div class="col-12 col-md-4 mb-2 mb-md-0">
          <select class="form-select form-select-sm dt-filter" id="filter-event" data-column="2">
            <option value="" selected>{{ __('notification_triggers.all_events') }}</option>
            @foreach($availableEvents as $eventKey => $eventLabel)
              <option value="{{ $eventLabel }}">{{ $eventLabel }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-4 mb-2 mb-md-0">
          <select class="form-select form-select-sm dt-filter" id="filter-status" data-column="5">
            <option value="" selected>{{ __('notification_triggers.all_status') }}</option>
            <option value="Active">{{ __('notification_triggers.active') }}</option>
            <option value="Inactive">{{ __('notification_triggers.inactive') }}</option>
          </select>
        </div>
      </div>
      <!-- /Datatable filter configuration fields -->

      <!-- Triggers Table -->
      <div class="card-datatable table-responsive">
        <table id="triggers-list-datatable" class="table datatables-ajax">
          <thead>
            <tr class="table-light">
              <th>{{ __('notification_triggers.name') }}</th>
              <th>{{ __('notification_triggers.display_title') }}</th>
              <th>{{ __('notification_triggers.event') }}</th>
              <th>{{ __('notification_triggers.template') }}</th>
              <th>{{ __('notification_triggers.priority') }}</th>
              <th>{{ __('notification_triggers.status') }}</th>
              <th>{{ __('notification_triggers.created_by') }}</th>
              <th>{{ __('notification_triggers.created_at') }}</th>
              <th>{{ __('notification_triggers.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($triggers as $trigger)
            <tr id="row_{{ $trigger->id }}">
              <td>
                <code class="text-primary">{{ $trigger->name }}</code>
              </td>
              <td>
                <strong>{{ $trigger->title }}</strong>
              </td>
              <td data-search="{{ $availableEvents[$trigger->event] ?? $trigger->event }}">
                <span class="badge bg-label-info">
                  <i class="ti ti-bolt me-1"></i>
                  {{ $availableEvents[$trigger->event] ?? $trigger->event }}
                </span>
              </td>
              <td>
                <a href="{{ route('notification-templates.show', $trigger->template) }}" class="text-decoration-none">
                  {{ $trigger->template->title }}
                </a>
              </td>
              <td>
                <span class="badge bg-label-{{ $trigger->priority > 50 ? 'danger' : ($trigger->priority > 25 ? 'warning' : 'secondary') }}">
                  {{ $trigger->priority }}
                </span>
              </td>
              <td data-search="{{ $trigger->is_active ? 'Active' : 'Inactive' }}">
                <span class="badge bg-label-{{ $trigger->is_active ? 'success' : 'secondary' }}">
                  <i class="ti ti-circle-dot me-1"></i>
                  {{ $trigger->is_active ? __('notification_triggers.active') : __('notification_triggers.inactive') }}
                </span>
              </td>
              <td>
                <small>{{ $trigger->creator->name ?? __('notification_triggers.unknown') }}</small>
              </td>
              <td>
                <small class="text-nowrap">{{ $trigger->created_at->format('M d, Y') }}</small>
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="ti ti-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('notification-triggers.show', $trigger) }}">
                      <i class="ti ti-eye me-1"></i> {{ __('notification_triggers.view') }}
                    </a>
                    <a class="dropdown-item" href="{{ route('notification-triggers.edit', $trigger) }}">
                      <i class="ti ti-edit me-1"></i> {{ __('notification_triggers.edit') }}
                    </a>
                    <button type="button" class="dropdown-item" onclick="toggleActive({{ $trigger->id }})">
                      <i class="ti ti-{{ $trigger->is_active ? 'player-pause' : 'player-play' }} me-1"></i>
                      {{ $trigger->is_active ?  __('notification_triggers.deactivate') : __('notification_triggers.activate') }}
                    </button>
                    <button type="button" class="dropdown-item" onclick="showCloneModal({{ $trigger->id }}, '{{ $trigger->name }}')">
                      <i class="ti ti-copy me-1"></i>{{ __('notification_triggers.clone') }}
                    </button>
                    <button type="button" class="dropdown-item" onclick="testTrigger({{ $trigger->id }})">
                      <i class="ti ti-send me-1"></i> {{ __('notification_triggers.test') }}
                    </button>
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item text-danger" id="deleteAction" data-id="{{ $trigger->id }}" data-url="{{ route('notification-triggers.destroy', $trigger) }}">
                      <i class="ti ti-trash me-1"></i> {{ __('notification_triggers.delete') }}
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      
    </div>
  </div>
</div>

<!-- Clone Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Clone Notification Trigger</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="clone-form" method="POST">
        @csrf
        <div class="modal-body">
          <div class="form-group mb-3">
            <label for="clone-name" class="form-label">{{ __('notification_triggers.trigger_name') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="clone-name" name="name" required>
          </div>
          <div class="form-group mb-3">
            <label for="clone-title" class="form-label">New Trigger Title</label>
            <input type="text" class="form-control" id="clone-title" name="title">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('notification_triggers.cancel') }}</button>
          <button type="submit" class="btn btn-primary">Clone Trigger</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('pagescripts')
<script>
$(document).ready(function() {
    const isRtl = {{ $l == 'ar' ? 'true' : 'false' }};

    // Initialize DataTable
    var triggersTable = $('#triggers-list-datatable').DataTable({
      responsive: true,
      pageLength: 10,
      order: [[7, "desc"]],
      language: isRtl ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      initComplete: function() {
        $('.filters').append( $('#datatable-filters') );
      },
      columnDefs: [
        {
          targets: [8], // Actions column
          orderable: false,
          searchable: false
        }
      ],
      buttons: [
        {
          extend: 'csv',
          text: 'CSV',
          className: 'd-none',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7]
          }
        },
        {
          extend: 'excel',
          text: 'Excel',
          className: 'd-none',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7]
          }
        }
      ]
    });

    // Custom filtering for DataTable
    $('.dt-filter').on('change', function() {
      var columnIdx = $(this).data('column');
      var value = $(this).val();
      triggersTable.column(columnIdx).search(value).draw();
    });

    // Handle export buttons
    $('#exportCsv').on('click', function() {
      triggersTable.button('.buttons-csv').trigger();
    });

    $('#exportExcel').on('click', function() {
      triggersTable.button('.buttons-excel').trigger();
    });

    // Handle delete action
    $(document).on("click", "#deleteAction", function (e) {
      e.preventDefault();
      var url = $(this).data('url');
      var id = $(this).data('id');
      var token = '{{ csrf_token() }}';
      
      releaseAlert('warning', {
        title: "{{ __('notification_triggers.are_you_sure') }}",
        message: "{{ __('notification_triggers.delete_warning') }}",
        confirmText: "{{ __('notification_triggers.yes_delete') }}",
        cancelText: "{{ __('notification_triggers.no_cancel') }}",
        confirmColor: 'danger'
      })
      .then((ok) => {
        if (ok) {
          $.post(url, {'_method': 'DELETE' , '_token': token},
            function(response) {
              triggersTable.row('#row_' + id).remove().draw();
              releaseAlert('success', {
                title: "{{ __('notification_triggers.deleted') }}",
                message: "{{ __('notification_triggers.delete_success') }}",
              });
            }
          ).fail(function(error) {
            releaseAlert('danger', {
              title: "{{ __('notification_triggers.error') }}",
              message: error?.message || "{{ __('notification_triggers.unexpected_error') }}",
            });
          });
        }
      });
    });
});

function toggleActive(triggerId) {
    if (confirm('{{ __('notification_triggers.are_you_sure') }}')) {
        fetch(`/admin/notification-triggers/${triggerId}/toggle-active`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __('notification_triggers.unexpected_error') }}');
        });
    }
}

function showCloneModal(triggerId, triggerName) {
    $('#clone-name').val(triggerName + ' (Copy)');
    $('#clone-title').val('');
    $('#clone-form').attr('action', `/admin/notification-triggers/${triggerId}/clone`);
    new bootstrap.Modal(document.getElementById('cloneModal')).show();
}

function testTrigger(triggerId) {
    if (confirm('Are you sure you want to send a test notification using this trigger?')) {
        fetch(`/admin/notification-triggers/${triggerId}/test`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __('notification_triggers.unexpected_error') }}');
        });
    }
}

function deleteTrigger(triggerId, triggerName) {
    if (confirm(`{{ __('notification_triggers.are_you_sure') }} "${triggerName}"? {{ __('notification_triggers.delete_warning') }}`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/notification-triggers/${triggerId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
