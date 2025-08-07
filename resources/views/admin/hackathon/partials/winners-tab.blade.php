
<div class="card">
  <div class="card-body">
    <h5 class="card-title">
      {{ $l == 'en' ? 'Prizes and Rewards' : 'الجوائز والمكافآت' }}
    </h5>
    <p>
      {{ $l == 'en' ? 'Winning teams will be honored with valuable prizes, in addition to advanced support
      opportunities from the incubator ecosystem.' : 'سيتم تكريم الفرق الفائزة بجوائز قيمة، بالإضافة إلى فرص دعم
      متقدمة من منظومة المسرعة.' }}
    </p>
    <div class="row text-center" id="hackathon-prizes-list">
      <div class="col-md-4 mb-3">
        <div class="card h-100 border-warning">
          <div class="card-body">
            <i class="bi bi-award-fill display-3 text-warning me-2"></i>
            <h6 class="card-title mt-2">
              {{ $l == 'en' ? 'First Place' : 'المركز الأول' }}
            </h6>
            <p class="card-text">
              {{--  {{ $l == 'en' ? 'Prize of 20,000 SAR + incubation opportunity in the accelerator.' : 'جائزة مالية قدرها 20,000 ريال سعودي + فرصة احتضان في المسرعة.' }}  --}}
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card h-100 border-secondary">
          <div class="card-body">
            <i class="bi bi-award display-3 text-secondary me-2"></i>
            <h6 class="card-title mt-2">
              {{ $l == 'en' ? 'Second Place' : 'المركز الثاني' }}
            </h6>
            <p class="card-text">
              {{--  {{ $l == 'en' ? 'Prize of 10,000 SAR + access to specialized mentoring programs.' : 'جائزة مالية قدرها 10,000 ريال سعودي + وصول لبرامج إرشاد متخصصة.' }}  --}}
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card h-100 border-danger">
          <div class="card-body">
            <i class="bi bi-award display-3" style="color: #cd7f32; me-2"></i>
            <h6 class="card-title mt-2">
              {{ $l == 'en' ? 'Third Place' : 'المركز الثالث' }}
            </h6>
            <p class="card-text">
              {{--  {{ $l == 'en' ? 'Prize of 5,000 SAR + invitations to exclusive events.' : 'جائزة مالية قدرها 5,000 ريال سعودي + دعوات لفعاليات حصرية.' }}  --}}
            </p>
          </div>
        </div>
      </div>
    </div>
    <p class="mt-3">
      <strong>
        {{ $l == 'en' ? 'Additional Prizes:' : 'جوائز إضافية:' }}
      </strong>
      {{ $l == 'en' ? 'There may be special prizes from sponsors for specific themes or outstanding solutions.'
      : 'قد تكون هناك جوائز خاصة مقدمة من الرعاة لبعض المحاور أو الحلول المتميزة.' }}
    </p>
  </div>
</div>

