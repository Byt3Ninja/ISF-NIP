@extends('layouts.admin')

@section('title', __('admin.Custom Landing Pages'))
@section('subTitle', __('admin.Custom Landing Pages'))

@php
$pages = [
  [
    'id' => 1,
    'title' => 'Summer Accelerator 2025',
    'logo' => '/vuexy/assets/img/branding/brand-img-small.png',
    'url' => 'https://unsplash.com/',
    'status' => 1,
    'description' => 'Landing page for the upcoming summer batch focusing on AI and SaaS startups.',
    'programs' => 12,
    'created_by' => [
      'id' => 20,
      'name' => 'Kelly Mentor',
      'img_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSYscfUBUbqwGd_DHVhG-ZjCOD7MUpxp4uhNe7toUg4ug&s'
    ]
  ],
  [
    'id' => 2,
    'title' => 'Fintech Hackathon',
    'logo' => null,
    'url' => 'https://unsplash.com/',
    'status' => 0,
    'description' => 'Registration page for the weekend fintech hackathon.',
    'programs' => 25,
    'created_by' => [
      'id' => 25,
      'name' => 'Mentor MeMo',
      'img_url' => null
    ]
  ],
];
@endphp

@section('content')

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">
        {{ $l == 'ar' ? 'صفحات هبوط مخصصة' : 'Custom Landing Pages' }}
      </h4>
      <p class="text-secondary mb-0">
        {{ $l == 'ar' ? 'أنشئ صفحات هبوط مخصصة لبرامجك وأدِرها.' : 'Manage and create tailored landing pages for your programs.' }}
      </p>
    </div>

    <a href="#" class="btn btn-primary">
      <i class="ti ti-plus me-2"></i>
      {{ $l == 'ar' ? 'إضافة صفحة جديدة' : 'Add New Page' }}
    </a>
  </div>

  {{-- Filters --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" id="status-form">
        <div class="row g-3 align-items-center">
          <div class="col-lg-4">
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
              <input 
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

          <div class="col-lg-2 ms-lg-auto">
            <select class="form-select" id="status-select" name="status">
              <option value="">{{ __('admin.All_statuses') }}</option>
              <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                {{ __('admin.Active') }}
              </option>
              <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>
                {{ __('admin.Inactive') }}
              </option>
            </select>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Cards --}}
  <div class="row g-4">
    @if (empty($pages))
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center">
            <img src="/icons/not-found.svg" width="250" height="250" alt="No Result">
            <h4 class="mb-2">
              {{ $l == 'ar' ? 'لم يتم إنشاء أي صفحات هبوط بعد' : 'No landing pages created yet' }}
            </h4>
            <p class="text-secondary mb-4">
              {{ $l == 'ar' ? 'لم تقم بإنشاء أي صفحات هبوط مخصصة' : "You haven't created any custom landing pages for your" }} <br>
              {{ $l == 'ar' ? 'لبرامجك' : 'programs' }}. {{ $l == 'ar' ? 'ابدأ بإنشاء صفحة الهبوط الأولى الخاصة بك.' : 'Get started by creating your first landing page.' }}
            </p>
            <button class="btn btn-primary">
              <i class="ti ti-plus me-2"></i>
              {{ $l == 'ar' ? 'إضافة اول صفحة' : 'Create First Page' }}
            </button>
          </div>
        </div>
      </div>
    @else
      @foreach($pages as $page)

        <div class="col-xl-4 col-md-6" data-id="{{ $page['id'] }}">

          <div class="card landing-card h-100 shadow-sm" data-url="{{ $page['url'] }}">

            <div class="card-body d-flex flex-column">

              <div class="d-flex gap-3 mb-3">
                <div class="avatar avatar-lg">
                    @if ($page['logo'])
                      <img 
                        src="{{ $page['logo'] }}"
                        alt="logo" 
                        class="rounded object-fit-contain"
                      />
                    @else
                      <div class="avatar-initial rounded">
                        <i class="ti ti-layout-dashboard ti-lg text-dark"></i>
                      </div>
                    @endif
                  </div>
                <div>

                  <h5 class="card-title mb-1">
                    {{ $page['title'] }}
                  </h5>

                  <div class="badge rounded-pill border border-{{ $page['status'] == 1 ? 'info' : 'danger' }} bg-label-{{ $page['status'] == 1 ? 'info' : 'danger' }} mb-3 pb-1">
                    <i class="ti ti-point-filled ti-xs"></i>
                    {{ $page['status'] == 1 ? __('admin.Active') : __('admin.Inactive') }}
                  </div>

                </div>

              </div>

              <p class="card-text text-secondary">
                {{ $page['description'] ?? ($l == 'ar' ? 'لم تتم إضافة وصف' : 'No description added') }}
              </p>

              <a href="/dashboard/show/UserProfile/{{ $page['created_by']['id'] }}" class="d-flex align-items-center gap-2 mt-auto">
                <div class="avatar">
                  <div class="avatar-initial rounded-circle overflow-hidden">
                    @if($page['created_by']['img_url'])
                      <img src="{{ $page['created_by']['img_url'] }}" alt="logo" class="rounded object-fit-cover" />
                    @else
                      <i class="ti ti-user text-dark"></i>
                    @endif
                  </div>
                </div>
                <span class="text-body">{{ __('admin.created_by') }}</span>
                <span 
                  class="fw-semibold text-dark"
                >
                  {{ $page['created_by']['name'] }}
                </span>
              </a>

            </div>

            <div class="card-footer bg-card border-top pt-6 d-flex justify-content-between align-items-center">

              <div class="text-secondary small">
                <i class="ti ti-rocket me-1"></i>
                <strong>{{ $page['programs'] ?? 0 }}</strong> {{ __('admin.Programs') }}
              </div>

              <div class="d-flex">

                <a href="{{ $page['url'] }}" class="btn btn-sm btn-icon btn-text-secondary rounded-circle" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.Preview') }}">
                  <i class="ti ti-eye"></i>
                </a>

                <a href="#" class="btn btn-sm btn-icon btn-text-secondary rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.Edit') }}">
                  <i class="ti ti-edit"></i>
                </a>

                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-circle copy-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.Copy') }}">
                  <i class="ti ti-copy"></i>
                </button>

                <button
                  type="button" 
                  class="btn btn-sm btn-icon btn-text-secondary rounded-circle delete-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.Delete') }}" 
                  data-id="{{ $page['id'] }}" 
                  data-title="{{ $page['title'] }}" 
                  {{-- data-url="{{ route('landing-pages.destroy', ['page' => $page['id']]) }}" --}}
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
@endsection

@section('pagescripts')
  <script>
    $(document).on('change', '#status-select', function (e) {
      $("#status-form").submit();
    });

    $(document).on('click', '.copy-btn', function () {
      const text = $(this).closest('.landing-card').data('url');
      if (text) {
        navigator.clipboard.writeText(text).then(() => {
          toastr.success("{{ $l == 'ar' ? 'تم نسخ رابط صفحة الهبوط بنجاح.' : 'Landing page URL copied successfully.' }}");
        });
      }
    });

    $(document).on('click', '.delete-btn', function (e) {
      var url = $(this).data('url');
      var token = '{{ csrf_token() }}';
      var pageName = $(this).data('title') || '';
      var isArabic = {{ $l == 'ar' ? 'true' : 'false' }};
      var title = isArabic
        ? "'" + pageName + "'؟ سيتم حذف صفحة الهبوط نهائيًا ولن يكون رابطها العام متاحًا بعد"
        : "Are you sure you want to delete '" + pageName + "'?";

      releaseWarningAlert({
        title: title,
        message: "{{ $l == 'ar' ? 'سيتم حذف صفحة الهبوط نهائيًا ولن يكون رابطها العام متاحًا بعد ذلك.' : 'This landing page will be permanently removed, and its public URL will no longer be accessible.' }}",
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
                releaseSuccessAlert({
                  title: "{{ __('admin.Deleted') }}!",
                  message: "{{ $l == 'ar' ? 'تم حذف صفحة الهبوط بنجاح.' : 'Landing page deleted successfully.' }}",
                });

                // Remove the landing card from DOM
                $('div[data-id="' + $(this).data('id') + '"]').fadeOut(300, function () {
                  $(this).remove();
                });

                // Reload the page
                location.reload();

              } else {
                releaseFailureAlert({
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
            releaseFailureAlert({
              title: "{{ __('admin.Error') }}",
              message: errorMessage
            })
          });
        }
      });
    });
  </script>
@endsection
