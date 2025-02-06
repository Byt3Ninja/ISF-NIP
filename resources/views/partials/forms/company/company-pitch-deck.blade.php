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
                <iframe src="{{ asset($client->company->pitch_deck) }}"
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
                <input 
                  class="form-control border-light light-shadow px-8"
                  type="file" name="pitch_deck" accept="application/pdf">
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
                  <source src="{{ asset($client->company->pitch_deck_video) }}"
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
                  class="form-control border-light light-shadow px-8"
                  type="file" name="pitch_deck_video" accept="video/*">
               
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
            if($client->company->documents && is_array($client->company->documents)){
              // تحويل السلسلة إلى مصفوفة بعد إزالة الشرطات المائلة
              $documents = json_decode($client->company->documents, true);
              // استخراج اسم كل ملف من الروابط
              $fileNames = array_map(function ($url) {
                              return basename($url); // استخراج اسم الملف من الرابط
                          }, $documents);
            }else{
              $documents = [];
            }
            @endphp
            @foreach ($documents as $index => $document)
              @if (is_string($document) && !empty($document))
                <div class="card shadow-sm file-card" style="border-radius: 15px;" data-index="{{ $index }}" data-file="{{ $document }}">
                  <div class="card-body">
                    <div
                      class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-3">
                        <i class="ti ti-file fs-1 text-warning"></i>
                        <span>{{ $fileNames[$index] }}</span>
                      </div>
                      <a class="btn btn-transparent"
                        href="{{ asset($document) }}" target="_blank" download="{{ $fileNames[$index] }}">
                        <i class="ti ti-download fs-3 text-primary"></i>
                      </a>

                      <span class="btn btn-transparent text-danger delete-file" data-index="{{ $index }}" data-file="{{ $document }}">
                        <i class="ti ti-trash fs-3"></i>
                    </span>

                    </div>
                  </div>
                </div>
              @endif
            @endforeach
            @endif
            <div class="mt-3" style="display: grid; place-items: center;">


              <input id="document"
                class="form-control border-light light-shadow px-8"
                type="file" multiple name="documents[]" >
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

  $(document).on('click', '.delete-file', function () {
        var fileIndex = $(this).data('index');  // رقم الفهرس
        var filePath = $(this).data('file');    // مسار الملف
        var cardElement = $(this).closest('.file-card'); // العنصر الذي يحتوي على الملف

        if (confirm('هل أنت متأكد من حذف هذا الملف؟')) {
            $.ajax({
                url: '{{ route("delete.document") }}',  // تأكد من أن هذا هو المسار الصحيح
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    index: fileIndex,
                    file: filePath
                },
                success: function (response) {
                    if (response.success) {
                        cardElement.remove(); // حذف العنصر من الواجهة
                        alert('تم حذف الملف بنجاح.');
                    } else {
                        alert('حدث خطأ أثناء الحذف.');
                    }
                },
                error: function () {
                    alert('تعذر الاتصال بالخادم.');
                }
            });
        }
    });
</script>
