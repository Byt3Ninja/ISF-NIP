@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      <div class="content-body">
        <h4 class="mb-4">{{__('settings.View training content')}}</h4>
        <section id="multiple-column-form">
          <div class="row match-height">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <div class="card-title"> {{ $content->title }}
                    <div class="float-right">
                      <a title="Edit" href="/dashboard/training/editcontent/{{ $content->id }}"
                        class="btn btn-icon rounded-circle btn-label-success">
                        <i class="ti ti-edit text-info"></i></a>

                      <a title="Back" href="/dashboard/training/all/{{ $content->training_id }}"
                        class="btn btn-icon rounded-circle btn-label-primary">
                        <i class="ti ti-arrow-back"></i></a>
                    </div>
                  </div>
                  <hr>
                </div>
                <div class="card-content">
                  <div class="card-body">
                    {!! $content->content !!}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- // Basic multiple Column BuilderForm section end -->
      </div>
    </div>
  </div>
  <!-- END: Content-->
@stop

@section('pagescripts')
  <script>
    //document.body.classList.add("menu-collapsed");
  </script>
@stop
