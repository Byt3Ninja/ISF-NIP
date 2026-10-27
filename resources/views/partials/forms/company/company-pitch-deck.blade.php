@php
  $formAction = $_user->hasAnyRole('Admin|organizationManager')
    ? route('company.profile_update_mine', $client->company->id)
    : route('company.profile_update_mine');
@endphp

<div class="row">
  <!-- Pills Navigation -->
  <div class="col-12">
    <ul class="nav nav-pills">
      <li class="nav-item me-2">
        <a class="nav-link active" id="pitch-tab" data-bs-toggle="pill" href="#pitch-deck-content">{{ __('admin.Pitch_Deck') }}</a>
      </li>
      <li class="nav-item me-2">
        <a class="nav-link" id="pitch-tab" data-bs-toggle="pill" href="#pitch-deck-video-content">{{ __('admin.Pitch_Deck_Video') }}</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="pitch-tab" data-bs-toggle="pill" href="#documents-content">{{ __('admin.Documents') }}</a>
      </li>
    </ul>
  </div>

  <div id="pitch-tab-content" class="tab-content w-100">
    <!-- Pitch Deck Column -->
    <div class="pitch-content tab-pane fade show active" id="pitch-deck-content">
      <div class="col-12 mb-3">
        <form
          method="post"
          action="{{ $formAction }}"
          enctype="multipart/form-data"
        >
          @csrf
          <input type="hidden" value="{{ \App\Enums\CompanyFormStep::PitchDeckFile->value }}" name="CompanyN" />
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">{{ __('admin.Pitch_Deck') }}</h5>
              <div
                class="d-flex h-100 flex-column justify-content-between align-items-center"
              >
                @if($client->company)
                @if ($client->company->viewerFileUrl('pitch_deck'))
                <iframe
                  src="{{ $client->company->viewerFileUrl('pitch_deck') }}"
                  class="mb-2 border rouned"
                  target="_blank"
                  width="100%"
                  height="250px"
                >
                  <p>Your browser does not support iframes.</p>
                </iframe>
                @endif
                @else
                <img
                  src="/images/deck.svg"
                  style="width: 350px; height: 250px;"
                />
                @endif
                <div class="mt-auto d-flex flex-column w-100">
                  <input
                    id="pitch-deck-input"
                    name="pitch_deck"
                    class="form-control px-8 d-none"
                    type="file"
                    accept="application/pdf"
                    onchange="handlePitchDickUpload('pitch-deck-input', 'pitch-deck-dropzone')"
                  />
                  <div
                    id="pitch-deck-dropzone"
                    class="d-flex flex-column justify-content-center align-items-center gap-1 border border-dashed p-3 text-center w-100 rounded"
                    style="cursor: pointer;"
                    onclick="document.getElementById('pitch-deck-input').click();"
                  >
                    <i class="ti ti-upload fs-4"></i>
                    <p>{{ __('admin.upload_file') }}</p>
                  </div>
                </div>
                <div class="invalid-feedback" id="pitch-deck-error" style="display: none">
                  {{ __('admin.pitch_deck_should_be_pdf') }}
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end mt-6">
                <button
                  type="submit"
                  class="btn btn-sm btn-primary mr-1 mb-1 pitch-deck-upload-btn"
                  id="submit-button-pitch-deck"
                  disabled="true"
                >
                  <span id="button-text">{{ __('admin.Save') }}</span>
                  <span
                    id="button-spinner"
                    class="spinner-border spinner-border-sm d-none"
                    role="status"
                    aria-hidden="true"
                  ></span>
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Pitch Deck Video Column -->
    <div class="pitch-content tab-pane fade" id="pitch-deck-video-content">
      <div class="col-12 mb-3">
        <form
          method="post"
          action="{{ $formAction }}"
          enctype="multipart/form-data"
          id="pitch-deck-video-form"
        >
          @csrf
          <input type="hidden" value="{{ \App\Enums\CompanyFormStep::PitchDeckVideo->value }}" name="CompanyN" />
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">{{ __('admin.Pitch_Deck_Video') }}</h5>
            </div>
            <div class="card-body">
              <div
                class="d-flex h-100 flex-column justify-content-between align-items-center"
              >
                @if($client->company)
                @if ($client->company->pitch_deck_video != null)
                <a href="{{ asset($client->company->pitch_deck_video) }}" target="_blank" class="mb-2 text-start w-100 cursor-pointer">
                  {{ __('admin.View_Pitch_Deck_Video') }}
                </a>
                @endif
                @else
                <img
                  id="deck-video-image"
                  src="/images/deck-video.svg"
                  style="width: 350px; height: 250px;"
                />
                @endif
                <div class="mt-auto d-flex flex-column w-100 mt-2">
                  <input
                    id="video-input"
                    class="form-control"
                    placeholder="{{ __('admin.upload_video_url') }}"
                    type="text"
                    name="pitch_deck_video"
                  />
                  {{-- <div
                    id="video-dropzone"
                    class="d-flex flex-column justify-content-center align-items-center gap-1 border border-dashed border-2 p-3 text-center w-100 rounded"
                    style="cursor: pointer;"
                    onclick="document.getElementById('video').click();"
                  >
                    <i class="ti ti-upload fs-4"></i>
                    <p>{{ __('admin.upload_video') }}</p>
                  </div> --}}
                  <div class="invalid-feedback" id="video-error" style="display: none">
                    {{ __('admin.pitch_deck_video_should_be_valid_video_url') }}
                  </div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end mt-6">
                <button
                  type="submit"
                  class="btn btn-sm btn-primary mr-1 mb-1 pitch-video-upload-btn"
                  id="submit-button-video"
                  disabled="true"
                >
                  <span id="button-text">{{ __('admin.Save') }}</span>
                  <span
                    id="button-spinner"
                    class="spinner-border spinner-border-sm d-none"
                    role="status"
                    aria-hidden="true"
                  ></span>
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Documents Column -->
    <div class="pitch-content tab-pane fade" id="documents-content">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">{{ __('admin.Documents') }}</h5>
        </div>
        <div class="card-body">
          <div id="documents-list" class="mb-4">
            @if($client->company)
              @php
                if($client->company->documents && !is_null($client->company->documents)){
                  $documents = json_decode($client->company->documents, true);
                  if( json_last_error() != JSON_ERROR_NONE ) {
                    $documents = [];
                  }
                  $fileNames = array_map(function ($url) {
                    return basename($url);
                  }, $documents);
                } else{
                 $documents = [];
                }
              @endphp
              @foreach ($documents as $index => $document)
                @if (is_string($document) && !empty($document))
                  <div class="p-2 rounded border file-card mb-2" data-file="{{ $document }}">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-3">
                        <i class="ti ti-file fs-5 text-warning"></i>
                        <span class="fs-6">{{ $fileNames[$index] }}</span>
                      </div>
                      <div class="d-flex align-items-center gap-1">
                        <a
                          class="btn btn-sm btn-icon rounded-circle btn-transparent"
                          href="{{ $client->company->protectedPathUrl('documents', $document) }}"
                          target="_blank"
                          download="{{ $fileNames[$index] }}"
                        >
                          <i class="ti ti-download fs-6 text-primary"></i>
                        </a>
                        <span
                          class="btn btn-sm btn-icon rounded-circle btn-transparent text-danger delete-file"
                          data-index="{{ $index }}"
                          data-file="{{ $document }}"
                        >
                          <i class="ti ti-trash fs-6"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                @endif
              @endforeach
            @endif
          </div>
          <form method="post" action="{{ $formAction }}" enctype="multipart/form-data">
            @csrf
            <fieldset>
              <input type="hidden" value="{{ \App\Enums\CompanyFormStep::SupplementaryDocuments->value }}" name="CompanyN" />
              <div class="form-group mt-3">
                <input
                  id="document"
                  class="form-control"
                  type="file"
                  accept="application/pdf"
                  multiple
                  name="documents[]"
                  onchange="handleDocumentUpload('document')"
                />
                <div class="invalid-feedback" id="document-error" style="display: none">
                  {{ __('admin.documents_should_be_pdf') }}
                </div>
              </div>
            </fieldset>
            <div class="w-100 d-flex justify-content-end mt-6">
              <button
                type="submit"
                class="btn btn-sm btn-primary document-upload-btn"
                id="submit-button-documents"
                disabled="true"
              >
                <span id="button-text">{{ __('admin.Save') }}</span>
                <span
                  id="button-spinner"
                  class="spinner-border spinner-border-sm d-none"
                  role="status"
                  aria-hidden="true"
                ></span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function updateImage(inputId, imgId, spanId) {
    var input = document.getElementById(inputId);
    var img = document.getElementById(imgId);
    var span = document.getElementById(spanId);

    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        img.src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
      span.textContent = input.files[0].name;
    } else {
      img.src = '/images/deck.svg';
      span.textContent = '';
    }
  }

  // Handle pitch dick document upload
  function handlePitchDickUpload(inputId, dropzoneId) {
    const input = document.getElementById(inputId);
    const dropzone = document.getElementById(dropzoneId);
    const pitchDickUploadButton = document.querySelector('.pitch-deck-upload-btn');
    const pitchDickError = document.getElementById('pitch-deck-error');

    const allowedExtensions = /(\.pdf)$/i;
    if (!allowedExtensions.exec(input.value)) {
      pitchDickError.style.display = 'block';
      pitchDickUploadButton.disabled = true;
      input.value = ''; // Clear the input field
      return;
    }

    if (input.files && input.files[0]) {
      pitchDickError.style.display = 'none';
      pitchDickUploadButton.disabled = false;
      const file = input.files[0];
      const reader = new FileReader();
      reader.onload = function (e) {
        dropzone.innerHTML = `
          <div class="d-flex justify-content-between align-items-center w-100">
            <span>${file.name}</span>
            <span class="btn btn-sm btn-icon rounded-circle btn-transparent text-danger" onclick="removeFile('${inputId}', '${dropzoneId}', event)">
              <i class="ti ti-trash fs-6"></i>
            </span>
          </div>
        `;
      };
      reader.readAsDataURL(file);
    }
  };

  const videoInput = document.getElementById('video-input');
  const videoUploadButton = document.querySelector('.pitch-video-upload-btn');
  const videoError = document.getElementById('video-error');
  const pickDickVideoForm = document.getElementById('pitch-deck-video-form');

  videoInput.addEventListener('input', function () {
    const videoInputValue = videoInput.value;
    const validVideoLink = videoInputValue.match(/(http(s?):\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+|((http(s?):)([/|.|\w|\s|-])*\.(?:mp4|mkv|avi|mov))/);

    if (!validVideoLink) {
      videoError.style.display = 'block';
      videoUploadButton.disabled = true;
    } else {
      videoError.style.display = 'none';
      videoUploadButton.disabled = false;
    }
  });

  pickDickVideoForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const videoInputValue = videoInput.value;
    const validVideoLink = videoInputValue.match(/(http(s?):\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+|((http(s?):)([/|.|\w|\s|-])*\.(?:mp4|mkv|avi|mov))/);
    if (!validVideoLink) {
      videoError.style.display = 'block';
      videoUploadButton.disabled = true;
      return;
    }

    videoError.style.display = 'none';
    videoUploadButton.disabled = false;
    pickDickVideoForm.submit();
  });

  // Handle upload documents 
  function handleDocumentUpload(inputId) {
    console.log('handleDocumentUpload');
    const input = document.getElementById(inputId);
    const documentUploadButton = document.querySelector('.document-upload-btn');
    const documentError = document.getElementById('document-error');
    const documentList = document.getElementById('documents-list');

    if (input.files && input.files[0]) {
      for (let i = 0; i < input.files.length; i++) {
        const allowedExtensions = /(\.pdf)$/i;
        if (!allowedExtensions.exec(input.files[i].name)) {
          // alert('{{ __('admin.documents_should_be_pdf') }}');
          documentError.style.display = 'block';
          input.value = ''; // Clear the input field
          documentUploadButton.disabled = true;
          return;
        }

        documentError.style.display = 'none';
        documentUploadButton.disabled = false;

        // var reader = new FileReader();
        // reader.onload = function (e) {        
        //   documentCard = `
        //     <div class="p-2 rounded border file-card" data-file="${input.files[i].name}">
        //       <div class="d-flex justify-content-between align-items-center">
        //         <div class="d-flex align-items-center gap-3">
        //           <i class="ti ti-file fs-5 text-warning"></i>
        //           <span class="fs-6">${input.files[i].name}</span>
        //         </div>
        //         <div class="d-flex align-items-center gap-1">
        //           <a
        //             class="btn btn-sm btn-icon rounded-circle btn-transparent"
        //             href="${e.target.result}"
        //             target="_blank"
        //             download=""
        //           >
        //             <i class="ti ti-download fs-6 text-primary"></i>
        //           </a>
        //           <span
        //             class="btn btn-sm btn-icon rounded-circle btn-transparent text-danger delete-file"
        //             data-index="${i}"
        //             data-file="${input.files[i].name}"
        //           >
        //             <i class="ti ti-trash fs-6"></i>
        //           </span>
        //         </div>
        //       </div>
        //     </div>
        //   `;
        //   documentsList.appendChild(documentCard);
        // };
      }
    }
  }
  
  $(document).on('click', '.delete-file', function () {
    var fileIndex = $(this).data('index');
    var filePath = $(this).data('file');
    var cardElement = $(this).closest('.file-card');

    releaseAlert('warning', {
      title: "{{__('admin.SureDelete')}}",
      message: "{{__('admin.Recover')}}",
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('admin.No') }}",
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        $.ajax({
          url: '{{ route("company.profile_documents_delete") }}',
          type: 'POST',
          data: {
              _token: '{{ csrf_token() }}',
              index: fileIndex,
              file: filePath,
          },
          success: function (response) {
              if (response.success) {
                  cardElement.remove();
                  releaseAlert('success', {
                    title: 'تم الحذف!',
                    message: 'تم حذف الملف بنجاح.'
                  });
              } else {
                  releaseAlert('danger', {
                    title: 'خطأ!',
                    message: 'حدث خطأ أثناء الحذف.'
                  });
              }
          },
          error: function () {
              releaseAlert('danger', {
                title: 'خطأ!',
                message: 'تعذر الاتصال بالخادم.'
              });
          },
        });
      }
    });
  });

  function removeFile(inputId, dropzoneId, event) {
    if (event) {
      event.stopPropagation();
    }
    
    var input = document.getElementById(inputId);
    var dropzone = document.getElementById(dropzoneId);

    input.value = '';
    dropzone.innerHTML = `
      <i class="ti ti-upload fs-4"></i>
      <p>{{ __('admin.upload_file') }}</p>
    `;
  }
</script>