<form class="form d-flex flex-column h-100" action="{{ route('settings.organization_social_manage') }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="id" value="{{ $organization?->id }}">
  
    <div class="form-content flex-grow-1">
      <!-- Website URL -->
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="website_url">{{ __('admin.Website') }}</label>
        <input id="website_url" type="url" class="form-control" name="website_url" value="{{ old('website_url', $organization?->website_url) }}">
      </div>
  
      <!-- Facebook Page URL -->
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="facebook_url">{{ __('admin.Facebook') }}</label>
        <input id="facebook_url" type="url" class="form-control" name="facebook_url" value="{{ old('facebook_url', $organization?->facebook_page_url) }}">
      </div>
  
      <!-- Twitter Handle -->
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="twitter_handle">{{ __('admin.Twitter')}}</label>
        <input id="twitter_handle" type="text" class="form-control" name="twitter_handle" value="{{ old('twitter_handle', $organization?->twitter_handle) }}">
      </div>
  
      <!-- YouTube Channel URL -->
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="youtube_url">{{ __('admin.Youtube') }}</label>
        <input id="youtube_url" type="url" class="form-control" name="youtube_url" value="{{ old('youtube_url', $organization?->youtube_channel_url	) }}">
      </div>
  
      <!-- Instagram Profile URL -->
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="instagram_url">{{ __('admin.Instagram') }}</label>
        <input id="instagram_url" type="url" class="form-control" name="instagram_url" value="{{ old('instagram_url', $organization?->instagram_profile_url) }}">
      </div>
  
      <!-- LinkedIn Company URL -->
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="linkedin_url">{{ __('admin.LinkedIn') }}</label>
        <input id="linkedin_url" type="url" class="form-control" name="linkedin_url" value="{{ old('linkedin_url', $organization?->linkedin_company_url) }}">
      </div>
  
      <!-- TikTok URL -->
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="tiktok_url">{{ __('admin.Tiktok')}}</label>
        <input id="tiktok_url" type="url" class="form-control" name="tiktok_url" value="{{ old('tiktok_url', $organization?->tiktok_url) }}">
      </div>
    </div>
  
    <div class="form-group d-flex justify-content-end mt-auto">
      <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Submit') }}</button>
    </div>
  </form>
  