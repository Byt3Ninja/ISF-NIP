<!-- Mentors -->
<section class="mb-10 mt-8">
  <div class="row" id="mentorsContainer">
    {{-- GV-3554: hide mentor cards until the founder's program reaches the mentorship stage --}}
    @if (!($mentorshipActive ?? true))
    <div class="col-12" id="mentorshipNotActive">
      @include('includes.no-result', ['message' => __('cycles.Mentorship_Not_Available_Yet')])
    </div>
    @else
    <!-- Mentors List -->
    @if(count($mentors) > 0)
    @foreach ($mentors as $key => $mentor)
      @include('mentors.partials.mentor-card')
      @if ($canRequestMentorship && auth()->user()->company->id)
        @include('partials.book-session-modal')
      @endif
    @endforeach
    @else
    <!-- No Mentors Result -->
    <div class="col-12" id="noMentorsResult">
      @if(request()->has('programFilter') && request('programFilter') != '')
        @include('includes.no-result', ['message' => __('cycles.No_Mentors_For_Selected_Program')])
      @else
        @if(request()->get('enrolledInProgramFilter') == '1')
          @include('includes.no-result', ['message' => __('cycles.No_Mentors_Enrolled')])
        @else
          @include('includes.no-result', ['message' => __('cycles.No_Mentors_Found')])
        @endif
      @endif
    </div>
    @endif
    <!-- / Mentors List -->
    @endif
  </div>
</section>
<!-- / Mentors -->
