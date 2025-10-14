<div class="col-12">
  @php
    $cards = [
      [ 
        'value' => $mentors_count ?? 0, 
        'label' => __('admin.Mentors'), 
        'icon' => 'users',
        'color' => 'primary', 
        'filter' => 'Mentor', 
        'href' => '/dashboard/show/mentors' 
      ],
      [ 
        'value' => $cycle_mentors_count ?? 0, 
        'label' => __('cycles.Mentors_Enrolled_In_Programs'), 
        'icon' => 'user-check',
        'color' => 'secondary', 
        'filter' => '', 
        'href' => '/dashboard/show/mentors?enrolled=true' 
      ],
      [ 
        'value' => $total_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_Requests'), 
        'icon' => 'calendar',
        'color' => 'warning', 
        'filter' => '', 
        'href' => '/dashboard/mentorship/sessions?show=all' 
      ],
      [ 
        'value' => $mentorships_period ?? '0h 0m', 
        'label' => __('cycles.Mentorship_Sessions_Duration'), 
        'icon' => 'clock',
        'color' => 'secondary', 
        'filter' => '', 
        'href' => '/dashboard/mentorship/sessions?show=all' 
      ],
      [ 
        'value' => $approved_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_Approved_Requests'), 
        'icon' => 'check',
        'color' => 'success', 
        'filter' => 'approved', 
        'href' => '/dashboard/mentorship/sessions?show=approved' 
      ],
      [ 
        'value' => $disapproved_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_Disapproved_Requests'), 
        'icon' => 'circle-x',
        'color' => 'danger', 
        'filter' => 'disapproved', 
        'href' => '/dashboard/mentorship/sessions?show=disapproved' 
      ],
      [ 
        'value' => $new_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_New_Requests'), 
        'icon' => 'sparkles',
        'color' => 'info', 
        'filter' => '', 
        'href' => '/dashboard/mentorship/sessions?show=new' 
      ],
      [ 
        'value' => $done_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_Completed_Sessions'), 
        'icon' => 'checkbox',
        'color' => 'primary', 
        'filter' => '', 
        'href' => '/dashboard/mentorship/sessions?show=all&done=yes' 
      ],
      [ 
        'value' => $pending_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_Pending_Sessions'), 
        'icon' => 'hourglass',
        'color' => 'dark', 
        'filter' => '', 
        'href' => '/dashboard/mentorship/sessions?show=all&done=no' 
      ]
    ];
  @endphp

  <!-- KPI Cards -->
  <div class="row g-6 d-flex justify-content-center mb-8">
    @foreach($cards as $key => $card)
      <div class="col-lg-2 col-sm-6">
        <a href="{{ $card['href'] }}" class="col-lg-2 col-sm-6">
          <div class="card card-border-shadow-{{ $card['color'] }} h-100 cursor-pointer">
            <div class="card-body py-3" style="display: grid; place-items: center;">
              <div class="d-flex align-items-center mb-2">
                <div class="avatar me-4">
                  <span class="avatar-initial rounded bg-label-{{ $card['color'] }}">
                    <i class="ti ti-{{ $card['icon'] }} ti-20px"></i>
                  </span>
                </div>
                <h5 class="mb-0">{{ $card['value'] }}</h5>
              </div>
              <small class="text-center text-capitalize">{{ $card['label'] }}</small>
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>
</div>

<!-- Mentors Table -->
<div class="card border">
  <!-- Heading & Actions -->
  <div class="card-header d-md-flex justify-content-between border-bottom pb-3 mb-6">
    <h5 class="card-title">{{ __('cycles.Mentors_Enrolled_In_Programs') }}</h5>
    <div class="d-flex align-items-center gap-3">
      <div class="btn-group">
        <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="ti ti-file-export me-2"></i>{{ __('settings.Export') }}
        </button>
        <ul class="dropdown-menu">
          @foreach(['csv', 'excel'] as $key => $option)
            <li>
              <a class="dropdown-item text-uppercase" href="javascript:void(0)" onclick="exportMentors('{{$option}}')">
                {{$option}}
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  <div class="card-body">
    <!-- Table Data -->
    <div class="table-responsive">
      <table class="table table-hover" id="tblMentors">
        <thead class="table-light">
          <tr>
            <th>{{ __('cycles.Mentor') }}</th>
            <th>{{ __('cycles.Rate') }}</th>
            <th>{{ __('admin.Mentorship requests number') }}</th>
            <th>{{ __('admin.Mentorship requests Duration') }}</th>
            <th>{{ __('admin.Mentorship done Request') }}</th>
            <th>{{ __('admin.Mentorship done requests Duration') }}</th>
            <th>{{ __('admin.Company Attendance Rate') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($cycle_mentors as $mentor)
            <tr>
              <td>
                <a href="/dashboard/UserProfile/{{ $mentor->id }}" class="d-flex align-items-center">
                  <img 
                    src="{{ $mentor->image_url }}" 
                    onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                    alt="avatar" 
                    class="rounded-circle me-2"
                    height="40" width="40" 
                  />
                  <h6 class="mb-0 text-nowrap">{{ $mentor->name }}</h6>
                </a>
              </td>
              <td>
                <small>{!! str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>', $mentor->mrating()) !!}</small>
              </td>
              <td>
                {{ $mentor->mentorSessions()->where('approved', 'Yes')->count() }}
              </td>
              <td>
                {{ $mentor->mentorSessions()->where('approved', 'Yes')->sum('period') / 60 }}
              </td>
              <td>
                {{ $mentor->mentorSessions()->whereDate('zoom_date', '<', now()->format('Y-m-d'))->where('approved', 'Yes')->count() }}
              </td>
              <td>
                {{ $mentor->mentorSessions()->whereDate('zoom_date', '<', now()->format('Y-m-d'))->where('approved', 'Yes')->sum('period') / 60 }}
              </td>
              <td>
                @if ($mentor->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->count())
                  {{ (int) (($mentor->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->sum('done') / $mentor->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->count()) * 100) }}%
                @else
                  0%
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {    
    // Initialize data table
    let mentorsTable = $('#tblMentors').DataTable({
      pageLength: 10,
      columnDefs: [{
        orderable: false,
        targets: [0]
      }],
      order: [[0, 'asc']],
      language: {
        @if ($l == 'ar')
          sEmptyTable: "ليست هناك بيانات متاحة في الجدول",
          sLoadingRecords: "جارٍ التحميل...",
          sProcessing: "جارٍ التحميل...",
          sLengthMenu: "أظهر _MENU_ مدخلات",
          sZeroRecords: "لم يعثر على أية سجلات",
          sInfo: "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
          sInfoEmpty: "يعرض 0 إلى 0 من أصل 0 سجل",
          sInfoFiltered: "(منتقاة من مجموع _MAX_ مُدخل)",
          sInfoPostFix: "",
          sSearch: "ابحث:",
          sUrl: "",
          oPaginate: {
            sFirst: "الأول",
            sPrevious: "السابق",
            sNext: "التالي",
            sLast: "الأخير"
          },
          oAria: {
            sSortAscending: ": تفعيل لترتيب العمود تصاعدياً",
            sSortDescending: ": تفعيل لترتيب العمود تنازلياً"
          }
        @endif
      },
      buttons: [
        { extend: 'csv', text: '<i class="ti ti-file-text me-1"></i>CSV', className: 'd-none', title: `tblMentors_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } },
        { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel', className: 'd-none', title: `tblMentors_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } }
      ]
    });

    // Global export mentors function
    window.exportMentors = function (format) {
      if (!mentorsTable) {
        releaseFailureAlert({ message: 'No data available to export' });
        return;
      }
      try {
        mentorsTable.button(`.buttons-${format}`).trigger();
      } catch (error) {
        console.error('Export error:', error);
        releaseFailureAlert({ message: 'Failed to export data. Please try again.' });
      }
    }
  });
</script>