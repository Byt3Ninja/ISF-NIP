@extends('layouts.admin')

@section('title', __('dashboard.Mentors'))
@section('subTitle', __('dashboard.Mentors'))

@php
$user = auth()->user();
$company = $user->company;

$canRequestMentorship =
  $company &&
  $company->cycle !== 0 &&
  (
    $user->hasRole('Registered') ||
    (!$user->hasRole('Mentor') && !$user->hasRole('Admin'))
  );
  
if ($canRequestMentorship) {
  $getCycle = auth()->user()->company->cycledata;
}

$currentRoute = \Route::currentRouteName();

$tabs = [
  ['title' => __('admin.allMentors'), 'route' => 'mentors.list', 'icon' => 'users'],
  ['title' => __('admin.invitations'), 'route' => 'invitations.index', 'icon' => 'inbox'],
];
@endphp

@section('content')
@if (!Auth::user()->first_name_ar)
<div class="d-none">
  {!! redirect()->route('user.profile_view')->with(['msg' => 'WaitingYourRedirect', 'class' => 'danger']) !!}
</div>
@endif

<div class="">
  <div class="">
    <div class="content-body">
      @if (session()->has('msg'))
        <x-alert 
          type="{{ session()->get('class') ?? 'danger' }}" 
          message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" 
        />
      @endif

      {{-- Tabs Navigation --}}
      @if ($_user->hasAnyRole('Admin|organizationManager|subadmin'))
        <x-navigation-pills :tabs="$tabs" />
      @endif

      @include('mentors.partials.header')

      @include('mentors.partials.mentor-list')
    </div>
  </div>
</div>

@include('mentors.partials.filter-modal')
@stop
