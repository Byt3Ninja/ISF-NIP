@extends('layouts.admin')
@section('title', __('admin.Documentations'))
@section('index_url', route('documentation.index'))
@section('subTitle', __('admin.Edit Document'))
@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      
      <div class="content-body">

        <section id="basic-input">
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">{{ __('admin.Edit Document') }}</h4>
                </div>
                <div class="card-content">
                  <div class="card-body">
                    @if (session()->has('msg'))
                      <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                    @endif

                    <!-- Validation messages -->
                    @if (count($errors) > 0)
                      <div class="row">
                        <div class="col-md-12">
                          <div class="alert alert-danger">
                            <ul>
                              @foreach ($errors->all() as $error)
                                <li>{{ $error }} </li>
                              @endforeach
                            </ul>
                          </div>
                        </div>
                      </div>
                    @endif
                    <br>
                    <form method="post"
                      action="{{ route('documentation.update', ['documentation' => $documentation->id]) }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}
                      @method('patch')
                      <div class="form-body">
                        <div class="row">
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="menu_ar">{{ __('admin.Arabic Title') }}</label>
                              <input id="menu_ar" type="text"
                                value="{{ $documentation->menu_ar }}" class="form-control"
                                placeholder="{{ __('admin.Arabic Title') }}" name="menu_ar" required>
                            </div>
                            <br>
                          </div>
                          <div class="col-md-12 col-12 mb-6">
                            <div class="form-label-group">
                              <label class="form-label" for="menu_en">{{ __('admin.English Title') }}</label>
                              <input id="menu_en" type="text"
                                value="{{ $documentation->menu_en }}" class="form-control"
                                placeholder="{{ __('admin.English Title') }}" name="menu_en" required>
                            </div>
                          </div>
                          <div class="col-md-6 col-6">
                            <div class="form-group">
                              <label class="form-label" for="arr">{{ __('admin.arrange') }}:</label>
                              <input id="arr" type="number" name="arr" class="select form-control"
                                value="{{ $documentation->arr }}" required>
                            </div>
                            <br>
                          </div>
                          <div class="col-md-6 col-6">
                            <div class="form-group">
                              <label class="form-label" for="role">{{ __('admin.Rolefor') }}:</label>
                              <select id="role" name="role" class="select form-control">
                                <option value="all" {{ $documentation->role == 'all' ? 'selected' : '' }}>
                                  {{ __('admin.All') }}</option>
                                <option value="user" {{ $documentation->role == 'user' ? 'selected' : '' }}>
                                  {{ __('admin.Users') }}</option>
                                <option value="mentor" {{ $documentation->role == 'mentor' ? 'selected' : '' }}>
                                  {{ __('admin.Mentors') }}</option>
                                <option value="judge" {{ $documentation->role == 'judger' ? 'selected' : '' }}>
                                  {{ __('admin.Judgers') }}</option>
                                <option value="admin" {{ $documentation->role == 'admin' ? 'selected' : '' }}>
                                  {{ __('admin.Managers') }}</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="content_ar">{{ __('admin.Arabic Content') }}</label>
                              <textarea class="form-control" id="content_ar" rows="8" name="content_ar" required>
                                {!! $documentation->content_ar !!}
                              </textarea>
                              <br>
                            </div>
                          </div>
                          <div class="col-md-12 col-12 mb-6">
                            <div class="form-label-group">
                              <label class="form-label" for="content_en">{{ __('admin.English Content') }}</label>
                              <textarea class="form-control" id="content_en" rows="8" name="content_en" required>
                                {!! $documentation->content_en !!}
                              </textarea>
                            </div>
                          </div>
                          <div class="col-12 d-flex justify-content-between align-items-center">
                            <a id="back-button" href="#" role="button" aria-label="Go back" class="btn btn-label-secondary me-2">
                              <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
                              {{ __('admin.Back') }}
                            </a>
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('cycles.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->
@stop

@section('pagescripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    tinymce.init({
      selector: 'textarea',
      plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons',
      imagetools_cors_hosts: ['picsum.photos'],
      menubar: 'edit view insert format tools table help',
      toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview  print | insertfile image media template link anchor codesample | ltr rtl',
      toolbar_sticky: true,
      autosave_ask_before_unload: true,
      autosave_interval: '30s',
      autosave_prefix: '{path}{query}-{id}-',
      autosave_restore_when_empty: false,
      autosave_retention: '2m',
      image_advtab: true,
      importcss_append: true,
      template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
      template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
      height: 600,
      image_caption: true,
      quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
      noneditable_noneditable_class: 'mceNonEditable',
      toolbar_mode: 'sliding',
      contextmenu: 'link image imagetools table',
      content_style: 'body { font-family:Times New Roman,Arial,sans-serif; font-size:14px }',
      image_title: true,
      automatic_uploads: true,
      images_upload_url: "{{ route('trainings.files_upload', ['_token' => csrf_token()]) }}",
      file_picker_types: 'image',
      relative_urls: false,
      remove_script_host: true,
      convert_urls: true,
      file_picker_callback: function(cb, value, meta) {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.onchange = function() {
          var file = this.files[0];
          var reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = function() {
            var id = 'blobid' + (new Date()).getTime();
            var blobCache = tinymce.activeEditor.editorUpload.blobCache;
            var base64 = reader.result.split(',')[1];
            var blobInfo = blobCache.create(id, file, base64);
            blobCache.add(blobInfo);
            cb(blobInfo.blobUri(), {
              title: file.name
            });
          };
        };
        input.click();
      }
    });
    @if ($l == 'ar')
      tinyMCE.settings['directionality'] = 'rtl';
    @endif
  </script>
@stop
