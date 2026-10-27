{{-- Projects Gallery Tab --}}
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0 d-flex align-items-center">
      <i class="ti ti-folder me-2"></i>
      {{ $l == 'en' ? 'Projects Gallery' : 'معرض المشاريع' }}
    </h5>
    <div class="header-info">
      <span class="badge bg-label-primary">
        {{ $teams->count() }} {{ $l == 'en' ? 'Projects' : 'مشروع' }}
      </span>
    </div>
  </div>
  
  <div class="card-body">
    @if($teams->isNotEmpty())
      {{-- Gallery Controls --}}
      <div class="row mb-6">
        <div class="col-md-8 mb-3 mb-md-0">
          <div class="filter-tags d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-outline-secondary active" onclick="filterByCategory('all')" data-category="all">
              {{ $l == 'en' ? 'All Projects' : 'جميع المشاريع' }}
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="filterByCategory('winner')" data-category="winner">
              <i class="ti ti-trophy me-1"></i>
              {{ $l == 'en' ? 'Winners Only' : 'الفائزون فقط' }}
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="filterByCategory('completed')" data-category="completed">
              <i class="ti ti-check me-1"></i>
              {{ $l == 'en' ? 'Completed Projects' : 'المشاريع المكتملة' }}
            </button>

          </div>
        </div>
        <div class="col-md-4">
          <div class="row">
            <div class="col-12">
              <div class="input-group input-group-merge">
                <span class="input-group-text">
                  <i class="ti ti-search text-muted"></i>
                </span>
                <input 
                  type="text" 
                  class="form-control" 
                  id="project-search" 
                  placeholder="{{ $l == 'en' ? 'Search projects...' : 'البحث في المشاريع...' }}"
                  onkeyup="filterProjects()"
                >
              </div>
            </div>
            {{-- <div class="col-4 col-lg-5">
              <select class="form-select" id="region-filter" onchange="filterByRegion(this.value)">
                <option value="all">{{ $l == 'en' ? 'All Regions' : 'جميع المناطق' }}</option>
                <option value="riyadh">{{ $l == 'en' ? 'Riyadh' : 'الرياض' }}</option>
                <option value="jeddah">{{ $l == 'en' ? 'Jeddah' : 'جدة' }}</option>
                <option value="dammam">{{ $l == 'en' ? 'Dammam' : 'الدمام' }}</option>
                <option value="makkah">{{ $l == 'en' ? 'Makkah' : 'مكة' }}</option>
              </select>
            </div> --}}
          </div>
        </div>
      </div>

      {{-- Projects List View --}}
      <div id="projects-list" class="projects-gallery">
        <div class="row">
          <div class="col-12">
            @foreach($teams as $team)
              @if($team->project)
                <div class="card border shadow-xs project-list-item mb-3" 
                     data-category="{{ ($team->winner && $team->winner > 0) ? 'winner' : 'participant' }}"
                     data-project-name="{{ strtolower($team->project->name ?? '') }}"
                     data-team-name="{{ strtolower($team->name ?? '') }}"
                     data-project-status="completed"
                     data-team-region="{{ strtolower($team->region ?? '') }}">
                  <div class="card-body">
                    <div class="row align-items-center">
                      <div class="col-md-2">
                        @if($team->img)
                          <img src="{{ $team->img }}" alt="{{ $team->project->name ?? 'Project' }}" class="img-fluid rounded" style="max-height: 80px;">
                        @else
                          <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 80px;">
                            <i class="ti ti-folder-open text-muted fs-2"></i>
                          </div>
                        @endif
                      </div>
                      <div class="col-md-8">
                        <div class="d-flex align-items-start justify-content-between">
                          <div>
                            <h5 class="mb-1">
                              {{ $team->project->name ?? ($l == 'en' ? 'Untitled Project' : 'مشروع بدون عنوان') }}
                              @if($team->winner && $team->winner > 0)
                                @if($team->winner == 1)
                                  <span class="badge bg-label-warning ms-2 fs-tiny">
                                    <i class="ti ti-trophy ti-16px me-1"></i>{{ $l == 'en' ? '1st Place' : 'المركز الأول' }}
                                  </span>
                                @elseif($team->winner == 2)
                                  <span class="badge bg-label-secondary ms-2 fs-tiny">
                                    <i class="ti ti-award ti-16px me-1"></i>{{ $l == 'en' ? '2nd Place' : 'المركز الثاني' }}
                                  </span>
                                @elseif($team->winner == 3)
                                  <span class="badge bg-label-info ms-2 fs-tiny">
                                    <i class="ti ti-award ti-16px me-1"></i>{{ $l == 'en' ? '3rd Place' : 'المركز الثالث' }}
                                  </span>
                                @endif
                              @endif
                            </h5>
                            <p class="text-muted small mb-2">
                              <i class="ti ti-users me-1"></i>{{ $l == 'en' ? 'By Team' : 'بواسطة فريق' }} {{ $team->name }}
                              @if($team->region)
                                <span class="mx-2">•</span>
                                <i class="ti ti-map-pin me-1"></i>{{ $team->region }}
                              @endif
                            </p>
                            <p class="mb-2">{{ Str::limit($team->project->description, 200) }}</p>
                            <div class="d-flex align-items-center">
                              <span class="badge bg-label-success me-2">
                                <i class="ti ti-check me-1"></i>{{ $l == 'en' ? 'Completed' : 'مكتمل' }}
                              </span>
                              @if($team->likes && $team->likes->count() > 0)
                                <span class="text-muted small me-3">
                                  <i class="ti ti-heart text-danger me-1"></i>{{ $team->likes->count() }} {{ $l == 'en' ? 'Likes' : 'إعجاب' }}
                                </span>
                              @endif
                              <span class="text-muted small">
                                <i class="ti ti-user me-1"></i>{{ $team->members->count() }} {{ $l == 'en' ? 'Members' : 'عضو' }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-2 text-end mt-2 mt-md-0">
                        <div class="d-grid gap-2">
                          <a href="{{ route('hackathon.viewTeam', $team->id) }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-users ti-sm me-1"></i>
                            {{ $l == 'en' ? 'View Team' : 'عرض الفريق' }}
                          </a>
                          <button class="btn btn-outline-secondary btn-sm like-project-action" data-project="{{ $team->id }}">
                            <i class="ti ti-heart me-1"></i>
                            <span class="likes-count">{{ $team->likes ? $team->likes->count() : 0 }}</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            @endforeach
          </div>
        </div>
      </div>

      {{-- No Results Message --}}
      <div id="no-results" class="text-center py-5 d-none">
        <i class="ti ti-search text-muted" style="font-size: 4rem;"></i>
        <h4 class="text-muted mt-3 mb-2">{{ $l == 'en' ? 'No Projects Found' : 'لا توجد مشاريع' }}</h4>
        <p class="text-muted">{{ $l == 'en' ? 'Try adjusting your search or filter criteria.' : 'حاول تعديل معايير البحث أو التصفية.' }}</p>
      </div>

    @else
      {{-- No Projects State --}}
      <div class="text-center py-5">
        <i class="ti ti-folder text-muted" style="font-size: 4rem;"></i>
        <h4 class="text-muted mt-3 mb-2">{{ $l == 'en' ? 'No Projects Available' : 'لا توجد مشاريع متاحة' }}</h4>
        <p class="text-muted">{{ $l == 'en' ? 'Projects from this hackathon will be displayed here.' : 'ستظهر مشاريع هذا الهاكاثون هنا.' }}</p>
      </div>
    @endif
  </div>
</div>

@push('scripts')
{{-- JavaScript Functions --}}
<script>
// Filter by category
function filterByCategory(category) {
  // Update active filter button
  document.querySelectorAll('[data-category]').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector(`[data-category="${category}"]`).classList.add('active');
  
  applyAllFilters();
}

// Filter by region
function filterByRegion(region) {
  applyAllFilters();
}



// Search/filter projects
function filterProjects() {
  applyAllFilters();
}

// Apply all filters together
function applyAllFilters() {
  const searchTerm = document.getElementById('project-search').value.toLowerCase();
  const selectedRegion = document.getElementById('region-filter').value;
  const activeCategory = document.querySelector('[data-category].active')?.getAttribute('data-category') || 'all';
  
  const projects = document.querySelectorAll('.project-list-item');
  let visibleCount = 0;
  
  projects.forEach(project => {
    const projectName = project.getAttribute('data-project-name');
    const teamName = project.getAttribute('data-team-name');
    const teamRegion = project.getAttribute('data-team-region');
    const projectCategory = project.getAttribute('data-category');
    const projectStatus = project.getAttribute('data-project-status');
    
    let showProject = true;
    
    // Search filter
    if (searchTerm && !projectName.includes(searchTerm) && !teamName.includes(searchTerm)) {
      showProject = false;
    }
    
    // Region filter
    if (selectedRegion !== 'all' && !teamRegion.includes(selectedRegion.toLowerCase())) {
      showProject = false;
    }
    
    // Category filter
    if (activeCategory === 'winner' && projectCategory !== 'winner') {
      showProject = false;
    } else if (activeCategory === 'completed' && projectStatus !== 'completed') {
      showProject = false;
    }
    // 'all' category shows everything (no additional filter)
    
    if (showProject) {
      project.style.display = '';
      visibleCount++;
    } else {
      project.style.display = 'none';
    }
  });
  
  // Show/hide no results message
  const noResults = document.getElementById('no-results');
  if (visibleCount === 0) {
    noResults.classList.remove('d-none');
  } else {
    noResults.classList.add('d-none');
  }
}



// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>
@endpush
