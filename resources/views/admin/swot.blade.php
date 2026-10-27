@extends('layouts.admin')

@section('content')
  {{-- Alerts --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-6" role="alert">
      <div class="d-flex align-items-center">
        <i class="ti ti-circle-check me-3 ti-md"></i>
        <span>{{ session('success') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div id="error-message" class="alert alert-danger d-none mb-6" role="alert">
    <div class="d-flex align-items-center">
      <i class="ti ti-alert-triangle me-3 ti-md"></i>
      <span></span>
    </div>
  </div>

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-6">
    <div>
      <h4 class="mb-0">{{ __('admin.swot_analysis') }}</h4>
      <p class="text-muted mb-0 mt-1 small">
        {{ __('admin.swot_description') }}</p>
    </div>
    <div class="d-flex gap-2">
      <button id="generate-swot" class="btn btn-label-warning">
        <i class="ti ti-sparkles me-2"></i>{{ __('admin.generate') }}
      </button>
      <a href="{{ route('swot.export') }}" class="btn btn-label-danger">
        <i class="ti ti-file-export me-2"></i>{{ __('admin.export') }}
      </a>
    </div>
  </div>

  {{-- Form --}}
  <form action="{{ route('swot.save') }}" method="POST">
    @csrf
    <input type="hidden" name="company_id" value="{{ auth()->user()?->company?->id }}">

    <div class="row g-4">

      {{-- Strengths --}}
      <div class="col-lg-6">
        <div class="card h-100 card-border-shadow-success">
          <div class="card-header border-bottom pb-3 mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ti ti-arrow-up-circle ti-lg"></i>
                </span>
              </div>
              <div>
                <h5 class="card-title mb-0">{{ __('admin.strengths') }}</h5>
                <small class="text-muted">{{ __('admin.strengths_subtitle') }}</small>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <label class="form-label fw-semibold fs-6">{{ __('admin.strengths') }}</label>
              <textarea name="strength" rows="5" class="form-control"
                placeholder="{{ __('admin.strengths_placeholder') }}">{{ $swotAnalysis->strength ?? '' }}</textarea>
            </div>
            <div>
              <label class="form-label fw-semibold fs-6">{{ __('admin.strengths_recommendations') }}</label>
              <textarea name="srecommendation" rows="4" class="form-control"
                placeholder="{{ __('admin.recommendations_placeholder') }}">{{ $swotAnalysis->srecommendation ?? '' }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- Weaknesses --}}
      <div class="col-lg-6">
        <div class="card h-100 card-border-shadow-danger">
          <div class="card-header border-bottom pb-3 mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="ti ti-arrow-down-circle ti-lg"></i>
                </span>
              </div>
              <div>
                <h5 class="card-title mb-0">{{ __('admin.weaknesses') }}</h5>
                <small class="text-muted">{{ __('admin.weaknesses_subtitle') }}</small>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <label class="form-label fw-semibold fs-6">{{ __('admin.weaknesses') }}</label>
              <textarea name="weakness" rows="5" class="form-control"
                placeholder="{{ __('admin.weaknesses_placeholder') }}">{{ $swotAnalysis->weakness ?? '' }}</textarea>
            </div>
            <div>
              <label class="form-label fw-semibold fs-6">{{ __('admin.weaknesses_recommendations') }}</label>
              <textarea name="wrecommendation" rows="4" class="form-control"
                placeholder="{{ __('admin.recommendations_placeholder') }}">{{ $swotAnalysis->wrecommendation ?? '' }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- Opportunities --}}
      <div class="col-lg-6">
        <div class="card h-100 card-border-shadow-primary">
          <div class="card-header border-bottom pb-3 mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti ti-bulb ti-lg"></i>
                </span>
              </div>
              <div>
                <h5 class="card-title mb-0">{{ __('admin.opportunities') }}</h5>
                <small class="text-muted">{{ __('admin.opportunities_subtitle') }}</small>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <label class="form-label fw-semibold fs-6">{{ __('admin.opportunities') }}</label>
              <textarea name="opportunity" rows="5" class="form-control"
                placeholder="{{ __('admin.opportunities_placeholder') }}">{{ $swotAnalysis->opportunity ?? '' }}</textarea>
            </div>
            <div>
              <label class="form-label fw-semibold fs-6">{{ __('admin.opportunities_recommendations') }}</label>
              <textarea name="orecommendation" rows="4" class="form-control"
                placeholder="{{ __('admin.recommendations_placeholder') }}">{{ $swotAnalysis->orecommendation ?? '' }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- Threats --}}
      <div class="col-lg-6">
        <div class="card h-100 card-border-shadow-warning">
          <div class="card-header border-bottom pb-3 mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti ti-alert-triangle ti-lg"></i>
                </span>
              </div>
              <div>
                <h5 class="card-title mb-0">{{ __('admin.threats') }}</h5>
                <small class="text-muted">{{ __('admin.threats_subtitle') }}</small>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <label class="form-label fw-semibold fs-6">{{ __('admin.threats') }}</label>
              <textarea name="threat" rows="5" class="form-control"
                placeholder="{{ __('admin.threats_placeholder') }}">{{ $swotAnalysis->threat ?? '' }}</textarea>
            </div>
            <div>
              <label class="form-label fw-semibold fs-6">{{ __('admin.threats_recommendations') }}</label>
              <textarea name="trecommendation" rows="4" class="form-control"
                placeholder="{{ __('admin.recommendations_placeholder') }}">{{ $swotAnalysis->trecommendation ?? '' }}</textarea>
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- Save --}}
    <div class="d-flex justify-content-end mt-6">
      <button type="submit" class="btn btn-primary">
        <i class="ti ti-device-floppy me-2"></i>
        {{ __('admin.Save') }}
      </button>
    </div>
  </form>
@endsection

@section('pagescripts')
  <script>
    document.getElementById('generate-swot').addEventListener('click', function () {
      var btn = this;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> {{ __('admin.generating') ?? 'Generating...' }}';

      fetch("{{ route('swot.generate') }}", {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": "{{ csrf_token() }}",
          "Content-Type": "application/json"
        }
      })
        .then(response => response.json())
        .then(data => {
          btn.disabled = false;
          btn.innerHTML = '<i class="ti ti-sparkles me-2"></i>{{ __('admin.generate') }}';

          if (data.error) {
            releaseAlert('danger', {
              title: "{{ __('admin.Error!') }}",
              message: data.error + (data.missing_fields ? "\n" + data.missing_fields.join(", ") : ""),
              confirmText: "OK",
              confirmColor: 'primary'
            });
          } else {
            releaseAlert('info', {
              title: "{{ __('admin.Success!') }}",
              message: "{{ __('admin.swot_generated') }}",
              confirmText: "{{ __('admin.OK') }}",
              confirmColor: 'primary'
            }).then(() => {
              location.reload();
            });
          }
        })
        .catch(error => {
          btn.disabled = false;
          btn.innerHTML = '<i class="ti ti-sparkles me-2"></i>{{ __('admin.generate') }}';

          let errorDiv = document.getElementById('error-message');
          errorDiv.querySelector('span').textContent = "{{ __('admin.something_went_wrong') ?? 'Something went wrong while generating the SWOT Analysis.' }}";
          errorDiv.classList.remove('d-none');
        });
    });
  </script>
@endsection
