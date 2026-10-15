<section class="mb-8">
  <!-- Company Info Card -->
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-12">
          <div class="d-flex flex-column flex-md-row align-items-start" style="gap: 1.5rem;">
            <div class="flex-shrink-0">
              <div class="company-logo me-4 me-md-0 shadow-xs border rounded">
                <img 
                  src="{{ $company->logo ? asset( $company->logo) : '/images/logo-placeholder.png' }}"
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
                      <a href="{{ strpos($company->website, 'http') === 0 ? $company->website : 'http://' . $company->website }}"  target="_blank" class="text-heading text-decoration-none">
                        <h5 class="mb-0 fw-bold text-heading">{{ $company->{'name_' . $l} }}</h5>
                      </a>
                      @else
                        <h5 class="mb-0 text-heading">{{ $company->{'name_' . $l} }}</h5>
                      @endif
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-2 mb-d mb-md-0">
                    @if ($company->est_date)
                      <span class="badge bg-label-info rounded-pill">
                        <i class="ti ti-calendar ti-xs me-1"></i>
                        {{ __('admin.Established in') }} {{ \Carbon\Carbon::parse($company->est_date)->format('Y') }}
                      </span>
                    @endif
                    @if ($company->team_num)
                      <span class="badge bg-label-warning rounded-pill">
                        <i class="ti ti-users-group ti-xs me-1"></i>
                        {{ __('startup.startup_size') }}:

                        @php
                            $singleDigits = ['1','2','3','4','5','6','7','8','9'];
                            
                            if (in_array($company->team_num, $singleDigits)) {
                                echo __('startup.startup_size_' . $company->team_num);
                            } else {
                                // Translate "employees" based on language
                                $numRange = $company->team_num; // e.g., "11-50 employees"
                                if ($l === 'ar') {
                                    $numRange = str_replace('employees', 'موظف', $numRange);
                                } else {
                                    // Keep English as is
                                    $numRange = str_replace('موظف', 'employees', $numRange);
                                }
                                echo $numRange;
                            }
                        @endphp
                      </span>
                    @endif

                    {{-- Alumni Badge --}}
                    @if ($company->alumni == '1' || $company->alumni == 1)
                      <span class="badge bg-label-danger rounded-pill">
                        <i class="ti ti-pennant ti-xs me-1"></i>
                        {{ __('startup.alumni') }}
                      </span>
                    @endif
                  </div>
                </div>
                <div class="social-links d-flex align-items-center justify-content-start justify-content-md-end">
                  <div class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2">
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
                      <i class="fa-brands fa-whatsapp fs-5"></i>
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
              <!-- Company Details Section -->
              <div class="company-details mt-4">
                @if ($company->info_company)
                <div class="mb-4">
                  <div>
                    <h6 class="mb-0">{{ __('admin.Description') }}</h6>
                    <small class="mb-0 text-secondary">{{ $company->info_company }}</small>
                  </div>
                </div>
                @endif
                
                <!-- Attachments -->
                {{-- Shown only to people the file route would let through (FileAccess), not by role name. --}}
                @if ($company->viewerFileUrl('Commercial_Register') || $company->viewerFileUrl('Tax_Card'))
                  @if($company->CreatedTax == '1')
                    <div class="mb-4">
                      <h6 class="mb-2">{{ $l == 'en' ? 'Attachments' : 'المرفقات' }}</h6>
                      <div class="d-flex flex-wrap gap-3">
                        @if($company->viewerFileUrl('Commercial_Register'))
                          @php
                            $fileUrl = $company->viewerFileUrl('Commercial_Register');
                            $extension = strtolower(pathinfo($company->Commercial_Register, PATHINFO_EXTENSION));
                          @endphp
                          <div class="d-flex align-items-center gap-2">
                            @if(in_array($extension, ['jpg','jpeg','png']))
                              <i class="ti ti-photo text-secondary fs-5"></i>
                            @elseif($extension === 'pdf')
                              <i class="ti ti-file-type-pdf text-danger fs-5"></i>
                            @else
                              <i class="ti ti-file text-secondary fs-5"></i>
                            @endif
                            <a class="badge bg-label-secondary" href="{{ $fileUrl }}" target="_blank">
                              {{ $l == 'en' ? 'Commercial Register' : 'السجل التجاري' }}
                            </a>
                          </div>
                        @endif
                        @if($company->viewerFileUrl('Tax_Card'))
                          @php
                            $fileUrl = $company->viewerFileUrl('Tax_Card');
                            $extension = strtolower(pathinfo($company->Tax_Card, PATHINFO_EXTENSION));
                          @endphp
                          <div class="d-flex align-items-center gap-2">
                            @if(in_array($extension, ['jpg','jpeg','png']))
                              <i class="ti ti-photo text-secondary fs-5"></i>
                            @elseif($extension === 'pdf')
                              <i class="ti ti-file-type-pdf text-danger fs-5"></i>
                            @else
                              <i class="ti ti-file text-secondary fs-5"></i>
                            @endif
                            <a class="badge bg-label-secondary" href="{{ $fileUrl }}" target="_blank">
                              {{ $l == 'en' ? 'Tax Card' : 'البطاقة الضريبية' }}
                            </a>
                          </div>
                        @endif
                      </div>
                    </div>
                  @endif
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
                          {{ optional($company->state)->{'name_' . $l} ? optional($company->state)->{'name_' . $l} . ', ' : '' }}
                          {{ optional($company->city)->{'name_' . $l} ? optional($company->city)->{'name_' . $l} . ', ' : '' }} 
                          {{ optional($company->country)->{'name_' . $l} }}
                        </a>
                      @else
                        <span class="text-secondary">
                          {{ optional($company->state)->{'name_' . $l} }}, {{ optional($company->city)->{'name_' . $l} }}, {{ optional($company->country)->{'name_' . $l} }}
                        </span>
                      @endif
                    </div>
                  </div>                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- / Company Info Card -->
</section>

<!-- Sectors Modal -->
@if(!empty($sectors) && $sectors->count() > 6)
<div class="modal fade" id="sectorsModal" tabindex="-1" aria-labelledby="sectorsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sectorsModalLabel">{{ $l == 'en' ? 'Industry & Sectors' : 'الصناعة والقطاعات' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if(!empty($industry))
        <div class="mb-3">
          <h6 class="mb-2">{{ $l == 'en' ? 'Industry' : 'الصناعة' }}</h6>
          <div class="badge bg-label-primary rounded-pill">{{ $industry->{'name_' . $l} ?? $industry->name_en ?? $industry->name ?? 'N/A' }}</div>
        </div>
        @endif
        <div>
          <h6 class="mb-2">{{ $l == 'en' ? 'Sectors' : 'القطاعات' }}</h6>
          <div class="d-flex flex-wrap gap-2">
            @foreach($sectors as $sector)
              <div class="badge bg-label-secondary rounded-pill">
                {{ $sector->{'name_' . $l} ?? $sector->name_en ?? $sector->name ?? 'N/A' }}
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif