<div class="row">
  <!-- Pitch Deck Column -->
  <div class="col-md-6 mb-3">
    @if(Auth::user()->hasanyRole('Admin|organizationManager'))
    <form method="post" action="{{ route('update_my_company', $client->company->id) }}"
      enctype="multipart/form-data">
@else
<form method="post" action="{{ route('update_my_company') }}"
  enctype="multipart/form-data">
@endif
      {!! csrf_field() !!}
      <input type="hidden" value="9" name="CompanyN">

      <div class="card shadow-sm" style="border-radius: 15px; height: 420px;">
        <div class="p-3">
          <h5 class="card-title">{{ __('admin.Pitch_Deck') }}</h5>
          <div class="card-body text-center">
            <div
              class="d-flex h-100 flex-column justify-content-between align-items-center">
              @if($client->company)
              @if ($client->company->pitch_deck != null)
                <iframe src="{{ url($client->company->pitch_deck) }}"
                  target="_blank" width="100%" height="250px"
                  style="border: none;">
                  <p>Your browser does not support iframes.</p>
                </iframe>
                @endif
              @else
                <img src="/images/deck.svg"
                  style="width: 350px; height: 250px;" />
              @endif
              {{-- <img id="deck-image" src="/images/deck.svg" class="radius-15 img-fluid" style="width: 350px; height: 350px;" /> --}}
              <div class="mt-auto d-flex flex-column">
                <input id="file"
                  class="form-control border-light light-shadow px-8 d-none"
                  type="text" name="pitch_deck">
                <span class="text-muted mt-2">To get analysis for your pitch
                  deck please</span>
                <a href="#" data-input="file" data-preview="holderfile"
                  class="lfm text-primary fw-medium text-decoration-underline mt-1">{{ __('admin.Click_here') }}</a>
                <span id="file-name" class="mt-1"></span>
              </div>

            </div>
            <div class="">
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1"
                  id="submit-button">
                  <span id="button-text">{{ __('admin.Update') }}</span>
                  <span id="button-spinner"
                    class="spinner-border spinner-border-sm d-none"
                    role="status" aria-hidden="true"></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <!-- Pitch Deck Video Column -->
  <div class="col-md-6">
    @if(Auth::user()->hasanyRole('Admin|organizationManager'))
    <form method="post" action="{{ route('update_my_company', $client->company->id) }}"
      enctype="multipart/form-data">
@else
<form method="post" action="{{ route('update_my_company') }}"
  enctype="multipart/form-data">
@endif
      {!! csrf_field() !!}
      <input type="hidden" value="10" name="CompanyN">

      <div class="card shadow-sm"
        style="border-radius: 15px; height: 420px;">
        <div class="p-3">
          <h5 class="card-title">{{ __('admin.Pitch_Deck_Video') }}</h5>
          <div class="card-body text-center d-grid justify-content-between">
            <div
              class="d-flex flex-column justify-content-between align-items-center">
              @if($client->company)
              @if ($client->company->pitch_deck_video != null)
                <video width="100%" controls>
                  <source src="{{ $client->company->pitch_deck_video }}"
                    type="video/mp4">
                  Your browser does not support the video tag.
                </video>
                @endif
              @else
                <img id="deck-video-image" src="/images/deck-video.svg"
                  class="radius-15" />
              @endif

              <div class="mt-auto d-flex flex-column justify-content-between">
                <input id="video"
                  class="form-control border-light light-shadow px-8 d-none"
                  type="text" name="pitch_deck_video">
                <span class="text-muted mt-2">To get analysis for your pitch
                  deck please upload your pitch deck video</span>
                <a data-input="video" data-preview="holdervideo"
                  href="javascript:void(0)"
                  class="lfm text-primary fw-medium text-decoration-underline mt-1">{{ __('admin.Click_here') }}</a>
                <span id="video-name" class="mt-1"></span>
              </div>
            </div>
            <div class=" mt-6">
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1"
                  id="submit-button">
                  <span id="button-text">{{ __('admin.Update') }}</span>
                  <span id="button-spinner"
                    class="spinner-border spinner-border-sm d-none"
                    role="status" aria-hidden="true"></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </form>
  </div>
  <!-- Documents Column -->
  <div class="col-md-6 mt-3">
    @if(Auth::user()->hasanyRole('Admin|organizationManager'))
    <form method="post" action="{{ route('update_my_company', $client->company->id) }}"
      enctype="multipart/form-data">
@else
<form method="post" action="{{ route('update_my_company') }}"
  enctype="multipart/form-data">
@endif
      {!! csrf_field() !!}
      <input type="hidden" value="11" name="CompanyN">

      <div class="card shadow-sm" style="border-radius: 15px;">
        <div class="p-3">
          <h5 class="card-title">{{ __('admin.Documents') }}</h5>
          <div class="d-flex flex-column gap-2">
            @if($client->company)
            @php

              // تحويل السلسلة إلى مصفوفة بعد إزالة الشرطات المائلة
              $documents = explode(',', $client->company->documents);
              // استخراج اسم كل ملف من الروابط
              $fileNames = array_map(function ($url) {
                  return basename($url); // استخراج اسم الملف من الرابط
              }, $documents);

            @endphp
            @foreach ($documents as $index => $document)
              @if (is_string($document) && !empty($document))
                <div class="card shadow-sm" style="border-radius: 15px;">
                  <div class="card-body">
                    <div
                      class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-3">
                        <i class="ti ti-file fs-1 text-warning"></i>
                        <span>{{ $fileNames[$index] }}</span>
                      </div>
                      <a class="btn btn-transparent"
                        href="{{ $document }}" target="_blank" download>
                        <i class="ti ti-download fs-3 text-primary"></i>
                      </a>
                    </div>
                  </div>
                </div>
              @endif
            @endforeach
            @endif
            <div class="mt-3" style="display: grid; place-items: center;">


              <input id="document"
                class="form-control border-light light-shadow px-8 d-none"
                type="text" name="documents">
              <a data-input="document" data-preview="holderdocument"
                href="#"
                class="lfm btn btn-outline-primary text-black fw-bold rounded-pill">
                <i
                  class="ti ti-plus me-3"></i>{{ __('admin.Add new document') }}
              </a>
            </div>

            <div class="">
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary mr-1 mb-1"
                  id="submit-button">
                  <span id="button-text">{{ __('admin.Update') }}</span>
                  <span id="button-spinner"
                    class="spinner-border spinner-border-sm d-none"
                    role="status" aria-hidden="true"></span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="col-md-6 mt-3">
    @if(Auth::user()->hasanyRole('Admin|organizationManager'))
    <form method="post" action="{{ route('update_my_company', $client->company->id) }}"
      enctype="multipart/form-data">
@else
<form method="post" action="{{ route('update_my_company') }}"
  enctype="multipart/form-data">
@endif
      {!! csrf_field() !!}
      <input type="hidden" value="6" name="CompanyN">
      <div class="card shadow-sm" style="border-radius: 15px;">
        <div class="p-3">
          <h5 class="card-title">{{ __('admin.Pitch_Deck_Information') }}</h5>
          <div class="d-flex flex-column gap-2">
            <div class="col-12 mb-3 px-4">
              {{-- <label
                class="form-label">{{ __('admin.Pitch_Deck_Information') }}</label> --}}
              <select name="pitch_deck_info"
                class="form-select light-shadow border-light">
                <option value="" selected disabled>{{ __('admin.Pitch_Deck_Information') }}</option>
                <option value="yes"
                @if($client->company)
                  {{ $client->company->pitch_deck_description == 'yes' ? 'selected' : '' }} @endif>
                  {{ __('admin.Yes') }}</option>
                <option value="no"
                @if($client->company)
                  {{ $client->company->pitch_deck_description == 'no' ? 'selected' : '' }} @endif>
                  {{ __('admin.No') }}</option>
              </select>
            </div>

            <div class="col-12 mb-3">
              <div class="form-group px-4">
                {{-- <label class="form-label"
                  for="pitch_deck_summary">{{ __('admin.Pitch_Deck_Summary') }}</label> --}}
                <textarea name="pitch_deck_summary" autocomplete="pitch_deck_summary"
                  class="form-control light-shadow border-light" id="pitch_deck_summary"
                  placeholder="{{ __('admin.Pitch_Deck_Summary') }}">{{ $client->company ? $client->company->pitch_deck_summary : old('pitch_deck_summary') }}</textarea>
              </div>
            </div>

            <div class="col-12 mb-3">
              <div class="form-group px-4">
                {{-- <label class="form-label"
                  for="problem_facing">{{ __('admin.Problem_We_Are_Facing') }}</label> --}}
                <textarea name="problem_facing" autocomplete="problem_facing"
                  class="form-control light-shadow border-light" id="problem_facing"
                  placeholder="{{ __('admin.Problem_We_Are_Facing') }}">{{ $client->company ? $client->company->problem : old('problem_facing') }}</textarea>
              </div>
            </div>

            <div class="col-12 mb-3">
              <div class="form-group px-4">
                {{-- <label class="form-label"
                  for="solution_problem">{{ __('admin.solution_problem') }}</label> --}}
                <textarea name="solution_problem" autocomplete="solution_problem"
                  class="form-control light-shadow border-light" id="solution_problem"
                  placeholder="{{ __('admin.solution_problem') }}">{{ $client->company ? $client->company->solution : old('solution_problem') }}</textarea>
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary mr-1 mb-1"
                  id="submit-button">
                  <span id="button-text">{{ __('admin.Update') }}</span>
                  <span id="button-spinner"
                    class="spinner-border spinner-border-sm d-none"
                    role="status" aria-hidden="true"></span>
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </form>
  </div>
</div>

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
