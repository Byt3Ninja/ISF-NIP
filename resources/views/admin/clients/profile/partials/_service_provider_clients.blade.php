@php
  $org = $client->service_provider ?? null;
  $spClients = $org ? $org->clients : collect();
  $showClients = $org ? $org->show_clients : true;
  $providerId = $org->id ?? null;
@endphp

<div class="row g-6" id="step-5-clients">
  <div class="col-12">
    <div class="card shadow-none border">
      <div class="p-3">
        <h5 class="card-title">{{ __('admin.Visibility') }}</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="clients_visibility" name="clients_visibility" {{ $showClients ? 'checked' : '' }}>
          <label class="form-check-label" for="clients_visibility">{{ __('admin.public') }}</label>
        </div>
        <small class="text-muted d-block mt-2" id="clients_visibility_desc">
          {{ $showClients ? (__('admin.visibility_public_desc') ?? 'Clients is currently visible to everyone.') : (__('admin.visibility_private_desc') ?? 'Clients are private and only visible to you.') }}
        </small>
      </div>
    </div>
  </div>

  {{-- Informational Note --}}
  <div class="col-12">
    <div class="alert alert-info alert-dismissible" role="alert" id="infoNote">
      <i class="ti ti-info-circle me-2"></i>
      <strong>{{ __('admin.note') }}:</strong> {{ __('admin.one_client_note') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>

  {{-- Add New Client Button --}}
  <div class="col-12 d-flex justify-content-end">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal" data-mode="add">
      <i class="ti ti-plus me-2"></i> {{ __('admin.add_new_client') }}
    </button>
  </div>

  {{-- Clients List --}}
  @if ($spClients->isEmpty())
    <div class="col-12" id="no-clients-placeholder">
      <div class="text-center py-5 border-2 border-dashed border-light rounded-3">
        <i class="ti ti-circle-off display-3 text-secondary opacity-25"></i>
        <p class="text-muted mt-3">{{ __('admin.no_clients_added_yet') }}</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal" data-mode="add">
          <i class="ti ti-plus me-2"></i> {{ __('admin.add_new_client') }}
        </button>
      </div>
    </div>
  @else
    <div class="col-12 mb-6" id="clients-list-container">
      <div class="row g-3" id="clients-list">
        @foreach ($spClients as $spClient)
        <div class="col-md-6" id="client-{{ $spClient->id }}">
          <div class="border rounded-3 p-3 bg-white d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-3">
              {{-- Logo --}}
              <div class="bg-secondary-subtle rounded-3 d-flex align-items-center justify-content-center overflow-hidden"
                style="width:60px;height:60px;">
                @if ($spClient->logo_url)
                <img src="{{ asset($spClient->logo_url) }}" class="img-fluid" alt="{{ $spClient->name }}">
                @else
                <span class="text-secondary small">Logo</span>
                @endif
              </div>

              {{-- Info --}}
              <div class="d-flex flex-column w-px-300">
                <h6 class="fw-semibold line-clamp-1 mb-1">{{ $spClient->name }}</h6>

                <div class="text-secondary small">
                  @if ($spClient->external_link)
                    <a href="{{ $spClient->external_link }}" target="_blank" class="text-secondary line-clamp-1 text-decoration-none">
                      {{ $spClient->external_link }}
                      <i class="ti ti-external-link ms-1"></i>
                    </a>
                  @else
                    <span class="fst-italic">{{ __('admin.no_link_provided') }}</span>
                  @endif
                </div>
              </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-1 text-secondary">
              <button
                type="button"
                class="btn btn-icon btn-sm btn-text-secondary waves-effect waves-light rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#clientModal"
                data-mode="edit"
                data-id="{{ $spClient->id }}"
                data-name="{{ $spClient->name }}"
                data-url="{{ $spClient->external_link }}"
              >
                <i class="ti ti-edit"></i>
              </button>

              <button
                type="button"
                class="btn btn-icon btn-sm btn-text-secondary waves-effect waves-light rounded-pill"
                onclick="confirmDeleteClient({{ $spClient->id }}, '{{ addslashes($spClient->name) }}')"
              >
                <i class="ti ti-trash"></i>
              </button>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  @endif
</div>

{{-- Add / Edit Client Modal --}}
<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="clientForm" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="clientModalTitle">{{ __('admin.add_client') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="client-id">

          <div class="form-group mb-4">
            <label class="form-label">{{ __('admin.client_name') }}</label>
            <input type="text" class="form-control" name="client_name" id="client-name" placeholder="{{ __('admin.client_name') }}">
            <div class="invalid-feedback">{{ __('admin.Required_Field') }}</div>
          </div>

          <div class="form-group mb-4">
            <label class="form-label">{{ __('admin.external_link') }}</label>
            <input type="url" class="form-control" name="client_url" id="client-url" placeholder="https://example.com">
            <div class="invalid-feedback">{{ __('admin.Please enter a valid URL') }}</div>
          </div>

          <div class="form-group mb-4">
            <label class="form-label">{{ __('admin.client_logo') }}</label>
            <input type="file" id="client-logo" class="form-control" name="client_logo">
            <div class="invalid-feedback"></div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{ __('admin.Cancel') }}
          </button>
          <button type="button" class="btn btn-primary" onclick="submitClientForm()">
            {{ __('admin.Save') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  const spClientProviderId = {{ $providerId ?? 'null' }};

  /* Clients Visibility */
  $('#clients_visibility').change(function() {
    $('#clients_visibility_desc').text(
      $(this).is(':checked')
        ? '{{ __("admin.visibility_public_desc") ?? "Clients are currently visible to everyone." }}'
        : '{{ __("admin.visibility_private_desc") ?? "Clients are private and only visible to you." }}'
    );

    if (spClientProviderId) {
      $.ajax({
        url: `/dashboard/services-providers/${spClientProviderId}/clients-visibility`,
        method: 'POST',
        data: { show_clients: $(this).is(':checked') ? 1 : 0 },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      });
    }
  });

  /* Add / Edit Modal */
  $('#clientModal').on('show.bs.modal', function (e) {
    const $btn = $(e.relatedTarget);
    const mode = $btn.data('mode');
    const $modal = $(this);

    if (mode === 'edit') {
      $modal.find('#clientModalTitle').text("{{ __('admin.edit_client') }}");
      $modal.find('#client-id').val($btn.data('id'));
      $modal.find('#client-name').val($btn.data('name'));
      $modal.find('#client-url').val($btn.data('url') || '');
    } else {
      $modal.find('#clientModalTitle').text("{{ __('admin.add_client') }}");
      $modal.find('input').val('');
    }
  });

  /* Reset modal when closed */
  $('#clientModal').on('hidden.bs.modal', function () {
    $(this).find('form')[0].reset();
    $(this).find('.is-invalid').removeClass('is-invalid');
  });

  /* Submit Client Form */
  function submitClientForm() {
    if (!spClientProviderId) {
      releaseFailureAlert({
        title: '{{ __("admin.Error") }}',
        message: '{{ __("admin.save_basics_first") }}'
      });
      return;
    }

    const $form = $('#clientForm');
    const formData = new FormData($form[0]);
    const id = $('#client-id').val();

    // Validation
    let isValid = true;

    const name = $('#client-name').val().trim();
    const url  = $('#client-url').val().trim();

    if (!name) {
      $('#client-name').addClass('is-invalid');
      isValid = false;
    } else {
      $('#client-name').removeClass('is-invalid');
    }

    if (url) {
      try {
        new URL(url);
        $('#client-url').removeClass('is-invalid');
      } catch {
        $('#client-url').addClass('is-invalid');
        isValid = false;
      }
    } else {
      $('#client-url').removeClass('is-invalid');
    }

    if (!isValid) return;

    const ajaxUrl = id
      ? `/dashboard/services-providers/${spClientProviderId}/clients/${id}`
      : `/dashboard/services-providers/${spClientProviderId}/clients`;

    $.ajax({
      url: ajaxUrl,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend() {
        $form.find('button[onclick*="submitClientForm"]').prop('disabled', true);
      },
      success(response) {
        if (!response.success) {
          releaseFailureAlert({
            title: '{{ __("admin.Error") }}',
            message: response.message || "{{ __('admin.unexpected_error') }}"
          });
          return;
        }

        $('#clientModal').modal('hide');
        $form[0].reset();
        $('#client-id').val('');

        releaseSuccessAlert({
          title: '{{ __("admin.Success") }}',
          message: id
            ? '{{ __("admin.client_updated_successfully") }}'
            : '{{ __("admin.client_added_successfully") }}'
        });

        location.reload();
      },
      error(xhr) {
        releaseFailureAlert({
          title: '{{ __("admin.Error") }}',
          message: xhr.responseJSON?.message || "{{ __('admin.unexpected_error') }}"
        });
      },
      complete() {
        $form.find('button[onclick*="submitClientForm"]').prop('disabled', false);
      }
    });
  }

  $('#client-name').on('input', function() {
    if ($(this).val().trim()) {
      $(this).removeClass('is-invalid');
    }
  });

  $('#client-url').on('input', function () {
    const value = $(this).val().trim();
    if (!value) {
      $(this).removeClass('is-invalid');
      return;
    }
    try {
      new URL(value);
      $(this).removeClass('is-invalid');
    } catch {
      // still invalid
    }
  });

  /* Delete Confirmation */
  function confirmDeleteClient(id, name) {
    releaseWarningAlert({
      title: '{{ __("admin.delete_client") }}?',
      message: `{{ __("admin.you_are_about_to_delete") }} "<strong>${name}</strong>"`,
      confirmText: '{{ __("admin.DYes") }}',
      confirmColor: 'danger',
      onConfirm: () => {
        $.ajax({
          url: `/dashboard/services-providers/${spClientProviderId}/clients/${id}`,
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success(response) {
            if (response.success) {
              $('#client-' + id).fadeOut(300, function() { $(this).remove(); });
              releaseSuccessAlert({
                title: '{{ __("admin.Deleted") }}',
                message: '{{ __("admin.client_deleted_successfully") }}'
              });
            }
          },
          error(xhr) {
            releaseFailureAlert({
              title: '{{ __("admin.Error") }}',
              message: xhr.responseJSON?.message || "{{ __('admin.unexpected_error') }}"
            });
          }
        });
      }
    });
  }

  /* Upload Logo validation */
  $('#client-logo').on('change', function() {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/svg+xml'];

    if (this.files[0] && this.files[0].size > maxSize) {
      this.value = '';
      $(this).addClass('is-invalid');
      $(this).siblings('.invalid-feedback').text('{{ __("admin.file_exceeds_5_limit") }}').show();
      return;
    }

    if (this.files[0] && !allowedTypes.includes(this.files[0].type)) {
      this.value = '';
      $(this).addClass('is-invalid');
      $(this).siblings('.invalid-feedback').text('{{ __("admin.image_only_allowed") }}').show();
      return;
    }

    $(this).siblings('.invalid-feedback').hide();
    $(this).removeClass('is-invalid');
  });
</script>
@endpush
