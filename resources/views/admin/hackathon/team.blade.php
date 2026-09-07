@extends('layouts.admin')

@section('title', $team->name . ' - Team Details')

@section('content')
<!-- Main Content -->
<div class="">
  <div class="">
    
    {{-- Breadcrumb Navigation --}}
    {{-- <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('dashboard') }}">
            <i class="ti ti-home"></i> {{ $l == 'ar' ? 'الرئيسية' : 'Home' }}
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ url()->previous() }}">
            <i class="ti ti-trophy"></i> {{ $l == 'ar' ? 'الهاكاثون' : 'Hackathon' }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ $team->name }}
        </li>
      </ol>
    </nav> --}}

    {{-- Check if the team object exists --}}
    @if($team)
      
      {{-- Page Header with Team Overview --}}
      <div class="row mb-4">
        <div class="col-12">
          <div class="card bg-gradient-primary text-white">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-md-2 text-center">
                  {{-- Team Avatar --}}
                  @if($team->photo)
                    <img 
                      src="{{ $team->photo }}" 
                      alt="{{ $team->name }}" 
                      class="rounded-circle border-3 border-white"
                      style="width: 120px; height: 120px; object-fit: cover;"
                      onerror="this.onerror=null;this.src='https://placehold.co/400x200?text={{ urlencode($team->name) }}';"
                    >
                    @else
                    <img 
                      src="https://ui-avatars.com/api/?name={{ urlencode($team->name) }}&background=random&color=fff&size=120&rounded=true"
                      alt="{{ $team->name }}"
                      class="rounded-circle border-3 border-white shadow"
                      style="width: 120px; height: 120px; object-fit: cover;"
                    >
                    <div class="mt-2 d-flex align-items-center text-white-75 small">
                      <i class="ti ti-info-circle me-1"></i>
                      {{ $l == 'ar' ? 'لا توجد صورة للفريق' : 'No team photo available' }}
                    </div>
                  @endif
                </div>
                
                <div class="col-md-7">
                  <h1 class="text-white mb-2">{{ $team->name }}</h1>
                  @if($team->desc)
                    <p class="text-white-75 mb-3">{{ $team->desc }}</p>
                  @endif
                  
                  {{-- Quick Stats --}}
                  <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center text-white-75">
                      <i class="ti ti-users me-2"></i>
                      <span>{{ count($team->members) }} {{ $l == 'ar' ? 'أعضاء' : 'Members' }}</span>
                    </div>
                    @if($team->region)
                      <div class="d-flex align-items-center text-white-75">
                        <i class="ti ti-map-pin me-2"></i>
                        <span>{{ __('admin.'.$team->region) }}</span>
                      </div>
                    @endif
                    <div class="d-flex align-items-center text-white-75">
                      <i class="ti ti-calendar me-2"></i>
                      <span>{{ \Carbon\Carbon::parse($team->created_at)->format('M Y') }}</span>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 d-flex justify-content-end">
                  {{-- Status Badges --}}
                  @if($team->winner_status)
                    <div class="badge bg-warning text-dark fs-6 mb-2 d-block">
                      <i class="ti ti-trophy me-1"></i>{{ $l == 'ar' ? 'فائز' : 'Winner' }}
                    </div>
                  @endif
                  
                  {{-- @if($team->cycle)
                    <div class="text-white small w-px-100 text-start">
                      <strong>{{ $l == 'ar' ? 'الدورة:' : 'Cycle:' }}</strong><br>
                      {{ $team->cycle->title }}
                    </div>
                  @endif --}}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Navigation Tabs --}}
      <div class="row mb-4">
        <div class="col-12">
          <ul class="nav nav-pills nav-fill" id="teamTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" 
                      data-bs-target="#overview" type="button" role="tab" 
                      aria-controls="overview" aria-selected="true">
                <i class="ti ti-eye me-2"></i>{{ $l == 'ar' ? 'نظرة عامة' : 'Overview' }}
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="members-tab" data-bs-toggle="pill" 
                      data-bs-target="#members" type="button" role="tab" 
                      aria-controls="members" aria-selected="false">
                <i class="ti ti-users me-2"></i>{{ $l == 'ar' ? 'الأعضاء' : 'Members' }} ({{ count($team->members) }})
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="project-tab" data-bs-toggle="pill" 
                      data-bs-target="#project" type="button" role="tab" 
                      aria-controls="project" aria-selected="false">
                <i class="ti ti-bulb me-2"></i>{{ $l == 'ar' ? 'المشروع' : 'Project' }}
              </button>
            </li>
            @if(count($team->shared_files) > 0)
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="files-tab" data-bs-toggle="pill" 
                      data-bs-target="#files" type="button" role="tab" 
                      aria-controls="files" aria-selected="false">
                <i class="ti ti-paperclip me-2"></i>{{ $l == 'ar' ? 'الملفات' : 'Files' }} ({{ count($team->shared_files) }})
              </button>
            </li>
            @endif
            
            @if(isset($team->todo_lists[0]) && $team->todo_lists[0]?->team_formation_id == auth()->user()->team_member?->team_formation_id)
            @if(count($team->todo_lists) > 0)
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tasks-tab" data-bs-toggle="pill" 
                      data-bs-target="#tasks" type="button" role="tab" 
                      aria-controls="tasks" aria-selected="false">
                <i class="ti ti-checklist me-2"></i>{{ $l == 'ar' ? 'المهام' : 'Tasks' }} ({{ count($team->todo_lists) }})
              </button>
            </li>
            @endif
            @endif
          </ul>
        </div>
      </div>

      {{-- Tab Content --}}
      <div class="tab-content px-0" id="teamTabsContent">
        
        {{-- Overview Tab --}}
        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
          <div class="row">
            {{-- Team Statistics Cards --}}
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body text-center">
                  <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                      <i class="ti ti-users ti-lg"></i>
                    </span>
                  </div>
                  <h4 class="mb-1">{{ count($team->members) }}</h4>
                  <p class="mb-0">{{ $l == 'ar' ? 'إجمالي الأعضاء' : 'Total Members' }}</p>
                </div>
              </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body text-center">
                  <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-success">
                      <i class="ti ti-paperclip ti-lg"></i>
                    </span>
                  </div>
                  <h4 class="mb-1">{{ count($team->shared_files) }}</h4>
                  <p class="mb-0">{{ $l == 'ar' ? 'الملفات المشتركة' : 'Shared Files' }}</p>
                </div>
              </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body text-center">
                  <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-info">
                      <i class="ti ti-checklist ti-lg"></i>
                    </span>
                  </div>
                  <h4 class="mb-1">{{ count($team->todo_lists) }}</h4>
                  <p class="mb-0">{{ $l == 'ar' ? 'المهام' : 'Tasks' }}</p>
                </div>
              </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body text-center">
                  <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-warning">
                      <i class="ti ti-check ti-lg"></i>
                    </span>
                  </div>
                  <h4 class="mb-1">{{ collect($team->todo_lists)->where('status', 'completed')->count() }}</h4>
                  <p class="mb-0">{{ $l == 'ar' ? 'المهام المكتملة' : 'Completed Tasks' }}</p>
                </div>
              </div>
            </div>
          </div>

          {{-- Team Information Card --}}
          <div class="row">
            <div class="col-12 mb-4">
              <div class="card">
                <div class="card-header">
                  <h5 class="mb-0">
                    <i class="ti ti-info-circle me-2"></i>{{ $l == 'ar' ? 'معلومات الفريق' : 'Team Information' }}
                  </h5>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">{{ $l == 'ar' ? 'اسم الفريق' : 'Team Name' }}</label>
                      <p class="mb-0 text-muted">{{ $team->name }}</p>
                    </div>
                    @if($team->desc)
                    <div class="col-md-12 mb-3">
                      <label class="form-label fw-semibold">{{ $l == 'ar' ? 'الوصف' : 'Description' }}</label>
                      <p class="mb-0 text-muted">{{ $team->desc }}</p>
                    </div>
                    @endif
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">{{ $l == 'ar' ? 'تاريخ الإنشاء' : 'Created Date' }}</label>
                      <p class="mb-0 text-muted">{{ \Carbon\Carbon::parse($team->created_at)->format('F d, Y \a\t H:i') }}</p>
                    </div>
                    @if($team->region)
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">{{ $l == 'ar' ? 'المنطقة' : 'Region' }}</label>
                      <p class="mb-0 text-muted">{{ __('admin.'.$team->region) }}</p>
                    </div>
                    @endif
                    @if($team->max_members)
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">{{ $l == 'ar' ? 'الحد الأقصى للأعضاء' : 'Max Members' }}</label>
                      <p class="mb-0 text-muted">{{ $team->max_members }}</p>
                    </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Members Tab --}}
        <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
          <div class="row">
            @foreach($team->members as $member)
              <div class="col-lg-4 col-md-6 mb-4">
                <a href="{{ route('mentors.profile_view', $member->id) }}">
                  <div class="card h-100">
                    <div class="card-body text-center position-relative">
                      {{-- Leader Badge --}}
                      @if($member->is_leader)
                      <div class="position-absolute top-0 start-50 translate-middle">
                        <span class="badge bg-warning text-dark">
                          <i class="ti ti-crown"></i> {{ $l == 'ar' ? 'قائد الفريق' : 'Team Leader' }}
                        </span>
                      </div>
                      @endif
                      
                      {{-- Member Avatar --}}
                      <div class="avatar avatar-xl mx-auto mb-3">
                        <img 
                          src="{{ '/191014/'. $member->img_url ?? '/icons/account.svg' }}"
                          onerror="this.onerror=null;this.src='/icons/account.svg';"
                          alt="{{ $member->name }}" 
                          class="avatar-img rounded-circle"
                        >
                      </div>
                      
                      <h5 class="card-title mb-1">{{ $member->name }}</h5>
                      @if($member->title)
                        <p class="text-primary fw-medium mb-2">{{ $member->title }}</p>
                      @endif
                      
                      {{-- Contact Information --}}
                      <div class="mb-3">
                        @if($member->email)
                          <div class="text-muted small mb-1">
                            <i class="ti ti-mail me-1"></i>{{ $member->email }}
                          </div>
                        @endif
                        @if($member->phone)
                          <div class="text-muted small mb-1">
                            <i class="ti ti-phone me-1"></i>{{ $member->phone }}
                          </div>
                        @endif
                        @if($member->company && $member->company->name)
                          <div class="text-muted small">
                            <i class="ti ti-building me-1"></i>{{ $member->company->name }}
                          </div>
                        @endif
                      </div>
                      
                      {{-- Bio --}}
                      @if($member->bio)
                      <p class="text-muted small mb-3">{{ Str::limit($member->bio, 100) }}</p>
                      @endif
                      
                      {{-- Social Media Links --}}
                      <div class="d-flex justify-content-center gap-2">
                        {{-- @if($member->email)
                        <a href="mailto:{{ $member->email }}" class="btn btn-sm btn-outline-primary rounded-pill">
                          <i class="ti ti-mail"></i>
                        </a>
                        @endif --}}
                        @if($member->linkedin)
                        <a href="{{ $member->linkedin }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                          <i class="ti ti-brand-linkedin"></i>
                        </a>
                        @endif
                      </div>
                      
                      {{-- Join Date --}}
                      @if($member->joined_at)
                      <div class="text-muted small mt-3">
                        {{ $l == 'ar' ? 'انضم في' : 'Joined' }} {{ \Carbon\Carbon::parse($member->joined_at)->format('M Y') }}
                      </div>
                      @endif
                    </div>
                  </div>
                </a>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Project Tab --}}
        <div class="tab-pane fade" id="project" role="tabpanel" aria-labelledby="project-tab">
          @if($team->project)
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">
                <i class="ti ti-bulb me-2"></i>{{ $l == 'ar' ? 'معلومات المشروع' : 'Project Information' }}
              </h5>
            </div>
            <div class="card-body">
              @if($team->project->name)
              <div class="mb-4">
                <label class="form-label fw-semibold">{{ $l == 'ar' ? 'اسم المشروع' : 'Project Name' }}</label>
                <h4 class="text-primary">{{ $team->project->name }}</h4>
              </div>
              @endif
              
              @if($team->project->description)
              <div class="mb-4">
                <label class="form-label fw-semibold">{{ $l == 'ar' ? 'وصف المشروع' : 'Project Description' }}</label>
                <p class="fs-6">{{ $team->project->description }}</p>
              </div>
              @endif

              @if($team->project->status)
              <div class="mb-4">
                <label class="form-label fw-semibold">{{ $l == 'ar' ? ' وصف حالة المشروع' : 'Project Status Description' }}</label>
                @if($team->project->status == 20)
                      <p class="fs-6">{{ __('admin.Define the problem and form a team with clear roles.') }}/p>
                    @elseif($team->project->status == 40)
                      <p class="fs-6">{{ __('admin.Validate the idea, study users, and outline key features.') }}</p>
                    @elseif($team->project->status == 60)
                      <p class="fs-6">{{ __('admin.Sketch UI, set up the project, and develop core functionality.') }}</p>
                    @elseif($team->project->status == 80)
                      <p class="fs-6">{{ __('admin.Prepare demo, finalize slides, and rehearse the presentation.') }}</p>
                    @elseif($team->project->status == 100)
                      <p class="fs-6">{{ __('admin.Submit code, share the demo, and complete documentation. story.') }}</p>
                    @else
                      <p class="fs-6">{{ $l == 'en' ? 'Not Set' : 'غير محدد' }}</p>
                    @endif
                {{-- <p class="fs-6">{{ $team->project->description }}</p> --}}
              </div>
              @endif
              
              <div class="row">
                @if($team->project->status)
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ $l == 'ar' ? 'حالة المشروع' : 'Project Status' }}</label>
                  <div>
                    @if($team->project->status == 20)
                      <span class="badge bg-label-primary">{{ __('admin.Idea + Team') }}</span>
                    @elseif($team->project->status == 40)
                      <span class="badge bg-label-info">{{ __('admin.Research') }}</span>
                    @elseif($team->project->status == 60)
                      <span class="badge bg-label-warning">{{ __('admin.Design & Build') }}</span>
                    @elseif($team->project->status == 80)
                      <span class="badge bg-label-success">{{ __('admin.Ready for Pitch') }}</span>
                    @elseif($team->project->status == 100)
                      <span class="badge bg-label-dark">{{ __('admin.Project Submitted') }}</span>
                    @else
                      <span class="badge bg-label-secondary">{{ $l == 'en' ? 'Not Set' : 'غير محدد' }}</span>
                    @endif
                  </div>
                </div>
                @endif
                
                {{-- @if($team->project->region)
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ $l == 'ar' ? 'منطقة المشروع' : ' Project Region' }}</label>
                  <div>
                    <span class="badge bg-label-secondary fs-6">{{ __('admin.'.$team->project->region) }}</span>
                  </div>
                </div>
                @endif --}}
                
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">{{ $l == 'ar' ? 'تاريخ الإنشاء' : 'Created Date' }}</label>
                  <p class="mb-0">{{ \Carbon\Carbon::parse($team->created_at)->format('M d, Y') }}</p>
                </div>
              </div>
            </div>
          </div>
          @else
          <div class="text-center py-5">
            <div class="avatar avatar-xl mx-auto mb-3">
              <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="ti ti-bulb-off ti-lg"></i>
              </span>
            </div>
            <h6 class="mb-1">{{ $l == 'ar' ? 'لا توجد معلومات مشروع' : 'No Project Information' }}</h6>
            <p class="text-muted">{{ $l == 'ar' ? 'لم يتم إضافة معلومات المشروع بعد' : 'Project information has not been added yet' }}</p>
          </div>
          @endif
        </div>

        {{-- Files Tab --}}
        {{-- @if(isset($team->shared_files[0]) && $team->shared_files[0]?->team_formation_id == auth()->user()->team_member?->team_formation_id) --}}
        @if(count($team->shared_files) > 0)
        <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
          <div class="row">
            @foreach($team->shared_files as $file)
            <div class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                      @php
                        $extension = pathinfo($file->file, PATHINFO_EXTENSION);
                        $iconClass = 'ti ti-file';
                        $colorClass = 'text-secondary';
                        if(in_array($extension, ['pdf'])) { 
                          $iconClass = 'ti ti-file-type-pdf'; 
                          $colorClass = 'text-danger';
                        }
                        elseif(in_array($extension, ['doc', 'docx'])) { 
                          $iconClass = 'ti ti-file-type-doc'; 
                          $colorClass = 'text-primary';
                        }
                        elseif(in_array($extension, ['ppt', 'pptx'])) { 
                          $iconClass = 'ti ti-presentation'; 
                          $colorClass = 'text-warning';
                        }
                        elseif(in_array($extension, ['zip', 'rar'])) { 
                          $iconClass = 'ti ti-file-zip'; 
                          $colorClass = 'text-info';
                        }
                        elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) { 
                          $iconClass = 'ti ti-photo'; 
                          $colorClass = 'text-success';
                        }
                      @endphp
                      <div class="avatar">
                        <span class="avatar-initial rounded {{ $colorClass }}">
                          <i class="{{ $iconClass }} ti-lg"></i>
                        </span>
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1">{{ basename($file->file) }}</h6>
                      @if($file->description)
                        <p class="text-muted small mb-2">{{ $file->description }}</p>
                      @endif
                      <div class="text-muted small mb-3">
                        <div class="mb-1">
                          <i class="ti ti-user me-1"></i>{{ $file->uploader->name }}
                        </div>
                        <div>
                          <i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($file->created_at)->format('M d, Y') }}
                        </div>
                      </div>
                      <a href="{{ asset($file->file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-download me-1"></i>{{ $l == 'ar' ? 'تحميل' : 'Download' }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif
        
        {{-- @endif --}}

        {{-- Tasks Tab --}}
        @if(isset($team->todo_lists[0]) && $team->todo_lists[0]?->team_formation_id == auth()->user()->team_member?->team_formation_id)
        @if(count($team->todo_lists) > 0)
        <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
          <div class="row">
            @foreach($team->todo_lists as $todo)
            <div class="col-12 mb-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <div class="d-flex align-items-center mb-2">
                        @if($todo->status == 'completed')
                          <i class="ti ti-circle-check text-success me-2"></i>
                        @elseif($todo->status == 'in_progress')
                          <i class="ti ti-clock text-warning me-2"></i>
                        @else
                          <i class="ti ti-circle text-secondary me-2"></i>
                        @endif
                        <h6 class="mb-0">{{ $todo->title }}</h6>
                      </div>
                      @if($todo->description)
                        <p class="text-muted mb-3">{{ $todo->description }}</p>
                      @endif
                      <div class="row text-muted small">
                        <div class="col-md-4 mb-2">
                          <i class="ti ti-user me-1"></i>
                          <strong>{{ $l == 'ar' ? 'منشئ:' : 'Creator:' }}</strong> {{ $todo->creator->name }}
                        </div>
                        @if($todo->assignee->name)
                        <div class="col-md-4 mb-2">
                          <i class="ti ti-user-check me-1"></i>
                          <strong>{{ $l == 'ar' ? 'مكلف:' : 'Assigned:' }}</strong> {{ $todo->assignee->name }}
                        </div>
                        @endif
                        @if($todo->due_date)
                        <div class="col-md-4 mb-2">
                          <i class="ti ti-calendar me-1"></i>
                          <strong>{{ $l == 'ar' ? 'تاريخ الاستحقاق:' : 'Due Date:' }}</strong> {{ \Carbon\Carbon::parse($todo->due_date)->format('M d, Y') }}
                        </div>
                        @endif
                      </div>
                    </div>
                    <div class="flex-shrink-0 ms-3">
                      @if($todo->status == 'completed')
                        <span class="badge bg-success">{{ $l == 'ar' ? 'مكتمل' : 'Completed' }}</span>
                      @elseif($todo->status == 'in_progress')
                        <span class="badge bg-warning">{{ $l == 'ar' ? 'قيد التنفيذ' : 'In Progress' }}</span>
                      @else
                        <span class="badge bg-secondary">{{ $l == 'ar' ? 'معلق' : 'Pending' }}</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif
        @endif

      </div> {{-- End tab content --}}

    @else
      {{-- No team found message --}}
      @include('includes.no-result', ['message' => $l == 'ar' ? 'لا توجد معلومات متاحة عن الفريق.' : 'No team information available.'])
    @endif

  </div>
</div>
@stop
