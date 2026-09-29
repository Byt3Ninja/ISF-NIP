<div class="col-12">
  <div class="card mb-6">
    <h5 class="card-header">Pitch Deck Video</h5>
    <div class="card-body">
      <form 
        action="{{ $formAction }}" 
        class="dropzone needsclick pitch-deck-video-form" 
        id="dropzone-video"
        enctype="multipart/form-data"
        method="post"
      >
        @csrf
        <input type="hidden" value="{{ \App\Enums\CompanyFormStep::PitchDeckFile->value }}" name="CompanyN" />
        <div class="dz-message needsclick">
          Drop files here or click to upload
          <span class="note needsclick">
            ( Upload pitch deck video in MP4 format )
          </span>
        </div>
        @if($client->company && $client->company->pitch_deck_video != null)
          <div class="dz-preview dz-file-preview" id="pitch-deck-video-file">
            <div class="dz-details">
              <div class="dz-thumbnail">
                <iframe src="{{ $client->company->pitch_deck_video }}" width="100%" height="200" frameborder="0" allowfullscreen></iframe>
                <span class="dz-nopreview">No preview</span>
                <div class="dz-success-mark"></div>
                <div class="dz-error-mark"></div>
                <div class="dz-error-message"><span data-dz-errormessage></span></div>
                <div class="progress">
                  <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
                </div>
              </div>
              <div class="dz-filename" data-dz-name></div>
              <div class="dz-size" data-dz-size></div>
            </div>
          </div>
        @else
          <div class="fallback">
            <input 
              id="pitch-deck-video-input"
              name="pitch_deck_video" 
              type="text"
            />
          </div>
        @endif
      </form>
      
      <div id="pitch-deck-video-error" class="invalid-feedback" style="display: none;">
        Please upload a pitch deck video
      </div>
    </div>
    <div class="card-footer">
      
      <button
        type="button"
        onclick="onUploadPitchDeckVideo()"
        class="btn btn-sm btn-primary">
        Save
      </button>
    </div>
  </div>
</div>

<script>
  const onUploadPitchDeckVideo = () => {
    const pitchDeckVideoFile = document.querySelector('#pitch-deck-video-file');
    console.log('pitchDeckVideoFile', pitchDeckVideoFile)
    const pitchDeckVideoError = document.querySelector('#pitch-deck-video-error');
    
    if (!pitchDeckVideoFile) {
      pitchDeckVideoError.style.display = 'block';
      return;
    }
  
    pitchDeckVideoError.style.display = 'none';
    const pitchDeckVideoForm = document.querySelector('.pitch-deck-video-form');
    pitchDeckVideoForm.action = '{{ $formAction }}';
    pitchDeckVideoForm.submit();
  };

  document.addEventListener('DOMContentLoaded', () => {
    const previewTemplate = 
      `
      <iframe 
        src="${URL.createObjectURL(file)}" 
        width="100%" 
        height="200" 
        frameborder="0" 
        allowfullscreen
      ></iframe>
    `;

    // Basic Dropzone
    // --------------------------------------------------------------------
    const dropzoneVideo = document.querySelector('#dropzone-video');
    if (dropzoneVideo) {
      const myDropzone = new Dropzone(dropzoneVideo, {
        // previewTemplate: previewTemplate,
        parallelUploads: 1,
        maxFilesize: 50,
        addRemoveLinks: true,
        maxFiles: 1,
        acceptedFiles: '.mp4'
      });

      myDropzone.on('addedfile', (file) => {
        console.log('file name', file?.name)
        const fallback = document.querySelector('.fallback');
        if (fallback) {
          fallback.innerHTML = `
            <iframe 
              src="${URL.createObjectURL(file)}" 
              width="100%" 
              height="200" 
              frameborder="0" 
              allowfullscreen
            ></iframe>
          `;
        }
      });
    }

  });
</script>
