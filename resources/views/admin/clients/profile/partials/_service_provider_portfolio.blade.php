<div id="step-4-portfolio">
  <div class="row g-6">
    <div class="col-12">
      <div class="card shadow-none border">
        <div class="p-3">
          <h5 class="card-title">{{ __('admin.Visibility') }}</h5>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="portfolio_visibility" name="portfolio_visibility" checked>
            <label class="form-check-label" for="portfolio_visibility">{{ __('admin.public') }}</label>
          </div>
          <small class="text-muted d-block mt-2" id="portfolio_visibility_desc">
            {{ __('admin.visibility_public_desc') ?? 'Portfolio is currently visible to everyone.' }}
          </small>
        </div>
      </div>
    </div>
    
    <div class="col-md-12">

      <div id="itemsContainer"></div>

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

        <div class="d-flex justify-content-between mb-3">
          <h6 class="text-muted mb-0 work-item-title">{{ __('admin.work_item') }} 1</h6>
          <button type="button"
            class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item">
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

            <input type="file" class="d-none media-input" name="media"
              accept=".png,.jpg,.jpeg,.webp,.mp4,.mov,.avi,.mkv,.wmv,.flv,.m4v" />
          </div>

          <!-- TEXT -->
          <div class="col-md-8">
            <div class="mb-4">
              <label class="form-label">{{ __('admin.Title') }}</label>
              <input type="text" class="form-control title-input" placeholder="{{ __('admin.Title') }}" maxlength="50">
            </div>

            <div class="mb-4">
              <label class="form-label">{{ __('admin.Description') }}</label>
              <textarea class="form-control desc-input" rows="3" placeholder="{{ __('admin.Description') }}"
                maxlength="100"></textarea>
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
        <h5 class="modal-title">Media Preview</h5>
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

  // Initial call
  addItem();

  $addBtn.on('click', addItem);

  function updateItemOrder() {
    $('.work-item-card').each(function(index) {
      $(this).find('.work-item-title').text(`{{ __('admin.work_item') }} ${index + 1}`);
    });
  }

  function addItem() {
    // Clone content from the template
    const $clone = $($template.prop('content')).clone();
    const $item = $clone.find('.work-item-card');
    
    $container.append($item);
    bindItem($item);
    updateItemOrder();

    // Rebuild validation after adding item
    if (typeof window.rebuildPortfolioValidation === 'function') {
      window.rebuildPortfolioValidation();
    }
  }

  function bindItem($item) {
    const $fileInput = $item.find('.media-input');
    const $fileName = $item.find('.file-name');
    const $previewBtn = $item.find('.preview-btn');

    // Trigger file input on upload area click
    $item.find('.upload-area').on('click', () => $fileInput.click());

    // Remove item logic
    $item.find('.remove-item').on('click', () => {
      if ($container.children().length > 1) {
        $item.remove();
        updateItemOrder();

        // Rebuild validation so the indexes (0, 1, 2) match the remaining DOM
        if (typeof window.rebuildPortfolioValidation === 'function') {
          window.rebuildPortfolioValidation();
        }
      } else {
        releaseWarningAlert({
          message: "{{ __('admin.one_item_required') }}"
        });
      }
    });

    // File input change logic
    $fileInput.on('change', function() {
      const file = this.files[0];

      const allowedTypes = [
        'image/png', 'image/jpg', 'image/jpeg', 'image/webp',
        'video/mp4', 'video/mov', 'video/avi', 'video/mkv',
        'video/wmv', 'video/flv', 'video/m4v'
      ];

      if (!file) {
        resetPreview();
        return;
      }

      if (file.size > 10 * 1024 * 1024) {
        // alert('Max file size is 10MB');
        $(this).val('');
        resetPreview();
        return;
      }

      if (!allowedTypes.includes(file.type)) {
        // alert('Invalid file type. Please select a valid image or video file.');
        $(this).val('');
        resetPreview();
        return;
      }

      $fileName.text(file.name);
      $previewBtn.removeClass('d-none');

      // Use .off().on() to prevent multiple click bindings if change is triggered again
      $previewBtn.off('click').on('click', () => showPreview(file));
    });

    function resetPreview() {
      $fileName.text('No file selected');
      $previewBtn.addClass('d-none');
    }
  }

  function showPreview(file) {
    const $modalContent = $('#modalPreviewContent').empty();
    const reader = new FileReader();

    reader.onload = (e) => {
      if (file.type.startsWith('image')) {
        $('<img>', {
          src: e.target.result,
          class: 'img-fluid rounded'
        }).appendTo($modalContent);
      } else {
        $('<video>', {
          src: e.target.result,
          controls: true,
          class: 'w-100 rounded'
        }).appendTo($modalContent);
      }
      
      // Keep vanilla Bootstrap modal trigger or use jQuery if preferred
      new bootstrap.Modal('#mediaPreviewModal').show();
    };

    reader.readAsDataURL(file);
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