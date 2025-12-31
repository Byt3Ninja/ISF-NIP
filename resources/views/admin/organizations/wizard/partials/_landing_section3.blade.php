<!-- Media & Recognition Section -->
<div class="row" id="mediaContent">
  <div class="col-12 mb-6">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{{ __('admin.Media & Recognition') }}</h5>
      <div class="form-check form-switch me-3">
        <input
          type="checkbox"
          class="form-check-input"
          id="media_enabled"
          name="media_enabled"
          value="1"
          {{ old('media_enabled', $landingPage->media_enabled ?? false) ? 'checked' : '' }}
          data-section="media"
        />
        <label class="form-check-label" for="media_enabled">
          {{ __('admin.Enable Section') }}
        </label>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <label class="form-label" for="media_title">{{ __('admin.Section Title') }}</label>
        <input
          type="text"
          class="form-control"
          id="media_title"
          name="media_title"
          value="{{ old('media_title', $landingPage->media_title ?? '') }}"
          placeholder="{{ __('admin.Enter section title') }}"
          maxlength="255"
        >
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="media_subtitle">{{ __('admin.Section Subtitle') }}</label>
        <input
          type="text"
          class="form-control"
          id="media_subtitle"
          name="media_subtitle"
          value="{{ old('media_subtitle', $landingPage->media_subtitle ?? '') }}"
          placeholder="{{ __('admin.Enter section subtitle') }}"
          maxlength="255"
        >
      </div>
      <div class="col-12 mb-3">
        <label class="form-label" for="media_description">{{ __('admin.Section Description') }}</label>
        <textarea
          class="form-control"
          id="media_description"
          name="media_description"
          rows="2"
          placeholder="{{ __('admin.Enter section description') }}"
          maxlength="1000"
        >{{ old('media_description', $landingPage->media_description ?? '') }}</textarea>
      </div>
    </div>

    <!-- Articles -->
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">{{ __('admin.Press Articles') }}</h6>
        <button type="button" class="btn btn-sm btn-outline-primary add-item-btn" data-type="article" data-max="8">
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Article') }}
        </button>
      </div>
      <div class="repeater-container" id="articlesContainer">
        @forelse($articles as $index => $article)
          <div class="repeater-item card shadow-none mb-3 border" data-index="{{ $index }}">
            <div class="card-body py-3">
              <div class="row">
                <div class="col-md-3 mb-2">
                  <input type="text" class="form-control form-control-sm" name="articles[{{ $index }}][publication_name]" value="{{ $article['publication_name'] ?? '' }}" placeholder="{{ __('admin.Publication Name') }}" maxlength="255">
                </div>
                <div class="col-md-2 mb-2">
                  <input type="date" class="form-control form-control-sm" name="articles[{{ $index }}][date]" value="{{ $article['date'] ?? '' }}">
                </div>
                <div class="col-md-3 mb-2">
                  <input type="text" class="form-control form-control-sm" name="articles[{{ $index }}][title]" value="{{ $article['title'] ?? '' }}" placeholder="{{ __('admin.Article Title') }}" maxlength="255">
                </div>
                <div class="col-md-3 mb-2">
                  <input type="url" class="form-control form-control-sm" name="articles[{{ $index }}][link]" value="{{ $article['link'] ?? '' }}" placeholder="{{ __('admin.Article Link') }}" maxlength="500">
                </div>
                <div class="col-md-1 text-end mb-2">
                  <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
                <div class="col-md-8 mb-2">
                  <textarea class="form-control form-control-sm" name="articles[{{ $index }}][summary]" rows="2" placeholder="{{ __('admin.Article Summary') }}" maxlength="1000">{{ $article['summary'] ?? '' }}</textarea>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label small">{{ __('admin.Article Image') }}</label>
                  @if(!empty($article['image']))
                    <div class="mb-2">
                      <img src="{{ $landingPage->getArticleImageUrl($article['image']) }}" class="img-thumbnail" style="max-height: 60px;">
                      <input type="hidden" name="articles[{{ $index }}][existing_image]" value="{{ $article['image'] }}">
                    </div>
                  @endif
                  <input type="file" class="form-control form-control-sm" name="articles[{{ $index }}][image]" accept="image/png,image/jpeg,image/jpg">
                  <small class="text-muted">PNG, JPG ({{ __('admin.Max size') }}: 5MB)</small>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4 empty-state rounded border-dashed" id="articlesEmptyState">
            <i class="ti ti-news fs-1 mb-2 d-block opacity-50"></i>
            <p class="mb-0">{{ __('admin.Maximum 8 articles allowed') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>