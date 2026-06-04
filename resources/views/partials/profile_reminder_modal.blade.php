{{-- Profile Reminder Modal Partial --}}
@php
  if( in_array( Route::currentRouteName(), ['user.profile_Edit', 'company.profile_view_mine'] ) ) { $is_profile_completed = true; $is_company_completed = true; }
@endphp

{{-- Only show the modal if startup had not completed the profile or company profile --}}
@if($_user->hasRole('registered') && ( $is_profile_completed == false || $is_company_completed == false ))
  <!-- Profile Reminder Modal -->
  <div class="modal fade" id="profileReminderModal" tabindex="-1" aria-labelledby="profileReminderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-center">
          <h4 class="modal-title">
            {{ $l == 'ar' ?  'تذكير الملف الشخصي' : 'Profile Reminder' }}
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
          <div class="text-center mb-6">
            <p class="text-muted">{{ $l == 'ar' ?  'لم يتم استكمال الملف الشخصي / ملف الشركة بعد، يرجى التأكد من استكمال جميع البيانات المطلوبة في كليهما' : 'Your Profile/Company data is not yet completed, make sure that you filled out all required data' }}</p>
          </div>
          <div class="row g-4">
            @if( $is_profile_completed == false )
            <div class="col-12">
              <div class="row">
                <div class="col-10">
                  {{ $l == 'ar' ? 'استكمال الملف الشخصي' : 'Complete Profile' }}
                </div>
                <div class="col-2">
                  <a href="{{ route('user.profile_Edit') }}" class="btn btn-primary w-100">
                    {{ $l == 'ar' ? 'تحديث' : 'Update' }}
                  </a>
                </div>
              </div>
            </div>
            @endif
            @if( $is_company_completed == false )
            <div class="col-12">
              <div class="row">
                <div class="col-10">
                  {{ $l == 'ar' ? 'استكمال ملف الشركة' : 'Complete Company Profile' }}
                </div>
                <div class="col-2">
                  <a href="{{ route('company.profile_view_mine') }}" class="btn btn-primary w-100">
                    {{ $l == 'ar' ? 'تحديث' : 'Update' }}
                  </a>
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

@push('scripts')
 @if($_user->hasRole('registered') && ( $is_profile_completed == false || $is_company_completed == false ))
  <script>
    // Profile reminder modal - show every 5 minutes
    function showProfileReminder() {
      const modal = new bootstrap.Modal(document.getElementById('profileReminderModal'));
      modal.show();
    }

    // Show modal immediately on page load
    document.addEventListener('DOMContentLoaded', function() {
      showProfileReminder();
    });

    // Show modal every 5 minutes (300,000 milliseconds)
    setInterval(function() {
      showProfileReminder();
    }, 300000);
  </script>
@endif
@endpush