@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.Services directory'))

@section('content')
<div class="">
  <div class="">

    <div class="card">
      <div class="card-content">
        <div class="card-header border-bottom">
          <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('admin.directory') }}</h5>
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="card-body shadow-none card-dashboard">
          <div class="table-responsive">
            <table class="table services-table">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>{{ __('admin.Name') }}</th>
                  <th>{{ __('admin.Description') }}</th>
                  <th>{{ __('admin.Category') }}</th>
                  <th>{{ __('admin.Price') }}</th>
                  <th>{{ __('admin.Rate') }}</th>
                  <th>{{ __('admin.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($services as $num => $row)
                <tr>
                  <td>{{ $num + 1 }}</td>
                  <td>
                    <a href="javascript:void(0)" onclick="openServiceDetails({{ $row->id }})">{{ $row->{'name_' . $l} }}</a>
                  </td>
                  <td>{{ substr(strip_tags($row->{'description_' . $l}), 0, 40) }}...</td>
                  <td>{{ $row->{'category_name_' . $l} }}</td>
                  <td>{{ $row->price . ' ' . ($currency->currency_code ?? 'SAR') }}</td>

                  <td>
                    <span class="d-flex align-items-center">
                      {!! $row->totalRate()
                      ? str_repeat('
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#fc960f"
                        stroke="#fc960f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-star" style="margin: 0 5px 0 5px;">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path
                          d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                      </svg>
                      ',
                      $row->totalRate(),
                      )
                      : '
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#fc960f"
                        stroke="#fc960f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-star" style="margin: 0 5px 0 5px;">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path
                          d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                      </svg> 0'
                      !!}
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-primary btn-sm" onclick="openServiceDetails({{ $row->id }})">
                      {{ __('dashboard.Request') }}
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div> <!-- end of table -->
        </div>
      </div>
    </div>
  </div>
</div>

<!--Service Modal -->
<div class="modal fade text-left" id="service-data" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header mb-0">
        <h4 class="modal-title" id="title"></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="content-left">
          <input type="hidden" id="sid">
          <p id="description"> </p>
          <div>
            <span class="form-label">{{ __('admin.Price') }}: </span>
            <span id="price"></span>
          </div>
          <br>
          <div id="vendor-info">
            <span class="form-label">{{ __('admin.Vendor_Name') }}: </span>
            <span class="mb-2" id="vendor-name"></span> <br /><br />
            <span class="form-label">{{ __('admin.Vendor_Phone') }}: </span>
            <span class="mb-2" id="vendor-phone"></span> <br /><br />
            <span class="form-label">{{ __('admin.Vendor_Email') }}: </span>
            <span id="vendor-email"></span>
          </div>

          <div class="form-group mt-4" id="showdates">
            <label class="form-label">{{ __('admin.Request Date/ Time') }}:</label>
            <input class='date width-200 form-control' type="text" name="datetime" id="date" value="" required
              autocomplete="off">
          </div>
          <br>
          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="checkboxGlow1" onclick="$('#textarea').toggle()">
            <label class="form-check-label me-1" for="checkboxGlow1">{{ __('admin.Add comment') }}</label>
          </div>
          <fieldset class="form-label-group" style="display: none;" id="textarea">
            <label class="form-label" for="mycomment">{{ __('admin.Add your notes') }}</label>
            <textarea id="mycomment" class="form-control" rows="3"
              placeholder="{{ __('admin.Add your notes') }}"></textarea>
          </fieldset>
        </div>
        <div class="content-right">
          <div id="simg"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ __('admin.Close') }}
        </button>
        <button class="btn btn-primary" type="button" onclick="requestserv()">
          {{ __('admin.Make a request') }}
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script>
  $(document).ready(function() {
    const isArabic = "{{ $l }}" === "ar";

    // Initialize Datatable
    let table = $('.services-table').DataTable({
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
          title: isArabic ? 'الخدمات' : 'Services',
          exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
        },
        {
          extend: 'csv',
          title: isArabic ? 'الخدمات' : 'Services',
          exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
        },
      ],
      columnDefs: [{ orderable: false, targets: [0, 6] }],
      language: isArabic ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
      initComplete: function() {
        const api = this.api();

        // Create Category Filter
        const categorySelect = $('<select class="form-select form-select-sm ms-md-8 mb-2 mb-md-0" style="width:auto;min-width:170px;">' +
          `<option value="">${!isArabic ? 'All Categories' : 'جميع الفئات'}</option>` +
        '</select>');

        const categories = [...new Set(api.column(3).data().map(d => d.trim()).toArray())].sort();
        categories.forEach(category => categorySelect.append(`<option value="${category}">${category}</option>`));
        $('.dataTables_length').after(categorySelect);

         // Filter logic
        categorySelect.on('change', function() {
          api.column(3).search(this.value).draw();
        });
      }
    });

    // Export button handlers
    $('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
    $('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());
  });
</script>

<script>
  // Date & TIme
    $('#date').flatpickr({
      enableTime: true,
      dateFormat: "Y-m-d H:i",
      minDate: "{{ \App\Services\TimezoneService::platformNow()->addHour()->format('Y-m-d H:i') }}", // Minimum date is 1 hour from now
      maxDate: "{{ \App\Services\TimezoneService::platformNow()->addYear()->format('Y-m-d H:i') }}", // Maximum date is 1 year from now
      // time_24hr: true,
    });

    function openServiceDetails(id) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/directory/" + id,
        success: function(res) {
          if( res.status )
          {
            var response = res.service;
            let img = '<img class="img-fluid" src="/191014/' + response.img_url + '">';
            $('#title').text(response.name_{{ $l }});
            $('#description').html(response.description_{{ $l }});
            $('#simg').html(img);
            $('#sid').val(response.id);
            // Handle Price Visibility
            if (response.hide_price && !response.is_admin) {
              $('#price').closest('div').hide();
            } else {
              $('#price').closest('div').show();
              $('#price').html(response.price + ' {{ __('EGP') }}');
            }
  
            // Handle Vendor Info Visibility
            if (response.hide_vendor_info && !response.is_admin) {
                $('#vendor-info').hide();
            } else {
                $('#vendor-info').show();
                $('#vendor-name').html(response.vendor_name);
                $('#vendor-phone').html(response.vendor_phone);
                $('#vendor-email').html(response.vendor_email);
            }  
            $('#service-data').modal('show');
          }
          else
          {
						releaseAlert('warning', {
						  title: res.title || 'Failed',
						  message: res.message || 'Something went wrong',
						  confirmText: 'OK',
						  confirmColor: 'primary',
						  hideCancel: true,
						  autoClose: 2500
						});
          }
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    function requestserv() {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      let id = $('#sid').val();
      let comment = $('#mycomment').val();
      let datetime = $('#date').val();
      if (!datetime) {
        alert('{{ __('admin.Adddatefirst') }}');
        return false;
      }
      console.log(id + '-' + comment);
      $.ajax({
        type: "POST",
        url: "/dashboard/service/request",
        data: {
          "id": id,
          "comment": comment,
          "datetime": datetime,
        },
        success: function(response) {
          console.log(response);
          $('#service-data').modal('hide');
          displayMessage('{{ __('admin.request sent') }}');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    function displayMessage(message) {
      //toastr.success(message, '{{ __('admin.Service request') }}');
      releaseAlert('success', {
        title: '{{ __('admin.Service request') }}',
        message: message,
        confirmText: "{{ __('admin.OK') }}",
        confirmColor: 'primary'
      });
    }
</script>

@stop