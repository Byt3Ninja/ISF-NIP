@extends('layouts.admin')
@section('title', __('admin.Services Categories') )
@section('index_url', route('serviceCategories.index') )
@section('subTitle', __('admin.Add new service category'))
@php
    $user = auth()->user();
    $isOrgMan = $user->hasRole('organizationManager');
    $org_id = $user->organization_id ?? 0;
@endphp
@section('content')
<!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
        
        <div class="content-body">
            <!-- BuilderForm start -->
            <section id="basic-input">
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8 col-lg-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{__('admin.Add new service category')}}</h5>
                            </div>
                            <div class="card-body">
                                @if(session()->has('msg'))
                                    <div class="alert alert-warning alert-danger alert-dismissible fade show mb-3" role="alert">
                                        <i class="ti ti-error me-2"></i>
                                        <span>{{ __('admin.'.session()->get('msg')) }}</span>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        {{ Session::forget('msg') }}
                                    </div>
                                @endif
                                 <!-- Validation messages -->
                                 @if(count($errors) >0)
                                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $error)
                                        <li>{{ $error }} </li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                @endif
                                <form method="post" action="{{route('serviceCategories.store')}}">
                                    {!! csrf_field() !!}
                                    @php
                                        $authUser = auth()->user();
                                        $isSingleOrg = $isOrgMan || ($authUser->isSubAdmin() && count($authUser->organizations_ids) == 1);
                                        $showOrgDropdown = $authUser->hasRole('Admin') || ($authUser->isSubAdmin() && count($authUser->organizations_ids) > 1);
                                    @endphp
                                    <div class="mb-3 {{ $isSingleOrg ? 'd-none' : '' }}">
                                        <label for="organization_id" class="form-label">{{ __('cycles.Select_Organization')}}</label>
                                        <select {{ $authUser->hasRole('Admin') || ($authUser->isSubAdmin() && count($authUser->organizations_ids) > 1) ? '' : 'required' }} class="form-select" id="organization_id" name="organization_id" data-placeholder="{{ __('cycles.Select_Organization') }}">
                                            @if( $showOrgDropdown ) <option selected disabled value="">{{ __('cycles.Select_Organization') }}</option> @endif
                                            @if($authUser->hasRole('Admin'))
                                                <option value="all">{{ __('admin.All_Organizations') }}</option>
                                                @foreach( \App\Organization::all() as $org )
                                                    <option value="{{ $org->id }}">{{ $org->{'name_' . app()->getLocale()} }}</option>
                                                @endforeach
                                            @elseif($authUser->isSubAdmin())
                                                <option value="all">{{ __('admin.All_Organizations') }}</option>
                                                @foreach( $authUser->organizations as $org )
                                                    <option value="{{ $org->id }}">{{ $org->{'name_' . app()->getLocale()} }}</option>
                                                @endforeach
                                            @else
                                                <option selected value="{{ $org_id }}">{{ \App\Organization::find($org_id)?->{'name_' . app()->getLocale()} }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="name_en" class="form-label">{{ __('admin.English Name')}}</label>
                                        <input style="direction: ltr;" type="text" class="form-control" id="name_en" name='name_en'
                                            required placeholder="{{__('admin.English Name')}}"
                                            value="{{ old('name_en') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="name_ar" class="form-label">{{__('admin.Arabic Name')}}</label>
                                        <input style="direction: rtl;" type="text" class="form-control" id="name_ar" name='name_ar'
                                            required placeholder="{{__('admin.Arabic Name')}}"
                                            value="{{ old('name_ar') }}">
                                    </div>
                                    @if( $_user->ableToAny(['serviceCategories.create', 'serviceCategories.store'], $permissions) )
                                    <div class="d-flex justify-content-end mt-8">
                                        <button type="submit" class="btn btn-primary">{{__('admin.Save')}}</button>
                                    </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- BuilderForm end -->
        </div>
    </div>
</div>
<!-- END: Content-->
@stop
