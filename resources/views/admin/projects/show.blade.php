@extends('layouts.admin')

@section('title', __('admin.Project Details'))

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('content')

<div class="">
  <div class="">
    <div class="w-100 d-flex justify-content-between align-items-center mt-4 mb-6">
      <h4 class="mb-0 fw-bold">{{ $project?->name }}</h4>
      <div class="d-flex align-items-center gap-3">
        <!-- Link Program Button -->
        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addProgramModal">
          <i class="ti ti-git-merge me-2"></i>
          {{ __('admin.link_program') }}
        </button>
    
        <!-- Add Team Button -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamMemberModal">
          <i class="ti ti-user-plus me-2"></i> 
          {{ __('admin.add_team') }}
        </button>
      </div>
    </div>
    
    @if(session()->has('message'))
    <div class="alert alert-success alert-dismissible" role="alert">
      {{ __('admin.'.session()->get('message')) }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Linked Programs Table -->
    <section class="organizations-list-wrapper mb-6">
      <div class="organizations-list-table">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0 border-bottom pb-3 fw-semibold">{{__('admin.Linked_Programs')}}</h5>
          </div>
          <div class="card-content">
            <div class="card-body">
              <div class="table-responsive">
                <table id="organizations-list-datatable" class="table">
                  <thead class="table-light">
                    <tr>
                      <th>{{ __('admin.Program_name') }}</th>
                      <th class="text-center">{{ __('admin.Status') }}</th>
                      <th class="text-center">{{ __('admin.Date') }}</th>
                      <th class="text-center">{{ __('admin.EndDate') }}</th>
                      <th class="text-center">{{ __('admin.Actions') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($linkedPrograms as $program)
                    <tr>
                      <td>
                        <a href="{{ route('programlist.show', $program->id) }}" class="text-primary">
                          {{ $program->title }}
                        </a>
                      </td>
                      <td class="text-center">
                        @php 
                          $status = $program->pivot->status;
                          $badgeColor = $status === 'Active' ? 'success' : ($status === 'Inactive' ? 'secondary' : 'info');
                        @endphp
                        <span class="badge bg-label-{{$badgeColor}}">{{ $program->pivot->status }}</span>
                      </td>
                      <td class="text-center">{{ $program->start ? $program->start->format('Y-m-d') : '' }}</td>
                      <td class="text-center">{{ $program->end ? $program->end->format('Y-m-d') : '' }}</td>
                      <td class="text-center">
                        <button class="btn btn-label-danger btn-sm btn-icon rounded-circle unlink-btn"
                          data-url="{{ route('projects.programs_unlink', [$project, $program]) }}">
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
            <h5 class="card-title mb-0 border-bottom pb-3 fw-semibold">{{__('admin.Project_Team')}}</h5>
          </div>
          <div class="card-body">
            <table class="table" id="team-members-table">
              <thead class="table-light">
                <tr>
                  <th>{{ __('admin.Name') }}</th>
                  <th class="text-center">{{ __('admin.Role') }}</th>
                  <th class="text-center">{{ __('admin.Date') }}</th>
                  <th class="text-center">{{ __('admin.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse($project->teamMembers as $member)
                <tr>
                  <td>{{ $member->Name }}</td>
                  <td class="text-center">{{ $member->pivot->role }}</td>
                  <td class="text-center">{{ $member->pivot->created_at->format('Y-m-d') }}</td>
                  <td class="text-center">
                    <button class="btn btn-label-danger btn-sm btn-icon rounded-circle remove-member-btn"
                      data-url="{{ route('projects.team_remove_member', [$project, $member]) }}">
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
  <div class="content-backdrop fade"></div>
</div>

<!-- Link Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramLabel" aria-hidden="true">
  <div class="modal-dialog"> <!-- Increased width -->
    <div class="modal-content bg-label-secondary p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="modal-title fw-semibold mb-0 mt-2" id="addProgramLabel">
          <i class="ti ti-git-merge ti-24px me-2"></i> {{ __('admin.Select_Programs_to_Link') }}
        </h5>
        <button type="button" class="btn btn-xs btn-icon btn-outline-primary rounded" data-bs-dismiss="modal" aria-label="Close">
          <i class="ti ti-x ti-16px"></i>
        </button>
      </div>
      <form id="linkProgramsForm" action="{{ route('projects.programs_link', $project) }}" method="POST" class="bg-label-secondary">
        @csrf
        <div class="card">
          <div class="card-body">
            <!-- Search Bar -->
            <div class="position-relative mb-4">
              <input type="text" id="searchProgram" class="form-control px-3" placeholder="{{ __('admin.Search programs') }}">
              <span class="position-absolute end-0 top-50 translate-middle-y me-3">
                <i class="ti ti-search text-muted"></i>
              </span>
            </div>
            <!-- Programs List -->
            <div class="overflow-auto border rounded-3 p-3" style="max-height: 300px;">
              @foreach($availablePrograms as $program)
              <div class="form-check mb-2 border-bottom pb-3">
                <input type="checkbox" class="form-check-input" name="programs[]" value="{{ $program->id }}" id="program-{{ $program->id }}">
                <label class="form-check-label ms-2" for="program-{{ $program->id }}">
                  {{ $program->title }} - {{ $program->organization?->{'name_' . app()->getLocale()} }}
                </label>
              </div>
              @endforeach
            </div>
          </div>
        </div>
        <!-- Footer -->
        <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            {{ __('admin.Cancel') }}
            <i class="ti ti-circle-x ms-2"></i>
          </button>
          <button type="submit" class="btn btn-primary">
            {{ __('admin.Save') }}
            <i class="ti ti-circle-check ms-2"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Team Member Modal -->
<div class="modal fade" id="addTeamMemberModal" tabindex="-1" aria-labelledby="addTeamMemberLabel" aria-hidden="true">
  <div class="modal-dialog"> <!-- Increased width -->
    <div class="modal-content bg-label-secondary p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="modal-title fw-semibold mb-0 mt-2" id="addTeamMemberLabel">
          <i class="ti ti-users ti-24px me-2"></i> {{ __('admin.Select_Team_Members') }}
        </h4>
        <button type="button" class="btn btn-xs btn-icon btn-outline-primary rounded" data-bs-dismiss="modal" aria-label="Close">
          <i class="ti ti-x ti-16px"></i>
        </button>
      </div>
      <form id="addTeamMemberForm" action="{{ route('projects.team_add_member', $project) }}" method="POST" class="bg-label-secondary">
        <div class="card">
          <div class="card-body">
            <!-- Search Bar -->
            <div class="position-relative mb-4">
              <input type="text" id="searchTeamMember" class="form-control px-3" placeholder="{{ __('admin.Search team members') }}">
              <span class="position-absolute end-0 top-50 translate-middle-y me-3">
                <i class="ti ti-search text-muted"></i>
              </span>
            </div>
            <!-- Members List -->
            <form>
              @csrf
              <div class="overflow-auto border rounded-3 p-3" style="max-height: 300px;">
                @foreach($availableTeamMembers as $member)
                <div class="member-container align-items-center justify-content-between py-2 border-bottom" style="display: flex;">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input rounded-circle" name="team_members[]" value="{{ $member->id }}" id="member-{{ $member->id }}">
                    <label class="form-check-label ms-2" for="member-{{ $member->id }}">{{ $member->Name }}</label>
                  </div>
                  <div class="form-check-select">
                    <select name="roles[{{ $member->id }}]" class="form-select form-select-sm">
                      @foreach($distinctRoles as $role)
                      <option value="{{ $role }}">{{ __('admin.'.$role) }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          <!-- Footer -->
          <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              {{ __('admin.Cancel') }}
              <i class="ti ti-circle-x ms-2"></i>
            </button>
            <button type="submit" class="btn btn-primary">
              {{ __('admin.Save') }}
              <i class="ti ti-circle-check ms-2"></i>
            </button>
          </div>
        </form>
      </form>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
<script>
  $(document).ready(function() {
    const lang = "{{ $l }}";

  const datatableLanguage = lang === 'ar' ? {
    "sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
    "sLoadingRecords": "جارٍ التحميل...",
    "sProcessing": "جارٍ التحميل...",
    "sLengthMenu": "أظهر _MENU_ مدخلات",
    "sZeroRecords": "لم يعثر على أية سجلات",
    "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
    "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
    "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
    "sInfoPostFix": "",
    "sSearch": "ابحث:",
    "sUrl": "",
    "oPaginate": {
      "sFirst": "الأول",
      "sPrevious": "السابق",
      "sNext": "التالي",
      "sLast": "الأخير"
    },
    "oAria": {
      "sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
      "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
    }
  } : {}; // If not Arabic, fallback to default (English)

     $('#organizations-list-datatable').DataTable({
    responsive: true,
    autoWidth: false,
    language: datatableLanguage
  });

  $('#team-members-table').DataTable({
    responsive: true,
    autoWidth: false,
    language: datatableLanguage
  });
  
  });
</script>
@stop

@section('pagescripts')
<script>
  $(document).ready(function() {
    $('.unlink-btn').click(function() {
      let button = $(this);
      let deleteUrl = button.data('url');

      let confirmationMessage = "{{ __('admin.delete_program') }}"; 
      Swal.fire({
       	title: "{{__('admin.SureDelete')}}",
        text: "{{__('admin.Recover')}}",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "{{ __('admin.DYes') }}",
        cancelButtonText: "{{ __('admin.No') }}",
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-label-secondary',
        },
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: deleteUrl,
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              // Swal.fire(
              //   '{{ __('admin.Deleted') }}',
              //   response.message,
              //   'success'
              // );

              button.closest('tr').fadeOut(300, function() {
                $(this).remove();
              });
            },
            error: function() {
              Swal.fire(
                '{{ __('admin.Error') }}',
                '{{ __('admin.could_not_unlink_program') }}',
                'error'
              );
            }
          });
        }
      });
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
  $(document).ready(function() {
    $('.remove-member-btn').click(function() {
      let button = $(this);
      let deleteUrl = button.data('url');
      let confirmationMessage = "{{ __('admin.delete_team_member') }}"; 
      Swal.fire({
       	title: "{{__('admin.SureDelete')}}",
        text: "{{__('admin.Recover')}}",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "{{ __('admin.DYes') }}",
        cancelButtonText: "{{ __('admin.No') }}",
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-label-secondary',
        },
        buttonsStyling: false,
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: deleteUrl,
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              // Swal.fire(
              //   '{{ __('admin.Deleted') }}',
              //   response.message,
              //   'success'
              // );

              button.closest('tr').fadeOut(300, function() {
                $(this).remove();
              });
            },
            error: function() {
              Swal.fire(
                '{{ __('admin.Error') }}',
                '{{ __('admin.could_not_remove_team_member') }}',
                'error'
              );
            }
          });
        }
      });
    });
  });
</script>

<script>
  document.getElementById('searchTeamMember').addEventListener('input', function() {
    let query = this.value.toLowerCase();
    document.querySelectorAll('#addTeamMemberForm .member-container').forEach(item => {
      let memberName = item.querySelector('.form-check-label').innerText.toLowerCase();
      item.style.display = memberName.includes(query) ? 'flex' : 'none';
    });
  });
</script>
@stop