<div class="card">
  <div class="card-body">
    <h4 class="card-title mb-4">{{ $l == 'en' ? 'Mentors and Judging Panel' : 'المرشدون ولجنة التحكيم' }}</h4>

    <h3 class="h5 mb-3">{{ $l == 'en' ? 'Mentors' : 'المرشدون' }}</h3>
    <p class="text-muted mb-5">
      {{ $l == 'en' ? 'Participants will have the opportunity to receive guidance from experts in technology and
      entrepreneurship fields.' : 'سيتاح للمشاركين فرصة الحصول على إرشاد من خبراء في مجالات التكنولوجيا وريادة
      الأعمال.' }}
    </p>

    <div class="row" id="hackathon-mentors-list">
      @if($mentors->isEmpty())
      <div class="col-12">
        <div class="alert alert-info">{{ $l == 'en' ? 'No mentors currently.' : 'لا يوجد مرشدين حالياً.' }}
        </div>
      </div>
      @else
      @foreach($mentors as $mentor)
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
      @endforeach
      @endif
    </div>

    <h3 class="h5 mt-5 mb-3">{{ $l == 'en' ? 'Judging Panel' : 'لجنة التحكيم' }}</h3>
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
    </div>
  </div>
</div>