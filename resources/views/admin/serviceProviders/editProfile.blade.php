@extends('layouts.admin')
@section('title', __('admin.edit_service_provider'))
@section('subTitle', $provider->company_name)

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
@stop

@section('style')
<style>
  #phone, #whatsapp {
    width: 100%;
  }

  @media screen and (min-width: 768px) {
    #phone, #whatsapp {
      width: 350px;
    }
  }
  .iti__country-list {
    position: absolute;
    left: 1rem;
    z-index: 9999;
    list-style: none;
    text-align: left;
    padding: 0;
    margin: 0 0 0 -1px;
    box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
    background-color: white;
    border: 1px solid #CCC;
    white-space: nowrap;
    max-height: 200px;
    width: 350px;
    overflow-y: scroll;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    background-color: #fff;
  }
</style>
@stop

@php
$serviceProviderSteps = [
  ['id' => 'basics', 'title' => __('admin.profile_basics'), 'subtitle' => __('admin.provider_basics_subtitle')],
  ['id' => 'booking', 'title' => __('admin.intro_booking'), 'subtitle' => __('admin.provider_booking_subtitle')],
  ['id' => 'stats', 'title' => __('admin.key_stats'), 'subtitle' => __('admin.provider_stats_subtitle')],
  ['id' => 'portfolio', 'title' => __('admin.portfolio'), 'subtitle' => __('admin.provider_portfolio_subtitle')],
  ['id' => 'clients', 'title' => __('admin.clients'), 'subtitle' => __('admin.provider_clients_subtitle')]
];

$serviceProviderId = $provider->id;
$save_redirect_url = $_user->hasRole('serviceProvider') ? '/dashboard/show/myProfile' : '/dashboard/services_providers';
@endphp

@section('content')
<div class="">
  <div class="">
    @if (session()->has('success'))
      <x-alert type="success" message="{{ session('success') }}" />
    @endif
    @if (session()->has('error'))
      <x-alert type="danger" message="{{ session('error') }}" />
    @endif
    @if (count($errors) > 0)
      @foreach ($errors->all() as $error)
        <x-alert type="danger" message="{{ $error }}" />
      @endforeach
    @endif

    <div class="card p-0 shadow-none">
      @include('admin.clients.profile.partials._service_provider_wizard')
    </div>
  </div>
  <div class="content-backdrop fade"></div>
</div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
<script src="/vuexy/assets/js/service-provider-wizard.js"></script>
<script>
  const lang = $('html').attr('lang') || 'en';
  function submitFormData() {
    releaseAlert('success', {
      title: lang === 'ar' ? 'تم!' : 'Done!',
      message: lang === 'ar' ? 'تم حفظ الملف الشخصي بنجاح' : 'Profile saved successfully',
    });
    setTimeout(() => { window.location.href = '{{ $save_redirect_url }}'; }, 1500);
  }
</script>
@stop
