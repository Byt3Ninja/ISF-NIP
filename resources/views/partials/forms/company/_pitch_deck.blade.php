<div class="col-12">
  <div class="card mb-6">
    <h5 class="card-header">Pitch Deck</h5>
    <div class="card-body">
      <form 
        action="{{ $formAction }}" 
        class="dropzone needsclick pitch-deck-form" 
        id="dropzone-basic"
        enctype="multipart/form-data"
        method="post"
      >
        @csrf
        <input type="hidden" value="{{ \App\Enums\CompanyFormStep::PitchDeckFile->value }}" name="CompanyN" />
          <div class="dz-message needsclick">
          Drop files here or click to upload
          <span class="note needsclick">
            ( Upload pich deck in PDF format )
          </span>
        </div>
        <div class="fallback">
          <input 
            id="pitch-deck-input"
            name="pitch_deck" 
            type="file" 
            accept="application/pdf"
          />
        </div>
      </form>
      <div id="pitch-deck-error" class="invalid-feedback" style="display: none;">
        Please upload a pitch deck
      </div>
    </div>
    <div class="card-footer">
      <button
        type="button"
        onclick="onUploadPitchDeck()"
        class="btn btn-sm btn-primary">
        Save
      </button>
    </div>
  </div>
</div>

<script>
  const onUploadPitchDeck = () => {
    const pitchDeckFile = document.querySelector('#pitch-deck-file');
    console.log('pitchDeckFile', pitchDeckFile)
    const pitchDeckError = document.querySelector('#pitch-deck-error');
    
    if (!pitchDeckFile) {
      pitchDeckError.style.display = 'block';
      return;
    }
  
    pitchDeckError.style.display = 'none';
    const pitchDeckForm = document.querySelector('.pitch-deck-form');
    pitchDeckForm.action = '{{ $formAction }}';
    pitchDeckForm.submit();
  };

  document.addEventListener('DOMContentLoaded', () => {
    const previewTemplate = 
      `
      <div class="dz-preview dz-file-preview" id="pitch-deck-file">
        <div class="dz-details">
          <div class="dz-thumbnail">
            <img data-dz-thumbnail>
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
          <a href="{{ asset($client->company->pitch_deck) }}" class="dz-download-link" data-dz-download>Download</a>
        </div>
      </div>
    `;

    // Basic Dropzone
    // --------------------------------------------------------------------
    const dropzoneBasic = document.querySelector('#dropzone-basic');
    if (dropzoneBasic) {
      const myDropzone = new Dropzone(dropzoneBasic, {
        previewTemplate: previewTemplate,
        parallelUploads: 1,
        maxFilesize: 5,
        addRemoveLinks: true,
        maxFiles: 1,
        acceptedFiles: '.pdf'
      });

      @if($client->company && $client->company->pitch_deck != null)
        const mockFile = { name: "{{ $client->company->pitch_deck }}", size: 12345 };
        myDropzone.emit("addedfile", mockFile);
        myDropzone.emit("thumbnail", mockFile, "{{ asset($client->company->pitch_deck) }}");
        myDropzone.emit("complete", mockFile);
        myDropzone.files.push(mockFile);
        document.querySelector('.dz-download-link').href = "{{ asset($client->company->pitch_deck) }}";
      @endif
    }
  });
</script>