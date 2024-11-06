@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/jkanban/jkanban.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-kanban.css" />
  <style>
    .kanban-board-header .dropdown {
      display: none;
    }
    .kanban-container {
      display: flex;
      width: max-content !important;
      gap: 3rem;
    }
  </style>
@stop

@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->

  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="app-kanban">

      @if (!empty($mentorshipTasks))
      {{-- <!-- Add new board -->
      <div class="row">
        <div class="col-12">
          <form class="kanban-add-new-board">
            <label class="kanban-add-board-btn" for="kanban-add-board-input">
              <i class="ti ti-plus"></i>
              <span class="align-middle">Add new</span>
            </label>
            <input
              type="text"
              class="form-control w-px-250 kanban-add-board-input mb-4 d-none"
              placeholder="Add Board Title"
              id="kanban-add-board-input"
              required />
            <div class="mb-4 kanban-add-board-input d-none">
              <button class="btn btn-primary btn-sm me-4">Add</button>
              <button type="button" class="btn btn-label-secondary btn-sm kanban-add-board-cancel-btn">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div> --}}

      <!-- Kanban Wrapper -->
      <div 
        class="kanban-wrapper bg-white px-4 py-8" 
        style="border-radius: 1rem;">
      </div>

      <!-- Edit Task/Task & Activities -->
      <div class="offcanvas offcanvas-end kanban-update-item-sidebar">
        <div class="offcanvas-header border-bottom">
          <h5 class="offcanvas-title">Add Comment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-0">
          <div class="nav-align-top">
            <ul class="nav nav-tabs mb-5 rounded-0">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-update">
                  <i class="ti ti-edit ti-18px me-1_5"></i>
                  <span class="align-middle">Edit</span>
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity">
                  <i class="ti ti-chart-pie-2 ti-18px me-1_5"></i>
                  <span class="align-middle">Activity</span>
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content p-0">
            <!-- Update item/tasks -->
            <div class="tab-pane fade show active" id="tab-update" role="tabpanel">
              <form>
                <div class="mb-5">
                  <label class="form-label">Comment</label>
                  <div class="comment-editor border-bottom-0"></div>
                  <div class="d-flex justify-content-end">
                    <div class="comment-toolbar">
                      <span class="ql-formats me-0">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-link"></button>
                        <button class="ql-image"></button>
                      </span>
                    </div>
                  </div>
                </div>
                <div>
                  <div class="d-flex flex-wrap">
                    <button type="button" class="btn btn-primary me-4" data-bs-dismiss="offcanvas">
                      Update
                    </button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas">
                      Delete
                    </button>
                  </div>
                </div>
              </form>
            </div>
            <!-- Activities -->
            <div class="tab-pane fade text-heading" id="tab-activity" role="tabpanel">
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-success rounded-circle">HJ</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Jordan</span> Left the board.</p>
                  <small class="text-muted">Today 11:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/6.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0">
                    <span>Dianna</span> mentioned <span class="text-primary">@bruce</span> in a comment.
                  </p>
                  <small class="text-muted">Today 10:20 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/2.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Martian</span> added moved Charts & Maps task to the done board.</p>
                  <small class="text-muted">Today 10:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Barry</span> Commented on App review task.</p>
                  <small class="text-muted">Today 8:32 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-dark rounded-circle">BW</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Bruce</span> was assigned task of code review.</p>
                  <small class="text-muted">Today 8:30 PM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-danger rounded-circle">CK</span>
                </div>
                <div class="media-body">
                  <p class="mb-0">
                    <span>Clark</span> assigned task UX Research to
                    <span class="text-primary">@martian</span>
                  </p>
                  <small class="text-muted">Today 8:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/4.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0">
                    <span>Ray</span> Added moved <span>Forms & Tables</span> task from in progress to done.
                  </p>
                  <small class="text-muted">Today 7:45 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Barry</span> Complete all the tasks assigned to him.</p>
                  <small class="text-muted">Today 7:17 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-success rounded-circle">HJ</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Jordan</span> added task to update new images.</p>
                  <small class="text-muted">Today 7:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/6.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0">
                    <span>Dianna</span> moved task <span>FAQ UX</span> from in progress to done board.
                  </p>
                  <small class="text-muted">Today 7:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-danger rounded-circle">CK</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Clark</span> added new board with name <span>Done</span>.</p>
                  <small class="text-muted">Yesterday 3:00 PM</small>
                </div>
              </div>
              <div class="media d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-dark rounded-circle">BW</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Bruce</span> added new task in progress board.</p>
                  <small class="text-muted">Yesterday 12:00 PM</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @else
			<section class="row">
				<div class="col-xl-12 col-md-12 col-sm-12">
				  <div class="card px-4">
					<div class="card-header border-bottom mb-4">
					  <h4 class="card-title">{{ __('cycles.nosubscripe') }}</h4>
					</div>
	
					<div class="card-body">
					  {{ __('admin.No Quizzes added yet') }}, <a href="/dashboard">{{ __('cycles.Back') }}</a>.
					</div>
				  </div>
				</div>
			  </section>
			@endif
    </div>
  </div>
  <!-- / Content -->

  <div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->
@stop

@section('scripts')
  <script>
    // Remove taskId from URL
    document.addEventListener('DOMContentLoaded', () => {
      const searchParams = new URLSearchParams(window.location.search);
      if (searchParams.has('taskId')) {
        searchParams.delete('taskId');
        const newUrl = searchParams.toString() ? `${window.location.pathname}?${searchParams.toString()}` : window.location.pathname;
        window.history.pushState({}, '', newUrl);
      }
    });
  </script>
  <script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
  <script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/jkanban/jkanban.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
@stop

@section('pagescripts')
  <script>
    // Mentorship tasks data
    const mentorshipTasks = @json($mentorshipTasks);

    // Restructure tasks
    const restructureTasks = () => {
      if (mentorshipTasks?.length > 0) {
        return [
          {
            id: "board-in-progress",
            title: "Pending",
            item: mentorshipTasks.filter(task => task.status === 'pending'),
          },
          {
            id: "board-in-review",
            title: "In Review",
            item: mentorshipTasks.filter(task => task.status === 'in-review'),
          },
          {
            id: "board-done",
            title: "Done",
            item: mentorshipTasks.filter(task => task.status === 'completed'),
          }
        ]
      }
    }
    const tasks = restructureTasks();
  </script>
  <script src="/vuexy/assets/js/app-kanban.js"></script>
@stop


{{-- @extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible mb-2" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>

            </div>
          @endif

          @if (session()->has('message2') || !empty($errors->all()))
            <div class="alert alert-danger alert-dismissible mb-2" role="alert">
              <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <div class="d-flex align-items-center">
                <i class="ti ti-error"></i>
                <span>
                  {{ __('admin.' . session()->get('message2')) }}
                  @forelse($errors->all() as $message)
                    {{ $message }}
                  @empty
                  @endforelse

                </span>
              </div>
            </div>
          @endif
        </section>
        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header border-bottom mb-4">
                <h4 class="card-title align-items-center">
                  <i class='bx bx-card font-medium-4 mr-1'></i>{{ __('mentor.mentorshipTasks.all') }}
                </h4>
              </div>
              <div class="card-body">
                <div class="card-text">
                  <div class="table-responsive">
                    <table class="table mb-0" id="userstable">
                      <thead class="table-light">
                        <tr>
                          <th class="text-center">{{ __('mentor.mentorshipTasks.mentorship request') }}</th>
                          <th class="text-center">{{ __('mentor.mentorshipTasks.title') }}</th>
                          <th class="text-center">{{ __('cycles.Done') }}</th>
                          <th class="text-center">{{ __('cycles.Actions') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if (is_array($mentorshipTasks) && in_array('NoCompany', $mentorshipTasks))

                          <tr>
                            <td class="text-center">
                              {{ __('mentor.mentorshipTasks.noCompany') }}
                            </td>
                            <td class="text-center">

                            </td>
                            <td class="text-center">

                            </td>
                          </tr>
                        @else
                          @foreach ($mentorshipTasks as $task)
                            <tr>
                              <td class="text-center">
                                {{ $task->mentorshipRequest->zoom_date }}
                              </td>
                              <td class="text-center">
                                {{ $task->title ?? '-----' }}
                              </td>
                              <td class="text-center">
                                {{ $task->status == 'pending' ? __('No') : __('Yes') }}
                              </td>
                              <td class="text-center">
                                <a href="{{ route('startup.mentorshipTasks.show', ['mentorshipTask' => $task->id]) }}"
                                  title="Show" class="btn btn-icon rounded-circle btn-label-success">
                                  <i class="bx bxs-comment-add"></i></a>
                              </td>
                            </tr>
                          @endforeach
                        @endif
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->
@stop

@section('pagescripts')
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script>
    $(document).ready(function() {
      $('.table').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "columnDefs": [{
          "orderable": false,
          "targets": [7]
        }, ],
        @if ($l == 'ar')
          "language": {
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
          }
        @endif
      });
    });
  </script>
@stop --}}
