<div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createTeamModalLabel">
          {{ $l == 'en' ? 'Create New Team' : 'تكوين فريق جديد' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="/dashboard/hackathon/createTeam" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="step_id" value="{{ $currentHackathon['step_id'] }}">
          <input type="hidden" name="cycle_id" value="{{ $id }}">

          <div class="mb-3">
            <label for="img_team" class="form-label">
              {{ $l == 'en' ? 'Team Logo' : 'شعار الفريق' }}
            </label>
            <input type="file" class="form-control" name="team_image" id="img_team">
          </div>

          <div class="mb-3">
            <label for="teamName" class="form-label">
              {{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}
            </label>
            <input type="text" class="form-control" name="team_name" value="" id="teamName" required>
          </div>
          <div class="mb-3">
            <label for="teamDescription" class="form-label">
              {{ $l == 'en' ? 'Brief Team/Idea Description (Optional)' : 'وصف مختصر للفريق/الفكرة (اختياري)' }}
            </label>
            <textarea class="form-control" name="team_description" id="teamDescription" rows="2"></textarea>
          </div>

          <hr>

          {{-- <div class="mb-3">
            <label for="Project_Name_en" class="form-label">
              {{ __('admin.Project_Name') ." " . __('admin.English') }}
            </label>
            <input type="text" class="form-control" name="Project_Name_en" value="" id="Project_Name_en">
          </div>
          <div class="mb-3">
            <label for="Project_Name_ar" class="form-label">
              {{__('admin.Project_Name') ." " . __('admin.Arabic') }}
            </label>
            <input type="text" class="form-control" name="Project_Name_ar" value="" id="Project_Name_ar">
          </div> --}}

          {{-- <div class="mb-3">
            <label for="Project_Description_en" class="form-label">
              {{ __('admin.Project_Description') ." " . __('admin.English') }}
            </label>
            <textarea class="form-control" name="Project_Description_en" id="Project_Description_en"
              rows="2"></textarea>
          </div>

          <div class="mb-3">
            <label for="Project_Description_ar" class="form-label">
              {{ __('admin.Project_Description') ." " . __('admin.Arabic') }}
            </label>
            <textarea class="form-control" name="Project_Description_ar" id="Project_Description_ar"
              rows="2"></textarea>
          </div> --}}

          {{-- <div class="mb-3">
            <label for="Target_Track" class="form-label">{{ $l == 'en' ? 'Target Track' : 'المحور المستهدف' }}</label>
            <select class="form-select" id="Target_Track" name="Target_Track">
              <option value="">{{ $l == 'en' ? 'Choose track...' : 'اختر المحور...' }}</option>
              @for ($i = 5; $i <= 100; $i +=5) <option value="{{ $i }}" {{ old('TargetTrack', $selected ?? '' )==$i
                ? 'selected' : '' }}>{{ $i .'%'}}</option>
                @endfor
            </select>
          </div>

          <div class="mb-3">
            <label for="Project_Status" class="form-label">{{ $l == 'en' ? 'Target Track' : 'المحور المستهدف'
              }}</label>
            <select class="form-select" id="Project_Status" name="Project_Status">
              <option value="">{{ $l == 'en' ? 'Choose Status...' : 'اختر الحالة...' }}</option>
              @for ($s = 0; $s <= 100; $s +=25) <option value="{{ $s }}" {{ old('Project_Status', $selected ?? '' )==$s
                ? 'selected' : '' }}>{{ $s .'%' }}</option>
                @endfor
            </select>
          </div> --}}



      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ $l == 'en' ? 'Cancel' : 'إلغاء' }}
        </button>
        <button type="submit" class="btn btn-primary">
          {{ $l == 'en' ? 'Create Team' : 'إنشاء الفريق' }}
        </button>
      </div>
      </form>
    </div>
  </div>
</div>