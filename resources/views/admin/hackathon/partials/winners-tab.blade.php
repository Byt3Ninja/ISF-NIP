
<div class="container bg-white p-8">
  @if( $currentHackathon['announcement'] == true )
    <div class="row">
      @if( $winners->isEmpty() )
      <div class="col-md-12 col-lg-12 col-sm-12 mb-4 text-center">
        {{ $l == 'en' ? 'No Winners' : 'لا يوجد فايزين' }}
      </div>
      @else
        @foreach( $winners as $winner )
          @php $winner = (object) $winner; @endphp
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow">
              <!-- Header -->
              <div class="card-header bg-light border-0 d-flex align-items-center py-3">
                <img src="{{ $winner->logo ?? '/images/logo-placeholder.png' }}" class="rounded-circle me-3 border shadow-xs" alt="Startup Logo" width="45" height="45">
                <div>
                  <h6 class="mb-0 fw-bold text-dark">{{ $winner?->name ?? '-' }}</h6>
                  <small class="text-dark">{{ $winner?->teamName ?? '-' }}</small>
                </div>
              </div>
              
              <div class="card-body px-4 py-4">
                <!-- Project Title -->
                <div class="row">
                  <div class="col-md-9 col-lg-9 col-sm-12">
                    <h5 class="card-title text-dark mb-3 fw-bold lh-base">{{ $winner?->projectTitle ?? '-' }}</h5>
                  </div>
                  <div class="col-md-3 col-lg-3 col-sm-12 text-end">
                    <i data-project="{{ $winner?->id ?? 0 }}" class="like-project-action {{ Auth::check() ? ( in_array( Auth::user()->id, $winner->likes->toArray() ) ? 'fas' : 'far' ) : 'fas' }} fa-fw fa-sm fa-heart text-danger cursor-pointer"></i>
                    <span class="likes-count"> @if($winner->likes == null) 0 @else {{ $winner->likes->count() }} @endif  </span>
                  </div>
                </div>
                
                <!-- Project Description -->
                <p class="card-text text-muted mb-4 lh-lg">{{ $winner?->description ?? '-' }}</p>
                          
                <!-- Team Members -->
                <div class="mb-0">
                  <h6 class="text-dark mb-3 fw-semibold d-flex align-items-center">
                    <span class="me-2">{{ $l == 'en' ? 'Team Members' : 'اعضاء الفريق' }}</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 small">{{ count($winner->teamMembers) }}</span>
                  </h6>
                  
                  @if(count($winner->teamMembers) <= 4)
                    <!-- Full width layout for 4 or fewer members -->
                    <div class="row g-2">
                      @foreach ($winner->teamMembers as $member)
                      <div class="col-12">
                        <div class="bg-white border rounded p-2 {{ $member->isLeader ? 'border-primary bg-opacity-10' : '' }}">
                          <div class="d-flex align-items-center">
                            <img src="{{ $member->image ?? '/icons/account.svg' }}" class="rounded-circle me-3 border" alt="{{ $member->name }}" width="40" height="40">
                            <div class="flex-grow-1">
                              <div class="fw-medium text-dark small mb-1 d-flex align-items-center">
                                {{ $member->name }}
                              </div>
                              <small class="text-muted">{{ $member->role }}</small>
                            </div>
                            @if($member->isLeader)
                              <span class="badge bg-label-primary text-primary fw-semibold px-2 py-1 small">
                                <i class="bi bi-award me-1"></i> {{ $l == 'ar' ? 'قائد' : 'Leader' }}
                              </span>
                            @endif
                          </div>
                        </div>
                      </div>
                      @endforeach
                    </div>
                  @else
                    <!-- Compact list layout for more than 4 members -->
                    <div class="bg-light rounded p-3">
                      <div class="row g-2">
                        @foreach ($winner->teamMembers as $index => $member)
                        <div class="col-12">
                          <div class="d-flex align-items-center py-1 {{ $member->isLeader ? 'bg-warning bg-opacity-10 rounded px-2' : '' }}">
                            <img src="{{ $member->image ?? '/icons/account.svg' }}" class="rounded-circle me-3 border" alt="{{ $member->name }}" width="32" height="32">
                            <div class="flex-grow-1">
                              <div class="fw-medium text-dark small d-flex align-items-center">
                                {{ $member->name }}
                                @if($member->isLeader)
                                  <i class="bi bi-star-fill text-warning ms-2" title="Team Leader"></i>
                                @endif
                              </div>
                              <small class="text-muted">{{ $member->role }}</small>
                            </div>
                            @if($member->isLeader)
                              <span class="badge bg-warning text-dark fw-semibold px-2 py-1 small">
                                <i class="bi bi-award me-1"></i>{{ $l == 'ar' ? 'قائد' : 'Leader' }}
                              </span>
                            @endif
                          </div>
                          @if(!$loop->last)
                          <hr class="my-2 opacity-25">
                          @endif
                        </div>
                        @endforeach
                      </div>
                      
                      @if(count($winner->teamMembers) > 6)
                      <div class="text-center mt-2">
                        <small class="text-muted">{{ count($winner->teamMembers) }} team members total</small>
                      </div>
                      @endif
                    </div>
                  @endif
                </div>
              </div>
              <div class="card-footer">
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                  <button class="btn btn-sm btn-outline-secondary open-details-modal" data-id="{{ $winner->id }}">
                    {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
                  </button>
                  @if(isset($currentHackathon['visibility']) && $currentHackathon['visibility'] === 'public')
                    <div class="share-buttons-card d-flex gap-1" data-winner-id="{{ $winner->id }}">
                      <button type="button" class="btn btn-sm btn-linkedin card-share-btn" data-platform="linkedin" data-winner-id="{{ $winner->id }}" title="{{ $l == 'en' ? 'Share on LinkedIn' : 'شارك على لينكدان' }}">
                        <i class="ti ti-brand-linkedin"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-twitter card-share-btn" data-platform="twitter" data-winner-id="{{ $winner->id }}" title="{{ $l == 'en' ? 'Share on X' : 'شارك على X' }}">
                        <i class="ti ti-brand-twitter"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-facebook card-share-btn" data-platform="facebook" data-winner-id="{{ $winner->id }}" title="{{ $l == 'en' ? 'Share on Facebook' : 'شارك على فيسبوك' }}">
                        <i class="ti ti-brand-facebook"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-secondary card-copy-link-btn" data-winner-id="{{ $winner->id }}" title="{{ $l == 'en' ? 'Copy Link' : 'نسخ الرابط' }}">
                        <i class="ti ti-link"></i>
                      </button>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @endforeach
      @endif
    </div>
  @endif
  @if( $currentHackathon['announcement'] == false )
    <div class="row">
      <div class="col-md-12 col-lg-12 col-sm-12 mb-4 text-center">
        {{ $l == 'en' ? 'No Winners Announced' : 'لم يتم الاعلان عن الفائزين' }}
      </div>
    </div>
  @endif
</div>

@push('scripts')
  @include('includes.winnerModal', ['winners' => $winners, 'currentHackathon' => $currentHackathon, 'l' => $l])
@endpush
<div class="modal fade" id="solutionDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title border-bottom pb-3 w-100" id="solutionDetailsModalLabel"> {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }} </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table table-custom table-bordered">
            <tbody>
              <tr>
                <td>{{ __('admin.Team Name') }}</td>
                <td id="td-team"></td>
              </tr>
              <tr>
                <td>{{ __('admin.Project Name') }}</td>
                <td id="td-project"></td>
              </tr>
              <tr>
                <td>{{ __('admin.description') }}</td>
                <td id="td-description"></td>
              </tr>
            </tbody>
          </table>

          @if(isset($currentHackathon['visibility']) && $currentHackathon['visibility'] === 'public')
          <div class="mt-4 pt-3 border-top">
            <h6 class="mb-3">
              <i class="ti ti-share me-1"></i>
              {{ $l == 'en' ? 'Share this Solution' : 'شارك هذا الحل' }}
            </h6>
            <div id="modal-share-buttons" class="share-buttons d-flex flex-wrap gap-2">
              <!-- Share buttons will be dynamically inserted here -->
            </div>
          </div>
          @endif
        </div>
      </div>
  </div>
</div>
