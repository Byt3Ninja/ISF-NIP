@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/css{{ $l == 'ar' ? '-rtl' : '' }}/pages/search.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" /> --}}
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.Services directory'))

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body card">
        <div class="search-bar-wrapper">
          <form id="search" method="get">
            <!-- Search Bar -->
            <div class="search-bar card-body mb-6">
              <h4 class="content-header-title mb-3">{{ __('admin.directory') }}</h4>
              <!-- input search -->
              <fieldset class="search-input form-group position-relative">
                <input name="find" value="{{ Request::get('find') }}" type="search"
                  class="form-control rounded-right form-control-lg shadow pl-2" id="searchbar"
                  placeholder="{{ __('admin.Search') }}">
                <button class="btn btn-primary search-btn rounded" type="submit">
                  <span>{{ __('admin.Search') }}</span>
                  <i class="bx bx-search d-block d-sm-none"></i>
                </button>
              </fieldset>
              <!--/ input search -->
            </div>
            <div class="row search-menu">
              <div class="col-12 mb-4">
                <!-- search menu tab -->
                <ul class="list-unstyled px-6">
                  <li class="d-inline-block px-3">
                    <fieldset>
                      <div class="radio">
                        <input type="radio" name="type" id="c0" value=""
                          {{ Request::get('type') ? '' : 'checked=""' }}>
                        <label class="form-label" for="c0">{{ __('admin.All') }}</label>
                      </div>
                    </fieldset>
                  </li>
                  @foreach (App\ServiceCategory::get() as $cat)
                    <li class="d-inline-block px-3">
                      <fieldset>
                        <div class="radio">
                          <input type="radio" name="type" id="c{{ $cat->id }}" value="{{ $cat->id }}"
                            {{ Request::get('type') == $cat->id ? 'checked=""' : '' }}>
                          <label class="form-label" for="c{{ $cat->id }}">{{ $cat->{'name_' . $l} }}</label>
                        </div>
                      </fieldset>
                    </li>
                  @endforeach
                </ul>
                <!--/ search menu tab -->
              </div>
            </div>
            <!-- Search Bar end -->
          </form>
          <!-- search result section -->
          <div id="searchResults" class="py-0 search-results">
            <div class="row">
              <div class="col-12">
                <div class="card shadow-none border-top">
                  <div class="card-content">
                    <div class="card-body shadow-none card-dashboard">
                      <div class="table-responsive">
                        <table class="table zero-configuration">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>{{ __('admin.Service') }}</th>
                              <th>{{ __('admin.Description') }}</th>
                              <th>{{ __('admin.Price') }}</th>
                              <th>{{ __('admin.Rate') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($services as $num => $row)
                              <tr>
                                <td>{{ $num + 1 }}</td>
                                <td>
                                  <a href="#" onclick="openservice({{ $row->id }})">{{ $row->{'name_' . $l} }}</a>
                                </td>
                                <td>{{ substr(strip_tags($row->{'description_' . $l}), 0, 40) }}...</td>
                                <td>{{ $row->price . ' ' . ($currency->currency_code ?? 'SAR') }}</td>

                                <td>
                                  <span class="d-flex align-items-center">
                                    {!! $row->totalRate()
                                      ? str_repeat
                                      ('
                                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#fc960f"     stroke="#fc960f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-star" style="margin: 0 5px 0 5px;">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                      </svg>
                                      ',
                                      $row->totalRate(),
                                      )
                                      : '
                                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#fc960f"     stroke="#fc960f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-star" style="margin: 0 5px 0 5px;">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                      </svg>  0' 
                                    !!}
                                  </span>
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
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- END: Content-->

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
              <label  class="form-label">{{ __('admin.Request Date/ Time') }}:</label>
              <input class='date width-200 form-control' type="text" name="datetime" id="date" value=""
                required autocomplete="off">
            </div>
            <br>
            <div class="form-check mb-3">
              <input type="checkbox" class="form-check-input" id="checkboxGlow1" onclick="$('#textarea').toggle()">
              <label class="form-check-label me-1" for="checkboxGlow1">{{ __('admin.Add comment') }}</label>
            </div>
            <fieldset class="form-label-group" style="display: none;" id="textarea">
              <label class="form-label" for="mycomment">{{ __('admin.Add your note') }}</label>
              <textarea id="mycomment" class="form-control" rows="3" placeholder="{{ __('admin.Add your note') }}"></textarea>
            </fieldset>
          </div>
          <div class="content-right">
            <div id="simg"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="button" onclick="requestserv()">
            {{ __('admin.Make a request') }}
          </button>
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{ __('admin.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
@stop
@section('scripts')
  @if ($l == 'ar')
    <script type="text/javascript">
      $(document).ready(function() {
        $('.zero-configuration').DataTable({
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
        });
      });
    </script>
  @endif
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

  <script>
    // Date & TIme
    $('#date').flatpickr({
      enableTime: true,
      dateFormat: "Y-m-d H:i",
      minDate: "{{ date('Y-m-d H:i', strtotime('+1 hour')) }}", // Minimum date is 1 hour from now
      maxDate: "{{ date('Y-m-d H:i', strtotime('+1 year')) }}", // Maximum date is 1 year from now
      // time_24hr: true,
    });
 
    //   $('#date').datetimepicker({
    //     format: 'Y-m-d H:i',
    //     onGenerate: function(ct) {
    //       jQuery(this).find('.xdsoft_date')
    //       .toggleClass('xdsoft_disabled');
    //   },
    //   maxDate: '{{ date('Y/m/d', strtotime('-1 days')) }}',
    //   timepicker: true
    // });

    function openservice(id) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/directory/" + id,
        success: function(response) {
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
        url: "/dashboard/servicerequest",
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
      Swal.fire({
        title: '{{ __('admin.Service request') }}',
        text: message,
        icon: 'success',
        showCancelButton: false,
        showConfirmButton: true,
        confirmButtonText: "{{ __('admin.OK') }}",
        customClass: {
            confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
    }
  </script>

@stop
