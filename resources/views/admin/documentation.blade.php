@extends('layouts.admin')

@section('content')
<div class="">
  <div class="">
    <div class="content-body">
      <!-- Overview -->
      <div class="card">
        <div class="card-header">
          @if ($doc->id==1)
          <a href="/dashboard">
            <img src="/images/logo/admin-logo.png" style="width: 50px; height: 50px;">
            <h2>VI-Portal v1</h2>
          </a>
          <h4 class="card-title">
            {{$l=='ar' ? 'الإصدار الأول من برنامج الحاضنةالإفتراضية' : 'Virtual incubation portal version 1.0'}}
          </h4>
          @else
          <h4 class="card-title">{{ $doc->{'menu_'.$l} }}</h4>
          @endif
          <div class="heading-elements">
            <ul class="list-inline mb-0">
              <li><a data-action="collapse"><i class="bx bx-chevron-down"></i></a></li>
              <li><a data-action="expand"><i class="bx bx-fullscreen"></i></a></li>
            </ul>
          </div>
        </div>
        <div class="card-content collapse show" aria-expanded="true">
          <div class="card-body">
            {!! $doc->{'content_'.$l} !!}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script src="/app-assets/vendors/js/vendors.min.js"></script>
<script src="/app-assets/js/core/app-menu.js"></script>
<script src="/app-assets/js/core/app.js"></script>
<script src="/app-assets/js/scripts/documentation.js" type="text/javascript"></script>
<script src="/app-assets/vendors/js/ui/affix.js" type="text/javascript"></script>
@stop