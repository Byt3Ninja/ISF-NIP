<form method="post"
action="{{ route('update_my_company') }}"
enctype="multipart/form-data">
{!! csrf_field() !!}

<input type="hidden" value="5" name="CompanyN">

<div class="row">




  <h2>Founders</h2>
  <div class="show_Founders row">
    <div class="col-md-4 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="founder_name">{{ __('admin.founder_name') }}</label>
        <input type="text"
          class="form-control mt-2 founder_name"
          id="founder_name"
          placeholder="{{ __('admin.founder_name') }}"
          value="{{ $client->company ? $client->company->founder_name : old('founder_name') }}"
          name="founder_name"
          autocomplete="founder_name">
      </div>
    </div>

    <div class="col-md-3 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="founder_email">{{ __('admin.founder_email') }}</label>
        <input type="text"
          class="form-control mt-2 founder_email"
          id="founder_email"
          placeholder="{{ __('admin.founder_email') }}"
          value="{{ $client->company ? $client->company->founder_email : old('founder_email') }}"
          name="founder_email"
          autocomplete="founder_email">
      </div>
    </div>
    <div class="col-md-3 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="founder_linkedin">{{ __('admin.founder_linkedin') }}</label>
        <input type="text"
          class="form-control mt-2 founder_linkedin"
          id="founder_linkedin"
          placeholder="{{ __('admin.founder_linkedin') }}"
          value="{{ $client->company ? $client->company->founder_linkedin : old('founder_linkedin') }}"
          name="founder_linkedin"
          autocomplete="founder_linkedin">
      </div>
    </div>

    <div class="col-md-2 col-12 mb-3">
      <div class="form-label-group mt-75 text-center">
        <br>
        <div class="btn btn-success AddFounders">
          {{ __('admin.Add') }}</div>
      </div>
    </div>

  </div>

  <h2>Shareholders</h2>
  <!-- ===================== Shareholders ===========================-->
  <div class="show_Shareholders row">
    <div class="col-md-4 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="shareholder_name">{{ __('admin.shareholder_name') }}</label>
        <input type="text"
          class="form-control mt-2 shareholder_name"
          id="shareholder_name"
          placeholder="{{ __('admin.shareholder_name') }}"
          value="{{ $client->company ? $client->company->shareholder_name : old('shareholder_name') }}"
          name="shareholder_name"
          autocomplete="shareholder_name">
      </div>
    </div>

    <div class="col-md-3 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="shareholder_role">{{ __('admin.shareholder_role') }}</label>
        <input type="text"
          class="form-control mt-2 shareholder_role"
          id="shareholder_role"
          placeholder="{{ __('admin.shareholder_role') }}"
          value="{{ $client->company ? $client->company->shareholder_role : old('shareholder_role') }}"
          name="shareholder_role"
          autocomplete="shareholder_role">
      </div>
    </div>
    <div class="col-md-3 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="shareholder_ownership_percentage">{{ __('admin.shareholder_ownership_percentage') }}</label>
        <input type="number"
          class="form-control mt-2 shareholder_ownership_percentage"
          id="shareholder_ownership_percentage"
          placeholder="{{ __('admin.shareholder_ownership_percentage') }}"
          value="{{ $client->company ? $client->company->shareholder_ownership_percentage : old('shareholder_ownership_percentage') }}"
          name="shareholder_ownership_percentage"
          autocomplete="shareholder_ownership_percentage">
      </div>
    </div>


    <div class="col-md-2 col-12 mb-3">
      <div class="form-label-group mt-75 text-center">
        <br>
        <div class="btn btn-success AddSherHolders">
          {{ __('admin.Add') }}</div>
      </div>
    </div>


  </div>


  <h2>Investors</h2>
  <!-- ===================== Investors  ===========================-->

  <div class="show_investor row">

    <div class="col-md-3 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="investor_name">{{ __('admin.investor_name') }}</label>
        <input type="text"
          class="form-control mt-2 investor_name"
          id="investor_name"
          placeholder="{{ __('admin.investor_name') }}"
          value="{{ $client->company ? $client->company->investor_name : old('investor_name') }}"
          name="investor_name"
          autocomplete="investor_name">
      </div>
    </div>

    <div class="col-md-3 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="investment_type">{{ __('admin.investment_type') }}</label>
        <input type="text"
          class="form-control mt-2 investment_type"
          id="investment_type"
          placeholder="{{ __('admin.investment_type') }}"
          value="{{ $client->company ? $client->company->investment_type : old('investment_type') }}"
          name="investment_type"
          autocomplete="investment_type">
      </div>
    </div>

    <div class="col-md-2 col-12 mb-3">
      <div class="form-label-group mt-75 text-center">
        <br>
        <div class="btn btn-success AddInvistor">
          {{ __('admin.Add') }}</div>
      </div>
      <br><br><br><br>
    </div>


  </div>


  <div class=" row">

    <div class="col-md-3 col-12 mb-3">
      <div class="form-label-group mt-75">
        <label class="fs-6 mb-2"
          for="team_num">{{ __('admin.team_num') }}</label>
        <input type="number" class="form-control mt-2"
          id="team_num"
          placeholder="{{ __('admin.team_num') }}"
          value="{{ $client->company ? $client->company->team_num : old('team_num') }}"
          name="team_num" autocomplete="new-team_num">
      </div>
    </div>


    <div class="col-md-3 col-12 mb-3">
      <div class="form-group">
        <label class="fs-6 mb-2"
          for="working_team">{{ __('admin.Working_Team_Details') }}
        </label>
        <textarea class="form-control mt-2" id="working_team" name='working_team'
          required placeholder="{{ __('admin.Working_Team_Details') }} *"
          maxlength="150">{{ $client->company ? $client->company->working_team_summary : old('working_team') }}</textarea>
      </div>
    </div>



    <div class="row">
      <div class="col-12 d-flex justify-content-end">
        <button type="submit"
          class="btn btn-primary mr-1 mb-1"
          id="submit-button">
          <span
            id="button-text">{{ __('admin.Update') }}</span>
          <span id="button-spinner"
            class="spinner-border spinner-border-sm d-none"
            role="status" aria-hidden="true"></span>
        </button>
      </div>
    </div>



  </div>

  <div class="ShowTableResults">

    <table class="table border">
      <thead>
        <tr>
          <th>{{ __('admin.TypeTeam') }}</th>
          <th>{{ __('admin.Name') }}</th>
          <th>{{ __('admin.Email') }}</th>
          <th>{{ __('admin.Linkedin') }}</th>
          <th>{{ __('admin.shareholder_role') }}</th>
          <th>
            {{ __('admin.shareholder_ownership_percentage') }}
          </th>
          <th>{{ __('admin.investment_type') }}</th>
          <th>{{ __('admin.Created At') }}</th>
          <th>{{ __('admin.Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($teamMembers as $member)
          <tr>
            <td>{{ $member->TeamMember }}</td>
            <td>{{ $member->Name }}</td>
            <td>{{ $member->Email }}</td>
            <td><a href="{{ $member->LinkedIn }}"
                target="_blank">{{ $member->LinkedIn }}</a>
            </td>
            <td>{{ $member->RoleShereholder }}</td>
            <td>{{ $member->PresentagShereholder }}</td>
            <td>{{ $member->investorType }}</td>
            <td>{{ $member->created_at }}</td>
            <td>
              <div class="btn btn-success">Edit</div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>


  </div>

</div>
</form>