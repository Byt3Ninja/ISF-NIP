@extends('layouts.admin')
@section('style')
    <style>
        .section {
            padding: 0 30px;
        }
        .bg-light {
            background-color: #92989e2e !important;
        }
        .bg-light .card-header, .bg-light .card-footer {
            background-color: #32181817;
            font-size: 1.2em;
        }
        hr{
            margin: 0 0 15px;
        }
    </style>
@stop
@section('title', __('admin.Builder') )
@section('subTitle', __('admin.Edit form'))
@section('content')
<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-body">
            <section id="kanban-wrapper">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header" style="padding-bottom: 15px;">
                                <h4 class="card-title">{{__('builder.Forms builder')}}</h4>
                            </div>
                            <hr>
                            <div class="card-body">
                                <span>{{__('builder.Select from')}}:</span>
                                <fieldset class="form-group">
                                    <div style="width: 70%;float: {{$l=='ar'?'right':'left'}};">
                                    <select class="select2 form-control" id="FormSelect"  onchange="window.open('/admin/show-builder/'+this.options[this.selectedIndex].value,'_self');">
                                        @foreach($builderForms as $form)
                                        <option value="{{$form->id}}" {{$title->id==$form->id?"selected":""}}>
                                            {{ $form->form_name }} {{ !is_null($form->organization)?' - '.$form->organization->name:'' }}
                                            @if( $form->cycles->count() )

                                                @foreach( $form->cycles as $cycle )
                                                {{ $cycle->title }} @if( !$loop->last ) - @endif
                                                @endforeach

                                            @endif
                                        </option>
                                        @endforeach
                                    </select></div>
                                    <span>
                                    <button class="btn btn-primary" onclick="showForm(0,{{$title->id}},'{{$title->form_name}}',{{ $title->organization_id }})">
                                        <i class="bx bx-edit"></i> <span class="align-middle ml-25">{{__('builder.Edit')}}</span>
                                    </button>
                                    <button class="btn btn-info" onclick="showForm(1,{{$title->id}},'{{$title->form_name}} {{__('builder.Copy.')}}')">
                                        <i class="bx bx-copy" ></i> <span class="align-middle ml-25">{{__('builder.Copy')}}</span>
                                    </button>

                                    <button class="btn btn-success" onclick="showForm(2,0,null)">
                                        <i class="bx bx-star" ></i> <span class="align-middle ml-25">{{__('builder.New')}}</span>
                                    </button>
                                    <button class="btn btn-danger" onclick="DoChange(3,{{$title->id}},null)">
                                        <i class="bx bx-trash"></i> <span class="align-middle ml-25">{{__('builder.Delete')}}</span>
                                    </button>
                                    </span>
                                </fieldset>
                            </div>
                        </div>
                    </div>
            <div class="col-xl-12 col-md-12 col-sm-12">
                <div class="card">
                        <div class="card-header" style="padding-bottom: 0px;">
                            <p>{{__('builder.Drag')}}<a href="/dashboard/show-form/{{$title->id}}"> ({{__('builder.Preview')}})</a>.</p>
                        </div>
                    <hr>
                    <div class="section">
                        <div class="row" id="builder-app"></div>
                        <div id="modal-root"></div>
                    </div>
                </div>
            </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!--Modify custom form step Modal -->
<div class="modal fade text-left" id="AddForm" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel33" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel33">{{__('builder.new form')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            <div class="modal-body">
                @if(is_null(auth()->user()->organization_id))
                    <div class="mb-2" id="mmenu1">
                        <label>{{__('builder.Choose Organization')}}: </label>
                        <div class="form-group mb-0">
                            <select class="select form-control" id="organization_id" name="organization_id" required>
                                @foreach(\App\Organization::all() as $org)
                                    <option value="{{$org->id}}" >{{$org->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="organization_id" value="{{auth()->user()->organization_id}}" id="organization_id">
                @endif

                    <label>{{__('builder.new form')}}: </label>
                    <div class="form-group">
                        <input id='mtitle' name='title' type="text" placeholder="{{__('cycles.Title')}}" class="form-control" required>
                    </div>

                </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('cycles.Close')}}</span>
                    </button>
                    <button type="button" class="btn btn-primary ml-1" id="modelSubmit" >
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('cycles.Save')}}</span>
                    </button>
                </div>
        </div>
    </div>
</div>
@stop
@section('pagescripts')
    <script>
        document.body.classList.add("menu-collapsed");

        function showForm(set,id , title , organization_id){

            $('#mtitle').val(title);
            if(typeof(organization_id) != "undefined"){
                $('#organization_id').val(organization_id)
            }
            $('#modelSubmit').attr('onclick',`DoChange(${set},${id},'${title}',${organization_id})`)
            $('#AddForm').modal('show')
        }

        function DoChange(set, id, title,organization_id=0) {
            if (set===0) {
                title=$('#mtitle').val();
                organization_id= $('#organization_id').val();
                {{--title = prompt("{{__('builder.new title')}}",title);--}}
            } else if (set===1) {
                title=$('#mtitle').val();
                organization_id= $('#organization_id').val();
{{--                title = prompt("{{__('builder.new copied')}}:", title + ' {{__('builder.Copy.')}}');--}}
            } else if (set===2) {
                {{--title = prompt("{{__('builder.new form')}}");--}}
                title=$('#mtitle').val();
                organization_id= $('#organization_id').val();
            }  else if (set===3) {
                title = confirm("{{__('builder.deleting form')}}?")
            }
            if (!title) {return false;}
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: "/admin/builder/" + set,
                data: {
                    "id": id,
                    "title" : title,
                    "organization_id" : organization_id
                },
                success: function (response) {
                    // console.log(response);
                    location.href = '/admin/show-builder/'+  response;
                },
                error:function(error){
                    // console.log(error);
                    alert("Error Occurred, Try again!");
                }
            });
        }
        window._rav = window._rav || {};
        _rav.save_route = "{{ '/admin/form/'.$title->id.'/save-form' }}";
        _rav.boardData = @json($builder_data);
    </script>
    <script src="{{ asset('js/formbuilder.js') }}"></script>
@stop