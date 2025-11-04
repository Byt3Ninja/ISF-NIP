<section>
  <!-- Company Info Card -->
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-12">
          <div class="d-flex flex-column flex-md-row align-items-start" style="gap: 1.5rem;">
            <div class="flex-shrink-0">
              <div class="company-logo me-4 me-md-0">
                <img src="{{ $company->logo ? asset( $company->logo) : '/images/logo-placeholder.png' }}"
                  onerror="this.src='/images/logo-placeholder.png'" 
                  class="rounded-3" alt="Company Logo" 
                  style="width: 170px; height: 150px; object-fit: cover;" />
              </div>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex flex-column flex-md-row justify-content-between align-items-start">
                <div class="company-header mb-3 mb-md-0">
                  <div class="d-flex align-items-center mb-2">
                    <i class="ti ti-building-skyscraper ti-lg me-2 text-primary"></i>
                    <div class="company-name">
                      @if ($company->website)
                        <h4 class="mb-0 fw-bold">
                          <a href="{{ strpos($company->website, 'http') === 0 ? $company->website : 'http://' . $company->website }}" 
                             target="_blank" 
                             class="text-heading text-decoration-none">
                            {{ $company->{'name_' . $l} }}
                          </a>
                        </h4>
                      @else
                        <h4 class="mb-0 fw-bold text-heading">{{ $company->{'name_' . $l} }}</h4>
                      @endif
                    </div>
                  </div>
                  @if ($company->est_date)
                  <div class="mb-2">
                    <span class="badge bg-label-info rounded-pill">
                      <i class="ti ti-calendar ti-xs me-1"></i>
                      {{ __('admin.Established in') }} {{ \Carbon\Carbon::parse($company->est_date)->format('Y') }}
                    </span>
                  </div>
                  @endif
                </div>
                <div class="social-links d-flex align-items-center justify-content-start justify-content-md-end">
                  <div class="d-flex align-items-center gap-2">
                    @if ($company->facebook)
                    <a href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                       target="_blank" 
                       class="btn btn-icon btn-label-primary rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="Facebook">
                      <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    @endif
                    @if ($company->linkedin)
                    <a href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                       target="_blank" 
                       class="btn btn-icon btn-label-info rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="LinkedIn">
                      <i class="fa-brands fa-linkedin-in"></i>
                    </a>
                    @endif
                    @if ($company->whatsapp)
                    <a href="{{ 'https://wa.me/' . $company->country_code_w_a . $company->whatsapp }}" 
                       target="_blank"
                       class="btn btn-icon btn-label-success rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="WhatsApp">
                      <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    @endif
                    @if ($company->twitter)
                    <a href="{{ strpos($company->twitter, 'http') === 0 ? $company->twitter : 'http://' . $company->twitter }}"
                       target="_blank" 
                       class="btn btn-icon btn-label-secondary rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="Twitter">
                      <i class="fa-brands fa-x-twitter"></i>
                    </a>
                    @endif
                    @if ($company->youtube)
                    <a href="{{ strpos($company->youtube, 'http') === 0 ? $company->youtube : 'http://' . $company->youtube }}"
                       target="_blank" 
                       class="btn btn-icon btn-label-danger rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="YouTube">
                      <i class="fa-brands fa-youtube"></i>
                    </a>
                    @endif
                    @if ($company->instagram)
                    <a href="{{ strpos($company->instagram, 'http') === 0 ? $company->instagram : 'http://' . $company->instagram }}"
                       target="_blank" 
                       class="btn btn-icon btn-label-danger rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="Instagram">
                      <i class="fa-brands fa-instagram"></i>
                    </a>
                    @endif
                    @if ($company->tiktok)
                    <a href="{{ strpos($company->tiktok, 'http') === 0 ? $company->tiktok : 'http://' . $company->tiktok }}"
                       target="_blank" 
                       class="btn btn-icon btn-label-secondary rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="TikTok">
                      <i class="fa-brands fa-tiktok"></i>
                    </a>
                    @endif
                  </div>
                </div>
              </div>
              <!-- Company Details Section -->
              <div class="company-details mt-4">
                @if ($company->info_company)
                <div class="mb-4">
                  <div>
                    <h6 class="mb-2">{{ __('admin.Description') }}</h6>
                    <small class="mb-0 text-secondary">{{ $company->info_company }}</small>
                  </div>
                </div>
                @endif
                
                <!-- Industry, Sectors, Technologies Row -->
                @if(!empty($industry) || (!empty($sectors) && $sectors->count() > 0) || (!empty($technologies) && $technologies->count() > 0))
                <div class="row mb-4">
                  @if(!empty($industry) || (!empty($sectors) && $sectors->count() > 0))
                  <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                    <div>
                      <h6 class="mb-3">
                        {{ $l == 'en' ? 'Industry' : 'الصناعة' }}
                        @if(!empty($sectors) && $sectors->count() > 0)
                          {{ $l == 'en' ? ' & Sectors' : ' والقطاعات' }}
                        @endif
                      </h6>
                      <div class="d-flex flex-wrap gap-2">
                        @if(!empty($industry))
                          <div class="badge bg-label-primary rounded-pill">{{ $industry->name ?? 'N/A' }}</div>
                        @endif
                        @if(!empty($sectors) && $sectors->count() > 0)
                          @foreach($sectors as $sector)
                            <div class="badge bg-label-secondary rounded-pill">
                              {{ $sector->{'name_' . $l} ?? $sector->name_en ?? $sector->name ?? 'N/A' }}
                            </div>
                          @endforeach
                        @endif
                      </div>
                    </div>
                  </div>
                  @endif
                  
                  @if(!empty($technologies) && $technologies->count() > 0)
                  <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                    <div>
                      <h6 class="mb-3">{{ $l == 'en' ? 'Technologies' : 'التقنيات' }}</h6>
                      <div class="d-flex flex-wrap gap-2">
                        @foreach($technologies as $technology)
                          <div class="badge bg-label-secondary rounded-pill">
                            {{ $technology->name }}
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                  @endif
                </div>
                @endif
                
                <!-- Technologies Used -->
                @if($company->technologies_used)
                <div class="mb-4">
                  <h6 class="mb-2">{{ __('Technologies Used') }}</h6>
                  <div class="d-flex flex-wrap gap-2">
                    @foreach(explode(',', $company->technologies_used) as $tech)
                      <span class="badge bg-label-primary rounded-pill">{{ trim($tech) }}</span>
                    @endforeach
                  </div>
                </div>
                @endif
                <!-- Contact Information -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start">
                  <!-- Location -->
                  <div class="mb-3 mb-md-0">
                    <h6 class="mb-1">{{ $l == 'en' ? 'Location' : 'الموقع' }}</h6>
                    <div class="location-info">
                      @if($company->google_map)
                        <a href="{{ $company->google_map }}" target="_blank" 
                           class="text-secondary text-decoration-none hover-primary"
                           data-bs-toggle="tooltip" data-bs-placement="top" title="Open in Google Maps">
                          <i class="ti ti-external-link ti-xs me-1"></i>
                          {{ optional($company->state)->{'name_' . $l} }}, {{ optional($company->city)->{'name_' . $l} }}, {{ optional($company->country)->{'name_' . $l} }}
                        </a>
                      @else
                        <span class="text-secondary">
                          {{ optional($company->state)->{'name_' . $l} }}, {{ optional($company->city)->{'name_' . $l} }}, {{ optional($company->country)->{'name_' . $l} }}
                        </span>
                      @endif
                    </div>
                  </div>
                  
                  <!-- Contact Actions -->
                  <div class="d-flex align-items-center gap-2">
                    @if ($company->phone)
                    <a href="tel:{{ $company->country_code . $company->phone }}" 
                       class="btn btn-icon btn-label-primary rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="Call {{ $company->country_code . $company->phone }}">
                      <i class="ti ti-phone ti-sm"></i>
                    </a>
                    @endif
                    @if ($company->email)
                    <a href="mailto:{{ $company->email }}" 
                       class="btn btn-icon btn-label-primary rounded-pill waves-effect waves-light"
                       data-bs-toggle="tooltip" data-bs-placement="top" title="Email {{ $company->email }}">
                      <i class="ti ti-mail ti-sm"></i>
                    </a>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Action Button -->
      {{-- <div class="col-md-2 d-flex flex-column align-items-center justify-content-center gap-2">
        @if( $_user->hasAnyRole('Admin|organizationManager') || $_user->ableTo('company.profile_view_mine', $permissions) )
        <a href="/dashboard/myCompany/{{ $company->id }}" class="btn btn-label-primary shadow-sm text-primary fw-bold"
          style="border-radius: 15px;">
          {{ __('admin.Edit') }} <i class="ti ti-edit mx-2"></i>
        </a>
        @else
        <a href="" data-bs-toggle="modal" data-bs-target="#MessageModal"
          class="btn btn-label-primary shadow-sm text-primary fw-bold text-nowrap" style="border-radius: 15px;">
          {{ __('admin.Contact') }} <i class="ti ti-message mx-2"></i>
        </a>
        @endif
      </div> --}}
      <!-- / Action Button -->
    </div>
  </div>
  <!-- / Company Info Card -->
</section>