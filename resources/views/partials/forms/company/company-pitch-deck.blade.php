<form method="post"
action="{{ route('update_my_company') }}"
enctype="multipart/form-data">
{!! csrf_field() !!}
<input type="hidden" value="6" name="CompanyN">

<div class="col-md-12 col-12 mb-3">
  <label
    class="fs-6 mb-2">{{ __('admin.Pitch_Deck') }}</label>
  <div class="input-group">

    <span class="input-group-btn">

      <a data-input="file" data-preview="holderfile"
        class="btn btn-primary lfm">
        <i class="fa fa-picture-o"></i>
        {{ __('admin.Choose') }}
      </a>
    </span>
    <input id="file" class="form-control"
      type="text" name="pitch_deck">
  </div>

</div>


<div class="col-md-12 col-12 mb-3">
  <label
    class="fs-6 mb-2">{{ __('admin.Pitch_Deck_Video') }}</label>
  <div class="input-group">

    <span class="input-group-btn">

      <a data-input="video" data-preview="holdervideo"
        class="btn btn-primary lfm">
        <i class="fa fa-picture-o"></i>
        {{ __('admin.Choose') }}
      </a>
    </span>
    <input id="video" class="form-control"
      type="text" name="pitch_deck_video">
  </div>

</div>




<!-- ===================== Pitch_Deck_Information  ===========================-->
<div class="col-md-6 col-12 mb-3">
  <label class="fs-6 mb-2">
    {{ __('admin.Pitch_Deck_Information') }} </label>
  <select name="pitch_deck_info"
    class="select2 form-control">
    <option value="">{{ __('admin.Choose') }}
    </option>
    <option value="yes ">{{ __('admin.Yes') }}
    </option>
    <option value="no ">{{ __('admin.No') }}
    </option>
  </select>
</div>


<div class="col-md-6 col-12 mb-3">
  <div class="form-label-group mt-75">
    <label class="fs-6 mb-2"
      for="pitch_deck_summary">{{ __('admin.Pitch_Deck_Summary') }}</label>
    <textarea name="pitch_deck_summary" autocomplete="pitch_deck_summary"
      class="form-control mt-2" id="pitch_deck_summary"
      placeholder="{{ __('admin.Pitch_Deck_Summary') }}">{{ $client->company ? $client->company->pitch_deck_summary : old('pitch_deck_summary') }}</textarea>
  </div>
</div>

<div class="col-md-6 col-12 mb-3">
  <div class="form-label-group mt-75">
    <label class="fs-6 mb-2"
      for="problem_facing">{{ __('admin.Problem_We_Are_Facing') }}</label>
    <textarea name="problem_facing" autocomplete="problem_facing"
      class="form-control mt-2" id="problem_facing"
      placeholder="{{ __('admin.problem_facing') }}">{{ $client->company ? $client->company->problem_facing : old('problem_facing') }}</textarea>
  </div>
</div>


<div class="col-md-6 col-12 mb-3">
  <div class="form-label-group mt-75">
    <label class="fs-6 mb-2"
      for="solution_problem">{{ __('admin.solution_problem') }}</label>

    <textarea name="solution_problem" autocomplete="solution_problem"
      class="form-control mt-2" id="solution_problem"
      placeholder="{{ __('admin.solution_problem') }}">{{ $client->company ? $client->company->solution_problem : old('solution_problem') }}</textarea>
  </div>
</div>


{{-- <div class="col-md-12 col-12 mb-3">
    @if (isset($client->company->documents))
      
    @endif
    <div class="custom-file mb-3" style="width: 300px;">
      <label class="fs-6 mb-2">{{ __('admin.Documents') }}</label>
      <input 
        type="file" 
        class="form-control"
        id="documents"
        name="documents[]"
        accept="application/pdf,application/vnd.openxmlformats-officedocument.presentationml.presentation"
      />
    </div>
  </div> --}}


<div class="col-md-12 col-12 mb-3">
  <label
    class="fs-6 mb-2">{{ __('admin.Documents') }}</label>
  <div class="input-group">

    <span class="input-group-btn">

      <a data-input="documents"
        data-preview="holderdocuments"
        class="btn btn-primary lfm">
        <i class="fa fa-picture-o"></i>
        {{ __('admin.Choose') }}
      </a>
    </span>
    <input id="documents" class="form-control"
      type="text" name="documents[]">
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

</form>