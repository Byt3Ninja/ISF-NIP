@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/dragula.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/plugins/extensions/drag-and-drop.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop
@section('content')

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2 mt-1">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h5 class="content-header-title float-left pr-1 mb-0">{{__('admin.Cycles')}}</h5>
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb p-0 mb-0">
                                <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a></li>
                                <li class="breadcrumb-item active">{{__('admin.Cycles`s List')}}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-header row">
            @if(session()->has('message'))
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible mb-2" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <div class="d-flex align-items-center"> <i class="bx bx-like"></i> <span> {{ __('admin.'.session()->get('message')) }} {{ Session::forget('message') }} </span> </div>
                    </div>
                </div>
            @endif
                @if(session()->has('message2'))
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissible mb-2" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="d-flex align-items-center">
                            <i class="bx bx-error"></i>
                            <span>
                                                {{ __('admin.'.session()->get('message2')) }}
                                            </span>
                        </div>
                    </div>
                    </div>
                @endif
        </div>

        <div class="content-body">
            <section class="organizations-list-wrapper">
                <div class="row pb-1">
                    <div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center" onclick="DoChange(2)">
                         <span class="btn btn-block btn-success align-middle ml-25"><i class="bx bx-star" ></i> {{__('cycles.New')}}</span>
                    </div>
                    
                <div class="btn btn-success ExportCycle">Export</div>
                </div>
                <div class="organizations-list-table">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <!-- datatable start -->
                                <div class="table-responsive">
                                    <table id="users-list-datatable" class="table">
                                        <thead>
                                        <tr class="table-light">
                                            <th>#</th>
                                            <th>{{__('cycles.Name')}}</th>
                                            <th>{{__('cycles.Program Start Date')}}</th>
                                            <th>{{__('cycles.Program End Date')}}</th>
                                            <th>{{__('cycles.Private Link')}}</th>
                                            <th>{{__('cycles.Number of joined Startup')}}</th>
                                            <th>{{__('cycles.Numbers of cycles')}}</th>
                                            <th>{{__('cycles.Organization Name')}}</th>
                                            <th>{{__('cycles.Organization logo')}}</th>
                                            
                                            <th>@can('edit_user'){{__('cycles.Edit')}}@endcan</th>
                                            <th>@can('delete_user'){{__('cycles.Delete')}}@endcan</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach( $cycles as $cycle )
                                        @php
                                        $totalSteps = app(\App\Step::class)->where("cycle_id","=", $cycle->id)->count();
                                        $totalcompany = app(\App\Company::class)->where("cycle","=", $cycle->id)->count();
                                        @endphp
                                        <tr>
                                            <td>{{$cycle->id}}</td>
                                            <td style="font-size: small;">{{ $cycle->title }}</td>
                                            <td style="font-size: small;">{{ $cycle->start }}</td>
                                            <td style="font-size: small;">{{ $cycle->end }}</td>
                                            <td style="font-size: small;">
                                                @if($cycle->private == null)
                                                    {{__('cycles.NA')}}
                                                @else
                                                <!-- Where to Redirect OR Take Copied ? -->
                                                <a target="_blank" href="cycles/{{ $cycle->private }}">اضغط هنا</a>
                                                @endif
                                            </td>
                                            <td style="font-size: small;"><a target="_blank" href="/dashboard/cycles/{{$cycle->id}}/registered">
                                                {{ $totalcompany }}</a></td>
                                            <td style="font-size: small;">{{$totalSteps}}</td>
                                                    @if(app()->getLocale() == 'ar')
                                                        <td style="font-size: small;">{{$cycle->organization->name_ar}}</td>
                                                    @else
                                                        <td style="font-size: small;">{{$cycle->organization->name_en}}</td>
                                                    @endif
                                            <td><img src="{{$cycle->organization->image_url}}" alt="Logo" class="" height="40" ></td>
                                            


                                    <td style="font-size: small;">
                                        <div class="btn btn-primary ccycle" set="1" do="{{$cycle->id}}" onclick="DoChange(1, {{$cycle->id}})">
                                             <span class="align-middle ml-25"><i class="bx bx-edit"></i>{{__('cycles.Edit')}}</span>
                                        </div>
                                    </td> 


                                    <td style="font-size: small;">
                                        @can('delete_user')
                                        <div class="btn btn-danger" onclick="DoChange(3, {{$cycle->id}})">
                                             <span class="align-middle ml-25"><i class="bx bx-trash"></i>{{__('cycles.Delete')}}</span>
                                        </div>
                                        @endcan
                                    </td> 
                                  

                                             
                                        </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<!--Cycle Modal -->
<div class="modal fade text-left" id="cycleForm" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel33" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form class="form" action= "{{URL::to('dashboard/cycles/modify')}}" method="POST" enctype="multipart/form-data" autocomplete="off">
                {!! csrf_field() !!}
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33">{{__('cycles.Cycle data')}}
                </h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>
                <div class="modal-body">
                    @if(is_null(auth()->user()->organization_id))
                        <label>{{__('builder.Choose Organization')}}: </label>
                        <div class="form-group ">
                            <select class="select form-control" id="organization_id" name="organization_id" required>
                                @foreach(\App\Organization::all() as $org)
                                    <option value="{{$org->id}}" >{{$org->name_ar}}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="organization_id" value="{{auth()->user()->organization_id}}" id="organization_id">
                    @endif
                    <label>{{__('cycles.Cycle title')}}: </label>
                    <div class="form-group">
                        <input type="hidden" name="type" id="type">
                        <input type="hidden" name="cycleid" id="cycleid">
                        <input id='title' name='title' type="text" placeholder="{{__('cycles.Title')}}" class="form-control" required>
                    </div>
                    <label>{{__('cycles.Valid from')}}: </label>
                    <div class="form-group">
                        <input id='start' name='start' type="text" placeholder="{{now()->format('Y-m-d')}}" class="form-control date" required>
                    </div>
                    <label>{{__('cycles.Valid to')}}: </label>
                    <div class="form-group">
                        <input id='end' name='end' type="text" placeholder="{{now()->addDays(15)->format('Y-m-d')}}" class="form-control date" required>
                    </div>
                    <label>{{__('cycles.Description')}}: </label>
                    <div class="form-group mb-0">
                        <textarea id='description' name='description' placeholder="{{__('cycles.Description')}}" class="form-control"></textarea>
                    </div>
                    <label> </label>
                    <div class="form-group mb-0 custom-switch">
                        <span>{{__('settings.Private')}}</span>&nbsp;
                        <input name="private" type="checkbox" class="custom-control-input" id="customSwitch3">
                        <label class="custom-control-label mr-1" for="customSwitch3"></label>
                        <a id="cyclelink" target="_blank"></a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('cycles.Close')}}</span>
                    </button>
                    <button type="submit" value="submit" class="btn btn-primary ml-1" >
                        <i class="bx bx-check d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">{{__('cycles.Submit')}}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('pagescripts')
    @php( $ccycle = \App\Cycle::find(0))
    <script src="/app-assets/js/scripts/pages/page-users.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
<script src="/app-assets/js/scripts/datatables/datatable.js"></script>
<script src="/app-assets/js/scripts/modal/components-modal.js"></script>

    <script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>
    <script src="/js/jquery.datetimepicker.full.min.js"></script>
    <script>
        $('.date').datetimepicker({timepicker:false,format:'Y-m-d'});
        $('#fromDate,#toDate,#mfromDate,#mtoDate').datetimepicker({format:'Y-m-d',
            minDate:'{{ !is_null($ccycle)?$ccycle->start->format('Y/m/d'):now()->format('Y/m/d') }}',
            maxDate:'{{ !is_null($ccycle)?$ccycle->end->format('Y/m/d'):now()->addDays(15)->format('Y/m/d') }}',
            timepicker:false});
        $(document).ready(function () {
            let drake =  dragula([document.getElementById('basic-list-group')]);
            drake.on('drop', function(el) {
                let badges = document.getElementsByClassName('carr');
                let ids = [];
                for (let i = 0; i<badges.length-1; ++i) {badges[i].innerText=i+1;let id = badges[i].id.split('-');ids.push(id[0]);}
                $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
                $.ajax({
                    type: "POST", url: "/dashboard/cycles/modify",
                    data: {
                        "steps": ids,
                        "type" : 4,
                    },
                    success: function (response) {
                        console.log(response);
                    },
                    error: function (error) {
                        console.log(error);
                        alert("Error Occurred, arrange failed");
                    }});
            })});

       

        function DoChange(set, cycle) {
           
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            switch (set) {
                case 1:
                    $.ajax({
                        type: "POST", url: "/dashboard/cycles/modify",
                        data: {
                            "cycleid": cycle,
                        },
                        success: function (response) {
                            $('#title').val(response.title);$('#description').val(response.description);
                            $('#cycleid').val(cycle);$('#type').val(1);$('#start').val(response.start);$('#end').val(response.end);
                            $('#organization_id').val(response.organization_id)
                            if (response.private) {$('#customSwitch3').attr('checked','checked');$('#cyclelink').html('{{__('cycles.Join URL')}}');$('#cyclelink').attr('href', '/dashboard/cycles/view/' + response.id + '?id=' + response.private);}
                            $('#cycleForm').modal('show');
                        },
                        error: function (error) {
                            console.log(error);
                            alert("Error Occurred, Try again!");
                        }
                    });
                    break;
                case 2:
                    $('#title').val('');$('#description').val('');
                    $('#cycleid').val('');$('#type').val(2);$('#start').val('');$('#end').val('');
                    $('#customSwitch3').removeAttr('checked');
                    $('#cycleForm').modal('show');
                    break;
                case 3:
                    if (confirm('{{__('cycles.sure delete cycle')}}?')) {
                    if (confirm('{{__('cycles.cant undo')}}'))
                    {$.ajax({
                            type: "POST", url: "/dashboard/cycles/modify",
                            data: {
                                "cycleid": cycle, "type" : 3,
                            },
                            success: function (response) {
                                console.log(response);
                                location.href = '/dashboard/programlist';
                                // location.reload();
                            },
                            error: function (error) {
                                console.log(error);
                                alert("Error Occurred, Try again!");
                        }});}}
                    break;
                case 4:
                    let step = $('#StepSelect').find(":selected").val();
                    let steptxt = $('#StepSelect').find(":selected").text();
                    let menu = $('#menu'+step);
                    $('#menu1,#menu2').hide();
                    if (menu !== null) {
                        $.ajax({
                            url: '{{ url("dashboard/builder/organization/")  }}'+'/'+$("#ccycle option:selected").data('organization'),
                            success: function (response) {
                                $('#FormSelect').empty()
                                $('#FormSelect').append(response)
                                menu.show();
                            },
                            error: function (error) {
                                console.log(error);
                                alert("Error Occurred, Try again!");
                            }
                        });

                    }
                    if (step==='5'){
                        $.ajax({
                            url: '{{ url("dashboard/builder/organization/")  }}'+'/'+$("#ccycle option:selected").data('organization'),
                            success: function (response) {
                                $('#FormSelect').empty()
                                $('#FormSelect').append(response)
                                $('#menu1').show();
                            },
                            error: function (error) {
                                console.log(error);
                                alert("Error Occurred, Try again!");
                            }
                        });
                    }
                    $('#stype').val(step);
                    $('#formtitle').text(steptxt);
                    $('#stepForm').modal('show');
                break;
            }}
        function ModifyStep(set,id) {
            var cycle = $('.ccycle').attr('do');
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
            switch (set) {
                case 1:
                    $.ajax({
                        type: "POST", url: "/dashboard/cycles/modify",data: {"type" : 7,"sid" : id,"cycleid" : 0,},
                        success: function (response)
                        {
                            step = response.stype;
                            let menu = $('#mmenu'+step);
                            $('#mmenu1,#mmenu2').hide();
                            if( response.data !== null )
                            {
                                if( step == 1 || step == 5 ) { $('#mFormSelect').val( response.data ); }
                                if( step == 2 ) { $('#mScreeningSelect').val( response.data ); }
                            }
                            if( menu !== null) {
                                $.ajax({
                                    url: '{{ url("dashboard/builder/organization/")  }}'+'/'+$("#ccycle option:selected").data('organization'),
                                    success: function (response) {
                                        $('#mFormSelect').empty()
                                        $('#mFormSelect').append(response)
                                        menu.show();
                                    },
                                    error: function (error) {
                                        console.log(error);
                                        alert("Error Occurred, Try again!");
                                    }
                                });

                            }
                            if( step == 5 ) {
                                $.ajax({
                                    url: '{{ url("dashboard/builder/organization/")  }}'+'/'+$("#ccycle option:selected").data('organization'),
                                    success: function (response) {
                                        $('#mFormSelect').empty()
                                        $('#mFormSelect').append(response)
                                        $('#mmenu1').show();
                                    },
                                    error: function (error) {
                                        console.log(error);
                                        alert("Error Occurred, Try again!");
                                    }
                                });

                            }

                            $('#sid').val(response.id);
                            $('#mtitle').val(response.title);$('#mdescription').val(response.description);
                            $('#mfromDate').val(response.from);$('#mtoDate').val(response.to);
                            $('#ModifyStepForm').modal('show');
                        },
                        error: function (error) {
                            console.log(error);
                            alert("Error Occurred, Try again!");
                        }
                    });
                    break;
            }}
            function deleteStep (id) {
                $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
                if (confirm('{{__('cycles.sure delete step')}}?'))
                {
                 
                    let badges = document.getElementsByClassName('carr');
                    
                    let ids = [];
                    for (let a = 0; a<badges.length; ++a) {badges[a].innerText=a+1;let id = badges[a].id.split('-');ids.push(id[0]);}
                    $.ajax({
                        type: "POST", url: "/dashboard/cycles/modify",
                        data: {"stepid": id,"steps": ids, "type" : 6,},
                        success: function (response) {
                            console.log(response);
                            // location.href = '/dashboard/cycles/'+$('#ccycle').find(":selected").val();
                            location.reload();
                        },
                        error: function (error) {
                            console.log(error);
                            alert("Error Occurred, Try again!");
                        }
                    });
                }}
    </script>
@stop