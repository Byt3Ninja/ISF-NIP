<div class="mt-6">
  <div class="row">
    <div class="col-12">
      @if($milestones && $milestones->count() > 0)
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <h5 class="mb-0">{{ __('startup.milestones') }}</h5>
                <span class="badge bg-label-primary ms-3">{{ count($milestones) }}</span>
              </div>
              @if(count($milestones) > 3)
                <div class="d-flex align-items-center gap-2">
                  <button id="milestonesPrev" class="btn btn-icon btn-outline-secondary shadow-xs rounded-circle" disabled>
                    <i class="ti ti-chevron-{{ $l == 'en' ? 'left' : 'right' }} ti-sm"></i>
                  </button>
                  <button id="milestonesNext" class="btn btn-icon btn-outline-secondary shadow-xs rounded-circle">
                    <i class="ti ti-chevron-{{ $l == 'en' ? 'right' : 'left' }} ti-sm"></i>
                  </button>
                </div>
              @endif
            </div>
            
            <div class="position-relative">
              <div id="milestonesCarousel" class="overflow-hidden">
                <div id="milestonesContainer" class="d-flex transition-all my-2" style="transform: translateX(0px); transition: transform 0.3s ease;">
                  @foreach($milestones as $index => $milestone)
                  <div class="milestone-card flex-shrink-0 pe-4" style="width: 33.333%;">
                    <div class="card h-100 border shadow-xs">
                      <div class="card-body d-flex flex-column">
                        <!-- Milestone Header -->
                        <div class="d-flex align-items-center mb-3">
                          <div class="flex-grow-1" style="max-width: calc(100% - 10px);">
                            <h6 class="mb-0 fw-semibold line-clamp-2">{{ $milestone->name }}</h6>
                          </div>
                        </div>
                        
                        <!-- Milestone Description -->
                        <p class="text-muted mb-3 flex-grow-1" style="font-size: 0.9rem;">
                          {{ $milestone->description }}
                        </p>
                        
                        <!-- Progress Bar -->
                        <div class="mb-3">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">{{ $l == 'ar' ? 'نسبة الإكمال' : 'Completion' }}</small>
                            <small class="fw-semibold {{ $milestone->completion_percentage >= 100 ? 'text-success' : 'text-primary' }}">
                              {{ $milestone->completion_percentage ?? 0 }}%
                            </small>
                          </div>
                          <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $milestone->completion_percentage >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                  role="progressbar" 
                                  style="width: {{ $milestone->completion_percentage ?? 0 }}%"
                                  aria-valuenow="{{ $milestone->completion_percentage ?? 0 }}" 
                                  aria-valuemin="0" 
                                  aria-valuemax="100">
                            </div>
                          </div>
                        </div>
                        
                        <!-- Supporting Documents -->
                        @if($milestone->documents)
                          @php
                            $documents = is_string($milestone->documents) ? json_decode($milestone->documents, true) : $milestone->documents;
                            $documents = is_array($documents) ? $documents : [];
                            // only documents this viewer may open (their link is null otherwise)
                            $documents = array_values(array_filter($documents, fn ($doc) => !empty($doc['url'])));
                          @endphp
                          @if(count($documents) > 0)
                          <div class="mt-auto">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                              <small class="text-muted">{{ $l == 'ar' ? 'المستندات' : 'Documents' }}</small>
                              <span class="badge bg-label-secondary">{{ count($documents) }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                              @foreach(array_slice($documents, 0, 3) as $doc)
                                <a href="{{ $doc['url'] ?? '#' }}" 
                                    target="_blank" 
                                    class="btn btn-xs btn-outline-primary p-1" 
                                    title="{{ $doc['name'] ?? 'Document' }}">
                                  <i class="ti ti-file-text" style="font-size: 12px;"></i>
                                </a>
                              @endforeach
                              @if(count($documents) > 3)
                                <span class="btn btn-xs btn-outline-secondary p-1" title="{{ count($documents) - 3 }} more documents">
                                  +{{ count($documents) - 3 }}
                                </span>
                              @endif
                            </div>
                          </div>
                          @endif
                        @endif
                        
                        <!-- Milestone Status Badge -->
                        <div class="mt-2">
                          {{-- @if($milestone->completion_percentage >= 100)
                            <span class="badge bg-label-success">
                              <i class="ti ti-check me-1"></i>
                              {{ $l == 'ar' ? 'مكتمل' : 'Completed' }}
                            </span>
                          @elseif($milestone->completion_percentage > 0)
                            <span class="badge bg-label-warning">
                              <i class="ti ti-clock me-1"></i>
                              {{ $l == 'ar' ? 'قيد التنفيذ' : 'In Progress' }}
                            </span>
                          @else
                            <span class="badge bg-label-secondary">
                              <i class="ti ti-calendar me-1"></i>
                              {{ $l == 'ar' ? 'مخطط' : 'Planned' }}
                            </span>
                          @endif --}}
                          
                          @if($milestone->created_at)
                            <small class="text-muted d-block mt-1">
                              {{ $l == 'ar' ? 'أضيف في' : 'Added on' }} {{ $milestone->created_at->format('M Y') }}
                            </small>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
            </div>    
          </div>
        </div>
      @else
        @include('includes.no-result', ['message' => __('startup.no_milestones_added_yet')])
      @endif
    </div>
  </div>
</div>