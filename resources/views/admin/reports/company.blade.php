@extends('layouts.admin')
@section('title', __('admin.Profile') )
@section('subTitle', __('admin.Edit your profile information'))
@section('vendorstyle')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/swiper.min.css">
@stop
@section('style')

    <link rel="stylesheet" type="text/css" href="/app-assets/css/pages/page-user-profile.css">
@stop
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            @include('admin.includes.heading')
                <div class="content-body">
                    <section>
                        <div class="row">
                            <section>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header border-bottom">
                                                <div class="media align-items-center mt-2">
                                                    <div class="media-left mr-50">
                                                        <img src="/app-assets/images/logo_placeholder.png" alt="avatar" class="rounded-circle" height="40" width="40">
                                                    </div>
                                                    <div class="media align-items-center">
                                                        {{Auth::user()->company->name }}
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="card-body">
                                                <div class="row">

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </section>
                </div>
            </div>


            <div class="content-backdrop fade"></div>
        </div>

    <!-- END: Content-->
@stop
@section('scripts')
    <script src="/app-assets/js/core/dashboards-analytics.js"></script>
    <script src="/app-assets/vendors/js/charts/apexcharts.min.js"></script>

@stop


