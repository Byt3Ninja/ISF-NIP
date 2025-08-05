@extends('layouts.hr-admin')

@php
static $winnersData = [
[
'team' => 'Team Alpha',
'project' => 'FinStart',
'score' => 94,
'rank' => 'first',
],
[
'team' => 'Team Green',
'project' => 'GreenTech Revolution',
'score' => 90,
'rank' => 'second',
],
[
'team' => 'Team Nova',
'project' => 'EduNext',
'score' => 87,
'rank' => 'third',
],
[
'team' => 'Team Beta',
'project' => 'HealthAI',
'score' => 83,
'rank' => '',
],
[
'team' => 'Team Spark',
'project' => 'SmartEnergy',
'score' => 81,
'rank' => '',
],
[
'team' => 'Team Sky',
'project' => 'AgriBoost',
'score' => 78,
'rank' => '',
],
];
@endphp

@section('content')
<div class="container-fluid mt-4">
  <div class="row mb-6">
    <div class="col-12">
      <h5 class="card-title text-dark fw-bold">
        🏆 <span class="ms-2">{{ $l == 'en' ? 'Judging Results & Winner Selection' : 'نتائج التحكيم واختيار الفائزين'
          }}</span>
      </h5>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-12">
          <table class="table table-bordered">
            <thead class="">
              <tr>
                <th>{{ __('admin.Team Name') }}</th>
                <th>{{ __('admin.Project Name') }}</th>
                <th>{{ __('admin.Score') }}</th>
                <th>{{ __('admin.Winner Rank') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($winnersData as $winner)
              <tr @if($winner['rank']==='first' ) class="bg-label-warning" @elseif($winner['rank']==='second' )
                class="bg-label-secondary" @elseif($winner['rank']==='third' ) class="bg-label-success" @endif>
                <td class="px-4 py-2">{{ $winner['team'] }}</td>
                <td class="px-4 py-2">{{ $winner['project'] }}</td>
                <td class="px-4 py-2">{{ $winner['score'] }}</td>
                <td class="px-4 py-2">
                  <select onchange="updateWinnerClass(this)" class="border px-2 py-1 rounded">
                    <option value="" @if($winner['rank']=='' ) selected @endif>
                      {{ $l == 'en' ? 'None' : 'لا شيء' }}
                    </option>
                    <option value="first" @if($winner['rank']=='first' ) selected @endif>
                      🥇 {{ $l == 'en' ? 'First Place' : 'المركز الأول' }}
                    </option>
                    <option value="second" @if($winner['rank']=='second' ) selected @endif>
                      🥈 {{ $l == 'en' ? 'Second Place' : 'المركز الثاني' }}
                    </option>
                    <option value="third" @if($winner['rank']=='third' ) selected @endif>
                      🥉 {{ $l == 'en' ? 'Third Place' : 'المركز الثالث' }}
                    </option>
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
        <button class="btn btn-success">✅ {{ $l == 'en' ? 'Publish Winners' : 'نشر الفائزين' }}</button>
        <p class="text-sm text-muted mt-2">
          {{
          $l == 'en'
          ? 'Winners will be announced publicly and notified via email.'
          : 'سيتم الإعلان عن الفائزين علنًا وإخطارهم عبر البريد الإلكتروني.'
          }}
        </p>
      </div>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable) {
      $('.table').DataTable({
        paging: true,
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