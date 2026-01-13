<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form method="GET" action="{{ route('mentors.list') }}">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">
            <i class="ti ti-filter me-2"></i>
            {{ __('admin.Filter') }} {{ __('dashboard.Mentors') }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <!-- Program Filter -->
          <div class="form-group mb-4">
            <label for="programFilter" class="form-label">{{ __('cycles.Cycle') }}</label>
            <select name="programFilter" id="programFilter" class="form-select select2" data-placeholder="{{ __('admin.Select Program') }}">
              <option value="">{{ __('admin.All_Programs') }}</option>
              @if(isset($cycles) && count($cycles) > 0)
                @foreach ($cycles as $cycle)
                  <option value="{{ $cycle->id }}" {{ request('programFilter') == $cycle->id ? 'selected' : '' }}>
                    {{ $cycle->title }}
                  </option>
                @endforeach
              @endif
            </select>
          </div>

          @php 
            $scopeOptions = [
              ['key' => 'public', 'label' => __('admin.Public'), 'hint' => __('admin.public_scope_hint')],
              ['key' => 'organization', 'label' => __('admin.Organization'), 'hint' => __('admin.organization_scope_hint')],
            ];
          @endphp

          <!-- Scope Filter -->
          <div class="form-group mb-4">
            <label for="scopeFilter" class="form-label">{{ __('admin.Scope') }}</label>
            <select name="scopeFilter" id="scopeFilter" class="form-select select2" data-placeholder="{{ __('admin.select_scope') }}">
              <option value="all">{{ __('admin.allMentors') }}</option>
              @foreach ($scopeOptions as $option)
                <option value="{{ $option['key'] }}" {{ request('scopeFilter') == $option['key'] ? 'selected' : '' }}>
                  {{ $option['label'] }}  ( {{ $option['hint'] }} )
                </option>
              @endforeach
            </select>
          </div>

          <!-- Rate Filter -->
          <div class="form-group mb-4">
            <label for="rateSearch" class="form-label">{{ __('admin.rating') }}</label>
            <select name="rateSearch" id="rateSearch" class="form-select select2" data-placeholder="{{ __('admin.select_rating') }}">
              <option value="">{{ __('cycles.Any_Rating') }}</option>
              @foreach ([1, 2, 3, 4, 5] as $index => $rating)
                <option value="{{ $rating }}" {{ request('rateSearch') == $rating ? 'selected' : '' }}>
                  {{ $index !== 4 ? '+' : '' }} {{ $rating }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Expertise Filter -->
          <div class="form-group mb-4">
            <label for="expertises" class="form-label">{{ __('admin.Expertises') }}</label>
            <select name="expertises[]" id="expertises" class="form-select select2" multiple="multiple" data-placeholder="{{ __('admin.select_expertises') }}">
              @php
              $expertises = App\Expertise::whereNull('parent_id')->with('children')->get();
              $selectedExpertises = request('expertises', []);
              @endphp
              @foreach ($expertises as $expertise_group)
              <optgroup label="{{ $l == 'ar' ? $expertise_group->title_ar : $expertise_group->title }}">
                @foreach ($expertise_group->children as $expertise)
                <option value="{{ $expertise->id }}" {{ in_array($expertise->id, $selectedExpertises) ? 'selected' : '' }}>
                  {{ $expertise->title }}
                </option>
                @endforeach
              </optgroup>
              @endforeach
            </select>
          </div>

          <!-- LinkedIn Profile Filter -->
          <div class="form-group mb-4">
            <label class="form-label">
              {{ __('cycles.Has_LinkedIn_Profile') }}
            </label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="linkedin" value="" 
                       id="linkedinAll" {{ !request()->has('linkedin') || request('linkedin') === '' || is_null(request('linkedin')) ? 'checked' : '' }}>
                <label class="form-check-label" for="linkedinAll">
                  {{ __('admin.All') }}
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="linkedin" value="1"
                       id="linkedinYes" {{ request('linkedin') == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="linkedinYes">
                  {{ __('admin.Yes') }}
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="linkedin" value="0"
                       id="linkedinNo" {{ request('linkedin') == '0' ? 'checked' : '' }}>
                <label class="form-check-label" for="linkedinNo">
                  {{ __('admin.No') }}
                </label>
              </div>
            </div>
          </div>

          <!-- Enrolled in Program Filter -->
          <div class="form-group mb-4">
            <label class="form-label">
              {{ __('cycles.Enrolled_Program') }}
            </label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="enrolledInProgramFilter" value=""
                       id="enrolledAll" {{ !request()->has('enrolledInProgramFilter') || request('enrolledInProgramFilter') === '' || is_null(request('enrolledInProgramFilter')) ? 'checked' : '' }}>
                <label class="form-check-label" for="enrolledAll">
                  {{ __('admin.All') }}
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="enrolledInProgramFilter" value="1"
                       id="enrolledYes" {{ request('enrolledInProgramFilter') == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="enrolledYes">
                  {{ __('admin.Yes') }}
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="enrolledInProgramFilter" value="0"
                       id="enrolledNo" {{ request('enrolledInProgramFilter') == '0' ? 'checked' : '' }}>
                <label class="form-check-label" for="enrolledNo">
                  {{ __('admin.No') }}
                </label>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="ti ti-x me-2"></i>
            {{ __('admin.Cancel') }}
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-filter me-2"></i>
            {{ __('cycles.Apply_Filters') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>