@extends('layouts.admin')
@section('content')
    <style>
        .users-view {
            padding: 0.5rem 0;
        }
        .table-borderless td {
            padding: 0.8rem  ;
        }
    </style>
    <!-- BEGIN: Content .users-view .table-borderless td -->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-body">
                <!-- users view start -->
                <section class="users-view">
                    <!-- users view media object ends -->
                    <div class="row">
                        <div class="col-md-12">
                            <!-- users view card details start -->
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="col-12">
                                            <h5 class="mb-1"><i class="bx bx-info-circle"></i> {{__('mentor.mentorshipTasks.show')}}</h5>
                                            <hr>
                                            <form class="form-horizontal form-bordered" id="basicForm" action= "{{ route('startup.mentorshipTasks.complete',['mentorshipTask'=>$task->id]) }}" method="POST" enctype="multipart/form-data">
                                                {!! csrf_field() !!}
                                                <div class="form-body">
                                                    <table class="table table-borderless table-hover">
                                                        <tbody>
                                                        <tr>
                                                            <td class="width-200">{{__('mentor.mentorshipTasks.title')}}:</td>
                                                            <td>{{ $task->title }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{__('mentor.mentorshipTasks.status')}}:</td>
                                                            <td>
                                                                {{ $task->status =='pending'?__('No'):__('Yes') }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{__('mentor.mentorshipTasks.description')}}:</td>
                                                            <td >{!! $task->description !!}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>{{__('mentor.mentorshipTasks.reply')}}:</td>
                                                            <td>
                                                                @if($task->status =='pending')
                                                                <div class="form-label-group">
                                                                    <textarea class="form-control" id="description"  name="reply" required>
                                                                    {!! $task->reply !!}
                                                                    </textarea>
                                                                </div>
                                                                @else
                                                                    {!! $task->reply  !!}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    @if($task->status =='pending')
                                                        <div class="col-12 d-flex justify-content-end">
                                                            <button type="submit" class="btn btn-success mr-1 mb-1">{{__('settings.Complete')}}</button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- users view card details ends -->
                        </div>
                    </div>
                </section>
                <!-- users view ends -->
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
            images_upload_url: "{{ route('training.upload', ['_token' => csrf_token() ])}}",
            file_picker_types: 'image',
            relative_urls : false,
            remove_script_host : true,
            convert_urls : true,
            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.onchange = function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = function () {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);
                        cb(blobInfo.blobUri(), { title: file.location });
                    };
                };
                input.click();
            }
        });
        @if($l=='ar')
            tinyMCE.settings['directionality'] = 'rtl';
        @endif
    </script>
@stop