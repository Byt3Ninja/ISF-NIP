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
          <small class="text-muted d-block mt-2" id="portfolio_visibility_desc">
            {{ __('admin.visibility_public_desc') ?? 'Portfolio is currently visible to everyone.' }}
          </small>
        </div>
      </div>
    </div>
    
    <div class="col-12">
      <div class="mb-4">
        <label for="portfolio_description" class="form-label">{{ __('admin.services_short_description.label') }} *</label>
        <input type="text" class="form-control" id="portfolio_description" name="portfolio_description" value="{{ $org ? $org->portfolio_description : '' }}" maxlength="50" placeholder="{{ __('admin.services_short_description.label') ?? 'Briefly describe your work' }}">
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

                    <div class="upload-area border bg-light-subtle rounded p-6 text-center cursor-pointer">
                      @if($item->media_url)
                        @if($item->media_type === 'video')
                          <video src="{{ $item->media_full_url }}" class="img-fluid rounded" style="max-height: 100px;"></video>
                        @else
                          <img src="{{ $item->media_full_url }}" class="img-fluid rounded" style="max-height: 100px;">
                        @endif
                        <div class="small mt-1 text-primary">{{ __('admin.change_file') }}</div>
                      @else
                        <i class="ti ti-cloud-upload mb-2"></i>
                        <div class="fw-medium">{{ __('admin.click_to_upload') }}</div>
                        <div class="small text-muted">PNG, JPG, WEBP, MP4 ≤ 10MB</div>
                      @endif
                    </div>

                    <div class="preview-box mt-2 d-flex align-items-center gap-2">
                      <span class="file-name small text-muted text-truncate flex-grow-1">
                        {{ $item->media_url ? basename($item->media_url) : __('admin.no_file_selected') }}
                      </span>
                      <button type="button" class="btn btn-sm btn-outline-secondary preview-btn {{ $item->media_url ? '' : 'd-none' }}">
                        {{ __('admin.Preview') }}
                      </button>
                    </div>
                    <div class="invalid-feedback d-none file-error text-danger small"></div>

                    <input type="file" class="d-none media-input is-invalid-on-error" name="portfolio[{{ $index }}][media]" 
                      accept=".png,.jpg,.jpeg,.webp,.mp4"
                    />
                  </div>

                  <!-- TEXT -->
                  <div class="col-md-8">
                    <div class="mb-4">
                      <label class="form-label">{{ __('admin.Title') }}</label>
                      <input type="text" class="form-control title-input" name="portfolio[{{ $index }}][title]" value="{{ $item->title }}" placeholder="{{ __('admin.Title') }}" maxlength="50">
                    </div>

                    <div class="mb-4">
                      <label class="form-label">{{ __('admin.Description') }}</label>
                      <textarea class="form-control desc-input" name="portfolio[{{ $index }}][description]" rows="3" placeholder="{{ __('admin.Description') }}" maxlength="100">{{ $item->description }}</textarea>
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

  <!-- ================= TEMPLATE ================= -->
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
          <!-- MEDIA -->
          <div class="col-md-4 mb-4">
            <label class="form-label">{{ __('admin.media_label') }}</label>

            <div class="upload-area border bg-light-subtle rounded p-6 text-center cursor-pointer">
              <i class="ti ti-cloud-upload mb-2"></i>
              <div class="fw-medium">{{ __('admin.click_to_upload') }}</div>
              <div class="small text-muted">PNG, JPG, WEBP, MP4 ≤ 10MB</div>
            </div>

            <div class="preview-box mt-2 d-flex align-items-center gap-2">
              <span class="file-name small text-muted text-truncate flex-grow-1">
                {{ __('admin.no_file_selected') }}
              </span>
              <button type="button" class="btn btn-sm btn-outline-secondary preview-btn d-none">
                {{ __('admin.Preview') }}
              </button>
            </div>
            
            <input type="file" class="form-control d-none media-input is-invalid-on-error" name="portfolio[INDEX][media]" accept=".png,.jpg,.jpeg,.webp,.mp4" />
            <div class="invalid-feedback file-error text-danger small"></div>
          </div>

          <!-- TEXT -->
          <div class="col-md-8">
            <div class="mb-4">
              <label class="form-label">{{ __('admin.Title') }}</label>
              <input type="text" class="form-control title-input" name="portfolio[INDEX][title]" placeholder="{{ __('admin.Title') }}" maxlength="50">
            </div>

            <div class="mb-4">
              <label class="form-label">{{ __('admin.Description') }}</label>
              <textarea class="form-control desc-input" name="portfolio[INDEX][description]" rows="3" placeholder="{{ __('admin.Description') }}" maxlength="100"></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
</div>

<!-- ================= PREVIEW MODAL ================= -->
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
  $(function() {
    const $container = $('#itemsContainer');
    const $addBtn = $('#addWorkItem');
    const $template = $('#workItemTemplate');
    const MAX_ITEMS = 20;

    // Initial item if none exists
    if ($container.children().length === 0) {
      addItem();
    } else {
      // Bind existing items
      $container.find('.work-item-card').each(function() {
        bindItem($(this));
      });

      // Initialize existing upload areas to prevent JS interference with server-rendered media
      $container.find('.upload-area').off('click').on('click', function(e) {
        e.stopPropagation();
        const $item = $(this).closest('.work-item-card');
        const $fileInput = $item.find('.media-input');
        $fileInput.click();
      });
    }

    $addBtn.on('click', function() {
        if ($container.children(':visible').length < MAX_ITEMS) {
            addItem();
        } else {
            // SweetAlert or similar could be used here
            alert('Maximum ' + MAX_ITEMS + ' work items allowed.');
        }
    });

    function updateItemOrder() {
      let visibleIndex = 1;
      $container.find('.work-item-card:visible').each(function() {
        $(this).find('.work-item-title').text(`{{ __('admin.work_item') }} ${visibleIndex++}`);
      });
    }

    function addItem() {
      const index = $container.children().length;
      let html = $template.html();
      
      // Replace placeholders - do specific replacements first before general INDEX replacements
      html = html.replace(/INDEX_PLUS_1/g, index + 1);
      html = html.replace(/INDEX/g, index);
      
      const $item = $(html);
      $container.append($item);
      bindItem($item);
      updateItemOrder();
      
      if (typeof window.rebuildPortfolioValidation === 'function') {
        window.rebuildPortfolioValidation();
      }
    }

    function bindItem($item) {
      const $fileInput = $item.find('.media-input');
      const $fileName = $item.find('.file-name');
      const $previewBtn = $item.find('.preview-btn');
      const $uploadArea = $item.find('.upload-area');

      // Only bind upload click if no existing media (prevents overwriting server media)
      if ($uploadArea.find('img, video').length === 0) {
        $uploadArea.on('click', () => $fileInput.click());
      }

      $item.find('.remove-item').on('click', () => {
        const $idField = $item.find('input[name*="[id]"]');
        if ($idField.val()) {
          // If it has an ID, hide it and mark for deletion
          $item.find('.delete-flag').val('true');
          $item.find('input[type="text"], textarea').val('');
          $item.find('.media-input').val('');
          $item.find('.upload-area').empty();
          $item.hide();
        } else {
          // If it's a new unsaved item, just remove it from DOM
          $item.remove();
        }
        updateItemOrder();

        if (typeof window.rebuildPortfolioValidation === 'function') {
           window.rebuildPortfolioValidation();
        }
      });

      $fileInput.on('change', function() {
        const file = this.files[0];
        if (!file) {
          resetPreview();
          return;
        }

        const $errorEl = $fileInput.siblings('.file-error');
        const $parent = $fileInput.closest('.col-md-4');

        // Clear previous errors
        $errorEl.hide().addClass('d-none').empty();
        $parent.removeClass('is-invalid');

        if (file.size > 10 * 1024 * 1024) {
          console.log('Portfolio file size validation failed');
          $errorEl.html('{{ __("admin.file_size_too_large_portfolio") ?? "File size must be 10MB or less." }}').removeClass('d-none').show();
          $parent.addClass('is-invalid');
          $(this).val('');
          return;
        }
        
        const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/webp', 'video/mp4'];
        if (!allowedTypes.includes(file.type)) {
           console.log('Portfolio file type validation failed');
           $errorEl.html('{{ __("admin.unsupported_file_type_portfolio") ?? "Unsupported file type. Upload PNG, JPG, WEBP, or MP4." }}').removeClass('d-none').show();
           $parent.addClass('is-invalid');
           $(this).val('');
          return;
        }

        $fileName.text(file.name);
        $previewBtn.removeClass('d-none');
        $previewBtn.off('click').on('click', () => showPreview(file));
        
        // Show local preview in the upload area
        const reader = new FileReader();
        reader.onload = (e) => {
           $uploadArea.empty();
           if (file.type.startsWith('image')) {
             $('<img>', { src: e.target.result, class: 'img-fluid rounded', style: 'max-height: 100px;' }).appendTo($uploadArea);
           } else {
             $('<video>', { src: e.target.result, class: 'img-fluid rounded', style: 'max-height: 100px;' }).appendTo($uploadArea);
           }
           $uploadArea.append('<div class="small mt-1 text-primary">{{ __("admin.change_file") }}</div>');
        };
        reader.readAsDataURL(file);
      });

      function resetPreview() {
        $fileName.text('{{ __("admin.no_file_selected") }}');
        $previewBtn.addClass('d-none');
        const $parent = $fileInput.closest('.col-md-4');
        $parent.removeClass('is-invalid');
        const $errorEl = $fileInput.siblings('.file-error');
        if ($errorEl.length) $errorEl.hide().addClass('d-none').empty();
      }
      
      // Handle existing media preview - always bind if media present
      if ($uploadArea.find('img, video').length > 0) {
        $previewBtn.removeClass('d-none');
        $previewBtn.off('click.preview').on('click.preview', function() {
          const $mediaEl = $uploadArea.find('img, video');
          const type = $mediaEl.is('video') ? 'video' : 'image';
          const src = $mediaEl.attr('src');
          renderModalPreview(src, type);
        });
      }
    }

    function showPreview(file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        const type = file.type.startsWith('video') ? 'video' : 'image';
        renderModalPreview(e.target.result, type);
      };
      reader.readAsDataURL(file);
    }

    function renderModalPreview(src, type) {
      const $modalContent = $('#modalPreviewContent').empty();
      if (type === 'image') {
        $('<img>', { src: src, class: 'img-fluid rounded' }).appendTo($modalContent);
      } else {
        $('<video>', { src: src, controls: true, class: 'w-100 rounded' }).appendTo($modalContent);
      }
      new bootstrap.Modal('#mediaPreviewModal').show();
    }
  });

  $('#portfolio_visibility').change(function() {
    $('#portfolio_visibility_desc').text(
      $(this).is(':checked') 
        ? '{{ __("admin.visibility_public_desc") ?? "Portfolio is currently visible to everyone." }}' 
        : '{{ __("admin.visibility_private_desc") ?? "Portfolio is private and only visible to you." }}'
    );
  }).trigger('change');
</script>
@endpush