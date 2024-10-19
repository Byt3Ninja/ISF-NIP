<div class="row">
  <!-- Achieved Milestones Column -->
  <div class="col-md-4">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">{{ __('admin.Achieved_Milestones') }}</h5>
        

          <div class="show_Achieved_Milestones row m-2 border radius-15 light-shadow">
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 achieved_milestone_name light-shadow" id="achieved_milestone_name" placeholder="{{ __('admin.Milestone_Name') }}" value="{{ old('achieved_milestone_name') }}" name="achieved_milestone_name" autocomplete="achieved_milestone_name">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control my-2 achieved_milestone_description light-shadow" id="achieved_milestone_description" placeholder="{{ __('admin.Description') }}" value="{{ old('achieved_milestone_description') }}" name="achieved_milestone_description" autocomplete="achieved_milestone_description">
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2" style="height: 40px;">
            <div class="btn btn-outline-primary text-black fw-bold rounded-pill AddMileStone" do="Achieved_Milestones">
              <i class="ti ti-plus me-3"></i>
              {{ __('admin.Add') }}
            </div>
          </div>
       
          <div class="ShowTableAchieved"> 
          
        

            @foreach ($Achieveds as $key => $Achieved)
                  
            <div class="card shadow-sm" style="height: 132px; border-radius: 15px;">
              <div class="card-body">
                <h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                  <div
                    style="width: 32px; height: 32px;" 
                    class="rounded-circle bg-primary text-white d-flex 
                    justify-content-center align-items-center me-2">
                    {{ $key + 1 }}
                  </div>
                  {{$Achieved->name}}
                </h6>
                <p class="text-muted line-clamp-2 fs-6">
                  {{$Achieved->description}}
                </p>
              </div>
            </div>

            <div class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#EditMilstone{{ $Achieved->id }}">{{ __('admin.Edit') }}</div>
                  <form action="{{ route('DeleteMileStone_Comp') }}" method="POST" style="display: inline-block;">
                    @csrf
                    <input type="hidden" value="{{ $Achieved->id }}" name="id" >
                    <button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this ?')">{{ __('admin.Delete') }}</button>
                </form>


             <!-- Edit Customer Modal -->
             <div class="modal fade" id="EditMilstone{{ $Achieved->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                  <div class="modal-content">
                      <div class="modal-body">
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          <div class="text-center mb-6">
                              <h4 class="mb-2"> ({{$Achieved->name}})</h4>
                             
                          </div>

                              <div class="ecommerce-customer-add-basic mb-4" >

                                  <div class="col-md-12">

                                    <div class="mb-6 width49">
                                      <input type="text" class="border-light form-control my-2 Achieved_name{{ $Achieved->id }} light-shadow" id="Achieved_name" placeholder="{{ __('admin.Achieved_name') }}" value="{{ $Achieved->name ? $Achieved->name : old('Achieved_name') }}" name="Achieved_name" autocomplete="Achieved_name">
                                  </div>

                                    <div class="mb-6 width49">
                                      <input type="text" class="border-light form-control mt-2 Achieved_desc{{ $Achieved->id }} light-shadow" id="Achieved_desc" placeholder="{{ __('admin.Achieved_desc') }}" value="{{ $Achieved->description ? $Achieved->description : old('Achieved_desc') }}" name="Achieved_desc" autocomplete="Achieved_desc">
                                  </div>

                                  </div>
                              </div>


                              <div class="text-center">
                                <div type="submit" class="btn btn-primary me-3 data-submit EditMilestone" do="{{ $Achieved->id }}">{{ __('admin.submit') }}</div>
                                  <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
                              </div>
                         



                        </div>
                    </div>
                </div>
            </div>
              <!--/ Edit JO Modal -->
            @endforeach

      </div>
          

      </div>
    </div>
  </div>

  <!-- Planned Milestones Column -->
  <div class="col-md-4">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">{{ __('admin.Planned_Milestones') }}</h5>
        
          <div class="show_Planned_Milestones row m-2 border radius-15 light-shadow">

            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 planned_milestone_name light-shadow" id="planned_milestone_name" placeholder="{{ __('admin.Milestone_Name') }}" value="{{ old('planned_milestone_name') }}" name="planned_milestone_name" autocomplete="planned_milestone_name">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control my-2 planned_milestone_description light-shadow" id="planned_milestone_description" placeholder="{{ __('admin.Description') }}" value="{{ old('planned_milestone_description') }}" name="planned_milestone_description" autocomplete="planned_milestone_description">
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2" style="height: 40px;">
            <div class="btn btn-outline-primary text-black fw-bold rounded-pill AddMileStone" do="Planned_Milestones">
              <i class="ti ti-plus me-3"></i>
              {{ __('admin.Add') }}
            </div>
          </div>
       

          <div class="ShowTablePlanned"> 
          
        

            @foreach ($Planneds as $key => $Planned)
                  
            <div class="card shadow-sm" style="height: 132px; border-radius: 15px;">
              <div class="card-body">
                <h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                  <div
                    style="width: 32px; height: 32px;" 
                    class="rounded-circle bg-primary text-white d-flex 
                    justify-content-center align-items-center me-2">
                    {{ $key + 1 }}
                  </div>
                  {{$Planned->name}}
                </h6>
                <p class="text-muted line-clamp-2 fs-6">
                  {{$Planned->description}}
                </p>
              </div>
            </div>

            <div class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#EditMilstone{{ $Planned->id }}">{{ __('admin.Edit') }}</div>
                  <form action="{{ route('DeleteMileStone_Comp') }}" method="POST" style="display: inline-block;">
                    @csrf
                    <input type="hidden" value="{{ $Planned->id }}" name="id" >
                    <button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this ?')">{{ __('admin.Delete') }}</button>
                </form>


             <!-- Edit Customer Modal -->
             <div class="modal fade" id="EditMilstone{{ $Planned->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                  <div class="modal-content">
                      <div class="modal-body">
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          <div class="text-center mb-6">
                              <h4 class="mb-2"> ({{$Planned->name}})</h4>
                             
                          </div>

                              <div class="ecommerce-customer-add-basic mb-4" >

                                  <div class="col-md-12">


                                    <div class="mb-6 width49">
                                      <input type="text" class="border-light form-control my-2 Planned_name{{ $Planned->id }} light-shadow" id="Planned_name" placeholder="{{ __('admin.Planned_name') }}" value="{{ $Planned->name ? $Planned->name : old('Planned_name') }}" name="Planned_name" autocomplete="Planned_name">
                                  </div>


                                    <div class="mb-6 width49">
                                      <input type="text" class="border-light form-control mt-2 Planned_desc{{ $Planned->id }} light-shadow" id="Planned_desc" placeholder="{{ __('admin.Planned_desc') }}" value="{{ $Planned->description ? $Planned->description : old('Planned_desc') }}" name="Planned_desc" autocomplete="Planned_desc">
                                  </div>

                                  </div>
                              </div>


                              <div class="text-center">
                                <div type="submit" class="btn btn-primary me-3 data-submit EditMilestone" do="{{ $Planned->id }}">{{ __('admin.submit') }}</div>
                                  <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
                              </div>
                         



                        </div>
                    </div>
                </div>
            </div>
              <!--/ Edit JO Modal -->
            @endforeach

      </div>
        
      </div>
    </div>
  </div>



  <!-- Archive Milestones Column -->
  <div class="col-md-4">
    <div class="card shadow-sm" style="border-radius: 15px;">
      <div class="p-4">
        <h5 class="card-title">{{ __('admin.Archived_Milestones') }}</h5>
        
          <div class="show_Planned_Milestones row m-2 border radius-15 light-shadow">

            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control mt-2 Archived_milestone_name light-shadow" id="Archived_milestone_name" placeholder="{{ __('admin.Milestone_Name') }}" value="{{ old('Archived_milestone_name') }}" name="Archived_milestone_name" autocomplete="Archived_milestone_name">
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <input type="text" class="border-light form-control my-2 Archived_milestone_description light-shadow" id="Archived_milestone_description" placeholder="{{ __('admin.Description') }}" value="{{ old('Archived_milestone_description') }}" name="Archived_milestone_description" autocomplete="Archived_milestone_description">
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2" style="height: 40px;">
            <div class="btn btn-outline-primary text-black fw-bold rounded-pill AddMileStone" do="Archived_Milestones">
              <i class="ti ti-plus me-3"></i>
              {{ __('admin.Add') }}
            </div>
          </div>
        
      <div class="ShowTableArchive"> 

            @foreach ($Archiveds as $key => $Archived)
                  
            <div class="card shadow-sm" style="height: 132px; border-radius: 15px;">
              <div class="card-body">
                <h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                  <div
                    style="width: 32px; height: 32px;" 
                    class="rounded-circle bg-primary text-white d-flex 
                    justify-content-center align-items-center me-2">
                    {{ $key + 1 }}
                  </div>
                  {{$Archived->name}}
                </h6>
                <p class="text-muted line-clamp-2 fs-6">
                  {{$Archived->description}}
                </p>
              </div>
            </div>

            <div class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#EditMilstone{{ $Archived->id }}">{{ __('admin.Edit') }}</div>
                  <form action="{{ route('DeleteMileStone_Comp') }}" method="POST" style="display: inline-block;">
                    @csrf
                    <input type="hidden" value="{{ $Archived->id }}" name="id" >
                    <button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this ?')">{{ __('admin.Delete') }}</button>
                </form>


             <!-- Edit Customer Modal -->
             <div class="modal fade" id="EditMilstone{{ $Archived->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                  <div class="modal-content">
                      <div class="modal-body">
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          <div class="text-center mb-6">
                              <h4 class="mb-2"> ({{$Archived->name}})</h4>
                             
                          </div>

                              <div class="ecommerce-customer-add-basic mb-4" >

                                  <div class="col-md-12">


                                    <div class="mb-6 width49">
                                      <input type="text" class="border-light form-control my-2 Archived_name{{ $Archived->id }} light-shadow" id="Archived_name" placeholder="{{ __('admin.Archived_name') }}" value="{{ $Archived->name ? $Archived->name : old('Archived_name') }}" name="Archived_name" autocomplete="Archived_name">
                                  </div>


                                    <div class="mb-6 width49">
                                      <input type="text" class="border-light form-control mt-2 Archived_desc{{ $Archived->id }} light-shadow" id="Archived_desc" placeholder="{{ __('admin.Archived_desc') }}" value="{{ $Archived->description ? $Archived->description : old('Archived_desc') }}" name="Archived_desc" autocomplete="Archived_desc">
                                  </div>

                                  </div>
                              </div>


                              <div class="text-center">
                                <div type="submit" class="btn btn-primary me-3 data-submit EditMilestone" do="{{ $Archived->id }}">{{ __('admin.submit') }}</div>
                                  <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
                              </div>
                         



                        </div>
                    </div>
                </div>
            </div>
              <!--/ Edit JO Modal -->
            @endforeach

      </div>

      </div>
    </div>
  </div>



</div>

<script>
  function resetForm(form) {
    form.reset();
  }
</script>