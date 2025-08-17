@extends('layouts.admin')

@section('title', __('notification_templates.triggers_show_title', ['name' => $notificationTrigger->name ?? 'Trigger']))

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0">{{ $notificationTrigger->name ?? __('admin.notification_triggers.show') }}</h1>
        <div class="btn-group" role="group" aria-label="Actions">
            <a href="{{ route('admin.notification-triggers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('admin.back') }}</a>
            <a href="{{ route('admin.notification-triggers.edit', $notificationTrigger) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> {{ __('admin.edit') }}</a>
            <form action="{{ route('admin.notification-triggers.clone', $notificationTrigger) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-files"></i> {{ __('admin.clone') }}
                </button>
            </form>
            <form action="{{ route('admin.notification-triggers.toggle-active', $notificationTrigger) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-{{ $notificationTrigger->is_active ? 'warning' : 'success' }}">
                    <i class="bi bi-toggle-{{ $notificationTrigger->is_active ? 'off' : 'on' }}"></i>
                    {{ $notificationTrigger->is_active ? __('admin.deactivate') : __('admin.activate') }}
                </button>
            </form>
            <form action="{{ route('admin.notification-triggers.test', $notificationTrigger) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success">
                    <i class="bi bi-send"></i> {{ __('admin.test_send') }}
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">{{ __('admin.details') }}</h2>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('admin.name') }}</dt>
                        <dd class="col-sm-8">{{ $notificationTrigger->name }}</dd>

                        <dt class="col-sm-4">{{ __('admin.event') }}</dt>
                        <dd class="col-sm-8"><span class="badge text-bg-info">{{ $notificationTrigger->event ?? '-' }}</span></dd>

                        <dt class="col-sm-4">{{ __('admin.status') }}</dt>
                        <dd class="col-sm-8">
                            @if($notificationTrigger->is_active)
                                <span class="badge text-bg-success">{{ __('admin.active') }}</span>
                            @else
                                <span class="badge text-bg-secondary">{{ __('admin.inactive') }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">{{ __('admin.template') }}</dt>
                        <dd class="col-sm-8">
                            @if($notificationTrigger->template)
                                <a href="{{ route('admin.notification-templates.show', $notificationTrigger->template) }}">
                                    {{ $notificationTrigger->template->name }}
                                </a>
                            @else
                                <em>{{ __('admin.none') }}</em>
                            @endif
                        </dd>

                        <dt class="col-sm-4">{{ __('admin.recipient_rules') }}</dt>
                        <dd class="col-sm-8"><pre class="mb-0 small bg-light p-2 rounded">@json($notificationTrigger->recipient_rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)</pre></dd>

                        <dt class="col-sm-4">{{ __('admin.conditions') }}</dt>
                        <dd class="col-sm-8"><pre class="mb-0 small bg-light p-2 rounded">@json($notificationTrigger->conditions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)</pre></dd>

                        <dt class="col-sm-4">{{ __('admin.created_by') }}</dt>
                        <dd class="col-sm-8">{{ optional($notificationTrigger->creator)->name ?? '-' }}</dd>

                        <dt class="col-sm-4">{{ __('admin.created_at') }}</dt>
                        <dd class="col-sm-8">{{ optional($notificationTrigger->created_at)->format('Y-m-d H:i') }}</dd>

                        <dt class="col-sm-4">{{ __('admin.updated_at') }}</dt>
                        <dd class="col-sm-8">{{ optional($notificationTrigger->updated_at)->format('Y-m-d H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">{{ __('admin.quick_actions') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted small">{{ __('admin.quick_actions_help') }}</p>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a class="link-primary" href="{{ route('admin.notification-triggers.edit', $notificationTrigger) }}">{{ __('admin.edit_this_trigger') }}</a>
                        </li>
                        @if($notificationTrigger->template)
                        <li class="mb-2">
                            <a class="link-primary" href="{{ route('admin.notification-templates.show', $notificationTrigger->template) }}">{{ __('admin.view_related_template') }}</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
