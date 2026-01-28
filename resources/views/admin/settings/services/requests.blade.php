@extends('layouts.admin')

@section('content')
  <div class="">
    <div class="">
      @php
        $tabs = [
          ['title' => __('admin.All Services'), 'route' => 'services.index', 'icon' => 'arrows-move'],
          ['title' => __('admin.Services Requests'), 'route' => 'service_requests.list_all', 'icon' => 'ti ti-briefcase'],
          ['title' => __('admin.Services Categories'), 'route' => 'serviceCategories.index', 'icon' => 'layout-grid'],
        ]
      @endphp
      @if( !$_user->hasRole('serviceProvider') )
      <x-navigation-pills :tabs="$tabs" />
      @endif
      
      <div class="row">
        @if( session()->has('success') )
        <div class="col-md-12 mb-6">
          <div role="alert" class="alert alert-success alert-dismissible">
            {{ session()->get('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
        @endif
        @if( session()->has('error') )
        <div class="col-md-12 mb-6">
          <div role="alert" class="alert alert-danger alert-dismissible">
            {{ session()->get('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
        @endif
      </div>

      <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center border-bottom">
          <h5 class="card-title text-capitalize mb-3 mb-md-0">{{ __('cycles.Services requests') }}</h5>
          <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
              </ul>
            </div>
            @if( $_user->ableTo('service_requests.list_archived') || ( count($received) > 0 && $countold > 0 ) )
              <a href="/dashboard/requests/old" class="btn btn-primary text-nowrap">
                <i class="ti ti-eye me-1 me-md-2"></i>
                {{ __('cycles.Show old records') }}
              </a>
            @endif
          </div>
        </div>

        <div class="card-datatable table-responsive">
          <table class="table services-requests-table">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>{{ __('cycles.SenderName') }}</th>
                <th>{{ __('cycles.Service') }}</th>
                <th>{{ __('admin.Status') }}</th>
                <th>{{ __('cycles.Comments') }}</th>
                <th>{{ __('cycles.Date/Time') }}</th>
                <th>{{ __('cycles.Actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($received as $n => $rec)
                @php
                  $user = $rec->user?->{'first_name_' . $l} . ' ' . $rec->user?->{'last_name_' . $l};
                @endphp
                <tr>
                  <td>{{ $n + 1 }}</td>
                  <td>
                    @if (!empty($rec->user->company))
                      <div class="d-flex align-items-center gap-2">
                        <a href="/dashboard/companyProfile/{{ $rec->user->company->id }}">
                          <img 
                            src="/191014/{{ $rec->user->img_url ?? '/images/logo-placeholder.png' }}" 
                            alt="avatar"
                            class="rounded-circle border shadow-sm" 
                            height="40" 
                            width="40"
                            onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"  
                          />
                        </a>
                        <a href="/dashboard/companyProfile/{{ $rec->user->company->id }}">
                          <div class="media-body">
                            <h6 class="media-heading mb-0">{{ $user }}</h6>
                            <span class="font-small-2">{{ $rec->user->{'title_' . $l} }}
                              @if ($rec->user->company)
                                - {{ $rec->user->company->{'name_' . $l} }}
                              @endif
                            </span>
                          </div>
                        </a>
                      </div>
                    @else
                      No Company
                    @endif
                  </td>
                  <td>{{ $rec->service->{'name_' . $l} }}</td>
                  <td>
                    @if($rec->status == 'Pending')
                      <span class="badge bg-label-warning">{{ __('admin.Pending') }}</span>
                    @else
                      <span class="badge bg-label-success">{{ __('admin.Done') }}</span>
                    @endif
                  </td>
                  <td>{{ $rec->comment ?? '-' }}</td>
                  <td>{{ $rec->date_time }}</td>
                  <td>
                    <button
                      onclick="reply(
                        {{ $rec->id }}, 
                        '{{ addslashes($user) }}', 
                        '{{ addslashes($rec->service->{'name_' . $l} ?? '-') }}',
                        '{{ addslashes($rec->service->vendor_name ?? '-') }}',
                        '{{ addslashes($rec->service->vendor_email ?? '-') }}',
                        '{{ addslashes($rec->service->vendor_phone ?? '-') }}'
                    );"
                      type="button" class="btn btn-icon rounded-circle btn-label-info mr-1">
                      <i class="ti ti-edit text-info"></i>
                    </button>
                    <button
                      onclick="OpenModal({{ $rec->user_id }},'{{ $user }}','{{ $rec->service->{'name_' . $l} }}');"
                      type="button" class="btn btn-icon rounded-circle btn-label-primary mr-1">
                      <i class="ti ti-brand-telegram"></i></button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade text-left" id="MessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel33">
            {{ __('messages.SendMessageTo') }} <span id="uname"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <input type="hidden" name="sender" value="{{ Auth::user()->id }}">
            <input type="hidden" name="receiver" id="rid" value="">
            <div class="form-group mb-3">
              <label for="subject" class="form-label">{{ __('messages.Subject') }}:</label>
              <input id="subject" name="subject" type="text" placeholder="{{ __('messages.Subject') }}"
                class="form-control" required>
            </div>
            <div class="form-group mb-3">
              <label for="message" class="form-label">{{ __('messages.Message') }}: </label>
              <textarea class="form-control" id="message" name='message' rows="8"
                placeholder="{{ __('messages.Write your message') }}" required></textarea>
            </div>
            <div class="w-100 d-flex justify-content-end alitn-items-center gap-4 mt-4">
              <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                {{ __('messages.Close') }}
              </button>
              <button type="submit" class="btn btn-primary">
                {{ __('messages.Send') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Send response -->
  <div class="modal fade text-left" id="ReplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="myModalLabel33">
            {{ __('cycles.Response to') }} <span id="rname"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="sender" value="{{ Auth::user()->id }}">
          <input type="hidden" name="receiver" id="resid" value="">

          <div class="mb-3">
            <label class="form-label">{{ __('admin.Vendor_Name') }}:</label>
            <p id="vendor_name" class="form-control-plaintext">-</p>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('admin.Vendor_Email') }}:</label>
            <p id="vendor_email" class="form-control-plaintext">-</p>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('admin.Vendor_Phone') }}:</label>
            <p id="vendor_phone" class="form-control-plaintext">-</p>
          </div>

          <div class="mb-3">
            <label class="form-label">{{ __('cycles.Add a comment') }}:</label>
            <textarea class="form-control" id="admin_comment" name='admin_comment' rows="4"
              placeholder="{{ __('cycles.Write a response comment') }}"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-primary" data-bs-dismiss="modal" onclick="serviceRequestAction('approve')">
            <span class="d-none d-sm-block">{{ __('cycles.Approve') }}</span>
          </button>
          <button type="button" class="btn btn-label-danger ml-1" onclick="serviceRequestAction('reject')">
            <span class="d-none d-sm-block">{{ __('cycles.Disapprove') }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
@stop

@section('scripts')
<script>
  // --- DataTable Initialization ---
  $(document).ready(function() {
    const isArabic = "{{ $l }}" === "ar";

    // Initialize DataTable
    const table = $('.services-requests-table').DataTable({
      responsive: true,
      searching: true,
      lengthChange: true,
      pageLength: 10,
      order: [[5, "desc"]],
      dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-md-nowrap align-items-center"l>' +
           '<"col-12 col-md-3 d-flex justify-content-center justify-content-md-end"f>>' +
           't<"row"<"col-sm-12 col-md-6"i>' +
           '<"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
      buttons: [
        {
          extend: 'excel',
          title: isArabic ? 'طلبات الخدمات' : 'Services Requests',
          exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
        },
        {
          extend: 'csv',
          title: isArabic ? 'طلبات الخدمات' : 'Services Requests',
          exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
        },
      ],
      columnDefs: [{ orderable: false, targets: [0, 6] }],
      language: isArabic ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
      initComplete: function() {
        const api = this.api();

        // Create Service Filter
        const serviceSelect = $('<select class="form-select form-select-sm ms-md-8 mb-2 mb-md-0" style="width:auto;min-width:170px;">' +
          `<option value="">${isArabic ? 'جميع الخدمات' : 'All Services'}</option>` +
        '</select>');

        const services = [...new Set(api.column(2).data().map(d => d.trim()).toArray())].sort();
        services.forEach(service => serviceSelect.append(`<option value="${service}">${service}</option>`));
        $('.dataTables_length').after(serviceSelect);

        // Create Status Filter
        const statusSelect = $('<select class="form-select form-select-sm ms-md-2 mb-2 mb-md-0" style="width:auto;min-width:170px;">' +
          `<option value="">${isArabic ? 'جميع الحالات' : 'All Statuses'}</option>` +
          `<option value="{{ __("admin.Pending") }}">{{ __("admin.Pending") }}</option>` +
          `<option value="{{ __("admin.Done") }}">{{ __("admin.Done") }}</option>` +
        '</select>');
        serviceSelect.after(statusSelect);

        // Create Date Filter (after Status Filter)
        const dateInput = $('<input type="text" id="dateFilter" class="form-control form-control-sm ms-md-2" placeholder="' + 
          (isArabic ? 'تصفية حسب التاريخ' : 'Filter by Date') + 
        '" style="width:auto;min-width:160px;">');
        statusSelect.after(dateInput);

        // Initialize Flatpickr for date filter
        flatpickr('#dateFilter', {
          dateFormat: "Y-m-d",
           onReady: function (selectedDates, dateStr, instance) {
            let btn = document.createElement("button");
            btn.type = "button";
            btn.innerHTML = "{{ __('admin.Clear') }}";
            btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "my-4");
            btn.addEventListener("click", function () {
              instance.clear(); // <-- clear flatpickr value
              instance.close();
              api.column(5).search('').draw();
            });
            instance.calendarContainer.appendChild(btn);
          },
          onChange: function(selectedDates, dateStr) {
            api.column(5).search(dateStr).draw();
          }
        });

        // Filter logic
        serviceSelect.on('change', function() {
          api.column(2).search(this.value).draw();
        });

        statusSelect.on('change', function() {
          api.column(3).search(this.value).draw();
        });
      }
    });

    // Export button handlers
    $('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
    $('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());
  });

  // --- Modal and Approval Functions ---
  function OpenModal(id, name, title) {
    $('#uname').html(name);
    $('#rid').val(id);
    $('#subject').val('{{ __('cycles.Your request') }} - ' + title);
    $('#MessageModal').modal('show');
  }

  function reply(id, name, title, vendorName, vendorEmail, vendorPhone) {
    $('#rname').html('"' + title + '" {{ __('cycles.Request from') }} ' + name);
    $('#resid').val(id);
    $('#vendor_name').text(vendorName || '-');
    $('#vendor_email').text(vendorEmail || '-');
    $('#vendor_phone').text(vendorPhone || '-');
    $('#ReplyModal').modal('show');
  }

  function serviceRequestAction(stat) {
    const comment = $('#admin_comment').val();
    const id = $('#resid').val();
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    $.post('/dashboard/requests/take_action', { id, stat, comment })
      .done((response) => {
        if( response.status )
        {
					Swal.fire({
						icon: 'success',
						title: response.title || 'Success',
						html: response.message || 'Service request ' + stat + 'd successfully',
						timer: 2500,
						timerProgressBar: true,
						showCancelButton: false,
						confirmButtonText: 'Close',
						customClass: {
							confirmButton: 'btn mx-1 bg-primary text-white',
						}
					}).then((result) => { location.reload(); });
        }
        else
        {
					Swal.fire({
						icon: 'warning',
						title: response.title || 'Failed',
						html: response.message || 'Something went wrong',
						timer: 2500,
						timerProgressBar: true,
						showCancelButton: false,
						confirmButtonText: 'OK',
						customClass: {
							confirmButton: 'btn mx-1 bg-primary text-white',
						}
					});
        }
      })
      .fail(err => {
        var response = err.responseJSON;
        releaseFailureAlert({
          title: response.title || "{{ __('admin.Error') }}",
          message: response.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
        });
      });
  }
</script>
@stop

