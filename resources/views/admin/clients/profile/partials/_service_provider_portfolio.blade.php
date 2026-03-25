@php
  $org = $client->service_provider ?? null;
  $showPortfolio = $org ? $org->show_portfolio : true;
  $portfolios = $org ? $org->portfolios : collect();
@endphp

<div id="step-4-portfolio">
  <div class="row g-6">
    <div class="col-12">
      <div class="card shadow-none border">
        <div class="p-3">
          <h5 class="card-title">{{ __('admin.Visibility') }}</h5>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="portfolio_visibility" name="portfolio_visibility" {{ $showPortfolio ? 'checked' : '' }}>
            <label class="form-check-label" for="portfolio_visibility">{{ __('admin.public') }}</label>
          </div>
          <small class="text-muted d-block mt-2" id="portfolio_visibility_desc"></small>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="mb-4">
        <label for="portfolio_description" class="form-label">{{ __('admin.services_short_description.label') }} *</label>
        <input type="text" class="form-control" id="portfolio_description" name="portfolio_description"
          value="{{ $org ? $org->portfolio_description : '' }}" maxlength="50"
          placeholder="{{ __('admin.services_short_description.label') }}">
      </div>
    </div>

    <div class="col-md-12">
      <div id="itemsContainer">
        @foreach($portfolios as $index => $item)
          <div class="card mb-4 shadow-none border work-item-card" data-index="{{ $index }}">
            <div class="card-body">
              <input type="hidden" class="item-id" name="portfolio[{{ $index }}][id]" value="{{ $item->id }}">
              <input type="hidden" name="portfolio[{{ $index }}][delete]" value="false" class="delete-flag">

              <div class="d-flex justify-content-between mb-3">
                <h6 class="text-muted mb-0 work-item-title">{{ __('admin.work_item') }} {{ $index + 1 }}</h6>
                <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item">
                  <i class="ti ti-trash"></i>
                </button>
              </div>

              <div class="row">
                <!-- MEDIA -->
                <div class="col-md-4 mb-4">
                  <label class="form-label">{{ __('admin.media_label') }}</label>

                  {{-- ── Upload area: clicking this opens the file picker ── --}}
                  <div class="upload-area border bg-light-subtle rounded p-6 text-center cursor-pointer">
                    @if($item->media_url)
                      @if($item->media_type === 'video')
                        <video src="/191014/{{ $item->media_url }}" class="img-fluid rounded" style="max-height:100px;" preload="metadata"></video>
                      @else
                        <img src="/191014/{{ $item->media_url }}" class="img-fluid rounded" style="max-height:100px;">
                      @endif
                      <div class="small mt-1 text-primary">{{ __('admin.change_file') }}</div>
                    @else
                      <i class="ti ti-cloud-upload mb-2"></i>
                      <div class="fw-medium">{{ __('admin.click_to_upload') }}</div>
                      <div class="small text-muted">PNG, JPG, WEBP, MP4 ≤ 10MB</div>
                    @endif
                  </div>

                  {{-- ── Preview row: OUTSIDE upload-area so clicks don't bubble into it ── --}}
                  <div class="preview-box mt-2 d-flex align-items-center gap-2">
                    <span class="file-name small text-muted text-truncate flex-grow-1">
                      {{ $item->media_url ? basename($item->media_url) : __('admin.no_file_selected') }}
                    </span>
                    {{-- preview-btn is outside upload-area — no event conflict ── --}}
                    <button type="button" class="btn btn-sm btn-outline-secondary preview-btn {{ $item->media_url ? '' : 'd-none' }}">
                      {{ __('admin.Preview') }}
                    </button>
                  </div>

                  <div class="file-error text-danger small d-none"></div>

                  <input type="file" class="d-none media-input"
                    name="portfolio[{{ $index }}][media]"
                    accept=".png,.jpg,.jpeg,.webp,.mp4"
                    data-existing-src="{{ $item->media_url ? '/191014/'.$item->media_url : '' }}"
                    data-existing-type="{{ $item->media_type ?? '' }}">
                </div>

                <!-- TEXT -->
                <div class="col-md-8">
                  <div class="mb-4">
                    <label class="form-label">{{ __('admin.Title') }}</label>
                    <input type="text" class="form-control title-input" name="portfolio[{{ $index }}][title]"
                      value="{{ $item->title }}" placeholder="{{ __('admin.Title') }}" maxlength="50">
                  </div>
                  <div class="mb-4">
                    <label class="form-label">{{ __('admin.Description') }}</label>
                    <textarea class="form-control desc-input" name="portfolio[{{ $index }}][description]"
                      rows="3" placeholder="{{ __('admin.Description') }}" maxlength="100">{{ $item->description }}</textarea>
                  </div>
                </div>
              </div>

            </div>
          </div>
        @endforeach
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <button type="button" class="btn btn-outline-primary" id="addWorkItem">
          <i class="ti ti-plus me-2"></i> {{ __('admin.add_another_work_item') }}
        </button>
      </div>
    </div>
  </div>

  <!-- ═══════════════════ TEMPLATE ═══════════════════ -->
  <template id="workItemTemplate">
    <div class="card mb-4 shadow-none border work-item-card">
      <div class="card-body">
        <input type="hidden" class="item-id" name="portfolio[INDEX][id]" value="">
        <input type="hidden" class="delete-flag" name="portfolio[INDEX][delete]" value="false">

        <div class="d-flex justify-content-between mb-3">
          <h6 class="text-muted mb-0 work-item-title">{{ __('admin.work_item') }} 1</h6>
          <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item">
            <i class="ti ti-trash"></i>
          </button>
        </div>

        <div class="row">
          <div class="col-md-4 mb-4">
            <label class="form-label">{{ __('admin.media_label') }}</label>

            <div class="upload-area border bg-light-subtle rounded p-6 text-center cursor-pointer">
              <i class="ti ti-cloud-upload mb-2"></i>
              <div class="fw-medium">{{ __('admin.click_to_upload') }}</div>
              <div class="small text-muted">PNG, JPG, WEBP, MP4 ≤ 10MB</div>
            </div>

            <div class="preview-box mt-2 d-flex align-items-center gap-2">
              <span class="file-name small text-muted text-truncate flex-grow-1">{{ __('admin.no_file_selected') }}</span>
              <button type="button" class="btn btn-sm btn-outline-secondary preview-btn d-none">{{ __('admin.Preview') }}</button>
            </div>

            <div class="file-error text-danger small d-none"></div>

            <input type="file" class="d-none media-input"
              name="portfolio[INDEX][media]"
              accept=".png,.jpg,.jpeg,.webp,.mp4"
              data-existing-src=""
              data-existing-type="">
          </div>

          <div class="col-md-8">
            <div class="mb-4">
              <label class="form-label">{{ __('admin.Title') }}</label>
              <input type="text" class="form-control title-input" name="portfolio[INDEX][title]"
                placeholder="{{ __('admin.Title') }}" maxlength="50">
            </div>
            <div class="mb-4">
              <label class="form-label">{{ __('admin.Description') }}</label>
              <textarea class="form-control desc-input" name="portfolio[INDEX][description]"
                rows="3" placeholder="{{ __('admin.Description') }}" maxlength="100"></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
</div>

<!-- ═══════════════════ PREVIEW MODAL ═══════════════════ -->
<div class="modal fade" id="mediaPreviewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.media_preview') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center" id="modalPreviewContent"></div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(function () {

  const $container = $('#itemsContainer');
  const $template  = $('#workItemTemplate');
  const MAX_ITEMS  = 20;

  // ── Bootstrap the existing server-rendered cards, or add a blank first card
  if ($container.children().length === 0) {
    addItem();
  } else {
    $container.find('.work-item-card').each(function () {
      bindItem($(this));
    });
  }

  // ── Add new item button
  $('#addWorkItem').on('click', function () {
    if ($container.children(':visible').length < MAX_ITEMS) {
      addItem();
    } else {
      releaseWarningAlert({ message: "{{ __('admin.maximum_work_items_message') }}" });
    }
  });

  // ── Renumber visible cards
  function updateItemOrder() {
    let n = 1;
    $container.find('.work-item-card:visible').each(function () {
      $(this).find('.work-item-title').text('{{ __("admin.work_item") }} ' + n++);
    });
  }

  // ── Clone template and append
  function addItem() {
    const index = $container.children().length;
    let html = $template.html()
      .replace(/INDEX_PLUS_1/g, index + 1)
      .replace(/INDEX/g, index);
    const $item = $(html);
    $container.append($item);
    bindItem($item);
    updateItemOrder();
    if (typeof window.rebuildPortfolioValidation === 'function') {
      window.rebuildPortfolioValidation();
    }
  }

  // ══════════════════════════════════════════════════════════════
  //  bindItem — owns ALL event handling for one work-item card
  // ══════════════════════════════════════════════════════════════
  function bindItem($item) {
    const $fileInput  = $item.find('.media-input');
    const $uploadArea = $item.find('.upload-area');
    const $previewBtn = $item.find('.preview-btn');   // lives in .preview-box, NOT inside .upload-area
    const $fileName   = $item.find('.file-name');
    const $errorEl    = $item.find('.file-error');

    // ── Clicking the upload area always opens the file picker.
    //    The preview button is outside this div so there is no bubbling conflict.
    $uploadArea.on('click', function () {
      $fileInput.trigger('click');
    });

    // ── For server-rendered items that already have media: wire preview button.
    //    Read src/type from data attributes on the hidden file input (set server-side).
    const existingSrc  = $fileInput.data('existing-src')  || '';
    const existingType = $fileInput.data('existing-type') || '';

    if (existingSrc) {
      $previewBtn.removeClass('d-none');
      $previewBtn.off('click').on('click', function () {
        renderModalPreview(existingSrc, existingType === 'video' ? 'video' : 'image');
      });
    }

    // ── Remove / soft-delete
    $item.find('.remove-item').on('click', function () {
      const hasId = !!$item.find('.item-id').val();
      if (hasId) {
        $item.find('.delete-flag').val('true');
        $item.find('input[type="text"], textarea').val('');
        $item.find('.media-input').val('');
        $item.find('.upload-area').html(
          '<i class="ti ti-cloud-upload mb-2"></i>' +
          '<div class="fw-medium">{{ __("admin.click_to_upload") }}</div>' +
          '<div class="small text-muted">PNG, JPG, WEBP, MP4 ≤ 10MB</div>'
        );
        $item.hide();
      } else {
        $item.remove();
      }
      updateItemOrder();
      if (typeof window.rebuildPortfolioValidation === 'function') {
        window.rebuildPortfolioValidation();
      }
    });

    // ── File input change: validate → show thumbnail → wire preview button
    $fileInput.on('change', function () {
      const file = this.files[0];

      // clear errors
      $errorEl.addClass('d-none').empty();

      if (!file) {
        resetPreviewUI();
        return;
      }

      // size check
      if (file.size > 10 * 1024 * 1024) {
        $errorEl.text('{{ __("admin.file_size_too_large_portfolio") ?? "File size must be 10 MB or less." }}').removeClass('d-none');
        this.value = '';
        resetPreviewUI();
        return;
      }

      // type check
      const allowed = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'video/mp4'];
      if (!allowed.includes(file.type)) {
        $errorEl.text('{{ __("admin.unsupported_file_type_portfolio") ?? "Unsupported file type. Upload PNG, JPG, WEBP, or MP4." }}').removeClass('d-none');
        this.value = '';
        resetPreviewUI();
        return;
      }

      // show thumbnail in upload area
      const reader = new FileReader();
      reader.onload = function (e) {
        $uploadArea.empty();
        if (file.type.startsWith('image/')) {
          $('<img>', { src: e.target.result, class: 'img-fluid rounded', style: 'max-height:100px;' })
            .appendTo($uploadArea);
        } else {
          // For video thumbnails we use a <video> with preload=metadata
          $('<video>', { src: e.target.result, class: 'img-fluid rounded', style: 'max-height:100px;', preload: 'metadata' })
            .appendTo($uploadArea);
        }
        $('<div>', { class: 'small mt-1 text-primary', text: '{{ __("admin.change_file") }}' })
          .appendTo($uploadArea);
      };
      reader.readAsDataURL(file);

      // update file name label
      $fileName.text(file.name);

      // wire preview button to the newly selected local file
      $previewBtn.removeClass('d-none');
      $previewBtn.off('click').on('click', function () {
        showLocalPreview(file);
      });
    });

    function resetPreviewUI() {
      $fileName.text('{{ __("admin.no_file_selected") }}');
      // Only hide preview btn if there's no existing server media either
      if (!existingSrc) {
        $previewBtn.addClass('d-none');
        $previewBtn.off('click');
      }
    }
  }

  // ── Preview a locally selected File object
  function showLocalPreview(file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      renderModalPreview(e.target.result, file.type.startsWith('video/') ? 'video' : 'image');
    };
    reader.readAsDataURL(file);
  }

  // ── Render src into the modal and show it
  //    Key fix: for video we must set .src AFTER appending to DOM, then call .load()
  function renderModalPreview(src, type) {
    const $body = $('#modalPreviewContent').empty();

    if (type === 'image') {
      $('<img>', { src: src, class: 'img-fluid rounded' }).appendTo($body);
    } else {
      // Create video element, append first, THEN set src and load()
      const $video = $('<video>', {
        controls: true,
        class: 'w-100 rounded',
        style: 'max-height:70vh;',
        preload: 'auto'
      }).appendTo($body);

      // Setting src via .attr after DOM insertion + explicit .load() fixes
      // blank-video issue in Chrome/Safari where the element isn't ready yet.
      $video.attr('src', src);
      $video[0].load();
    }

    // Destroy any previous modal instance to avoid stale state, then show fresh
    const modalEl = document.getElementById('mediaPreviewModal');
    let bsModal = bootstrap.Modal.getInstance(modalEl);
    if (bsModal) {
      bsModal.dispose();
    }
    bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
  }

  // ── Stop video playback when modal is closed to avoid audio ghost
  $('#mediaPreviewModal').on('hide.bs.modal', function () {
    const $video = $(this).find('video');
    if ($video.length) {
      $video[0].pause();
      $video[0].src = '';
    }
  });

});

// ── Visibility toggle description
$('#portfolio_visibility').on('change', function () {
  $('#portfolio_visibility_desc').text(
    $(this).is(':checked')
      ? '{{ __("admin.visibility_public_desc") ?? "Portfolio is currently visible to everyone." }}'
      : '{{ __("admin.visibility_private_desc") ?? "Portfolio is private and only visible to you." }}'
  );
}).trigger('change');
</script>
@endpush