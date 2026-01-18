{{-- Survey Reminder Modal Partial --}}

@php


  $survey_data = []; // Initialize as empty array
  if (Auth::check() && Auth::user()->hasAnyRole(['Registered','Mentor'])) {
    $user_id = Auth::id();     
   
      $completed_survey_ids = \App\SurveyResponses::where('user_id', $user_id)
        ->whereNotNull('responses')
        ->pluck('survey_id')
        ->toArray();
      
      
      $incomplete_responses = \App\SurveyResponses::where('user_id', $user_id)
        ->whereNull('responses')
        ->whereHas('survey', function($query) {
          $query->where('is_required', 1);
        })
        ->with('survey')
        ->get();
     foreach ($incomplete_responses as $key => $response) {

          // لو trigger_on == training فقط
          if ($response->triggered_on === 'training') {

              if (Auth::check() && Auth::user()->hasAnyRole('Registered')) {
                  $existsInTraining = \DB::table('attendance_training_offline')
                      ->where('training_session_id', $response->triggered_at)
                      ->where('user_id', $user_id)
                      ->exists();

                  // لو مش موجود في الحضور، نصفي الرد من الـ collection
                  if (!$existsInTraining) {
                      $incomplete_responses->forget($key);
                  }
              }
          }
      }
       
      // تجهيز البيانات
      $survey_data = [];
      foreach ($incomplete_responses as $response) {
        $survey_data[] = [
          'id' => $response->id,
          'survey_id' => $response->survey->id,
          'title' => $response->survey->title,
          'description' => $response->survey->description,
          'is_required' => $response->survey->is_required,
          'type' => $response->triggered_on,
        ];
      }
      
  }
  
@endphp

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