<section>
  <!-- Company Info Card -->
  <div class="card" style="border-radius: 15px;">
    <div class="card-body row">
      <div class="col-md-10 py-4">
        <div class="d-flex w-100" style="gap: 1rem;">
          <div class="border d-none d-md-flex radius-15 d-flex flex-shrink-0" style="width: 90px; height: 90px;">
            <img src="{{ $company->logo ? asset( $company->logo) : '/images/logo-placeholder.png' }}"
              onerror="this.src='/images/logo-placeholder.png'" class="img-fluid m-auto" alt="logo"
              style="width: 90px; height: 90px; border-radius: 15px;" />
          </div>
          <div class="d-flex flex-column w-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
              <div class="d-flex">
                <i class="ti ti-building-skyscraper ti-23px me-2 text-primary"></i>
                <div class="d-flex flex-column">
                  <div class="d-flex align-items-center gap-2">
                    <h5 class="fs-bold mb-0 border rounded px-4">
                    @if ($company->website)
                      <a href="{{ strpos($company->website, 'http') === 0 ? $company->website : 'http://' . $company->website }}" target="_blank" class="text-decoration-none text-dark">
                      {{ $company->{'name_' . $l} }}
                      </a>
                    @else
                      {{ $company->{'name_' . $l} }}
                    @endif
                    </h5>
                    @if ($company->est_date)
                    <div class="fs-6">Established in <span class="fw-bold">{{ \Carbon\Carbon::parse($company->est_date)->year }}</span></div>
                    @endif
                  </div>
                  <!-- @php
                  $sector = App\Sector::where('id', $company->sector)->first();
                  @endphp
                  <div class="fs-6 mt-2">Sector: <span class="fw-bold">{{ $sector ? $sector->{'name_' . $l} : $company->sector }}</span></div> -->
                </div>
              </div>
              <div class="d-flex align-items-center mt-3 mt-md-0" style="gap: 1rem;">
                @if ($company->facebook)
                <a class="d-flex align-items-center"
                  href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                  target='_blank'>
                  <i class="fa-brands fa-facebook-f fs-6"></i>
                </a>
                @endif
                @if ($company->linkedin)
                <a class="d-flex align-items-center"
                  href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                  target='_blank'>
                  <img src="/icons/linkedin.svg" width="17" height="17" />
                </a>
                @endif
                <a href="{{ 'https://wa.me/' . $company->country_code_w_a . $company->whatsapp }}" target="_blank"
                  class="d-flex align-items-center">
                  <i class="fa-brands fa-whatsapp fs-5 text-success"></i>
                </a>
                @if ($company->twitter)
                <a class="d-flex align-items-center"
                  href="{{ strpos($company->twitter, 'http') === 0 ? $company->twitter : 'http://' . $company->twitter }}"
                  target='_blank'>
                  <i class="ti ti-brand-x fs-5 text-dark"></i>
                </a>
                @endif
                @if ($company->youtube)
                <a class="d-flex align-items-center"
                  href="{{ strpos($company->youtube, 'http') === 0 ? $company->youtube : 'http://' . $company->youtube }}"
                  target='_blank'>
                  <i class="fa-brands fa-youtube fs-5 text-danger"></i>
                </a>
                @endif
                @if ($company->instagram)
                <a class="d-flex align-items-center"
                  href="{{ strpos($company->instagram, 'http') === 0 ? $company->instagram : 'http://' . $company->instagram }}"
                  target="_blank">
                  <i i class="fa-brands fa-instagram fs-5 text-danger"></i>
                </a>
                @endif
                @if ($company->tiktok)
                <a class="d-flex align-items-center"
                  href="{{ strpos($company->tiktok, 'http') === 0 ? $company->tiktok : 'http://' . $company->tiktok }}"
                  target="_blank">
                  <i class="fa-brands fa-tiktok fs-5 text-dark"></i>
                </a>
                @endif
              </div>
            </div>
        
            <div class="d-flex flex-column">
              @if ($company->info_company)
              <div class="d-flex gap-2 mt-4 w-100">
                <i class="ti ti-message ti-23px text-primary"></i>
                <div class="d-flex flex-column w-100">
                  <div class="d-flex flex-column px-4 py-2 bg-label-secondary rounded w-100">
                    <span class="fw-bold text-dark fs-6 mb-1">{{ __('admin.Description') }}</span>
                    <div class="line-clamp-2 text-xs">
                      {{ $company->info_company }}
                    </div>
                  </div>
                </div>
              </div>
              @endif
              <div class="w-100 mt-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2"
                  onclick="window.open('{{ $company->google_map ? $company->google_map : '#'}}', '_blank')" style="cursor: pointer;"
                  title="Open in Google Maps"
                >
                  <i class="ti ti-map-pin ti-23px text-primary"></i>
                  <div>
                  {{ optional($company->state)->{'name_' . $l} }}, {{ optional($company->city)->{'name_' . $l} }}, {{ optional($company->country)->{'name_' . $l} }}
                  </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                  @if ($company->phone)
                  <a class="d-flex align-items-center" href="tel:{{ $company->country_code . $company->phone }}">
                    <i class="ti ti-phone ti-22px text-primary"></i>
                  </a>
                  @endif
                  @if ($company->email)
                  <a class="d-flex align-items-center text-primary" href="mailto:{{ $company->email }}">
                    <i class="ti ti-mail ti-22px text-primary me-2"></i>
                  </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Action Button -->
      <div class="col-md-2 d-flex align-items-center justify-content-end gap-2">
        @if( $_user->hasAnyRole('Admin|organizationManager') )
        @if( $_user->ableTo('my_company', $permissions) )
        <a href="/dashboard/myCompany/{{ $company->id }}" class="btn btn-label-primary shadow-sm text-primary fw-bold"
          style="border-radius: 15px;">
          {{ __('admin.Edit') }} <i class="ti ti-edit mx-2"></i>
        </a>
        @endif
        @endif
        @if( isset( $_user->company()->first()->id ) && $company->id == $_user->company()->first()->id )
        @if( $_user->ableTo('my_company', $permissions) )
        <a href="/dashboard/myCompany" class="btn btn-label-primary shadow-sm text-primary fw-bold"
          style="border-radius: 15px;">
          {{ __('admin.Edit') }} <i class="ti ti-edit mx-2"></i>
        </a>
        @endif
        @else
        <a href="" data-bs-toggle="modal" data-bs-target="#MessageModal"
          class="btn btn-label-primary shadow-sm text-primary fw-bold text-nowrap" style="border-radius: 15px;">
          {{ __('admin.Contact') }} <i class="ti ti-message mx-2"></i>
        </a>
        @endif
      </div>
      <!-- / Action Button -->
    </div>
  </div>
  <!-- / Company Info Card -->
</section>