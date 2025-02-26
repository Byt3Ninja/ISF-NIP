@extends('layouts.admin')
@section('title', __('admin.Projects') )
@section('index_url', route('projects.index') )
@section('content')
<!-- BEGIN: Content-->

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-body">
            <section id="basic-input">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('admin.edit_project') }}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <form action="{{ route('projects.update', $project->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>{{ __('admin.Name') }}</label>
                                                <input type="text" name="name" class="form-control" value="{{ old('name', $project->name) }}" required>
                                                @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12 mt-3">
                                                <label>{{__('admin.Description')}}</label>
                                                <textarea name="description" rows="5" class="form-control mt-2">{{ $project->description ?? '' }}</textarea>
                                            </div>

                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3">{{ __('admin.Save') }}</button>
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
@stop

<!-- END: Content-->

@section('pagescripts')
<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>

@stop
