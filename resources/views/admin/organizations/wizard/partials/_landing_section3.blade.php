<!-- Media & Recognition Section -->
<div class="row" id="mediaContent">
  <div class="col-12 mb-6">
    <div class="card border shadow-none">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
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
      </div>
    </div>
  </div>

  <div class="col-12">
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
  
            <!-- Card Header -->
            <div class="card-header d-flex justify-content-between align-items-center py-2">
              <h6 class="mb-0">{{ __('admin.Article') }} {{ $index + 1 }}</h6>
              <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
                <i class="ti ti-trash"></i>
              </button>
            </div>

            <div class="card-body py-3">
              <div class="row">

                <!-- Publication Name -->
                <div class="col-12 col-md-6 mb-3">
                  <label class="form-label">{{ __('admin.Publication Name') }}</label>
                  <input type="text" 
                        class="form-control"
                        name="articles[{{ $index }}][publication_name]" 
                        value="{{ $article['publication_name'] ?? '' }}" 
                        maxlength="255"
                        placeholder="{{ __('admin.Publication Name') }}">
                </div>

                <!-- Date -->
                <div class="col-12 col-md-6 mb-3">
                  <label class="form-label">{{ __('admin.Publication Date') }}</label>
                  <input type="text" 
                        class="form-control datepicker"
                        name="articles[{{ $index }}][date]" 
                        value="{{ $article['date'] ?? '' }}"
                        placeholder="{{ __('admin.Publication Date') }}">
                </div>

                <!-- Title -->
                <div class="col-12 mb-3">
                  <label class="form-label">{{ __('admin.Article Title') }}</label>
                  <input type="text" 
                        class="form-control"
                        name="articles[{{ $index }}][title]" 
                        value="{{ $article['title'] ?? '' }}" 
                        maxlength="255"
                        placeholder="{{ __('admin.Article Title') }}">
                </div>

                <!-- Link -->
                <div class="col-12 mb-3">
                  <label class="form-label">{{ __('admin.Article Link') }}</label>
                  <input type="url" 
                        class="form-control"
                        name="articles[{{ $index }}][link]" 
                        value="{{ $article['link'] ?? '' }}" 
                        maxlength="500"
                        placeholder="{{ __('admin.Article Link') }}">
                </div>

                <!-- Summary -->
                <div class="col-12 mb-3">
                  <label class="form-label">{{ __('admin.Article Summary') }}</label>
                  <textarea class="form-control"
                            name="articles[{{ $index }}][summary]"
                            rows="2"
                            maxlength="1000"
                            placeholder="{{ __('admin.Article Summary') }}">{{ $article['summary'] ?? '' }}</textarea>
                </div>

                <!-- Image -->
                <div class="col-12">
                  <label class="form-label">{{ __('admin.Article Image') }}</label>

                  @if(!empty($article['image']))
                    <div class="mb-2">
                      <img src="{{ $landingPage->getArticleImageUrl($article['image']) }}" 
                          class="img-thumbnail rounded" style="max-height: 60px;">
                      <input type="hidden" 
                            name="articles[{{ $index }}][existing_image]" 
                            value="{{ $article['image'] }}">
                    </div>
                  @endif

                  <input type="file" 
                        class="form-control article-image-input"
                        name="articles[{{ $index }}][image]" 
                        accept="image/png,image/jpeg,image/jpg,image/webp"> 
                        
                  <small class="text-muted d-block mt-1">
                    PNG, JPG, JPEG, WEBP ({{ __('admin.Max size') }}: 5MB)
                  </small>
                </div>

              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4 empty-state rounded border-light border-dashed" id="articlesEmptyState">
            <i class="ti ti-news fs-1 mb-2 d-block opacity-50"></i>
            <p class="small mb-0">{{ __('admin.no_items_added') }}</p>
            <p class="small mb-0">{{ __('admin.Maximum 8 articles allowed') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>