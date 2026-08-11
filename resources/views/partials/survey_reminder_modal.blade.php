{{-- Survey Reminder Modal Partial --}}


{{-- Only show the modal if there are pending surveys and user is not on the survey taking page --}}
@if(auth()->user()->hasAnyRole(['Registered','Mentor']) && Route::currentRouteName() !== 'surveys.take')
  <!-- Survey Reminder Modal -->
  <div class="modal fade" id="surveyReminderModal" tabindex="-1" aria-labelledby="surveyReminderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple">
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-center">
          <h4 class="modal-title">
            {{ $l == 'ar' ?  'تذكير الاستبيان' : 'Survey Reminder' }}
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
          <div class="text-center mb-6">
            <p class="text-muted">{{ $l == 'ar' ? 'لديك استبيانات معلقة لإكمالها. يرجى تخصيص بعض الوقت لملئها.' : 'You have pending surveys to complete. Please take a moment to fill them out.' }}</p>
          </div>
          <div class="row g-4">
            @foreach($survey_data as $survey)
              <div class="col-12">
                <div class="card border shadow-sm">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                          <h5 class="card-title mb-0">{{ $survey['title'] }}</h5>
                          @if($survey['type'] == 'mentorship')
                            <span class="badge bg-label-primary">
                              {{ $l == 'ar' ? 'الإرشاد' : 'Mentorship' }}
                            </span>
                          @elseif($survey['type'] == 'training')
                            <span class="badge bg-label-success">
                              {{ $l == 'ar' ? 'التدريب' : 'Training' }}
                            </span>
                          @else
                            <span class="badge bg-label-secondary">{{ ucfirst($survey['type']) }}</span>
                          @endif
                        </div>
                        <p class="card-text text-muted">{{ $survey['description'] }}</p>
                      </div>
                      <a href="{{ route('surveys.take', ['id' => $survey['id']]) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-clipboard-list me-1"></i> 
                        {{ $l == 'ar' ? 'ابدأ الاستبيان' : 'Start Survey' }}
                      </a>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        @if( $survey['type'] == 'mentorship' )
                          <div class="text-dark text-sm mb-0">
                            @if( $survey['trigger_by'] == 'session' )
                              {{ $l == 'ar' ? 'جلسة الإرشاد مع' : 'Mentorship session with' }} 
                              {{ $_user->id == $survey['metadata']['mentor']->id ? $survey['metadata']['startup']->name : $survey['metadata']['mentor']->name }}
                              | {{ __('mentor.' . strtolower( $survey['metadata']['session']->session_type ) ) }}
                              | {{ date('Y-m-d h:i A', strtotime( $survey['metadata']['session']->zoom_date ) ) }}
                            @elseif( $survey['trigger_by'] == 'due' )
                              {{ $l == 'ar' ? 'الانتهاء من مرحلة الارشاد' : 'Completion of mentorship step' }} [{{ $survey['metadata']['step']->title }}]
                            @elseif( $survey['trigger_by'] == 'quiz' )
                            @endif
                          </div>  
                        @elseif( $survey['type'] == 'training' )
                        @elseif( $survey['type'] == 'quiz' )
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

@push('scripts')
 @if(auth()->user()->hasAnyRole(['Registered','Mentor']) && count($survey_data) > 0)
  <script>
    // Survey reminder modal - show every 10 minutes
    function showSurveyReminder() {
      // Skip reminder if user is currently on the surveys.take route
      if (window.location.pathname.includes('/dashboard/surveys/start/')) {
        return;
      }

      const modal = new bootstrap.Modal(document.getElementById('surveyReminderModal'));
      modal.show();
    }

    // Show modal immediately on page load
    document.addEventListener('DOMContentLoaded', function() {
      showSurveyReminder();
    });

    // Show modal every 10 minutes (600,000 milliseconds)
    setInterval(function() {
      showSurveyReminder();
    }, 600000);
  </script>
@endif
@endpush