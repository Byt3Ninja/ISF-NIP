<div class="row">
  <!-- Founders Column -->
  <div class="col-md-4">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">Founders</h5>
        <form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data" onsubmit="resetForm(this)">
          {!! csrf_field() !!}
          <div class="show_Founders row border m-2 radius-15 light-shadow">
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 founder_name light-shadow" id="founder_name" placeholder="{{ __('admin.founder_name') }}" value="{{ $client->company ? $client->company->founder_name : old('founder_name') }}" name="founder_name" autocomplete="founder_name">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 founder_email light-shadow" id="founder_email" placeholder="{{ __('admin.founder_email') }}" value="{{ $client->company ? $client->company->founder_email : old('founder_email') }}" name="founder_email" autocomplete="founder_email">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control my-2 founder_linkedin light-shadow" id="founder_linkedin" placeholder="{{ __('admin.founder_linkedin') }}" value="{{ $client->company ? $client->company->founder_linkedin : old('founder_linkedin') }}" name="founder_linkedin" autocomplete="founder_linkedin">
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2" style="height: 40px;">
            <button type="submit" class="btn btn-outline-primary text-black fw-bold rounded-pill">
              <i class="ti ti-plus me-3"></i>
                {{ $l == 'en' ? 'Add new founder' : 'إضافة مؤسس جديد' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Shareholders Column -->
  <div class="col-md-4">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">Shareholders</h5>
        <form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data" onsubmit="resetForm(this)">
          {!! csrf_field() !!}
          <div class="show_Shareholders row m-2 border radius-15 light-shadow">
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 shareholder_name light-shadow" id="shareholder_name" placeholder="{{ __('admin.shareholder_name') }}" value="{{ $client->company ? $client->company->shareholder_name : old('shareholder_name') }}" name="shareholder_name" autocomplete="shareholder_name">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 shareholder_role light-shadow" id="shareholder_role" placeholder="{{ __('admin.shareholder_role') }}" value="{{ $client->company ? $client->company->shareholder_role : old('shareholder_role') }}" name="shareholder_role" autocomplete="shareholder_role">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="number" class="border-light form-control my-2 shareholder_ownership_percentage light-shadow" id="shareholder_ownership_percentage" placeholder="{{ __('admin.shareholder_ownership_percentage') }}" value="{{ $client->company ? $client->company->shareholder_ownership_percentage : old('shareholder_ownership_percentage') }}" name="shareholder_ownership_percentage" autocomplete="shareholder_ownership_percentage">
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2" style="height: 40px;">
            <button type="submit" class="btn btn-outline-primary text-black fw-bold rounded-pill">
              <i class="ti ti-plus me-3"></i>
              {{ $l == 'en' ? 'Add new shareholder' : 'إضافة مساهم جديد' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Investors Column -->
  <div class="col-md-4">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">Investors</h5>
        <form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data" onsubmit="resetForm(this)">
          {!! csrf_field() !!}
          <div class="show_investor row m-2 border radius-15 light-shadow">
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 investor_name light-shadow" id="investor_name" placeholder="{{ __('admin.investor_name') }}" value="{{ $client->company ? $client->company->investor_name : old('investor_name') }}" name="investor_name" autocomplete="investor_name">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control my-2 investment_type light-shadow" id="investment_type" placeholder="{{ __('admin.investment_type') }}" value="{{ $client->company ? $client->company->investment_type : old('investment_type') }}" name="investment_type" autocomplete="investment_type">
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2" style="height: 40px;">
            <button type="submit" class="btn btn-outline-primary text-black fw-bold rounded-pill">
              <i class="ti ti-plus me-3"></i>
              {{ $l == 'en' ? 'Add new investor' : 'إضافة مستثمر جديد' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function resetForm(form) {
    form.reset();
  }
</script>