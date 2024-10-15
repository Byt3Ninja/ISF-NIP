<form method="post"
action="{{ route('update_my_company') }}"
enctype="multipart/form-data">
{!! csrf_field() !!}

<input type="hidden" value="7" name="CompanyN">

<div class="row">
  <!-- ===================== Achieved_Milestones  ===========================-->
  <div class="col-md-3 col-12 mb-3">
    <label class="fs-6 mb-2">
      {{ __('admin.Milestones') }} </label>
    <select name="achieved_milestones"
      class="select2 form-control achieved_milestones">
      <option value="">{{ __('admin.Choose') }}
      </option>
      <option value="Achieved_Milestones ">
        {{ __('admin.Achieved_Milestones') }}</option>
      <option value="Planned_Milestones ">
        {{ __('admin.Planned_Milestones') }}</option>
      <option value="Archived_Milestones ">
        {{ __('admin.Archived_Milestones') }}</option>
    </select>
  </div>


  <div class="col-md-3 col-12 mb-3 ">
    <div class="form-label-group mt-75">
      <label class="fs-6 mb-2"
        for="achieved_milestone_name">{{ __('admin.Milestone_Name') }}</label>
      <input type="text"
        class="form-control mt-2 achieved_milestone_name"
        id="achieved_milestone_name"
        placeholder="{{ __('admin.Milestone_Name') }}"
        value="{{ $client->company ? $client->company->achieved_milestone_name : old('achieved_milestone_name') }}"
        name="achieved_milestone_name"
        autocomplete="achieved_milestone_name">
    </div>
  </div>

  <div class="col-md-3 col-12 mb-3 ">
    <div class="form-label-group mt-75">
      <label class="fs-6 mb-2"
        for="achieved_milestone_description">{{ __('admin.Description') }}</label>
      <input type="text"
        class="form-control mt-2 achieved_milestone_description"
        id="achieved_milestone_description"
        placeholder="{{ __('admin.Description') }}"
        value="{{ $client->company ? $client->company->achieved_milestone_description : old('achieved_milestone_description') }}"
        name="achieved_milestone_description"
        autocomplete="new-achieved_milestone_description">
    </div>
  </div>


  <div class="col-md-3 col-12 mb-3">
    <div class="form-label-group mt-75 text-center">
      <br>
      <div class="btn btn-success AddMileStone">
        {{ __('admin.Add') }}</div>
    </div>
  </div>



  <div class="ShowTableResultsMil">

    <table class="table border">
      <thead>
        <tr>
          <th>{{ __('admin.MileStone') }}</th>
          <th>{{ __('admin.Name') }}</th>
          <th>{{ __('admin.Description') }}</th>
          <th>{{ __('admin.Created At') }}</th>
          <th>{{ __('admin.Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($MileStones as $milestone)
          <tr>
            <td>{{ __('admin.' . $milestone->plan) }}
            </td>
            <td>{{ $milestone->name }}</td>
            <td>{{ $milestone->description }}</td>
            <td>{{ $milestone->created_at }}</td>
            <td>
              <div class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#Editmilestone{{ $milestone->id }}">
                {{ __('admin.Edit') }}</div>
            </td>
          </tr>


          <!-- Edit Customer Modal -->
          <div class="modal fade"
            id="Editmilestone{{ $milestone->id }}"
            tabindex="-1" aria-hidden="true">
            <div
              class="modal-dialog modal-lg modal-simple modal-edit-user">
              <div class="modal-content">
                <div class="modal-body">
                  <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"></button>
                  <div class="text-center mb-6">
                    <h4 class="mb-2">
                      {{ __('admin.Edit') }}
                      ({{ $milestone->name }})</h4>

                  </div>

                  <form class="add-new-user pt-0"
                    method="POST">
                    @csrf
                    @method('PUT')

                    <div
                      class="ecommerce-customer-add-basic mb-4">

                      <div class="col-md-12">

                        <div class="mb-6 width49">
                          <div>
                            <label class="fs-6 mb-2">
                              {{ __('admin.Milestones') }}
                            </label>
                          </div>

                          <select
                            name="achieved_milestones"
                            class="select2 form-control achieved_milestones">
                            <option value="">
                              {{ __('admin.Choose') }}
                            </option>
                            <option
                              value="Achieved_Milestones"
                              {{ $milestone->plan == 'Achieved_Milestones' ? 'selected' : '' }}>
                              {{ __('admin.Achieved_Milestones') }}
                            </option>
                            <option
                              value="Planned_Milestones"
                              {{ $milestone->plan == 'Planned_Milestones' ? 'selected' : '' }}>
                              {{ __('admin.Planned_Milestones') }}
                            </option>
                            <option
                              value="Archived_Milestones"
                              {{ $milestone->plan == 'Archived_Milestones' ? 'selected' : '' }}>
                              {{ __('admin.Archived_Milestones') }}
                            </option>
                          </select>
                        </div>

                        <div class="mb-6 width49">
                          <label class="fs-6 mb-2"
                            for="achieved_milestone_name">{{ __('admin.Milestone_Name') }}</label>
                          <input type="text"
                            class="form-control mt-2 achieved_milestone_name"
                            id="achieved_milestone_name"
                            placeholder="{{ __('admin.Milestone_Name') }}"
                            value="{{ $milestone->name ? $milestone->name : old('achieved_milestone_name') }}"
                            name="achieved_milestone_name"
                            autocomplete="achieved_milestone_name">
                        </div>

                        <div class="mb-6 width49">
                          <label class="fs-6 mb-2"
                            for="achieved_milestone_description">{{ __('admin.Description') }}</label>
                          <input type="text"
                            class="form-control mt-2 achieved_milestone_description"
                            id="achieved_milestone_description"
                            placeholder="{{ __('admin.Description') }}"
                            value="{{ $milestone->description ? $milestone->description : old('achieved_milestone_description') }}"
                            name="achieved_milestone_description"
                            autocomplete="new-achieved_milestone_description">
                        </div>



                      </div>
                    </div>


                    <div class="text-center">
                      <div
                        class="btn btn-primary me-3 AddMileStone ">
                        {{ __('admin.Update') }}</div>
                      <button type="reset"
                        class="btn btn-label-danger"
                        data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
                    </div>
                  </form>



                </div>
              </div>
            </div>
          </div>
          <!--/ Edit JO Modal -->
        @endforeach
      </tbody>
    </table>


  </div>


</div>
</form>