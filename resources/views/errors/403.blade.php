@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message')
<div>{!! __($exception->getMessage() ?: 'Forbidden') !!}</div>
@stop
