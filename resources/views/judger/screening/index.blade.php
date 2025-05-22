@extends('layouts.admin')

@section('style')
  <style>
    .line-clamp-4 {
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 4;
      overflow: hidden;
    }
    .fs-sm {
      font-size: 0.8rem;
    }
    .card {
      position: relative;
      overflow: hidden;
    }
    .card-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: var(--bs-white);
      color: var(--bs-white);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }
    .card:hover .card-overlay {
      opacity: 1;
    }
  </style>
@stop

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      @include('judger.screening.partials.screening-header-card')
      {{--  <div class="row">
        @forEach($screeningData as $screening)
          <div class="col-12 col-md-6 col-lg-3 col-xl-4 mb-6">
            @include('judger.screening.partials.screening-card', ['screening' => $screening])
          </div>
        @endforeach
      </div>  --}}
      <div class="row">
        <div class="col-12 col-md-12 col-lg-12 col-xl-12 mb-6">
          <div class="card screening-card">
            <div class="card-body">
              <div class="table-responsive">

              </div>
              <table class="table mt-3">
                <thead class="bg-label-light">
                  <tr>
                    <th>{{ __('admin.Program_name') }}</th>
                    <th>{{ __('admin.Step Name') }}</th>
                    <th>{{ __('admin.Startup Name') }}</th>
                    <th>{{ __('admin.Status') }}</th>
                    <th>{{ __('admin.Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach( $companies as $company )
                    <tr>
                      <td>{{ $company->program->name }}</td>
                      <td>{{ $company->step->name }}</td>
                      <td>{{ $company->name }}</td>
                      <td> <i class="{{ $company->icon }}"></i> {{ $company->status }} </td>
                      <td>
                        <a href="{{ route('company.profile_view', ['id' => $company->id]) }}" class="btn btn-sm btn-primary">Details</a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop

@section('pagescripts')
  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      const cards = document.querySelectorAll('.screening-card');

      searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase();
        cards.forEach(card => {
          const title = card.querySelector('.card-title').textContent.toLowerCase();
          const desc = card.querySelector('.card-text').textContent.toLowerCase();
          const status = card.querySelector('.status').textContent.toLowerCase();
          if (title.includes(searchTerm) || desc.includes(searchTerm) || status.includes(searchTerm)) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });

    const statusFilter = document.querySelectorAll('input[name="statusFilter"]');
    statusFilter.forEach(radio => {
      radio.addEventListener('change', function() {
        const selectedStatus = this.value;
        const cards = document.querySelectorAll('.screening-card');
        console.log('selectedStatus:', selectedStatus);
        cards.forEach(card => {
          const status = card.querySelector('.status').getAttribute('data-id').toLowerCase();
          if (selectedStatus === 'all' || status.includes(selectedStatus)) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      $('.table').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "columnDefs": [{
          "orderable": false,
          "targets": [4]
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
@stop