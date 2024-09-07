@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/skins/ui/oxide/content.min.css">
@stop
@section('content')
@php($con = \App\Training::where('id',$contents->pluck('training_id')[0])->first())
<!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2 mt-1">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h5 class="content-header-title float-left pr-1 mb-0">{{__('settings.View training content')}}</h5>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb p-0 mb-0">
                                    <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a></li>
									<li class="breadcrumb-item active">{{$ccontent->title}}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="multiple-column-form">
                    <div class="row match-height">
                        <div class="col-md-3 col-sm-12">
                            <div class="card ">
                                <div class="card-header">
                                    <h4 class="card-title">
                                        <br>
                                        <img width="250px" src="/191014/{{$con->image}}">
                                        <br><br>
                                        <h1>{{ $con->title  }}</h1>
                                        <br>
                                        {{ $con->dsc }}
                                    </h4>
                                </div>
                                <div class="card-content pt-2">
                                    <div class="card-body">
                                        <div class="accordion" id="trainingControllers">
                                            <div class="card my-0">
                                                <div class="card-header px-0">
                                                    <h2 class="mb-0">
                                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#contentsContainer" aria-expanded="true" aria-controls="contentsContainer">
                                                            {{__('admin.Training courses')}}
                                                        </button>
                                                    </h2>
                                                </div>
                                          
                                                <div id="contentsContainer" class="collapse show" data-parent="#trainingControllers">
                                                    <div class="card-body py-1">
                                                        <ul class="widget-timeline mb-0">
                                                            @foreach($contents as $key=>$content)
                                                                <li class="timeline-items timeline-icon-{{$ccontent->id==$content->id?'primary':'secondary' }} active">
                                                                    <a href="/dashboard/training/my/{{$content->training_id}}?c={{$content->id}}">
                                                                        <div class="timeline-time">
                                                                            @if ($content->done())
                                                                                <i title="Completed {{$content->done()}}" class="bx bxs-check-circle primary"></i>
                                                                            @endif
                                                                        </div>
                                                                    <h6 class="timeline-title {{$ccontent->id==$content->id?'text-bold-600 primary':''}}">{{$key+1}} - {{$content->title}}</h6>
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card my-0">
                                                <div class="card-header px-0">
                                                    <h2 class="mb-0">
                                                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#quizContainer" aria-expanded="false" aria-controls="quizContainer">
                                                            {{__('admin.quizzes')}}
                                                        </button>
                                                    </h2>
                                                </div>
                                                <div id="quizContainer" class="collapse" data-parent="#trainingControllers">
                                                    <div class="card-body py-1">
                                                        <ul class="widget-timeline mb-0">
                                                            @foreach($quizzes as $quiz)
                                                                @php( $myAttempts = $quiz->myAttempts )
                                                                @php( $failed_attempts = $myAttempts->where('score', '<', $quiz->passing_criteria)->count() )
                                                                <li class="timeline-items timeline-icon-primary">
                                                                    <a href="/dashboard/training/my/{{ $content->training_id }}/quiz/{{ $quiz->id }}">
                                                                        <div class="timeline-time">
                                                                            @if( $myAttempts->count() == 0 )
                                                                                <i data-toggle="tooltip" data-placement="top" title="Not started" class="bx bxs-info-circle light"></i>
                                                                            @elseif( $myAttempts->where('score', '>=', $quiz->passing_criteria)->count() > 0 )
                                                                                <i data-toggle="tooltip" data-placement="top" title="Passed" class="bx bxs-check-circle success"></i>
                                                                            @elseif( $failed_attempts >= $quiz->attempts )
                                                                                <i data-toggle="tooltip" data-placement="top" title="Failed" class="bx bxs-x-circle danger"></i>
                                                                            @else
                                                                                <i data-toggle="tooltip" data-placement="top" title="Failed {{ $failed_attempts }} time(s) out of {{ $quiz->attempts }} attempts" class="bx bxs-error warning"></i>
                                                                            @endif
                                                                        </div>
                                                                        <h6 class="timeline-title text-bold-600 primary">{{$quiz->title}}</h6>
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                          </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9 col-sm-12">
                            <div class="card ">
                                <div class="card-header">
                                    <h4 class="card-title"></h4>
                                </div>
                                <div class="card-content p-75">{!! $ccontent->content !!}</div>
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
    <script>
        document.body.classList.add("menu-collapsed");
        $(function () { $('[data-toggle="tooltip"]').tooltip() });
    </script>
@stop
