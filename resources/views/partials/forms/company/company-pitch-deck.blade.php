@php
  $formAction = Auth::user()->hasanyRole('Admin|organizationManager')
    ? route('update_my_company', $client->company->id)
    : route('update_my_company');
@endphp

<div class="row">
  <!-- Pills Navigation -->
  <div class="col-12">
    <ul class="nav nav-pills">
      <li class="nav-item me-2">
        <a class="nav-link" id="pitch-tab" data-bs-toggle="pill" href="#pitch-deck-content">{{ __('admin.Pitch_Deck') }}</a>
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
    <div class="pitch-content tab-pane fade" id="pitch-deck-content">
      <div class="col-12 mb-3">
        <form
          method="post"
          action="{{ $formAction }}"
          enctype="multipart/form-data"
        >
          @csrf
          <input type="hidden" value="9" name="CompanyN" />
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">{{ __('admin.Pitch_Deck') }}</h5>
              <div
                class="d-flex h-100 flex-column justify-content-between align-items-center"
              >
                @if($client->company)
                @if ($client->company->pitch_deck != null)
                <iframe
                  src="{{ asset($client->company->pitch_deck) }}"
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
                    class="form-control border-light light-shadow px-8 d-none"
                    type="file"
                    accept="application/pdf"
                    onchange="handleFileUpload('pitch-deck-input', 'pitch-deck-dropzone')"
                  />
                  <div
                    id="pitch-deck-dropzone"
                    class="d-flex flex-column justify-content-center align-items-center gap-1 border border-dashed border-2 p-3 text-center w-100 rounded"
                    style="cursor: pointer;"
                    onclick="document.getElementById('pitch-deck-input').click();"
                  >
                    <i class="ti ti-upload fs-4"></i>
                    <p>{{ __('admin.upload_file') }}</p>
                  </div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end mt-3">
                <button
                  type="submit"
                  class="btn btn-sm btn-primary mr-1 mb-1"
                  id="submit-button-pitch-deck"
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
        >
          @csrf
          <input type="hidden" value="10" name="CompanyN" />
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">{{ __('admin.Pitch_Deck_Video') }}</h5>
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
                    id="video"
                    class="form-control border-light light-shadow px-8 d-none"
                    type="file"
                    name="pitch_deck_video"
                    accept="video/mp4,video/mkv,video/avi,video/mov"
                    onchange="handleFileUpload('video', 'video-dropzone')"
                  />
                  <div
                    id="video-dropzone"
                    class="d-flex flex-column justify-content-center align-items-center gap-1 border border-dashed border-2 p-3 text-center w-100 rounded"
                    style="cursor: pointer;"
                    onclick="document.getElementById('video').click();"
                  >
                    <i class="ti ti-upload fs-4"></i>
                    <p>{{ __('admin.upload_video') }}</p>
                  </div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end mt-3">
                <button
                  type="submit"
                  class="btn btn-sm btn-primary mr-1 mb-1"
                  id="submit-button-video"
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
      <div class="col-12 mt-3">
        <form
          method="post"
          action="{{ $formAction }}"
          enctype="multipart/form-data"
        >
          @csrf
          <input type="hidden" value="11" name="CompanyN" />
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">{{ __('admin.Documents') }}</h5>
              <div class="d-flex flex-column gap-2">
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
                <div
                  class="card file-card"
                  data-index="{{ $index }}"
                  data-file="{{ $document }}"
                >
                  <div class="p-2">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-3">
                        <i class="ti ti-file fs-5 text-warning"></i>
                        <span class="fs-6">{{ $fileNames[$index] }}</span>
                      </div>
                      <div class="d-flex align-items-center gap-1">
                        <a
                          class="btn btn-sm btn-icon rounded-circle btn-transparent"
                          href="{{ asset($document) }}"
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
                </div>
                @endif
                @endforeach
                @endif
                <div class="mt-3" style="display: grid; place-items: center;">
                  <input
                    id="document"
                    class="form-control"
                    type="file"
                    accept="application/pdf"
                    multiple
                    name="documents[]"
                    onchange="handleFileUpload('document', 'documents-dropzone')"
                  />
                </div>
                <div class="mt-3">
                  <div class="col-12 d-flex justify-content-end">
                    <button
                      type="submit"
                      class="btn btn-sm btn-primary "
                      id="submit-button-documents"
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
            </div>
          </div>
        </form>
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

  // function addDocument() {
  //   var input = document.getElementById('document-upload');
  //   var documentsList = document.getElementById('documents-list');

  //   if (input.files && input.files[0]) {
  //     var reader = new FileReader();
  //     reader.onload = function (e) {
  //       var documentCard = document.createElement('div');
  //       documentCard.className = 'card';
  //       documentCard.innerHTML = `
  //         <div class="p-2">
  //           <div class="d-flex justify-content-between align-items-center">
  //             <div class="d-flex align-items-center gap-3">
  //               <i class="ti ti-file fs-5 text-warning"></i>
  //               <span class="fs-6">${input.files[0].name}</span>
  //             </div>
  //             <div class="d-flex align-items-center gap-1">
  //               <a
  //                 class="btn btn-sm btn-icon rounded-circle btn-transparent"
  //                 href="${e.target.result}"
  //                 target="_blank"
  //                 download=""
  //               >
  //                 <i class="ti ti-download fs-6 text-primary"></i>
  //               </a>
  //               <span
  //                 class="btn btn-sm btn-icon rounded-circle btn-transparent text-danger delete-file"
  //                 data-index="${index}"
  //                 data-file="${document}"
  //               >
  //                 <i class="ti ti-trash fs-6"></i>
  //               </span>
  //             </div>
  //           </div>
  //         </div>
  //       `;
  //       documentsList.appendChild(documentCard);
  //     };
  //     reader.readAsDataURL(input.files[0]);
  //   }
  // }

  $(document).on('click', '.delete-file', function () {
    var fileIndex = $(this).data('index');
    var filePath = $(this).data('file');
    var cardElement = $(this).closest('.file-card');

    if (confirm('هل أنت متأكد من حذف هذا الملف؟')) {
      $.ajax({
        url: '{{ route("delete.document") }}',
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          index: fileIndex,
          file: filePath,
        },
        success: function (response) {
          if (response.success) {
            cardElement.remove();
            alert('تم حذف الملف بنجاح.');
          } else {
            alert('حدث خطأ أثناء الحذف.');
          }
        },
        error: function () {
          alert('تعذر الاتصال بالخادم.');
        },
      });
    }
  });

  function handleFileUpload(inputId, dropzoneId) {
    var input = document.getElementById(inputId);
    var dropzone = document.getElementById(dropzoneId);

    if (inputId === 'pitch-deck-input') {
      var allowedExtensions = /(\.pdf)$/i;
      if (!allowedExtensions.exec(input.value)) {
        alert('{{ __('admin.pitch_deck_should_be_pdf') }}')
        return;
      }
    } else if (inputId === 'video') {
      var allowedExtensions = /(\.mp4|\.mkv|\.avi|\.mov)$/i;
      if (!allowedExtensions.exec(input.value)) {
        alert('{{ __('admin.pitch_deck_video_should_be_video') }}')
        return;
      }
      // // Check file size (25MB = 25 * 1024 * 1024 bytes)
      // var maxSize = 25 * 1024 * 1024; 

      // if (file.size > maxSize) {
      //   alert('{{ __('admin.pitch_deck_video_size_limit_exceeded') }}'); 
      //   input.value = ''; // Clear the input field
      //   return;
      // }
    } else if (inputId === 'document') {
      var allowedExtensions = /(\.pdf)$/i;
      for (var i = 0; i < input.files.length; i++) {
        if (!allowedExtensions.exec(input.files[i].name)) {
          alert('{{ __('admin.documents_should_be_pdf') }}')
          return;
        }
      }
    }

    console.log('input.files', input.files)

    if (input.files && input.files[0]) {
      var file = input.files[0];
      var reader = new FileReader();
      reader.onload = function (e) {
        dropzone.innerHTML = `
          <div class="d-flex justify-content-between align-items-center w-100">
            <span>${file.name}</span>
            <span class="btn btn-sm btn-icon rounded-circle btn-transparent text-danger" onclick="removeFile('${inputId}', '${dropzoneId}', event)">
              <i class="ti ti-trash fs-6"></i>
            </span>
          </div>
        `;
        // checkAllUploads();
      };
      reader.readAsDataURL(file);
    }
  }

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
    // checkAllUploads();
  }

  // function checkAllUploads() {
  //   var pitchDeckInput = document.getElementById('pitch-deck-input');
  //   var videoInput = document.getElementById('video');
  //   var documentInput = document.getElementById('document');

  //   var pitchDeckButton = document.getElementById('submit-button-pitch-deck');
  //   var videoButton = document.getElementById('submit-button-video');
  //   var documentsButton = document.getElementById('submit-button-documents');

  //   if (pitchDeckInput.files.length > 0) {
  //     pitchDeckButton.classList.remove('d-none');
  //   } else {
  //     pitchDeckButton.classList.add('d-none');
  //   }

  //   if (videoInput.files.length > 0) {
  //     videoButton.classList.remove('d-none');
  //   } else {
  //     videoButton.classList.add('d-none');
  //   }

  //   if (documentInput.files.length > 0) {
  //     documentsButton.classList.remove('d-none');
  //   } else {
  //     documentsButton.classList.add('d-none');
  //   }
  // }
</script>