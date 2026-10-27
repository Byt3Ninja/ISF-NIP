{{--
  A state-changing action (delete / approve ...) behind the shared releaseAlert() confirmation:
  a trigger button plus its own hidden form, so the request is a CSRF-protected POST/DELETE
  instead of a GET link. Needs partials/alert-modals (included by layouts.admin).

  @include('partials.forms.confirm-action', [
      'formId'  => 'delete-slide-' . $slide->id,   // unique per row
      'action'  => route('slides.delete', $slide->id),
      'method'  => 'DELETE',                        // optional, default DELETE
      'message' => __('settings.SureDelete'),       // optional
      'label'   => '<i class="ti ti-trash text-danger"></i>', // trusted markup only, never user data
      'class'   => 'btn btn-link p-0',              // optional
      'part'    => 'both',                          // 'button' / 'form' when the form must sit outside another form
  ])
--}}
@php($part = $part ?? 'both')
@if ($part !== 'form')
  <button type="button" class="{{ $class ?? 'btn btn-link p-0 align-baseline' }}" data-alert="danger"
    data-alert-title="{{ $title ?? __('admin.Delete') }}" data-alert-message="{{ $message ?? __('settings.SureDelete') }}"
    data-alert-confirm="{{ $confirm ?? __('admin.Delete') }}" data-alert-form="{{ $formId }}">{!! $label !!}</button>
@endif
@if ($part !== 'button')
  <form id="{{ $formId }}" action="{{ $action }}" method="POST" class="d-none">
    @csrf
    @method($method ?? 'DELETE')
  </form>
@endif
