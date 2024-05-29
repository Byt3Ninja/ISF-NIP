@extends('layouts.admin')
@section('title', __('admin.Profile') )
@section('subTitle', __('admin.Edit your profile information'))
@section('vendorstyle')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/swiper.min.css">
@stop
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/dashboard-analytics.css">
@stop
@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">

                <section id="basic-input">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">{{__('admin.Reports')}}</h4>
                                    <hr>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        @if(session()->has('msg'))
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="alert alert-{{Session::get('class')}} alert-dismissible  mb-2"
                                                         role="alert">
                                                        <button type="button" class="close" data-dismiss="alert"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-error"></i>
                                                            <span>
                                                        {{ __('admin.'.session()->get('msg')) }}
                                                                {{ Session::forget('msg') }}
                                                    </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <!-- Validation messages -->
                                        @if(count($errors) >0)
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="alert alert-danger">
                                                        <ul>
                                                            @foreach($errors->all() as $error)
                                                                <li>{{ $error }} </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif


                                        <div class="col-md-12 col-12">
                                            <div class="form-group">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <h6>{{__('admin.Select Report Kind')}}</h6>
                                                        <p></p>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="type"
                                                                               value="mentorship"
                                                                               id="rad0" class="type">
                                                                        <label for="rad0">{{__('admin.Mentorship')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="type" value="training"
                                                                               id="rad1" class="type">
                                                                        <label for="rad1">{{__('admin.Training sessions')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="type" value="cycles"
                                                                               id="rad2" class="type">
                                                                        <label for="rad2">{{__('admin.Cycles')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="type"
                                                                               value="companies"
                                                                               id="rad3" class="type">
                                                                        <label for="rad3">{{__('admin.Companies')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
{{--                                        <div class="col-md-12 col-12">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <div class="card-content">--}}
{{--                                                    <div class="card-body">--}}
{{--                                                        <h6>{{__('admin.SelState')}}</h6>--}}
{{--                                                        <p>{{__('admin.SelMultiState')}}</p>--}}
{{--                                                        <div class="form-group">--}}
{{--                                                            <select name="state[]" class=" form-control select2">--}}
{{--                                                             </select>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
                                    </div>

                                    <div class="row">
                                        <div class="col-12 d-flex justify-content-center">
                                            <button type="button" id="showReport" class="btn btn-success mr-1 mb-1">{{__('admin.ViewReport')}}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="reportContent">

                </section>

            </div>
        </div>
    </div>
    <!-- END: Content-->
@stop
@section('scripts')
    <script src="/app-assets/js/core/dashboards-analytics.js"></script>
    <script src="/app-assets/vendors/js/charts/apexcharts.min.js"></script>
    <script>
        $(document).ready(function (){
            $('#showReport').click(function (){

                $.ajax({
                    url: "{{ route('reports.view.ajax') }}/"+$('.type:checked').val(),
                    success: function(data) {
                        if (data['value'] === 1) {
                            $('#reportContent').html(data['view']);
                        }
                    }
                });
            });
        });
    </script>

@stop


