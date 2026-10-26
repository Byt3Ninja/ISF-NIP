{{-- Shared by the initial and final screening pages' Guidelines editor.
     Images are uploaded to disk and inserted by URL; without this Summernote inlines
     them as base64, which bloats the save request past the server's body limit (413). --}}
<script>
  // Mirrors UploadScreeningGuidelinesImageRequest (max:5120).
  const SCREENING_GUIDELINES_IMAGE_MAX_BYTES = 5 * 1024 * 1024;

  function uploadScreeningGuidelinesImage(file, $editor) {
    if (file.size > SCREENING_GUIDELINES_IMAGE_MAX_BYTES) {
      toastr.error('{{ __('admin.file_size_validation') }}');
      return;
    }

    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
      toastr.error('{{ __('admin.file_mimes_validation') }}');
      return;
    }

    const data = new FormData();
    data.append('image', file);
    data.append('step_id', '{{ $cstep->id }}');
    data.append('_token', '{{ csrf_token() }}');

    $.ajax({
      url: '{{ route('screening.guidelines.upload_image', ['id' => $cstep->id]) }}',
      method: 'POST',
      data: data,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.status) {
          $editor.summernote('insertImage', response.url);
        } else {
          toastr.error(response.message || '{{ __('admin.Image upload failed') }}');
        }
      },
      error: function (xhr) {
        // A 413 from the web server comes back as an HTML page, not JSON.
        const message = xhr.status === 413
          ? '{{ __('admin.file_size_validation') }}'
          : (xhr.responseJSON?.message || '{{ __('admin.Image upload failed') }}');
        toastr.error(message);
      }
    });
  }

  // Error text for a failed guidelines save. Content can still exceed the server's body
  // limit through pasted HTML carrying base64 images, which never hits onImageUpload.
  function screeningGuidelinesSaveErrorMessage(xhr) {
    if (xhr.status === 413) {
      return '{{ __('admin.screening_guidelines_too_large') }}';
    }
    return xhr.responseJSON?.message || '{{ __('admin.unexpected_error') }}';
  }
</script>
