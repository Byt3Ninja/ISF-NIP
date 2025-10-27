<div class="card">
  @php
    $request = request();
    if(auth()->user()->hasAnyRole('admin|organizationmanager')) {
      if($id != null){
      $request = new \Illuminate\Http\Request([
        'cycleIdHackathon' => $id,
      ]);
      }
    }else{
     if($id != null){
      $request = new \Illuminate\Http\Request([
        'cycleIdHackathon' => $user->team_member->teamFormation->user->company->cycle ?? null,
      ]);
      }
    }
    $mentorsLists = showMintors($request);
  
@endphp
  <div class="card-body">
    <h5 class="card-title mb-4">{{ $l == 'en' ? 'Mentors' : 'المرشدون' }}</h5>

    <h3 class="h5 mb-3">{{ $l == 'en' ? 'Mentors' : 'المرشدون' }}</h3>
    <p class="text-muted mb-5">
      {{ $l == 'en' ? 'Participants will have the opportunity to receive guidance from experts in technology and
      entrepreneurship fields.' : 'سيتاح للمشاركين فرصة الحصول على إرشاد من خبراء في مجالات التكنولوجيا وريادة
      الأعمال.' }}
    </p>

    <div class="row" id="hackathon-mentors-list">
    
      @if($mentorsLists == null)
      <div class="col-12">
        <div class="alert alert-info">{{ $l == 'en' ? 'No mentors currently.' : 'لا يوجد مرشدين حالياً.' }}
        </div>
      </div>
      @else
      {{-- @foreach($mentors as $mentor)
      <div class="col-md-6 col-lg-3 mb-4">
        <div class="card text-center h-100 shadow-sm border-0">
          <div class="card-body">
            <img src="{{ $mentor->u_img_url ?? '/images/user-placeholder.jpeg' }}" class="rounded-circle mx-auto mb-3"
              alt="Mentor" onerror="this.onerror=null; this.src='/images/user-placeholder.jpeg';"
              style="width:120px; height:120px; object-fit: cover;">
            <h5 class="card-title h6">
              {{ $mentor->{'user_name_'. $l} }}
            </h5>
            <p class="card-text small text-muted">
              {{ $mentor->{'u_title_'. $l} }}
            </p>
          </div>
        </div>
      </div>
      @endforeach --}}

       @foreach ($mentorsLists as $key => $mentor)
          <!-- Mentor Card -->
          <div class="col-md-4 radius-15 mb-3">
            <div class="border radius-15" style="background-image: url('{{ $mentor->image_url }}'); 
                    height:293px; background-size: cover;
                    background-position: center;">
            </div>
            <div class="card shadow-none mx-2 p-3"
              style="height: 350px; z-index: 1; margin-top: -25px; border-radius: 15px;">
              <div
                class="bg-white custom-shadow border radius-15 px-2 py-1 d-flex justify-content-between align-items-center mb-2"
                style="height: 55px;">
                <a target="_blank" href="{{ route('mentors.profile_view', $mentor->id) }}" class="d-flex flex-column">
                  <span class="text-black fw-bold fs-6">{{ $mentor->name }}</span>
                  <small class="text-secondary">{{ $mentor->title }}</small>
                </a>
                @if ($mentor->bio->linkedin ?? 0)
                <a title="Linkedin URL" target="_blank" href="{{ $mentor->bio->linkedin }}">
                  <img src="/icons/linkedin.svg" width="30" height="30" />
                </a>
                {{-- @else
                <!-- Please Change this SVG To color #eee -->
                <a title="Linkedin URL" target="_blank" href="#">
                  <img src="/icons/linkedin.svg" width="30" height="30" />
                </a> --}}
                @endif
              </div>
              <!-- Mentor Experience -->
              <div class="mb-3" style="max-height: 100px; overflow: auto;">
                <p class="fs-6 fw-bold text-black mb-2">{{ __('admin.Expertises') }}</p>
                <div class="d-flex flex-wrap" style="gap: 5px;">
                  @php
                  if ($mentor) {
                  $expertises = $mentor->mentorExpertises; // جلب جميع الخبرات المرتبطة بهذا الموجه
                  } else {
                  $expertises = [];
                  }
                  @endphp
                  @if ($mentor->mentorExpertises->isNotEmpty())
                  @foreach ($expertises as $expert)
                  <span class="btn btn-xs btn-outline-warning rounded-pill"> {{ $l === 'ar' ? $expert->title_ar : $expert->title }}</span>
                  @endforeach
                  @else
                  <span class="btn btn-xs btn-outline-warning rounded-pill">{{ __('cycles.NA') }}</span>
                  @endif
                </div>
              </div>
              <!-- / Mentor Experience -->
              <!-- Mentor Programs & Rating -->
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-white custom-shadow px-2 py-1 border" style="border-radius: 15px;">
                  <div class="d-flex flex-column justify-content-center align-items-center">
                    <small class="text-info fw-bold">{{ __('cycles.ProgramCount') }}</small>
                    <span class="text-black fw-bold fs-6">{{ $mentor->mentorCycles->count() }}</span>
                  </div>
                </div>
                @php
                $rateMentorAVG = \App\MentorshipRequest::where('mentor_id', $mentor->id)
                ->where(function ($query) {
                $query->where('approved', 'Yes')->orWhereNull('approved');
                })
                ->avg('rating');
                @endphp
                <div class="badge bg-label-light rounded rounded-pill">
                  <div class="d-flex align-items-center justify-content-center">

                    <span style="color: orange" class="fw-bold mt-1 mx-1">{{ number_format($rateMentorAVG, 2, '.', '')
                      }}</span>
                    <img src="/icons/star.svg" width="15" height="15" />
                  </div>
                </div>
              </div>
              <!-- / Mentor Programs & Rating -->
              <!-- Mentor Bio -->
              @if ($mentor->bio)
              <div class="mb-2 mt-auto">
                <p class="fs-6 fw-bold text-black mb-1">{{ __('dashboard.Bio') }}</p>
                <small class="text-muted line-clamp-3">
                  {{ $mentor->bio->{'bio_' . $l} }}
                </small>
              </div>
              @else
              <div class="mb-2 mt-auto">
                <p class="fs-6 fw-bold text-black mb-1">{{ __('dashboard.Bio') }}</p>
                <small class="text-muted line-clamp-4">
                  {{ __('cycles.NA') }}
                </small>
              </div>
              @endif
              <!-- / Mentor Bio -->
              <div class="row px-4 mt-auto">
                @if(auth()->user()->company && auth()->user()->company->cycle != 0 && auth()->user()->hasRole('Registered'))
                @php
                  $startup = auth()->user();
                  $getCycle = $startup->company?->cycledata;
                @endphp
                <button type="button" class="btn btn-sm btn-success rounded-pill custom-shadow"
                  onclick="request({{$mentor->id}},'{{optional($startup)->name }}', {{ $getCycle->organization_id }},{{ $getCycle->id }})"
                  title="Add new request">
                  {{ __('dashboard.Request') }} <i class="ti ti-square-plus mx-3"></i>
                </button>
                @else
                <div class="btn btn-sm btn-success rounded-pill custom-shadow">{{ __('cycles.nosubscripe') }}</div>
                @endif
                @if(!auth()->user()->hasRole('Registered') && !auth()->user()->hasRole('Mentor') && !auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('organizationManager'))
                @php
                $startup = auth()->user();
                $getCycle = $startup->company?->cycledata;
                @endphp
                <button type="button" class="btn btn-sm btn-success rounded-pill custom-shadow" data-bs-toggle="modal"
                  data-bs-target="#SendRequest" title="Add new request" onclick="request({{ $mentor->id }},
                    {{ optional($startup)->first_nam }} 
                    {{ optional($startup)->last_name }},
                    {{ $getCycle->organization_id }},
                    {{ $getCycle->id }} )">
                   
                  {{ __('dashboard.Request') }} <i class="ti ti-square-plus mx-3"></i>
                </button>
                @endif


              </div>
            </div>
          </div>
          <!-- Mentor Card -->

          @endforeach
      @endif
    </div>

    {{-- <h3 class="h5 mt-5 mb-3">{{ $l == 'en' ? 'Judging Panel' : 'لجنة التحكيم' }}</h3>
    <p class="text-muted mb-5">
      {{ $l == 'en' ? 'The judging panel consists of industry leaders and investors to evaluate projects and
      select winners.' : 'تتكون لجنة التحكيم من قادة الصناعة والمستثمرين لتقييم المشاريع واختيار الفائزين.' }}
    </p>

    <div class="row" id="hackathon-judges-list">
      @if(count($judges_users) > 0)
      @foreach($judges_users as $judge)
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card text-center h-100 shadow-sm border-0">
          <div class="card-body">
            <img src="{{ $judge->img_url ?? '/images/user-placeholder.jpeg' }}"
              onerror="this.onerror=null; this.src='/images/user-placeholder.jpeg';" class="rounded-circle mx-auto mb-3"
              alt="Judge" style="width:120px; height:120px; object-fit: cover;">
            <h5 class="card-title h6">
              {{ $judge->{'user_name_'. $l} }}
            </h5>
            <p class="card-text small text-muted">
              {{ $judge->{'title_'. $l} }}
            </p>
          </div>
        </div>
      </div>
      @endforeach
      @else
      <div class="col-12">
        <div class="alert alert-info">{{ $l == 'en' ? 'No judges currently.' : 'لا يوجد حكام حالياً.' }}</div>
      </div>
      @endif
    </div> --}}


  </div>
</div>