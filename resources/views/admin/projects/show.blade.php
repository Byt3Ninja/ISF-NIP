@extends('layouts.admin')

@section('title', __('admin.Project Details'))

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')

<div class="container mt-4 p-4 bg-white shadow rounded">
    @if(session()->has('message'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ __('admin.'.session()->get('message')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <h5 class="fw-bold">{{ $project?->name }}</h5>

    <!-- Add Program Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProgramModal">
        <i class="bi bi-plus-circle"></i> {{ __('admin.link_program') }}
    </button>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTeamMemberModal">
        <i class="bi bi-person-plus"></i> {{ __('admin.add_team') }}
    </button>

    <!-- Linked Programs Table -->
    <section class="organizations-list-wrapper">
        <div class="organizations-list-table">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title mb-0">{{__('admin.Linked_Programs')}}</h4>
            </div>
            <div class="card-content">
              <div class="card-body">
                    <div class="table-responsive">
                        <table id="organizations-list-datatable" class="table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">{{ __('admin.Program_name') }}</th>
                                                <th class="text-center">{{ __('admin.Status') }}</th>
                                                <th class="text-center">{{ __('admin.Date') }}</th>
                                                <th class="text-center">{{ __('admin.Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($linkedPrograms as $program)
                                            <tr>
                                                <td class="text-center">{{ $program->title }}</td>
                                                <td class="text-center"><span class="badge bg-info">{{ $program->pivot->status }}</span></td>
                                                <td class="text-center">{{ $program->pivot->created_at->format('Y-m-d') }}</td>
                                                <td class="text-center">
                                                    <button class="btn btn-danger btn-sm unlink-btn" data-url="{{ route('projects.unlinkProgram', [$project, $program]) }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>

                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">{{ __('admin.no_programs') }}</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                  </div>
                              </div>
                          </div>
                      </div>
            </div>
       
        </section>

        <!-- Project Team Table -->
 <section class="organizations-list-wrapper">
      <div class="organizations-list-table">
       <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">{{__('admin.Project_Team')}}</h4>
        </div>
        <div class="card-body">
            <table class="table" id="team-members-table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('admin.Name') }}</th>
                        <th>{{ __('admin.Role') }}</th>
                        <th>{{ __('admin.Date') }}</th>
                        <th>{{ __('admin.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($project->teamMembers as $member)
                    <tr>
                        <td>{{ $member->Name }}</td>
                        <td>{{ $member->pivot->role }}</td>
                        <td>{{ $member->pivot->created_at->format('Y-m-d') }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm remove-member-btn" data-url="{{ route('projects.removeTeamMember', [$project, $member]) }}">
                                <i class="ti ti-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">{{ __('admin.no_Team_member') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
    

</div>

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProgramLabel">{{ __('admin.Select_Programs_to_Link') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="searchProgram" class="form-control mb-3" placeholder="Search programs...">
                <form id="linkProgramsForm" action="{{ route('projects.linkPrograms', $project) }}" method="POST">
                    @csrf
                    <div class="overflow-auto" style="max-height: 250px;">
                        @foreach($availablePrograms as $program)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="programs[]" value="{{ $program->id }}">
                            <label class="form-check-label">{{ $program->title }}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="modal-footer mt-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Add Team Member Modal -->
<div class="modal fade" id="addTeamMemberModal" tabindex="-1" aria-labelledby="addTeamMemberLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('admin.Select_Team_Members') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="searchTeamMember" class="form-control mb-3" placeholder="Search team members...">
                <form id="addTeamMemberForm" action="{{ route('projects.addTeamMembers', $project) }}" method="POST">
                    @csrf
                    <div class="overflow-auto" style="max-height: 250px;">
                        @foreach($availableTeamMembers as $member)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="team_members[]" value="{{ $member->id }}">
                            <label class="form-check-label">{{ $member->Name }}</label>
                            <select name="roles[]" class="form-select">
                                <option value="Project Lead">Project Lead</option>
                                <option value="Contributor">Contributor</option>
                                <option value="Reviewer">Reviewer</option>
                            </select>
                        </div>
                        @endforeach
                    </div>
                    <div class="modal-footer mt-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@stop

@section('pagescripts')
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#organizations-list-datatable').DataTable({
        responsive: true,
        autoWidth: false
    });
    $('#team-members-table').DataTable({  
        responsive: true,
        autoWidth: false
    });
});
</script>


<script>
    document.querySelectorAll('.unlink-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to unlink this program?')) {
                fetch(this.dataset.url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => location.reload());
            }
        });
    });

</script>

<script>
    
 document.getElementById('searchProgram').addEventListener('keyup', function() {
     let query = this.value.toLowerCase();
     document.querySelectorAll('#linkProgramsForm .form-check').forEach(item => {
     item.style.display = item.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
  });
</script>
    


<script>
    document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.remove-member-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove this team member?')) {
                fetch(this.dataset.url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => location.reload());
            }
        });
    });


    document.getElementById('searchTeamMember').addEventListener('keyup', function() {
        let query = this.value.toLowerCase();
        document.querySelectorAll('#addTeamMemberForm .form-check').forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
     });
   });
</script>
@stop
