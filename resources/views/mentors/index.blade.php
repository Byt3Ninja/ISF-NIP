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
@endphp

@section('content')
@if (!Auth::user()->first_name_ar)
<div class="d-none">
  {!! redirect()->route('user.profile_view')->with(['msg' => 'WaitingYourRedirect', 'class' => 'danger']) !!}
</div>
@endif

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-body">
      @if (session()->has('msg'))
        <x-alert 
          type="{{ session()->get('class') ?? 'danger' }}" 
          message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" 
        />
      @endif

      {{-- Tabs Navigation --}}
      <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
          <a 
            class="nav-link {{ $currentRoute === 'mentors.list' ? 'active' : '' }}" 
            href="{{ route('mentors.list') }}"
          >
            {{ __('admin.allMentors') }}
          </a>
        </li>
        <li class="nav-item">
          <a 
            class="nav-link {{ $currentRoute === 'mentors.invitations' ? 'active' : '' }}" 
            href="{{ route('mentors.invitations') }}"
          >
            {{ __('admin.Invitations') }}
          </a>
        </li>
      </ul>

      @include('mentors.partials.header')

      @include('mentors.partials.mentor-list')
    </div>
  </div>
</div>

@include('mentors.partials.filter-modal')
@stop

@section('pagescripts')
<script>
$(function () {
  // Debounce helper
  const debounce = (fn, delay = 1000) => {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  };

  // Create debounced handler ONCE
  const handleSearch = debounce((value) => {
    const url = new URL(window.location);
      value
      ? url.searchParams.set('search', value)
      : url.searchParams.delete('search');

      window.location.href = url.href;
    }, 1000);

  // Bind event
  $('#searchInput').on('keyup', function () {
    handleSearch(this.value.toLowerCase());
  });
});
</script>
@stop