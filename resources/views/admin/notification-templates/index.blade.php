@extends('layouts.admin')

@section('title', __('notification_templates.notification_templates'))

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
            {{ __('notification_templates.notification_templates') }}
          </h5>
          <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i> {{ __('notification_templates.export') }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
              </ul>
            </div>
            <a href="{{ route('notification-templates.create') }}" class="btn btn-primary">
              <i class="ti ti-plus me-1"></i> {{ __('notification_templates.create_template') }}
            </a>
          </div>
        </div>
      </div>
      
      <!-- Datatable filter configuration fields -->
      <div class="row" id="datatable-filters">
        <div class="col-12 col-md-4 mb-2 mb-md-0">
          <select class="form-select form-select-sm dt-filter" id="filter-channel" data-column="2">
            <option value="" selected>{{ __('notification_templates.all_channels') }}</option>
            <option value="Email">Email</option>
            <option value="Whatsapp">WhatsApp</option>
            <option value="Pusher">Push</option>
          </select>
        </div>
        <div class="col-12 col-md-4 mb-2 mb-md-0">
          <select class="form-select form-select-sm dt-filter" id="filter-status" data-column="3">
            <option value="" selected>{{ __('notification_templates.all_status') }}</option>
            <option value="Active">{{ __('notification_templates.active') }}</option>
            <option value="Inactive">{{ __('notification_templates.inactive') }}</option>
          </select>
        </div>
      </div>

      <!-- Templates Table -->
      <div class="card-datatable table-responsive">
        <table id="templates-list-datatable" class="table datatables-ajax">
          <thead>
            <tr class="table-light">
              <th>{{ __('notification_templates.name') }}</th>
              <th>{{ __('notification_templates.title') }}</th>
              <th>{{ __('notification_templates.channel') }}</th>
              <th>{{ __('notification_templates.status') }}</th>
              <th>{{ __('notification_templates.version') }}</th>
              <th>{{ __('notification_templates.created_by') }}</th>
              <th>{{ __('notification_templates.created_at') }}</th>
              <th>{{ __('notification_templates.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($templates as $template)
            <tr id="row_{{ $template->id }}">
              <td>
                <strong>{{ $template->name }}</strong>
                @if($template->parent_template_id)
                  <small class="text-muted d-block">
                    <i class="ti ti-git-branch"></i> {{ __('notification_templates.cloned') }}
                  </small>
                @endif
              </td>
              <td>{{ $template->title }}</td>
              <td data-search="{{ ucfirst($template->channel) }}">
                <span class="badge bg-label-{{ $template->channel == 'email' ? 'danger' : ($template->channel == 'whatsapp' ? 'success' : 'info') }}">
                  <i class="ti ti-{{ $template->channel == 'email' ? 'mail' : ($template->channel == 'whatsapp' ? 'brand-whatsapp' : 'bell') }} ti-16px me-1"></i>
                  {{ ucfirst($template->channel) }}
                </span>
              </td>
              <td data-search="{{ $template->is_active ? 'Active' : 'Inactive' }}">
                <span class="badge bg-label-{{ $template->is_active ? 'success' : 'secondary' }}">
                  <i class="ti ti-circle-dot ti-16px me-1"></i>
                  {{ $template->is_active ? __('notification_templates.active') : __('notification_templates.inactive') }}
                </span>
              </td>
              <td>v{{ $template->version }}</td>
              <td>{{ $template->creator->first_name_en ?? __('notification_templates.unknown') }}</td>
              <td><small class="text-nowrap">{{ $template->created_at->format('M d, Y') }}</small></td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="ti ti-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('notification-templates.show', $template) }}">
                      <i class="ti ti-eye me-1"></i> {{ __('notification_templates.view') }}
                    </a>
                    <a class="dropdown-item" href="{{ route('notification-templates.edit', $template) }}">
                      <i class="ti ti-edit me-1"></i> {{ __('notification_templates.edit') }}
                    </a>
                    <button type="button" class="dropdown-item" onclick="previewTemplate({{ $template->id }})">
                      <i class="ti ti-search me-1"></i> {{ __('notification_templates.preview') }}
                    </button>
                    <button type="button" class="dropdown-item" onclick="cloneTemplate({{ $template->id }})">
                      <i class="ti ti-copy me-1"></i> {{ __('notification_templates.clone') }}
                    </button>
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item text-danger" id="deleteAction" data-id="{{ $template->id }}" data-url="{{ route('notification-templates.destroy', $template) }}">
                      <i class="ti ti-trash me-1"></i> {{ __('notification_templates.delete') }}
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

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('notification_templates.template_preview') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="preview-content"></div>
      </div>
    </div>
  </div>
</div>

<!-- Clone Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('notification_templates.clone_template') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="clone-form">
        <div class="modal-body">
          <div class="form-group mb-3">
            <label for="clone-name" class="form-label">{{ __('notification_templates.template_name') }}</label>
            <input type="text" class="form-control" id="clone-name" name="name" required>
          </div>
          <div class="form-group mb-3">
            <label for="clone-title" class="form-label">{{ __('notification_templates.template_title_optional') }}</label>
            <input type="text" class="form-control" id="clone-title" name="title">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('notification_templates.cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('notification_templates.clone_template') }}</button>
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
    var templatesTable = $('#templates-list-datatable').DataTable({
      responsive: true,
      pageLength: 10,
      order: [[6, "desc"]],
      language: isRtl ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      initComplete: function() {
        $('.filters').append( $('#datatable-filters') );
      },
      columnDefs: [
        {
          targets: [7], // Actions column
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
            columns: [0, 1, 2, 3, 4, 5, 6]
          }
        },
        {
          extend: 'excel',
          text: 'Excel',
          className: 'd-none',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6]
          }
        }
      ]
    });

    // Custom filtering for DataTable
    $('.dt-filter').on('change', function() {
      var columnIdx = $(this).data('column');
      var value = $(this).val();
      templatesTable.column(columnIdx).search(value).draw();
    });

    // Handle export buttons
    $('#exportCsv').on('click', function() {
      templatesTable.button('.buttons-csv').trigger();
    });

    $('#exportExcel').on('click', function() {
      templatesTable.button('.buttons-excel').trigger();
    });

    // Handle delete action
    $(document).on("click", "#deleteAction", function (e) {
      e.preventDefault();
      var url = $(this).data('url');
      var id = $(this).data('id');
      var token = '{{ csrf_token() }}';
      
      Swal.fire({
        title: "{{ __('notification_templates.are_you_sure') }}",
        text: "{{ __('notification_templates.delete_warning') }}",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "{{ __('notification_templates.yes_delete') }}",
        cancelButtonText: "{{ __('notification_templates.no_cancel') }}",
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-label-secondary',
        },
        buttonsStyling: false,
      })
      .then((result) => {
        if (result.value) {
          $.post(url, {'_method': 'DELETE' , '_token': token},
            function(response) {
              // Remove the row from DataTable
              templatesTable.row('#row_' + id).remove().draw();

              releaseSuccessAlert({
                title: "{{ __('notification_templates.deleted') }}",
                message: "{{ __('notification_templates.delete_success') }}",
              });
            }
          ).fail(function(error) {
            releaseFailureAlert({
              title: "{{ __('notification_templates.error') }}",
              message: error?.message || "{{ __('notification_templates.unexpected_error') }}",
            });
          });
        }
      });
    });
});

function previewTemplate(templateId) {
    $.get(`/admin/notification-templates/${templateId}/preview`)
        .done(function(response) {
            $('#preview-content').html(`
                <div class="card">
                    <div class="card-header">
                        <h6>Rendered Content</h6>
                    </div>
                    <div class="card-body">
                        <div class="rendered-content" style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                            ${response.rendered_content.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Sample Data Used:</h6>
                    <pre class="bg-light p-2">${JSON.stringify(response.sample_data, null, 2)}</pre>
                </div>
            `);
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        })
        .fail(function() {
            alert('{{ __('notification_templates.preview_failed') }}');
        });
}

function cloneTemplate(templateId) {
    $('#clone-form')[0].reset();
    new bootstrap.Modal(document.getElementById('cloneModal')).show();
    
    $('#clone-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: $('#clone-name').val(),
            title: $('#clone-title').val()
        };
        
        $.post(`/admin/notification-templates/${templateId}/clone`, formData)
            .done(function(response) {
                bootstrap.Modal.getInstance(document.getElementById('cloneModal')).hide();
                window.location.href = `/admin/notification-templates/${response.id}/edit`;
            })
            .fail(function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errorMsg = '';
                    Object.values(xhr.responseJSON.errors).forEach(errors => {
                        errors.forEach(error => errorMsg += error + '\n');
                    });
                    alert(errorMsg);
                } else {
                    alert('{{ __('notification_templates.clone_failed') }}');
                }
            });
    });
}

// Delete function is now handled by the DataTable event handler above
</script>
@endsection
