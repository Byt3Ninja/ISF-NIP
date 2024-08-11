@extends('layouts.admin')
@section('bodyclass')
    calendar-application
@stop
@section('content')
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css'/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
    @if ($l=='ar')
        <style>
            .fc-left {
                direction: rtl;
            }

            .toast-top-right {
                right: auto;
                left: 12px;
            }
        </style>
    @endif
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                @if( session()->has('success') )
                    <div class="alert alert-success alert-dismissible mb-2" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <div class="d-flex align-items-center">
                            <i class="bx bx-like"></i>
                            <span> {{ session()->get('success') }} </span>
                        </div>
                    </div>
                @endif

                @if( session()->has('error') )
                    <div class="alert alert-danger alert-dismissible mb-2" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <div class="d-flex align-items-center">
                            <i class="bx bx-error"></i>
                            <span> {{ session()->get('error') }} </span>
                        </div>
                    </div>
                @endif

                @if(session()->has('msg'))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-success  mb-2" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-like"></i>
                                    <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif(session()->has('message2'))
                    <div class="alert alert-danger alert-dismissible mb-2" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <div class="d-flex align-items-center">
                            <i class="bx bx-error"></i>
                            <span> {{ __('admin.'.session()->get('message2')) }} </span>
                        </div>
                    </div>
                @endif
                <div class="calendar-wrapper position-relative">
                    <div class="card">
                        <div class="card-body">
                            <div id='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->
    <div class="modal fade text-left" id="viewevent" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="title"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="body"></div>
                    <br>
                    <h7 id="start"></h7>
                    <h7 id="end"></h7>
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
{{--    @if ($l=='ar')--}}
{{--        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/ar.js"></script>--}}
{{--    @endif--}}
    <script>
        $(document).ready(function () {
            var SITEURL = "{{ url('/') }}";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var calendar = $('#calendar').fullCalendar({
                customButtons: {
                    google: {
                        text: 'Sync With Google Calender',
                        click: function () {
                            /// add
                            window.open('{{ route('calender.login.google') }}','_Self')
                            //// end
                        }
                    },
                    microsoft: {
                        text: 'Sync With Microsoft Calender',
                        click: function () {
                            /// add
                            window.open('{{ route('calender.login.microsoft') }}','_Self')
                            //// end
                        }
                    },

                },

                header: {
                    left: 'prev,next today google microsoft',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay '
                },

                events: SITEURL + "/dashboard/calendar",
                eventTextColor: '#0c0c0c',
                displayEventTime: true,
                editable: false,
                eventRender: function (event, element, view) {

                    if (event.isallDay === true) {
                        event.allDay = true;
                    } else {
                        event.allDay = false;
                    }
                },
                selectable: true,
                selectHelper: true,
                eventClick: function (event) {
                    switch (event.calendarId) {
                        case 'M':
                            openmodal(event,'{{ trans('admin.mentorship request between')  }}');
                            break;
                    }
                }

            });
        });

        function openmodal(event,title) {

                $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
                $.ajax({
                    type: "POST",
                    url: "{{ route('calendarAjax') }}",
                    data: {
                        "type" : event.calendarId,
                        "id" : event.id
                    },
                    success: function (response) {
                        $('#title').html(title);
                        $('#body').html(response.body);
                        $('#start').html("<b>{{__('admin.Start')}}: </b> "+ response.start );
                        $('#end').html("<b>{{__('admin.End')}}: </b> "+ response.end);
                        $('#viewevent').modal('show');
                    },
                    error: function (error) {
                        console.log(error);
                        alert("Error Occurred, Try again!");
                    }
                });

        }
    </script>
@stop