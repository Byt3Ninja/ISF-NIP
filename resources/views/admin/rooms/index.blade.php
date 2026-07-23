@extends('layouts.admin')

@section('title', __('Rooms'))
@section('subTitle', __('Rooms List'))

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
    @if(session()->has('msg'))
    <div class="row mb-6">
      <div class="col-md-12">
        <div class="alert alert-success alert-dismissible mb-2" role="alert">
          {{ __(''.session()->get('msg')) }}
          {{ Session::forget('msg') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
    </div>
    @endif
    <div class="content-body">
      <section id="basic-datatable">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header d-flex justify-content-between flex-wrap align-items-center border-bottom">
                <h5 class="card-title mb-3 mb-md-0">{{ __('Rooms') }}</h5>
                <div class="invoice-create-btn d-flex gap-2">
                  <a href="{{ route('rooms.create') }}" class="btn btn-primary glow">
                    <i class="ti ti-plus me-2"></i> {{ __('Add new') }}
                  </a>
                  <a href="{{ route('rooms.create') }}" class="btn btn-success glow">
                    <i class="ti ti-calendar me-2"></i> {{ __('Bookings') }}
                  </a>
                </div>
              </div>
              <div class="card-datatable table-responsive">
                <table class="table zero-configuration" id="rooms-list-datatable" style="font-size: 0.92rem; text-align: center;">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>{{__('Room')}}</th>
                      <th>{{__('Type')}}</th>
                      <th>{{__('Price')}}</th>
                      <th>{{__('Discount')}}</th>
                      <th>{{__('published')}}</th>
                      <th>{{__('User added')}}</th>
                      <th>{{__('Manage images')}}</th>
                      <th>{{__('Delete')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($rooms as $room)
                    <tr>
                      <td><a href="{{route('rooms.edit', ['room' => $room->id])}}">{{$room->id}}</a></td>
                      <td style="font-size: small;"><a href="{{route('rooms.edit', ['room' => $room->id])}}"> {{$room->{'name_'.$l} }}</a></td>
                      <td style="font-size: small;">{{$room->type_trans}}</td>
                      <td style="font-size: small;">{{$room->price}}</td>
                      <td style="font-size: small;">{{$room->discount}}</td>
                      <td style="font-size: small;">{{ __(''.$room->published?'Yes':'No')}}</td>
                      <td style="font-size: small;">{{$room->User->{'first_name_'.$l}.' '.$room->User->{'last_name_'.$l} }}</td>
                      <td style="font-size: small;">
                        @if( $_user->ableTo('delete_user', $permissions) )
                          <a href="{{route('rooms.images_manage', ['id' => $room->id])}}" class="btn btn-outline-warning btn-sm"><i class="bx bx-image"></i></a>
                        @endif
                      </td>
                      <td style="padding: 0px;font-size: small;">
                        @if( $_user->ableTo('delete_user', $permissions) )
                          <button class="btn btn-outline-danger btn-sm delete-room-action" data-id="{{$room->id}}" data-url="{{route('rooms.destroy', ['room' => $room->id])}}"><i class="ti ti-trash text-danger"></i></button>
                        @endif
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
      const isRtl = "{{ $l }}" === "ar";

      // Initialize DataTable
      $('#rooms-list-datatable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
        language: isRtl ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[0, 'desc']],
        columnDefs: [
          {
            targets: [8], // Delete column
            orderable: false,
            searchable: false
          }
        ]
      });

      // Handle delete action
      $(document).on("click", ".delete-room-action", function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var token = '{{ csrf_token() }}';
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
        })
        .then((result) => {
          if (result.value) {
            $.post(url, {'_method': 'DELETE' , '_token': token},
              function(response) {
                Swal.fire({
                  icon: 'success',
                  title: "{{ __('admin.Deleted') }}!",
                  html: "{{ __('admin.FileDeleted') }}.",
                  showCancelButton: false,
                  showConfirmButton: true,
                  confirmButtonText: "{{ __('admin.OK') }}",
                  customClass: {
                      confirmButton: 'btn btn-primary',
                  },
                  buttonsStyling: false,
                });
                // Remove the row from DataTable if still on same page
                $('#rooms-list-datatable').DataTable().row($(e.target).closest('tr')).remove().draw();
              }
            ).fail(function(error) {
              releaseFailureAlert({
                title: "{{ __('admin.Error') }}!",
                message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
              });
            });
          }
        });
      });
    });
</script>
@stop

