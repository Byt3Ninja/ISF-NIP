<form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data">
  {!! csrf_field() !!}
  <input type="hidden" value="6" name="CompanyN">

  <div class="row">
    <!-- Pitch Deck Column -->
    <div class="col-md-6">
      <div class="card shadow-sm" style="border-radius: 15px; height: 520px;">
        <div class="p-3">
          <h5 class="card-title">{{ __('admin.Pitch_Deck') }}</h5>
          <div class="card-body text-center">
            <div class="d-flex flex-column justify-content-between align-items-center">
              <img id="deck-image" src="/images/deck.svg" class="radius-15 img-fluid" style="width: 350px; height: 350px;" />
              <div class="mt-auto d-flex flex-column">
                <input id="file" class="form-control border-light light-shadow px-8 d-none" type="file" name="pitch_deck" onchange="updateImage('file', 'deck-image', 'file-name')">
                <span class="text-muted mt-2">To get analysis for your pitch deck please</span>
                <a href="javascript:void(0)" class="text-primary fw-medium text-decoration-underline mt-1" onclick="document.getElementById('file').click(); return false;">Click here</a>
                <span id="file-name" class="mt-1"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pitch Deck Video Column -->
    <div class="col-md-6">
      <div class="card shadow-sm" style="border-radius: 15px; min-height: 520px;">
        <div class="p-3">
          <h5 class="card-title">{{ __('admin.Pitch_Deck_Video') }}</h5>
          <div class="card-body text-center d-grid justify-content-between">
            <div class="d-flex flex-column justify-content-between align-items-center">
              <img id="deck-video-image" src="/images/deck-video.svg" class="radius-15" style="width: 350px; height: 350px;" />
              <div class="mt-auto d-flex flex-column">
                <input id="video" class="form-control border-light light-shadow px-8 d-none" type="file" name="pitch_deck_video" onchange="updateImage('video', 'deck-video-image', 'video-name')">
                <span class="text-muted mt-2">To get analysis for your pitch deck please upload your pitch deck video</span>
                <a href="javascript:void(0)" class="text-primary fw-medium text-decoration-underline mt-1" onclick="document.getElementById('video').click(); return false;">Click here</a>
                <span id="video-name" class="mt-1"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Documents Column -->
    <div class="col-md-6">
      <div class="card shadow-sm" style="border-radius: 15px;">
        <div class="p-3">
          <h5 class="card-title">Documents</h5>
          <div class="d-flex flex-column gap-2">
            <div id="documents-list">
              <div class="card shadow-sm" style="border-radius: 15px;">
              </div>
            </div>
            <div class="mt-3" style="display: grid; place-items: center;">
              <input id="document-upload" class="form-control border-light light-shadow px-8 d-none" type="file" name="documents[]" onchange="addDocument()">
              <a href="javascript:void(0)" class="btn btn-outline-primary text-black fw-bold rounded-pill" onclick="document.getElementById('document-upload').click(); return false;">
                <i class="ti ti-plus me-3"></i>Add new document
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 mt-3">
      <div class="card shadow-sm" style="border-radius: 15px;">
        <div class="p-3">
          <h5 class="card-title">Pitch deck information</h5>
          <div class="d-flex flex-column gap-2">
            <div class="col-12 mb-3 px-4">
              <label class="form-label">{{ __('admin.Pitch_Deck_Information') }}</label>
              <select name="pitch_deck_info" class="select2 form-control light-shadow border-light">
                <option value="">{{ __('admin.Choose') }}</option>
                <option value="yes">{{ __('admin.Yes') }}</option>
                <option value="no">{{ __('admin.No') }}</option>
              </select>
            </div>

            <div class="col-12 mb-3">
              <div class="form-group px-4">
                <label class="form-label" for="pitch_deck_summary">{{ __('admin.Pitch_Deck_Summary') }}</label>
                <textarea name="pitch_deck_summary" autocomplete="pitch_deck_summary" class="form-control light-shadow border-light" id="pitch_deck_summary" placeholder="{{ __('admin.Pitch_Deck_Summary') }}">{{ $client->company ? $client->company->pitch_deck_summary : old('pitch_deck_summary') }}</textarea>
              </div>
            </div>

            <div class="col-12 mb-3">
              <div class="form-group px-4">
                <label class="form-label" for="problem_facing">{{ __('admin.Problem_We_Are_Facing') }}</label>
                <textarea name="problem_facing" autocomplete="problem_facing" class="form-control light-shadow border-light" id="problem_facing" placeholder="{{ __('admin.problem_facing') }}">{{ $client->company ? $client->company->problem_facing : old('problem_facing') }}</textarea>
              </div>
            </div>

            <div class="col-12 mb-3">
              <div class="form-group px-4">
                <label class="form-label" for="solution_problem">{{ __('admin.solution_problem') }}</label>
                <textarea name="solution_problem" autocomplete="solution_problem" class="form-control light-shadow border-light" id="solution_problem" placeholder="{{ __('admin.solution_problem') }}">{{ $client->company ? $client->company->solution_problem : old('solution_problem') }}</textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-12 d-flex justify-content-end">
      <button type="submit" class="btn btn-primary mr-1 mb-1" id="submit-button">
        <span id="button-text">{{ __('admin.Update') }}</span>
        <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
      </button>
    </div>
  </div>
</form>

<script>
  function updateImage(inputId, imgId, spanId) {
    var input = document.getElementById(inputId);
    var img = document.getElementById(imgId);
    var span = document.getElementById(spanId);

    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        img.src = e.target.result;
      }
      reader.readAsDataURL(input.files[0]);
      span.textContent = input.files[0].name;
    } else {
      img.src = '/images/deck.svg'; // Default image if no file is selected
      span.textContent = '';
    }
  }

  function addDocument() {
    var input = document.getElementById('document-upload');
    var documentsList = document.getElementById('documents-list');

    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        var documentCard = document.createElement('div');
        documentCard.className = 'card shadow-sm';
        documentCard.style.borderRadius = '15px';
        documentCard.innerHTML = `
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-3">
                <i class="ti ti-file fs-1 text-warning"></i>
                <span>${input.files[0].name}</span>
              </div>
              <a href="${e.target.result}" class="btn btn-transparent" download>
                <i class="ti ti-download fs-3 text-primary"></i>
              </a>
            </div>
          </div>
        `;
        documentsList.appendChild(documentCard);
      }
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>