@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/importer/importer.css') }}?v={{ filemtime(public_path('css/importer/importer.css')) }}">

<div class="container-fluid importer-v2 px-0" data-locale="{{ app()->getLocale() }}">
    <div class="card card-body">
        <div class="mb-3">
            <h4 class="fw-bold mb-1">{{ __('importer.ui.title') }}</h4>
            <div class="small text-secondary">
                <a href="{{ route('importer.template') }}" class="text-primary fw-medium text-decoration-none">
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
            @include('admin.importer.v2._step_review')
        </div>
    </div>
</div>

<script>
    window.importerSchema   = {!! $schemaJson !!};
    window.importerSections = {!! $sectionsJson !!};
    window.importerI18n     = {!! $i18nJson !!};
    window.importerCsrf     = "{{ csrf_token() }}";
    window.importerRoutes   = {
        createSession:  "{{ route('importer.sessions.create') }}",
        showSession:    "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}
        uploadFile:     "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}/file
        deleteFile:     "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}/file
        discardSession: "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}
        automap:        "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}/automap
        validate:       "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}/validate
        conflicts:      "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}/conflicts
        organizations:  "{{ route('importer.organizations') }}",
        preview:        "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}/preview
        execute:        "{{ url('/dashboard/importer/sessions') }}",    // + /{uuid}/execute
    };
</script>
<script src="{{ asset('js/importer/importer-i18n.js') }}?v={{ filemtime(public_path('js/importer/importer-i18n.js')) }}"></script>
<script src="{{ asset('js/importer/importer-state.js') }}?v={{ filemtime(public_path('js/importer/importer-state.js')) }}"></script>
<script src="{{ asset('js/importer/importer-upload.js') }}?v={{ filemtime(public_path('js/importer/importer-upload.js')) }}"></script>
<script src="{{ asset('js/importer/importer-mapping.js') }}?v={{ filemtime(public_path('js/importer/importer-mapping.js')) }}"></script>
<script src="{{ asset('js/importer/importer-review.js') }}?v={{ filemtime(public_path('js/importer/importer-review.js')) }}"></script>
@endsection
