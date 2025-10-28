@extends('layouts.admin')

@section('style')
<style>
  .chart-card {
    height: 140px !important;
  }
</style>
@stop


@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Summary Section -->
    <div class="row">
      <!-- KPI Card: Total Forms -->
      <div class="col-lg-4 col-md-6 mb-6">
        <div class="card chart-card">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <h4 class="metric-value text-primary mb-1" id="total-forms"></h4>
              <p class="metric-label text-muted mb-0">{{ __('admin.Total Forms') }}</p>
            </div>
            <div class="icon text-primary fs-2">
              <i class="fas fa-file-alt"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI Card: Total Submissions -->
      <div class="col-lg-4 col-md-6 mb-6">
        <div class="card chart-card">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <h4 class="metric-value text-primary mb-1" id="total-submissions"></h4>
              <p class="metric-label text-muted mb-0">{{ __('admin.Total Submissions') }}</p>
            </div>
            <div class="icon text-success fs-2">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Chart Card: Forms by Status -->
      {{-- <div class="col-lg-3 col-md-6 mb-6">
        <div class="card chart-card">
          <div class="card-body">
            <canvas id="statusChart"></canvas>
            <!-- Chart will be rendered here -->
          </div>
        </div>
      </div> --}}

      <!-- Chart Card: Forms by Organization -->
      <div class="col-lg-4 col-md-6 mb-6">
        <div class="card chart-card">
          {{-- <div class="card-header bg-transparent border-0 pt-3 pb-0">
            <h6 class="mb-0 text-muted">Forms by Organization</h6>
          </div> --}}
          <div class="card-body">
            <canvas id="orgChart" style="max-height: 140px; padding-bottom: 2.5rem;"></canvas>
            <!-- Chart will be rendered here -->
          </div>
        </div>
      </div>
    </div>
    <!--/ Summary Section -->

    <!-- Forms List Table -->
    <div class="card">
      <div class="card-datatable table-responsive pt-0">
        <table class="datatables-basic table">
          <thead class="table-light">
            <tr>
              <th></th>
              <th>Form id</th>
              <th>Form Name</th>
              <th>Assigned Organization</th>
              <th>Submissions</th>
              <th>Last Modified</th>
              <th>Create At</th>
              {{-- <th>status</th> --}}
            </tr>
          </thead>
        </table>
      </div>
    </div>
   
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">{{ __('admin.New Form') }}</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
          aria-label="{{ __('admin.Close') }}"></button>
      </div>
      <div class="offcanvas-body flex-grow-1">
        <form class="add-new-record pt-0 row g-2 mt-12 manageForm" id="form-add-new-record"
          action="/dashboard/builder/2" method="POST">
          @csrf
          <div class="col-sm-12 mb-6">
            <label class="form-label" for="mtitle">{{ __('builder.Form_Title') }}</label>
            <input type="text" id="mtitle" class="form-control dt-form-name" name="title"
              placeholder="{{ __('builder.Form_Title') }}" aria-label="{{ __('builder.Form_Title') }}"  required/>
          </div>
          <div class="col-sm-12 mb-6">
            <label class="form-label" for="organization_id">{{ __('admin.Assigned to Organizations') }}</label>
            <select id="organization_id" name="organization_id" class="form-select select2 dt-org-select" data-placeholder="{{ __('admin.Select Organization') }}" data-required="true" required="true">
              <option value="" selected disabled>{{ __('admin.Select Organization') }}</option>
              @php
              
              if(auth()->user()->hasAnyRole(['organizationManager'])) {
                $organizations = App\Organization::where('id',auth()->user()->organization_id)->get();
              }else{
                $organizations = App\Organization::all();
              }
              @endphp
              
              @foreach ($organizations as $organization)
              <option value="{{ $organization->id }}">{{ $organization->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-12 mt-12">
            <button type="submit" id="submit_btn_add_form" class="btn btn-primary data-submit me-sm-4 me-1">{{
              __('admin.Submit') }}</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Cancel')
              }}</button>
          </div>
        </form>
      </div>
    </div>
    <!--/ Forms List Table -->

    <!-- Modal to edit record -->
    <div class="offcanvas offcanvas-end" id="edit-record">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="exampleModalLabel">{{ __('admin.Edit Form') }}</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
          aria-label="{{ __('admin.Close') }}"></button>
      </div>
      <div class="offcanvas-body flex-grow-1">
        <form class="edit-record pt-0 row g-2 mt-12 manageForm" id="form-edit-record" action="/dashboard/builder/0"
          method="POST">
          @csrf
          <input type="hidden" name="_method" value="PUT">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <input type="hidden" name="id" class="form_id" id="id">
          <div class="col-sm-12 mb-6">
            <label class="form-label" for="mtitle">{{ __('builder.Form_Title') }}</label>
            <input type="text" id="mtitle" class="form-control dt-form-name" name="title"
              placeholder="{{ __('builder.Form_Title') }}" aria-label="{{ __('builder.Form_Title') }}" required />
          </div>
          <div class="col-sm-12 mb-6">
            <label class="form-label" for="organization_id">{{ __('admin.Assigned to Organizations') }}</label>
            <select id="organization_id" name="organization_id" class="form-select select2 dt-org-select" data-placeholder="{{ __('admin.Select Organization') }}" data-required="true" required="true">
              <option value="" selected disabled>{{ __('admin.Select Organization') }}</option>
              @php
               if(auth()->user()->hasAnyRole(['organizationManager'])) {
                $organizations = App\Organization::where('id',auth()->user()->organization_id)->get();
              }else{
                $organizations = App\Organization::all();
              }
              @endphp
              @foreach ($organizations as $organization)
              <option value="{{ $organization->id }}" data-value="{{ $organization->name }}">{{ $organization->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-12 mt-12">
            <button type="submit" id="submit_btn_edit_form" class="btn btn-primary data-submit me-sm-4 me-1">{{
              __('admin.Submit') }}</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Cancel')
              }}</button>
          </div>
        </form>
      </div>
    </div>
    <!--/ Modal to edit record -->
  </div>
  <!--/ Content -->
</div>
<!-- / Content wrapper -->
@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
@stop

@section('pagescripts')
<script src="/vuexy/assets/js/form-list.js"></script>

<script>
  $('.manageForm').on('submit', function (e) {
    e.preventDefault();
    const form = $(this);
    const formData = form.serialize();

    const set = form.attr('id') === 'form-add-new-record' ? 2 : 0;

    const id = form.find('#id').val();
    const title = form.find('#mtitle').val();
    const organization_id = form.find('#organization_id').val();

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/builder/" + set,
        data: {
          "id": id,
          "title": title,
          "organization_id": organization_id
        },
        success: function(response) {
          if (response) {
            // console.log(response);
            location.href = 'show-builder/' + (response || '');        
          }
        },
        error: function(error) {
          // console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
  });
</script>


<script>
  let formListData = @json($data);
  // console.log(formListData);

  // Sample data for demonstration
  const totalForms = formListData ? formListData.length : 0;
  const totalSubmissions = formListData ? formListData.reduce((acc, form) => acc + (form.submissions || 0), 0) : 0;
  const submissionsByOrg = formListData ? formListData.reduce((acc, form) => {
    const org = form.assigned_organization || 'Unknown';
    acc[org] = (acc[org] || 0) + (form.submissions || 0);
    return acc;
  }, {}) : {};

  // Convert the object to arrays for charting
  const orgLabels = submissionsByOrg ? Object.keys(submissionsByOrg) : [];
  const orgData = submissionsByOrg ? Object.values(submissionsByOrg) : [];

  document.getElementById('total-forms').innerText = totalForms;
  document.getElementById('total-submissions').innerText = totalSubmissions;

  document.addEventListener('DOMContentLoaded', function () {
    // Action Button Clicks
    const tableBody = document.getElementById('formsTable')?.querySelector('tbody');
    if (tableBody) {
      tableBody.addEventListener('click', function (event) {
        const button = event.target.closest('.action-btn');
        if (button && !button.classList.contains('disabled')) {
          const formId = button.getAttribute('data-id');
          const action = button.getAttribute('title');
          alert(`Action: ${action} clicked for Form ID: ${formId}`);
          event.preventDefault();
        }
      });
    }

    // --- Placeholder Chart Initialization ---
    // In a real app, data would come from an API based on table/filtered data

    // Forms by Status Chart (Donut)
    // const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    // if (statusCtx) {
    //   new Chart(statusCtx, {
    //     type: "doughnut",
    //     data: {
    //       labels: ['{{ __('admin.Active') }}', '{{ __('admin.NotActive') }}'],
    //       datasets: [
    //         {
    //           label: "Forms by Status",
    //           data: [6, 1], // Data based on your sample: 6 Active, 1 Inactive
    //           backgroundColor: [config.colors.success, config.colors.secondary],
    //           borderColor: [config.colors.success, config.colors.secondary],
    //           borderWidth: 1,
    //         },
    //       ],
    //     },
    //     options: {
    //       responsive: true,
    //       maintainAspectRatio: false,
    //       cutout: "70%", // Adjust the cutout percentage to reduce the size of the circle
    //       plugins: {
    //         legend: {
    //           position: "left",
    //           labels: {
    //             boxWidth: 12,
    //           },
    //         },
    //       },
    //       layout: {
    //         padding: {
    //           top: 10,
    //           bottom: 10,
    //           left: 30,
    //           right: 10,
    //         },
    //       },
    //     },
    //   });

    //   // Reduce chart width and height
    //   statusCtx.canvas.style.width = "63px";
    //   statusCtx.canvas.style.height = "63px";
    // }

    // Forms by Organization Chart (Vertical Bar)
    const orgCtx = document.getElementById('orgChart')?.getContext('2d');
    if (orgCtx) {
      new Chart(orgCtx, {
        type: 'bar',
        data: {
          labels: orgLabels.length > 0 ? orgLabels : ['No Data'], // Use orgLabels or a placeholder
          datasets: [
            {
              label: '{{ __('admin.Count of Forms') }}',
              data: orgData.length > 0 ? orgData : [0], // Use orgData or a placeholder
              backgroundColor: [
                'rgba(13, 110, 253, 0.7)', // Primary
                'rgba(25, 135, 84, 0.7)', // Success
                'rgba(255, 193, 7, 0.7)', // Warning
                'rgba(108, 117, 125, 0.7)', // Secondary
              ],
              borderColor: [
                'rgba(13, 110, 253, 1)',
                'rgba(25, 135, 84, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(108, 117, 125, 1)',
              ],
              borderWidth: 0,
              barThickness: 3,
              borderWidth: 0,
              barThickness: 3, // Reduce bar width to make it thinner
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false, // Hide legend for bar chart with single dataset
            },
            tooltip: {
              enabled: true, // Enable tooltips
            },
          },
          scales: {
            x: {
              display: false, // Hide X-axis labels
            },
            y: {
              display: true, // Show Y-axis labels
              beginAtZero: true,
              ticks: { precision: 0 }, // Ensure Y axis starts at 0 and shows whole numbers
            },
          },
        },
      });
    }
  });
</script>
@stop