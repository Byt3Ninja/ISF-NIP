@extends('layouts.admin')

@section('title', __('admin.Custom Landing Pages'))
@section('subTitle', __('admin.Custom Landing Pages'))

@section('style')
  <style>
  .line-clamp-5 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
  }

  /* Status accent bar along the card's top edge, plus a matching hover tint.
     --status-color is declared once per status and reused by the bar, the hover
     shadow, and the hover border, so the three can never drift apart.
     Colors mirror .bg-label-primary / .bg-label-warning in theme-default.css. */
  .landing-card {
    --status-color: transparent;
    --status-shadow: rgba(0, 0, 0, .12);
    transition: transform .25s ease-in-out, box-shadow .25s ease-in-out, border-color .25s ease-in-out;
  }

  .landing-card.status-active {
    --status-color: var(--bs-primary);
    --status-shadow: rgba(75, 75, 75, 0.22);
  }

  .landing-card.status-inactive {
    --status-color: #ff9f43;
    --status-shadow: rgba(255, 159, 67, .22);
  }

  .landing-card.status-active,
  .landing-card.status-inactive {
    border-top: 3px solid var(--status-color);
  }

  .landing-card:hover {
    transform: translateY(-3px);
    border-color: var(--status-color);
    box-shadow: 0 .75rem 1.5rem var(--status-shadow) !important;
  }
  </style>
@endsection

@section('content')
  {{-- Header --}}
  <div class="card mb-6">
    <div class="card-header">
      <h4 class="mb-2">{{ __('admin.Custom Landing Pages') }}</h4>
      <p class="mb-0">{{ __('landing.Custom_Landing_Page_Description') }}</p>
    </div>
    <div class="card-body">
      <form method="GET" id="status-form">
        <div class="row g-4 align-items-center">
          <div class="col-12 col-lg-5 col-xl-6">
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
              <input 
                name="search"
                type="search" 
                id="debounced-search" 
                class="form-control"
                placeholder="{{ __('admin.Search') }}..."
                aria-label="Search landing pages"
                aria-describedby="search-landing-pages" 
                value="{{ request('search') ? request('search') : '' }}" 
              />
            </div>
          </div>
          <div class="col-12 col-lg-4 col-xl-4">
            <select class="form-select select2" id="status-select" name="status">
              <option value="all">{{ __('admin.All_statuses') }}</option>
              <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                {{ __('admin.Active') }}
              </option>
              <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>
                {{ __('admin.Inactive') }}
              </option>
            </select>
          </div>
          <div class="col-12 col-lg-3 col-xl-2 d-grid">
            <a href="{{ route('landing-pages.create') }}" class="btn btn-primary">
              <i class="ti ti-plus me-2"></i>
              {{ __('landing.Add_New_Page') }}
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Cards --}}
  <div class="row g-4">
    @if ($pages->isEmpty())
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center">
            <img src="/images/empty-state.png" class="img-fluid" style="max-width: 300px; height: auto;" alt="No Result">
            <h4 class="mb-2">
              {{ __('landing.No_LandingPages') }}
            </h4>
            <p class="text-secondary mb-4">
              {{ __('landing.Have_Not_Created') }}
              <br>
              {{ __('landing.Programs') }}. {{ __('landing.Get_Started') }}
            </p>
            <a href="{{ route('landing-pages.create') }}" class="btn btn-primary">
              <i class="ti ti-plus me-2"></i>
              {{ __('landing.Create_First_Page') }}
            </a>
          </div>
        </div>
      </div>
    @else
      @foreach($pages as $page)

        <div class="col-xl-4 col-md-6" data-id="{{ $page->id }}">

          <div class="card landing-card h-100 shadow-sm status-{{ $page->status ? 'active' : 'inactive' }}" data-url="{{ $page->public_url }}">

            <div class="card-body d-flex flex-column">

              <div class="d-flex gap-3 mb-3">
                <div class="avatar avatar-lg">
                    @if ($page->logo_url)
                      <img
                        src="{{ $page->logo_url }}"
                        alt="logo"
                        class="rounded object-fit-cover w-px-50 h-px-50 flex-shrink-0"
                      />
                    @else
                      <div class="avatar-initial rounded">
                        <i class="ti ti-layout-dashboard ti-lg text-dark"></i>
                      </div>
                    @endif
                  </div>
                <div>

                  <h5 class="card-title mb-1 line-clamp-1">
                    <a href="{{ route('landing-pages.edit', $page->id) }}" class="text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $page->title }}">
                      {{ Str::limit($page->title, $titleMaxLength) }}
                    </a>
                  </h5>

                  <div class="badge rounded-pill bg-label-{{ $page->status ? 'primary' : 'warning' }} mb-3 pb-1">
                    <i class="ti ti-point-filled ti-xs"></i>
                    {{ $page->status ? __('admin.Active') : __('admin.Inactive') }}
                  </div>

                </div>

              </div>

              <p class="card-text text-secondary line-clamp-5">
                {{ ($l == 'ar' ? $page->description_ar : $page->description) ?: (__('landing.No_Description') . '...') }}
              </p>

              @if ($_user->hasAnyRole('admin'))
                <a href="{{ route('mentors.profile_view', $page->created_by) }}" target="_blank" rel="noopener" class="d-flex align-items-center gap-2 mt-auto">
                  <div class="avatar avatar-xs">
                    <div class="avatar-initial rounded-circle overflow-hidden">
                      @if($page->createdBy && $page->createdBy->img_url)
                        <img src="{{ $page->createdBy->image_url }}" alt="logo" class="rounded object-fit-cover" />
                      @else
                        <i class="ti ti-user text-dark"></i>
                      @endif
                    </div>
                  </div>
                  <small class="text-body">{{ __('admin.created_by') }}</small>
                  <small
                    class="fw-semibold text-dark fs-xs"
                  >
                    {{ $page->createdBy->name ?? '' }}
                  </small>
                </a>
              @endif
            </div>

            <div class="card-footer bg-card border-top py-2 d-flex justify-content-between align-items-center">

              <div class="text-secondary small">
                <i class="ti ti-rocket me-1"></i>
                <strong>{{ $page->cycles_count ?? 0 }}</strong> {{ __('admin.Programs') }}
              </div>

              <div class="d-flex">

                <a href="{{ $page->public_url }}" class="btn btn-sm btn-icon btn-text-secondary rounded-circle" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.Preview') }}">
                  <i class="ti ti-eye"></i>
                </a>

                <a href="{{ route('landing-pages.edit', $page->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.Edit') }}">
                  <i class="ti ti-edit"></i>
                </a>

                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-circle copy-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.Copy') }}">
                  <i class="ti ti-copy"></i>
                </button>

                <button
                  type="button"
                  class="btn btn-sm btn-icon btn-text-secondary rounded-circle delete-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.Delete') }}"
                  data-id="{{ $page->id }}"
                  data-title="{{ $page->title }}"
                  data-url="{{ route('landing-pages.destroy', $page->id) }}"
                >
                  <i class="ti ti-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    @endif
  </div>

  @if ($pages->hasPages())
    <div class="mt-8">
      {{ $pages->links('pagination::bootstrap-5') }}
    </div>
  @endif
@endsection

@section('pagescripts')
  <script>
    $(document).on('change', '#status-select', function (e) {
      $("#status-form").submit();
    });

    $(document).on('click', '.copy-btn', function () {
      const text = $(this).closest('.landing-card').data('url');
      if (!text) return;

      // navigator.clipboard is only available in secure contexts (HTTPS or
      // localhost); fall back to the classic execCommand approach otherwise.
      (navigator.clipboard?.writeText(text) ?? Promise.reject())
        .catch(() => {
          const el = Object.assign(document.createElement('input'), { value: text });
          document.body.appendChild(el);
          el.select();
          document.execCommand('copy');
          document.body.removeChild(el);
        })
        .then(() => {
          toastr.success("{{ $l == 'ar' ? 'تم نسخ رابط صفحة الهبوط بنجاح.' : 'Landing page URL copied successfully.' }}");
        });
    });

    $(document).on('click', '.delete-btn', function (e) {
      var url = $(this).data('url');
      var token = '{{ csrf_token() }}';
      var pageName = $(this).data('title') || '';
      var isArabic = {{ $l == 'ar' ? 'true' : 'false' }};
      var warningMessage = isArabic
        ? "هل أنت متأكد من حذف '" + pageName + "'؟ سيتم حذف صفحة الهبوط نهائيًا ولن يكون رابطها العام متاحًا بعد ذلك."
        : "Are you sure you want to delete '" + pageName + "'? This landing page will be permanently removed, and its public URL will no longer be accessible.";

      releaseAlert('warning', {
        title: "{{ $l == 'ar' ? 'حذف صفحة الهبوط' : 'Delete Landing Page' }}",
        message: warningMessage,
        confirmText: "{{ $l == 'ar' ? 'حذف' : 'Delete' }}",
        cancelText: "{{ $l == 'ar' ? 'إلغاء' : 'Cancel' }}",
        confirmColor: 'danger',
        onConfirm: () => {
          $.post(url, {
            '_method': 'DELETE',
            '_token': token
          },
            function (response) {
              if (response.success) {
                releaseAlert('success', {
                  title: "{{ __('admin.Deleted') }}!",
                  message: "{{ $l == 'ar' ? 'تم حذف صفحة الهبوط بنجاح.' : 'Landing page deleted successfully.' }}",
                });

                // Remove the landing card from the list. Reload only when the
                // list is now empty so the empty-state renders correctly.
                $('div[data-id="' + $(this).data('id') + '"]').fadeOut(300, function () {
                  $(this).remove();
                  if ($('.landing-card').length === 0) {
                    location.reload();
                  }
                });

              } else {
                releaseAlert('danger', {
                  title: "{{ __('admin.Error') }}",
                  message: response.message ? response.message : "{{ __('admin.DeleteFailed') }}"
                });
              }
            }.bind(this)
          ).fail(function (xhr) {
            let errorMessage = "{{ __('admin.DeleteFailed') }}";
            if (xhr.responseJSON && xhr.responseJSON.message) {
              errorMessage = xhr.responseJSON.message;
            }
            releaseAlert('danger', {
              title: "{{ __('admin.Error') }}",
              message: errorMessage
            })
          });
        }
      });
    });
  </script>
@endsection
