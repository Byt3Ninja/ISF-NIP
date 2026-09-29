@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/css/hackathon.css" />
@stop
@section('content')
<div class="">
  <div class="">
    <div class="text-center hackathon-banner">
      <div class="banner-content">
        <div class="hackathon-status-badge"> {{ $hackathon->status }} </div>
        <h1 class="display-5 fw-bold" id="hackathon-main-title"> {{ $hackathon->title }} </h1>
        <p class="lead"> {{ $hackathon->description }} </p>
        <p class="lead" id="hackathon-dates-location">
          <i class="ti ti-calendar-event me-2"></i> {{ $hackathon->duration }}
          <span class="mx-3">|</span>
          <i class="ti ti-map-pin me-2"></i>
          {{ $hackathon->organization->name }}
        </p>
      </div>
    </div>
    <div class="row mb-6">
      <div class="col-12">
        <h5 class="card-title text-dark fw-bold row">
          <span class="ps-4 col-8"> 🏆 {{ $l == 'en' ? 'Judging Results & Winner Selection' : 'نتائج التحكيم واختيار الفائزين' }}</span>
          <span class="col-4 text-end pe-5">
            @if( $hackathon->announcement == true )
              <i class="fas fa-fw fa-sm fa-circle text-success"></i> {{ $l == 'en' ? 'Results Announced' : 'تم الاعلان عن نتايج التحكيم' }}
            @endif
            @if( $hackathon->announcement == false )
              <i class="fas fa-fw fa-sm fa-circle text-danger"></i> {{ $l == 'en' ? 'Results not Announced' : 'لم يتم الاعلان عن نتايج التحكيم' }}
            @endif
          </span>  
        </h5>
      </div>
    </div>
    <form action="/dashboard/hackathon/dashboard/winners/{{ $hackathon->id }}" method="POST">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-10 col-lg-10 col-sm-12">
              <div class="row">
                <div class="col-6">
									<div class="form-floating">
										<select class="form-select" name="announcementStatus" id="announcementStatus" aria-label="Announcement Status">
											<option value="0" {{ $hackathon->announcement == false ? 'selected' : '' }}>{{ $l == 'en' ? 'Not Announced' : 'لم يتم الاعلان' }}</option>
											<option value="1" {{ $hackathon->announcement == true ? 'selected' : '' }}>{{ $l == 'en' ? 'Announced' : 'تم الاعلان' }}</option>
										</select>
										<label for="announcementStatus">{{ $l == 'en' ? 'Announcement Status' : 'حالة الاعلان' }}</label>
									</div>
                </div>
                <div class="col-6">
									<div class="form-floating">
										<select class="form-select" name="announcementVisibility" id="announcementVisibility" aria-label="Announcement Visibility">
											<option value="public" {{ $hackathon->visibility == 'public' ? 'selected' : '' }}>{{ $l == 'en' ? 'Show results to public' : 'اظهار نتايج للجميع' }}</option>
											<option value="private" {{ $hackathon->visibility == 'private' ? 'selected' : '' }}>{{ $l == 'en' ? 'Restrict results to logged-in users' : 'عرض النتايج للمستخدمين المسجلين' }}</option>
										</select>
										<label for="announcementVisibility">{{ $l == 'en' ? 'Announcement Visibility' : 'رؤية الاعلان' }}</label>
									</div>
                </div>
              </div>
            </div>
            <div class="col-md-2 col-lg-2 col-sm-12">
              <div class="dropdown">
                <a class="btn btn-primary dropdown-toggle w-100" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ $l == 'en' ? 'Export Results' : 'تصدير نتايج التحكيم' }}</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" target="_blank" href='{{ URL::to("dashboard/hackathon/dashboard/winners/$hackathon->id/export?type=excel") }}'><i class="fas fa-fw fa-sm fa-file-excel me-2"></i> Excel</a></li>
                  <li><a class="dropdown-item" target="_blank" href='{{ URL::to("dashboard/hackathon/dashboard/winners/$hackathon->id/export?type=csv") }}'><i class="fas fa-fw fa-sm fa-file-csv me-2"></i> CSV</a></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <table class="table table-bordered">
                <thead class="">
                  <tr>
                    <th>{{ __('admin.Project Name') }}</th>
                    <th>{{ __('admin.team_leader') }}</th>
                    <th>{{ __('admin.Email') }}</th>
                    {{--  <th>{{ __('admin.Team Name') }}</th>  --}}
                    <th>{{ __('admin.Submission Step') }}</th>
                    <th>{{ __('admin.Judges') }}</th>
                    <th>{{ __('admin.Score') }}</th>
                    <th>{{ __('admin.Winner Rank') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($winnersData as $winner)
                  <tr @if($winner['rank']==='first' ) class="bg-label-warning" @elseif($winner['rank']==='second' )
                    class="bg-label-secondary" @elseif($winner['rank']==='third' ) class="bg-label-success" @endif>
                    <td class="px-4 py-2">{{ $winner['project'] }}</td>
                    <td class="px-4 py-2">{{ $winner['leader']?->name }}</td>
                    <td class="px-4 py-2">{{ $winner['leader']?->email }}</td>
                    {{--  <td class="px-4 py-2">{{ $winner['team'] }}</td>  --}}
                    <td class="px-4 py-2">{{ $winner['step'] }}</td>
                    <td class="px-4 py-2">
                      @forelse( $winner['judges'] as $judge )
                        <div>( {{ $judge->score }} ) {{ $judge->name }}</div>
                      @empty
                        <span class="badge bg-label-secondary">{{ __('admin.awaiting_evaluation') }}</span>
                      @endforelse
                    </td>
                    <td class="px-4 py-2">
                      @if( count($winner['judges']) > 0 )
                        {{ $winner['score'] }}
                      @else
                        <span class="text-muted">&mdash;</span>
                      @endif
                    </td>
                    <td class="px-4 py-2">
                      <select name="winners[{{ $winner['id'] }}]" onchange="updateWinnerClass(this)" class="border px-2 py-1 rounded">
                        <option value="" {{ $winner['rank'] == '' ? 'selected' : '' }}> {{ $l == 'en' ? 'None' : 'لا شيء' }} </option>
                        <option value="selected" {{ $winner['rank'] == 'selected' ? 'selected' : '' }}> {{ $l == 'en' ? 'Selected' : 'تم الاختيار' }} </option>
                        <option value="not_selected" {{ $winner['rank'] == 'not_selected' ? 'selected' : '' }}> {{ $l == 'en' ? 'Not Selected' : 'لم يتم الاختيار' }} </option>
                      </select>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="card-footer border">
          <div class="text-center mt-6">
            @csrf
            <button type="submit" class="btn btn-success">{{ $l == 'en' ? 'Save Data' : 'حفظ البيانات' }}</button>
            <p class="text-sm text-muted mt-2">
              {{ $l == 'en' ? 'Winners will be announced publicly and notified via email.' : 'سيتم الإعلان عن الفائزين علنًا وإخطارهم عبر البريد الإلكتروني.' }}
            </p>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable) {
      $('.table').DataTable({
        paging: false,
        searching: true,
        ordering: true,
        language: {
          @if($l == 'ar')
          "search": "{{ __('admin.Search') }}:",
          "lengthMenu": "{{ __('admin.Show') }} _MENU_ {{ __('admin.Entries') }}",
          "info": "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.Of') }} _TOTAL_ {{ __('admin.Entries') }}",
          "infoEmpty": "{{ __('admin.no_entries_available') }}",
          "infoFiltered": "({{ __('admin.filtered from') }} _MAX_ {{ __('admin.total entries') }})",
          "paginate": {
            "first": "{{ __('admin.First') }}",
            "last": "{{ __('admin.Last') }}",
            "next": "{{ __('admin.Next') }}",
            "previous": "{{ __('admin.Previous') }}"
          },
          "emptyTable": "{{ __('admin.No_data_available_in_table') }}",
          "zeroRecords": "{{ __('admin.No_matching_records_found') }}"
          @endif
        },
      });
    }
  });
</script>
<script>
  function updateWinnerClass(selectElement) {
      const row = selectElement.closest("tr");
      row.classList.remove("bg-label-warning", "bg-label-secondary", "bg-label-success");

      switch (selectElement.value) {
        case "first":
          row.classList.add("bg-label-warning");
          break;
        case "second":
          row.classList.add("bg-label-secondary");
          break;
        case "third":
          row.classList.add("bg-label-success");
          break;
      }
    }

    // // Pre-mark on load
    // window.onload = function () {
    //   document.querySelectorAll("select").forEach(select => {
    //     updateWinnerClass(select);
    //   });
    // };
</script>
@stop