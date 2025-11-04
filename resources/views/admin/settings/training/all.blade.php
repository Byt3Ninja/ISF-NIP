@extends('layouts.admin')

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      
      {{-- Alert Messages --}}
      @if (session()->has('message'))
        <div class="alert alert-{{ session()->get('class', 'info') }} alert-dismissible fade show mb-4" role="alert">
          <strong>{{ __('admin.' . session()->get('message')) }}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      {{-- Main Card --}}
      <div class="card">
        {{-- Card Header --}}
        <div class="card-header border-bottom">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h5 class="card-title mb-0">{{ __('cycles.all courses') }}</h5>
            <div class="d-flex align-items-center gap-3">
              @if ($_user->hasAnyRole('organizationManager|Mentor|Admin'))
                <div class="dropdown">
                  <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
                  </ul>
                </div>
                <a class="btn btn-primary"
                  href="{{ route('trainings.create') }}">
                  <i class="ti ti-plus me-2"></i>
                  {{ __('admin.Add_New') }}
                </a>
              @endif
            </div>
          </div>
        </div>

        {{-- Data Table --}}
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="coursesTable">
              <thead class="table-light">
                <tr>
                  <th class="text-nowrap">#</th>
                  <th class="text-nowrap">{{ __('cycles.Title') }}</th>
                  <th class="text-nowrap">{{ __('cycles.Created at') }}</th>
                  <th class="text-nowrap">{{ __('cycles.Added By') }}</th>
                  <th class="text-nowrap">{{ __('admin.quizStatus') }}</th>
                  <th class="text-nowrap">{{ __('cycles.Tags') }}</th>
                  <th class="text-nowrap">{{ $l == 'ar' ? 'الفئات' : 'Categories' }}</th>
                  <th class="text-nowrap">{{ __('admin.CountProgram') }}</th>
                  <th class="text-nowrap" class="text-center">{{ __('cycles.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($courses as $key => $course)
                  <tr id="course-row-{{ $course->id }}">
                    <td class="fw-medium">{{ $key + 1 }}</td>
                    <td>
                      <a href="{{ route('trainings.course_view', $course->id) }}" class="text-primary fw-medium">
                        {{ Str::limit($course->title, 50) }}
                      </a>
                    </td>
                    <td>{{ $course->created_at->format('Y-m-d') }}</td>
                    <td>
                      @if($course->user)
                        {{ $course->user->{"first_name_{$l}"} }} {{ $course->user->{"last_name_{$l}"} }}
                      @else
                        <span class="text-muted">{{ __('admin.Unknown') }}</span>
                      @endif
                    </td>
                    <td data-status="{{ $course->Status == '0' ? 'draft' : 'published' }}">
                      @if($course->Status == '0')
                        <span class="badge bg-label-warning">{{ __('admin.Draft') }}</span>
                      @else
                        <span class="badge bg-label-success">{{ __('admin.Published') }}</span>
                      @endif
                    </td>
                    <td>
                      @php
                        $tagsList = [];
                        if($course->tags) {
                          foreach($course->tags as $tag) {
                            $tagName = $tag->name ?? '';
                            if($tagName && (str_starts_with($tagName, '[') || str_starts_with($tagName, '{'))) {
                              $decoded = json_decode($tagName, true);
                              if(is_array($decoded)) {
                                foreach($decoded as $item) {
                                  if(is_array($item) && isset($item['value'])) {
                                    $tagsList[] = $item['value'];
                                  }
                                }
                              }
                            } else {
                              $tagsList[] = $tagName;
                            }
                          }
                        }
                        $tagsList = array_filter(array_map('trim', $tagsList));
                        $tagsText = implode(', ', $tagsList);
                      @endphp
                      <span data-export="{{ $tagsText }}">
                        @if(count($tagsList) > 0)
                          @foreach($tagsList as $tagName)
                            <span class="badge bg-label-info me-1 mb-1">{{ $tagName }}</span>
                          @endforeach
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </span>
                    </td>
                    <td>
                      @php
                        $categories = $course->categories;
                        $categoriesCount = $categories->count();
                        $displayLimit = 2;
                        $categoriesText = $categories->pluck('title')->implode(', ');
                      @endphp
                      <div data-categories="{{ $categoriesText }}">
                        @if($categoriesCount > 0)
                          @foreach($categories->take($displayLimit) as $category)
                            <span class="badge bg-label-secondary me-1 mb-1">{{ $category->title }}</span>
                          @endforeach
                          @if($categoriesCount > $displayLimit)
                            <button type="button" 
                              class="badge bg-label-primary border-0 mb-1" 
                              style="cursor: pointer;"
                              onclick="showAllCategories({{ $course->id }}, {{ json_encode($categories->pluck('title')) }})">
                              +{{ $categoriesCount - $displayLimit }}
                            </button>
                          @endif
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-label-primary">{{ $course->contents_count ?? $course->contents->count() }}</span>
                    </td>
                    @if ($_user->hasAnyRole('organizationManager|Mentor|Admin'))
                      <td>
                        <div class="d-flex align-items-center gap-2 justify-content-center">
                          <a title="Edit" href="/dashboard/training/EditTR/{{ $course->id }}"
                            class="btn btn-icon btn-label-info rounded-circle">
                            <i class="ti ti-edit"></i>
                          </a>
  
                          @if (isset($course))
                            <button title="Delete" class="btn btn-label-danger btn-icon rounded-circle"
                              onclick="deleteCourse({{ $course->id ?? 0 }})">
                              <i class="ti ti-trash"></i>
                            </button>
                          @endif
  
                          <a title="Show" href="/dashboard/training/viewCourse/{{ $course->id }}"
                            class="btn btn-icon btn-label-primary rounded-circle">
                            <i class="ti ti-eye"></i>
                          </a>
                        </div>
                      </td>
                    @else
                      <td>
                        <a title="Show" href="/dashboard/training/viewCourse/{{ $course->id }}"
                          class="btn btn-label-primary">{{ __('admin.Enroll') }}
                        </a>
                      </td>
                    @endif
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center py-5">
                      <i class="ti ti-file-off ti-lg text-muted d-block mb-2"></i>
                      <p class="text-muted mb-0">{{ $l == 'en' ? 'No courses found.' : 'لا توجد دورات.' }}</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Categories Modal --}}
  <div class="modal fade" id="categoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $l == 'en' ? 'All Categories' : 'جميع الفئات' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="categoriesList" class="d-flex flex-wrap gap-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Edit Modal (if still needed) --}}
  <div class="modal fade" id="editTrainingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="{{ route('trainings.store') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="id" id="courseId">
          
          <div class="modal-header">
            <h5 class="modal-title">{{ $l == 'en' ? 'Edit Course' : 'تعديل الدورة' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div id="imagePreview" class="text-center mb-3"></div>

            <div class="mb-3">
              <label for="courseTitle" class="form-label">{{ __('cycles.Course Title') }}</label>
              <input type="text" class="form-control" id="courseTitle" name="title" required>
            </div>

            <div class="mb-3">
              <label for="courseDescription" class="form-label">{{ __('cycles.Add a description') }}</label>
              <textarea class="form-control" id="courseDescription" name="dsc" rows="4"></textarea>
            </div>

            <div class="mb-3">
              <label for="courseImage" class="form-label">{{ __('cycles.Image') }}</label>
              <input type="file" class="form-control" id="courseImage" name="image" accept="image/*">
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              {{ __('cycles.Close') }}
            </button>
            <button type="submit" class="btn btn-primary">
              {{ __('admin.Submit') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('pagescripts')
  <script>
    let coursesTable;

    $(document).ready(function() {
      // Initialize DataTable with Buttons
      coursesTable = $('#coursesTable').DataTable({
        responsive: true,
        searching: true,
        lengthChange: true,
        pageLength: 10,
        order: [[0, 'asc']],
        dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-md-nowrap align-items-center"l><"col-12 col-md-3 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
        buttons: [
          {
            extend: 'excel',
            text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
            className: 'btn btn-label-success d-none',
            title: '{{ $l == "en" ? "Courses List" : "قائمة الدورات" }}',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7],
              format: {
                body: function(data, row, column, node) {
                  // Handle status column
                  if (column === 4) {
                    return $(node).attr('data-status') === 'draft' ? '{{ __("admin.Draft") }}' : '{{ __("admin.Published") }}';
                  }
                  // Handle tags column
                  if (column === 5) {
                    const exportData = $(node).find('[data-export]').attr('data-export');
                    return exportData || '-';
                  }
                  // Handle categories column
                  if (column === 6) {
                    const categories = $(node).find('[data-categories]').attr('data-categories');
                    return categories || '-';
                  }
                  // Handle content count column
                  if (column === 7) {
                    return $(node).find('.badge').text();
                  }
                  // Strip HTML for other columns
                  return $('<div>').html(data).text().trim();
                }
              }
            }
          },
          {
            extend: 'csv',
            text: '<i class="ti ti-file-text me-1"></i>CSV',
            className: 'btn btn-label-info d-none',
            title: '{{ $l == "en" ? "Courses List" : "قائمة الدورات" }}',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7],
              format: {
                body: function(data, row, column, node) {
                  if (column === 4) {
                    return $(node).attr('data-status') === 'draft' ? '{{ __("admin.Draft") }}' : '{{ __("admin.Published") }}';
                  }
                  if (column === 5) {
                    const exportData = $(node).find('[data-export]').attr('data-export');
                    return exportData || '-';
                  }
                  if (column === 6) {
                    const categories = $(node).attr('data-categories');
                    return categories || '-';
                  }
                  if (column === 7) {
                    return $(node).find('.badge').text();
                  }
                  return $('<div>').html(data).text().trim();
                }
              }
            }
          },
        ],
        columnDefs: [
          { orderable: false, targets: [5, 6, 8] }
        ],
        @if ($l === 'ar')
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
        },
        @endif
        initComplete: function() {
          // Create and insert Category Filter
          $('<select id="categoryFilter" class="form-select form-select-sm ms-md-8 my-3 my-md-0" style="width: auto; min-width: 150px;">' +
            '<option value="">{{ $l == "en" ? "All Categories" : "كل الفئات" }}</option>' +
            '@foreach (App\Expertise::all() as $expertise)' +
            '<option value="{{ $expertise->title }}">{{ $expertise->title }}</option>' +
            '@endforeach' +
            '</select>')
            .insertAfter('.dataTables_length label')
            .on('change', function() {
              const value = $(this).val();
              coursesTable.column(6).search(value).draw();
            });

          // Create and insert Status Filter
          $('<select id="statusFilter" class="form-select form-select-sm ms-2" style="width: auto; min-width: 150px;">' +
            '<option value="">{{ $l == "en" ? "All Statuses" : "كل الحالات" }}</option>' +
            '<option value="draft">{{ __("admin.Draft") }}</option>' +
            '<option value="published">{{ __("admin.Published") }}</option>' +
            '</select>')
            .insertAfter('#categoryFilter')
            .on('change', function() {
              const value = $(this).val();
              if (value === '') {
                coursesTable.column(4).search('').draw();
              } else {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                  const status = $(coursesTable.row(dataIndex).node()).find('td[data-status]').attr('data-status');
                  return value === '' || status === value;
                });
                coursesTable.draw();
                $.fn.dataTable.ext.search.pop();
              }
            });
        }
      });

      // Export button click handlers
      $('#exportExcel').on('click', function() {
        coursesTable.button('.buttons-excel').trigger();
      });

      $('#exportCsv').on('click', function() {
        coursesTable.button('.buttons-csv').trigger();
      });
    });

    // Show All Categories Modal
    function showAllCategories(courseId, categories) {
      const categoriesList = document.getElementById('categoriesList');
      categoriesList.innerHTML = '';
      
      categories.forEach(category => {
        const badge = document.createElement('span');
        badge.className = 'badge bg-label-secondary';
        badge.textContent = category;
        categoriesList.appendChild(badge);
      });
      
      const modal = new bootstrap.Modal(document.getElementById('categoriesModal'));
      modal.show();
    }

    // Delete Course Function with DataTable Row Removal
    function deleteCourse(id) {
      Swal.fire({
        title: "{{ __('admin.SureDelete') }}",
        text: "{{ __('admin.Recover') }}",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "{{ __('admin.DYes') }}",
        cancelButtonText: "{{ __('admin.No') }}",
        reverseButtons: true,
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/dashboard/training/delete/${id}`,
            type: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              _method: 'POST'
            },
            success: function(response) {
              const row = coursesTable.row($('#course-row-' + id));
              row.remove().draw();
              
              releaseSuccessAlert({
                title: "{{ __('admin.Deleted') }}",
                message: "{{ $l == 'en' ? 'The course has been deleted successfully.' : 'تم حذف الدورة بنجاح.' }}",
              });
            },
            error: function(xhr) {
              releaseFailureAlert({
                title: "{{ __('admin.Error') }}",
                message: "{{ __('admin.An unexpected error occurred, please try again later') }}",
              });
            }
          });
        }
      });
    }

    // Edit Course Modal (if needed)
    function editCourse(id) {
      if (id === 0) {
        $('#courseId').val('');
        $('#courseTitle').val('');
        $('#courseDescription').val('');
        $('#imagePreview').html('');
      } else {
        $.get(`/dashboard/training/edit/${id}`, function(response) {
          $('#courseId').val(id);
          $('#courseTitle').val(response.title);
          $('#courseDescription').val(response.dsc);
          
          if (response.image) {
            $('#imagePreview').html(`<img src="/191014/${response.image}" class="img-fluid rounded" style="max-height: 200px;">`);
          }
        }).fail(function() {
          Swal.fire({
            title: "{{ __('admin.Error') }}",
            text: "{{ __('admin.An unexpected error occurred, please try again later') }}",
            icon: "error",
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
          });
        });
      }
      
      $('#editTrainingModal').modal('show');
    }
  </script>
@endsection