@extends('layouts.admin')

@section('content')
  <div class="">
    <div class="">
      <!-- Alert -->
      @if (session()->has('message'))
        <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <!--/ Alert -->

      <!-- Tags Table -->
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between border-bottom">
          <h5 class="card-title mb-0 text-capitalize">{{ __('admin.all Tags') }}</h5>
          @if( $_user->ableToAny(['Tags.create', 'Tags.store'], $permissions) )
            <button class="btn btn-primary add-tag-btn" data-bs-toggle="modal" data-bs-target="#TagModal">
              <i class="ti ti-plus me-2"></i>
              {{ __('admin.Add_New') }}
            </button>
          @endif
        </div>

        <div class="card-datatable table-responsive datatable-basic">
          <table class="table" id="tags-table">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>{{ __('admin.Name') }}</th>
                <th>{{ __('cycles.Created at') }}</th>
                <th>{{ __('admin.updated_at') }}</th>
                @if( $_user->ableToAny(['Tags.edit', 'Tags.update', 'Tags.destroy'], $permissions) )
                  <th>{{ __('admin.Actions') }}</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @php $c=1; @endphp
              @foreach ($tags as $tag)
                <tr>
                  <td>{{$c++}}</td>
                  <td>{{$tag->name}}</td>
                  <td>{{$tag->created_at}}</td>
                  <td>{{$tag->updated_at}}</td>
                  @if( $_user->ableToAny(['Tags.edit', 'Tags.update', 'Tags.destroy'], $permissions) )
                  <td>
                    @if( $_user->ableToAny(['Tags.edit', 'Tags.update'], $permissions) )
                      <button class="btn btn-icon btn-label-info edit-tag-btn" data-bs-toggle="modal" data-bs-target="#TagModal" data-tag-id="{{ $tag->id }}" data-tag-name="{{ $tag->name }}">
                        <i class="ti ti-edit"></i>
                      </button>
                    @endif
                    @if( $_user->ableTo('Tags.destroy', $permissions) )
                      <form id="delete-tag-form" action="{{ route('Tags.destroy', $tag->id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-icon btn-label-danger" type="submit">
                          <i class="ti ti-trash"></i>
                        </button>
                      </form>
                    @endif
                  </td>
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <!--/ Tags Table -->
      
      <!-- Tag Modal -->
      <div class="modal fade" id="TagModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title mb-0" id="tagModalTitle">{{__('settings.add_new_tag')}}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="tagForm" method="POST" action="{{route('Tags.store')}}" class="pt-0">
                @csrf
                <div class="form-group"> 
                  <label for="tags_name" class="form-label">{{__('settings.tag_name')}}</label> 
                  <input
                    id="tags_name"
                    class="form-control"
                    type="text"
                    name="tags_name"
                    autocomplete="off"
                    placeholder="{{__('settings.tag_name')}}"
                    required
                  />
                </div>
                <div class="modal-footer p-0 mt-8">
                  <button type="submit" class="btn btn-primary me-sm-4 data-submit" id="tagSubmitBtn">
                    {{__('admin.submit')}}
                  </button>
                  <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
                    {{__('admin.Cancel')}}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!--/ Tag Modal -->
    </div>
  </div>
@stop

@section('pagescripts')
  <script>
    $(document).ready(function() {
      $('#tags-table').DataTable({
        responsive: true,
        @if ($l == 'ar')
        "language": {
          "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
          "sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
          "sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
          "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
          "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
          "sInfoPostFix":  "", "sSearch":       "ابحث:", "sUrl":          "",
          "oPaginate": {"sFirst":    "الأول", "sPrevious": "السابق", "sNext":     "التالي", "sLast":     "الأخير"},
          "oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}
        }
        @endif
      });
    });

    $('#delete-tag-form').on('submit', function(e) {
      e.preventDefault();
      releaseAlert('warning', {
        title: "{{__('admin.SureDelete')}}",
        message: "{{__('admin.Recover')}}",
        confirmText: "{{ __('admin.DYes') }}",
        cancelText: "{{ __('admin.No') }}",
        confirmColor: 'danger'
      }).then((ok) => {
        if (ok) {
          this.submit();
        }
      });
    });

    // Handle Add Tag button click
    $('.add-tag-btn').on('click', function() {
      $('#tagModalTitle').text("{{__('settings.add_new_tag')}}");
      $('#tagForm').attr('action', "{{route('Tags.store')}}");
      $('#tagForm').find('input[name="_method"]').remove(); // Remove PUT method if exists
      $('#tags_name').val('');
      $('#tagSubmitBtn').text("{{__('admin.submit')}}");
    });

    // Handle Edit Tag button click
    $('.edit-tag-btn').on('click', function() {
      var tagId = $(this).data('tag-id');
      var tagName = $(this).data('tag-name');

      $('#tagModalTitle').text("{{__('settings.edit_tag')}}");
      $('#tagForm').attr('action', "{{url('/dashboard/Tags')}}/" + tagId);
      $('#tagForm').append('<input type="hidden" name="_method" value="PUT">');
      $('#tags_name').val(tagName);
      $('#tagSubmitBtn').text("{{__('admin.submit')}}");
    });

    // Reset modal when closed
    $('#TagModal').on('hidden.bs.modal', function() {
      $('#tagForm')[0].reset();
      $('#tagForm').find('input[name="_method"]').remove();
    });
  </script>  
@stop
