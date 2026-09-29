@php
  $org        = $client->service_provider ?? null;
  $spClients  = $org ? $org->clients : collect();
  $showClients = $org ? $org->show_clients : true;
  $providerId = $org->id ?? null;
@endphp

<div class="row g-6" id="step-5-clients">

  {{-- Visibility Toggle --}}
  <div class="col-12">
    <div class="card shadow-none border">
      <div class="p-3">
        <h5 class="card-title">{{ __('admin.Visibility') }}</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="clients_visibility"
                 name="clients_visibility" {{ $showClients ? 'checked' : '' }}>
          <label class="form-check-label" for="clients_visibility">{{ __('admin.public') }}</label>
        </div>
        <small class="text-muted d-block mt-2" id="clients_visibility_desc">
          {{ $showClients
              ? __('admin.visibility_public_desc', [], null) ?? 'Clients are currently visible to everyone.'
              : __('admin.visibility_private_desc', [], null) ?? 'Clients are private and only visible to you.' }}
        </small>
      </div>
    </div>
  </div>

  {{-- Info Note --}}
  <div class="col-12">
    <div class="alert alert-info alert-dismissible" role="alert">
      <i class="ti ti-info-circle me-2"></i>
      <strong>{{ __('admin.note') }}:</strong> {{ __('admin.one_client_note') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>

  {{-- Add Button --}}
  <div class="col-12 d-flex justify-content-end">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
            data-bs-target="#clientModal" data-mode="add">
      <i class="ti ti-plus me-2"></i> {{ __('admin.add_new_client') }}
    </button>
  </div>

  {{-- Clients List --}}
  <div class="col-12" id="clients-list-wrapper">
    @if ($spClients->isEmpty())
      <div id="no-clients-placeholder" class="text-center py-5 border-2 border-dashed border-light rounded-3">
        <i class="ti ti-circle-off display-3 text-secondary opacity-25"></i>
        <p class="text-muted mt-3">{{ __('admin.no_clients_added_yet') }}</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#clientModal" data-mode="add">
          <i class="ti ti-plus me-2"></i> {{ __('admin.add_new_client') }}
        </button>
      </div>
    @else
      <div class="row g-3" id="clients-list">
        @foreach ($spClients as $spClient)
          <div class="col-12 col-sm-6 col-xl-4" id="client-{{ $spClient->id }}">
            <div class="border rounded-3 p-3 d-flex align-items-center justify-content-between gap-2 h-100">

              <div class="d-flex align-items-center gap-3 min-w-0">
                {{-- Logo --}}
                <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center overflow-hidden w-px-40 h-px-40">
                  @if ($spClient->logo_url)
                    @php
                      $clientLogo = $spClient->logo_url
                        ? (str_starts_with($spClient->logo_url, 'https://') ? $spClient->logo_url : '/191014/' . $spClient->logo_url)
                        : '/icons/account.svg';
                    @endphp
                    <img src="{{ $clientLogo }}"
                         class="w-100 h-100 object-fit-contain"
                         alt="{{ $spClient->name }}">
                  @else
                    <span class="text-secondary small">{{ __('admin.Logo') }}</span>
                  @endif
                </div>

                {{-- Info --}}
                <div class="min-w-0">
                  <h6 class="fw-semibold text-truncate mb-1">
                    {{ Str::limit($spClient->name, 25) }}
                  </h6>
                  <div class="text-secondary small text-truncate">
                    @if ($spClient->external_link)
                      <a href="{{ $spClient->external_link }}" target="_blank"
                         class="text-secondary text-decoration-none text-truncate d-block">
                        {{ Str::limit($spClient->external_link, 20) }}
                        <i class="ti ti-external-link ms-2"></i>
                      </a>
                    @else
                      <span class="fst-italic">{{ __('admin.no_link_provided') }}</span>
                    @endif
                  </div>
                </div>
              </div>

              {{-- Actions --}}
              <div class="d-flex flex-shrink-0 gap-1 text-secondary">
                <button type="button"
                        class="btn btn-icon btn-sm btn-text-secondary waves-effect waves-light rounded-pill"
                        data-bs-toggle="modal"
                        data-bs-target="#clientModal"
                        data-mode="edit"
                        data-id="{{ $spClient->id }}"
                        data-name="{{ $spClient->name }}"
                        data-url="{{ $spClient->external_link }}"
                        data-logo="{{ $spClient->logo_url ?? '' }}">
                  <i class="ti ti-edit"></i>
                </button>
                <button type="button"
                        class="btn btn-icon btn-sm btn-text-secondary waves-effect waves-light rounded-pill"
                        onclick="confirmDeleteClient({{ $spClient->id }}, '{{ addslashes($spClient->name) }}')">
                  <i class="ti ti-trash"></i>
                </button>
              </div>

            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

</div>

{{-- Add / Edit Client Modal --}}
<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="clientModalTitle">{{ __('admin.add_client') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="client-id">

        <div class="form-group mb-4">
          <label class="form-label">{{ __('admin.client_name') }} *</label>
          <input 
            type="text" 
            class="form-control" 
            id="client-name"
            placeholder="{{ __('admin.client_name') }}"
            maxlength="100"
          />
          <div class="invalid-feedback">{{ __('admin.Required_Field') }}</div>
        </div>

        <div class="form-group mb-4">
          <label class="form-label">{{ __('admin.external_link') }}</label>
          <input 
            type="url" 
            class="form-control" 
            id="client-url"
            placeholder="https://example.com"
            maxlength="500"
          />
          <div class="invalid-feedback">{{ __('admin.Please enter a valid URL') }}</div>
        </div>

        <div class="form-group mb-4">
          {{-- Current logo preview (edit mode) --}}
          <div id="client-logo-preview-wrap" class="mb-2 d-none">
            <p class="text-muted small mb-1 mb-0">{{ __('admin.current_logo') ?? 'Current logo' }}</p>
            <img id="client-logo-preview" src="" alt="Current Logo"
                 class="rounded-3 object-fit-contain border"
                 style="width:64px;height:64px;">
          </div>
          
          <label class="form-label">{{ __('admin.client_logo') }}</label>
          <input type="file" class="form-control" id="client-logo" accept="image/*">
          <span id="client-logo-filename" class="form-text"></span>
          <div class="invalid-feedback"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ __('admin.Cancel') }}
        </button>
        <button type="button" class="btn btn-primary" id="clientSubmitBtn">
          {{ __('admin.Save') }}
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(() => {
  'use strict';

  const PROVIDER_ID    = {{ $providerId ?? 'null' }};
  const BASE_URL       = PROVIDER_ID ? `/dashboard/services-providers/${PROVIDER_ID}` : null;
  const LOGO_BASE_PATH = '/191014/';
  const CSRF           = () => $('meta[name="csrf-token"]').attr('content');

  const visDescPublic  = @json(__('admin.visibility_public_desc')  ?? 'Clients are currently visible to everyone.');
  const visDescPrivate = @json(__('admin.visibility_private_desc') ?? 'Clients are private and only visible to you.');

  // ── Helpers ───────────────────────────────────────────────────────────────

  function requireProvider() {
    if (PROVIDER_ID) return true;
    releaseFailureAlert({
      title:   @json(__('admin.Error')),
      message: @json(__('admin.save_basics_first')),
    });
    return false;
  }

  function isValidUrl(value) {
    try { new URL(value); return true; } catch { return false; }
  }

  function buildFormData(fields) {
    const fd = new FormData();
    fd.append('_token', CSRF());
    Object.entries(fields).forEach(([k, v]) => v !== undefined && v !== null && fd.append(k, v));
    return fd;
  }

  /**
   * Resolve a logo_url from the API response into a usable <img> src.
   * - Already absolute (starts with https://) → use as-is.
   * - Relative path → prepend LOGO_BASE_PATH.
   * - Falsy → return null.
   */
  function resolveLogoSrc(logoUrl) {
    if (!logoUrl) return null;
    return logoUrl.startsWith('https://') ? logoUrl : LOGO_BASE_PATH + logoUrl;
  }

  /** Build the inner HTML for a single client card. */
  function buildClientCardHtml(client) {
    const logoSrc   = resolveLogoSrc(client.logo_url);
    const linkHtml  = client.external_link
      ? `<a href="${client.external_link}" target="_blank"
            class="text-secondary text-decoration-none text-truncate d-block">
           ${client.external_link.length > 20 ? client.external_link.slice(0, 20) + '…' : client.external_link}
           <i class="ti ti-external-link ms-2"></i>
         </a>`
      : `<span class="fst-italic">${@json(__('admin.no_link_provided'))}</span>`;

    const logoHtml = logoSrc
      ? `<img src="${logoSrc}" class="w-100 h-100 object-fit-contain" alt="${escapeHtml(client.name)}">`
      : `<span class="text-secondary small">${@json(__('admin.Logo'))}</span>`;

    return `
      <div class="border rounded-3 p-3 d-flex align-items-center justify-content-between gap-2 h-100">
        <div class="d-flex align-items-center gap-3 min-w-0">
          <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center overflow-hidden w-px-40 h-px-40">
            ${logoHtml}
          </div>
          <div class="min-w-0">
            <h6 class="fw-semibold text-truncate mb-1">${escapeHtml(client.name.length > 25 ? client.name.substring(0, 25) + '…' : client.name)}</h6>
            <div class="text-secondary small text-truncate">${linkHtml}</div>
          </div>
        </div>
        <div class="d-flex flex-shrink-0 gap-1 text-secondary">
          <button type="button"
                  class="btn btn-icon btn-sm btn-text-secondary waves-effect waves-light rounded-pill"
                  data-bs-toggle="modal"
                  data-bs-target="#clientModal"
                  data-mode="edit"
                  data-id="${client.id}"
                  data-name="${escapeHtml(client.name)}"
                  data-url="${client.external_link ?? ''}"
                  data-logo="${client.logo_url ?? ''}">
            <i class="ti ti-edit"></i>
          </button>
          <button type="button"
                  class="btn btn-icon btn-sm btn-text-secondary waves-effect waves-light rounded-pill"
                  onclick="confirmDeleteClient(${client.id}, '${escapeJs(client.name)}')">
            <i class="ti ti-trash"></i>
          </button>
        </div>
      </div>`;
  }

  /** Escape for HTML output */
  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  /** Escape for inline JS string (addslashes equivalent) */
  function escapeJs(str) {
    return String(str ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
  }

  // ── DOM helpers ───────────────────────────────────────────────────────────

  /** Ensure the #clients-list grid exists; hide placeholder if present. */
  function ensureClientsList() {
    $('#no-clients-placeholder').hide();
    if (!$('#clients-list').length) {
      $('#clients-list-wrapper').html('<div class="row g-3" id="clients-list"></div>');
    }
  }

  /** Remove the grid and show placeholder when list becomes empty. */
  function showPlaceholderIfEmpty() {
    if ($('#clients-list .col-12[id^="client-"]').length === 0) {
      $('#clients-list-wrapper').html(`
        <div id="no-clients-placeholder" class="text-center py-5 border-2 border-dashed border-light rounded-3">
          <i class="ti ti-circle-off display-3 text-secondary opacity-25"></i>
          <p class="text-muted mt-3">${@json(__('admin.no_clients_added_yet'))}</p>
          <button type="button" class="btn btn-primary"
                  data-bs-toggle="modal" data-bs-target="#clientModal" data-mode="add">
            <i class="ti ti-plus me-2"></i> ${@json(__('admin.add_new_client'))}
          </button>
        </div>`);
    }
  }

  // ── Visibility Toggle ─────────────────────────────────────────────────────

  $('#clients_visibility').on('change', function () {
    const isChecked = $(this).is(':checked');
    $('#clients_visibility_desc').text(isChecked ? visDescPublic : visDescPrivate);

    if (!PROVIDER_ID) return;
    $.ajax({
      url: `${BASE_URL}/clients-visibility`,
      method: 'POST',
      data: { show_clients: isChecked ? 1 : 0, _token: CSRF() },
    });
  });

  // ── Modal open ────────────────────────────────────────────────────────────

  $('#clientModal').on('show.bs.modal', function (e) {
    const $btn   = $(e.relatedTarget);
    const isEdit = $btn.data('mode') === 'edit';

    $('#clientModalTitle').text(isEdit ? @json(__('admin.edit_client')) : @json(__('admin.add_client')));
    $('#client-id').val(isEdit ? $btn.data('id')    : '');
    $('#client-name').val(isEdit ? $btn.data('name') : '');
    $('#client-url').val(isEdit ? ($btn.data('url') || '') : '');
    $('#client-logo').val('');

    // Show current logo preview in edit mode
    const logoUrl = isEdit ? ($btn.data('logo') || '') : '';
    const logoSrc = resolveLogoSrc(logoUrl);

    if (isEdit && logoSrc) {
      $('#client-logo-preview').attr('src', logoSrc);
      $('#client-logo-preview-wrap').removeClass('d-none');
    } else {
      $('#client-logo-preview-wrap').addClass('d-none');
      $('#client-logo-preview').attr('src', '');
    }
  });

  // ── Modal close ───────────────────────────────────────────────────────────

  $('#clientModal').on('hidden.bs.modal', function () {
    $('#client-id, #client-name, #client-url, #client-logo').val('');
    $('#client-logo-preview-wrap').addClass('d-none');
    $('#client-logo-preview').attr('src', '');
    $('.is-invalid').removeClass('is-invalid');
  });

  // ── Validation ────────────────────────────────────────────────────────────

  function validateForm() {
    let valid = true;

    const name = $('#client-name').val().trim();
    $('#client-name').toggleClass('is-invalid', !name);
    if (!name) valid = false;

    const url = $('#client-url').val().trim();
    if (url && !isValidUrl(url)) {
      $('#client-url').addClass('is-invalid');
      valid = false;
    } else {
      $('#client-url').removeClass('is-invalid');
    }

    return valid;
  }

  $('#client-name').on('input', function () {
    if ($(this).val().trim()) $(this).removeClass('is-invalid');
  });

  $('#client-url').on('input', function () {
    const value = $(this).val().trim();
    if (!value || isValidUrl(value)) $(this).removeClass('is-invalid');
  });

  // ── Submit (Add / Edit) ───────────────────────────────────────────────────

  $('#clientSubmitBtn').on('click', function () {
    if (!requireProvider() || !validateForm()) return;

    const id       = $('#client-id').val();
    const isEdit   = !!id;
    const logoFile = $('#client-logo')[0].files[0];

    const formData = buildFormData({
      client_name: $('#client-name').val().trim(),
      client_url:  $('#client-url').val().trim(),
      client_logo: logoFile,   // undefined when no file chosen → skipped by buildFormData
    });

    const $btn = $(this).prop('disabled', true);

    $.ajax({
      url:         isEdit ? `${BASE_URL}/clients/${id}` : `${BASE_URL}/clients`,
      method:      'POST',
      data:        formData,
      processData: false,
      contentType: false,

      success(res) {
        if (!res.success) {
          releaseFailureAlert({
            title:   @json(__('admin.Error')),
            message: res.message || @json(__('admin.unexpected_error')),
          });
          return;
        }

        const client = res.client;

        if (isEdit) {
          // ── Update existing card in-place ──────────────────────────────
          const $card = $(`#client-${id}`);
          if ($card.length) {
            $card.html(buildClientCardHtml(client));
          }
        } else {
          // ── Append new card ────────────────────────────────────────────
          ensureClientsList();
          const $col = $(`<div class="col-12 col-sm-6 col-xl-4" id="client-${client.id}"></div>`);
          $col.html(buildClientCardHtml(client));
          $col.hide();
          $('#clients-list').append($col);
          $col.fadeIn(300);
        }

        $('#clientModal').modal('hide');
        releaseSuccessAlert({
          title:   @json(__('admin.Success')),
          message: isEdit
            ? @json(__('admin.client_updated_successfully'))
            : @json(__('admin.client_added_successfully')),
        });
      },

      error(xhr) {
        releaseFailureAlert({
          title:   @json(__('admin.Error')),
          message: xhr.responseJSON?.message || @json(__('admin.unexpected_error')),
        });
      },

      complete() { $btn.prop('disabled', false); },
    });
  });

  // ── Delete ────────────────────────────────────────────────────────────────

  window.confirmDeleteClient = function (id, name) {
    releaseWarningAlert({
      title:        `${@json(__('admin.delete_client'))}?`,
      html:      `${@json(__('admin.you_are_about_to_delete'))} "<strong>${name}</strong>"`,
      confirmText:  @json(__('admin.DYes')),
      confirmColor: 'danger',
      onConfirm() {
        $.ajax({
          url:     `${BASE_URL}/clients/${id}`,
          method:  'DELETE',
          headers: { 'X-CSRF-TOKEN': CSRF() },
          success(res) {
            if (!res.success) return;
            $(`#client-${id}`).fadeOut(300, function () {
              $(this).remove();
              showPlaceholderIfEmpty();
            });
            releaseSuccessAlert({
              title:   @json(__('admin.Deleted')),
              message: @json(__('admin.client_deleted_successfully')),
            });
          },
          error(xhr) {
            releaseFailureAlert({
              title:   @json(__('admin.Error')),
              message: xhr.responseJSON?.message || @json(__('admin.unexpected_error')),
            });
          },
        });
      },
    });
  };

  // ── Logo file validation ──────────────────────────────────────────────────

  const MAX_LOGO_SIZE = 5 * 1024 * 1024;
  const ALLOWED_TYPES = new Set(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml']);

  $('#client-logo').on('change', function () {
    const file      = this.files[0];
    const $input    = $(this);
    const $feedback = $input.siblings('.invalid-feedback');

    const setError = (msg) => {
      this.value = '';
      $input.addClass('is-invalid');
      $feedback.text(msg).show();
    };

    if (!file) return;
    if (file.size > MAX_LOGO_SIZE)     return setError(@json(__('admin.file_exceeds_5_limit')));
    if (!ALLOWED_TYPES.has(file.type)) return setError(@json(__('admin.image_only_allowed')));

    $input.removeClass('is-invalid');
    $feedback.hide();

    // Live preview of newly selected logo
    const reader = new FileReader();
    reader.onload = (e) => {
      $('#client-logo-preview').attr('src', e.target.result);
      $('#client-logo-preview-wrap').removeClass('d-none');
    };
    reader.readAsDataURL(file);
  });

})();
</script>
@endpush