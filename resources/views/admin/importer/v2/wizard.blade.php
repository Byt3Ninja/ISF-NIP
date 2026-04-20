@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/importer/importer.css') }}?v={{ filemtime(public_path('css/importer/importer.css')) }}">

<div class="container-fluid importer-v2" data-locale="{{ app()->getLocale() }}">
    <div class="card card-body">
        <div class="mb-3">
            <h4 class="fw-bold mb-1">{{ __('importer.ui.title') }}</h4>
            <div class="small text-secondary">
                <a href="{{ route('importer.v2.template') }}" class="text-primary fw-medium text-decoration-none">
                    {{ __('importer.ui.actions.download') }}
                </a>
            </div>
        </div>

        <!-- Step progress -->
        <ol class="importer-steps d-flex list-unstyled mb-4">
            <li data-step="1" class="flex-fill">1. {{ __('importer.ui.step1') }}</li>
            <li data-step="2" class="flex-fill">2. {{ __('importer.ui.step2') }}</li>
            <li data-step="3" class="flex-fill">3. {{ __('importer.ui.step3') }}</li>
        </ol>

        <div id="importer-step-upload">
            @include('admin.importer.v2._step_upload')
        </div>
        <div id="importer-step-mapping" class="d-none">
            @include('admin.importer.v2._step_mapping')
        </div>
        <div id="importer-step-review" class="d-none">
            <!-- PR 6 fills this -->
        </div>
    </div>
</div>

<script>
    window.importerSchema   = {!! $schemaJson !!};
    window.importerSections = {!! $sectionsJson !!};
    window.importerI18n     = {!! $i18nJson !!};
    window.importerCsrf     = "{{ csrf_token() }}";
    window.importerRoutes   = {
        createSession:  "{{ route('importer.v2.sessions.create') }}",
        showSession:    "{{ url('/dashboard/importer/v2/sessions') }}",    // + /{uuid}
        uploadFile:     "{{ url('/dashboard/importer/v2/sessions') }}",    // + /{uuid}/file
        deleteFile:     "{{ url('/dashboard/importer/v2/sessions') }}",    // + /{uuid}/file
        discardSession: "{{ url('/dashboard/importer/v2/sessions') }}",    // + /{uuid}
        automap:        "{{ url('/dashboard/importer/v2/sessions') }}",    // + /{uuid}/automap
    };
</script>
<script src="{{ asset('js/importer/importer-i18n.js') }}?v={{ filemtime(public_path('js/importer/importer-i18n.js')) }}"></script>
<script src="{{ asset('js/importer/importer-state.js') }}?v={{ filemtime(public_path('js/importer/importer-state.js')) }}"></script>
<script src="{{ asset('js/importer/importer-upload.js') }}?v={{ filemtime(public_path('js/importer/importer-upload.js')) }}"></script>
<script src="{{ asset('js/importer/importer-mapping.js') }}?v={{ filemtime(public_path('js/importer/importer-mapping.js')) }}"></script>
@endsection
