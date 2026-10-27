<div class="col-12">
  @php
    $cards = [
      [ 
        'value' => $mentors_count ?? 0, 
        'label' => __('admin.Mentors'), 
        'icon' => 'users',
        'color' => 'primary', 
        'filter' => '', 
        'href' => '/dashboard/mentors' 
      ],
      [ 
        'value' => $cycle_mentors_count ?? 0, 
        'label' => __('cycles.Mentors_Enrolled_In_Programs'), 
        'icon' => 'user-check',
        'color' => 'secondary', 
        'filter' => '', 
        'href' => '/dashboard/mentors?enrolledInProgramFilter=1' 
      ],
      [ 
        'value' => $total_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_Requests'), 
        'icon' => 'calendar',
        'color' => 'warning', 
        'filter' => 'all', 
        'href' => '/dashboard/mentorship/sessions?show=all' 
      ],
      [ 
        'value' => $mentorships_period ?? '0h 0m', 
        'label' => __('cycles.Mentorship_Sessions_Duration'), 
        'icon' => 'clock',
        'color' => 'secondary', 
        'filter' => 'all', 
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
        'filter' => 'new', 
        'href' => '/dashboard/mentorship/sessions?show=new' 
      ],
      [ 
        'value' => $done_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_Completed_Sessions'), 
        'icon' => 'checkbox',
        'color' => 'primary', 
        'filter' => 'done', 
        'href' => '/dashboard/mentorship/sessions?show=all&done=yes' 
      ],
      [ 
        'value' => $pending_sessions_count ?? 0, 
        'label' => __('cycles.Mentorship_Pending_Sessions'), 
        'icon' => 'hourglass',
        'color' => 'dark', 
        'filter' => 'pending', 
        'href' => '/dashboard/mentorship/sessions?show=all&done=no' 
      ]
    ];

    // Collect unique values for filters
    $unique_cycles = collect();
    $unique_mentors = collect();
    $unique_companies = collect();
    $unique_users = collect();

    foreach ($sessions as $session) {
      if ($session->cycle_id && $cycles->firstWhere('id', $session->cycle_id)) {
        $unique_cycles->push($cycles->firstWhere('id', $session->cycle_id));
      }
      if ($session->mentor) {
        $unique_mentors->push($session->mentor);
      }
      if ($session->user && $session->user->company) {
        $unique_companies->push($session->user->company);
      }
      if ($session->user) {
        $unique_users->push($session->user);
      }
    }

    $unique_cycles = $unique_cycles->unique('id')->sortBy('title');
    $unique_mentors = $unique_mentors->unique('id')->sortBy('name');
    $unique_companies = $unique_companies->unique('id')->sortBy('name_' . $l);
    $unique_users = $unique_users->unique('id')->sortBy('first_name_' . $l);
  @endphp

  <!-- KPI Cards -->
  <div class="row g-6 d-flex justify-content-center mb-8">
    @foreach($cards as $key => $card)
      <div class="col-lg-2 col-sm-6">
        @if($card['filter'] === '')
          {{-- Card with href link --}}
          <a href="{{ $card['href'] }}" class="text-decoration-none">
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
        @else
          {{-- Card with filter functionality --}}
          <div class="card card-border-shadow-{{ $card['color'] }} h-100 cursor-pointer kpi-card" 
               data-filter="{{ $card['filter'] }}" 
               onclick="filterSessionsByKPI('{{ $card['filter'] }}', this)">
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
        @endif
      </div>
    @endforeach
  </div>
</div>

<!-- Mentorship Sessions Table -->
<div class="card border mt-6">
  <!-- Heading & Actions -->
  <div class="card-header d-md-flex justify-content-between border-bottom pb-3 mb-6">
    <h5 class="card-title">{{ __('cycles.Mentorship Sessions') }}</h5>
    <div class="d-flex align-items-center gap-3">
      <div class="btn-group">
        <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="ti ti-file-export me-2"></i>{{ __('settings.Export') }}
        </button>
        <ul class="dropdown-menu">
          @foreach(['csv', 'excel'] as $key => $option)
            <li>
              <a class="dropdown-item text-uppercase" href="javascript:void(0)" onclick="exportSessions('{{$option}}')">
                {{$option}}
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  <div class="card-body">
    <!-- Filters -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <select class="form-select form-select-sm" id="tblSessions_programFilter">
          <option value="">{{ __('cycles.All_Cycles') }}</option>
          @foreach($unique_cycles as $cycle)
            <option value="{{ $cycle->title }}">{{ $cycle->title }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select form-select-sm" id="tblSessions_mentorFilter">
          <option value="">{{ __('admin.allMentors') }}</option>
          @foreach($unique_mentors as $mentor)
            <option value="{{ $mentor->name }}">{{ $mentor->name }}</option>
           @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <select class="form-select form-select-sm" id="tblSessions_startupFilter">
          <option value="">{{ __('cycles.All Startups') }}</option>
          @foreach($unique_companies as $company)
            <option value="{{ $company->{'name_' . $l} }}">{{ $company->{'name_' . $l} }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <select class="form-select form-select-sm" id="tblSessions_userFilter">
          <option value="">{{ __('admin.List All') }}</option>
          @foreach($unique_users as $user)
            <option value="{{ $user->{'first_name_' . $l} }} {{ $user->{'last_name_' . $l} }}">{{ $user->{'first_name_' . $l} }} {{ $user->{'last_name_' . $l} }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-center">
        <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="tblSessions_clearFilters">{{ __('cycles.Clear Filters') }}</button>
      </div>
    </div>
    <!-- Table Data -->
    <div class="table-responsive">
      <table class="table table-hover" id="tblSessions">
        <thead class="table-light">
          <tr>
            <th>{{ __('cycles.Company') }}</th>
            <th>{{ __('cycles.User/Title') }}</th>
            <th>{{ __('cycles.Mentor') }}</th>
            <th>{{ __('cycles.Cycle') }}</th>
            <th>{{ __('cycles.Rate/Comment') }}</th>
            <th>{{ __('cycles.Date/Time') }}</th>
            <th>{{ __('cycles.Approved') }}</th>
            <th>{{ __('cycles.Done') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($sessions as $session)
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <a href="/dashboard/companyProfile/{{ $session->user->company->id ?? '' }}">
                    <img 
                      onerror="this.src='/images/logo-placeholder.png'"
                      src="{{ $session->user->company->logo_url ?? '' }}" 
                      alt="avatar" 
                      class="rounded-circle me-2 border bg-label-light" 
                      height="40" 
                      width="40"
                    />
                  </a>
                  <a href="/dashboard/companyProfile/{{ $session->user->company->id ?? '' }}">
                    {{ $session->user->company->{'name_' . $l} ?? '' }}
                  </a>
                </div>
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <a href="/dashboard/users/{{ $session->user_id }}/edit">
                    <img 
                      onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                      src="{{ $session->user->image_url }}" 
                      alt="avatar" 
                      class="rounded-circle me-2" 
                      height="40" 
                      width="40"
                    />
                  </a>
                  <div>
                    <h6 class="mb-0 text-nowrap">
                      {{ $session->user->{'first_name_' . $l} }} {{ $session->user->{'last_name_' . $l} }}
                    </h6>
                  </div>
                </div>
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <img 
                    onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                    src="{{ $session->mentor->image_url ?? '' }}" 
                    alt="avatar" 
                    class="rounded-circle me-2" 
                    height="40" 
                    width="40"
                  />
                  {{ $session->mentor->name ?? '' }}
                </div>
              </td>
              <td>
                <small class="text-nowrap w-px-150 text-truncate">{{ optional($cycles->firstWhere('id', $session->cycle_id))->title }}</small>
              </td>
              <td>
                <small>{!! $session->rate_comment ? str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>', $session->rating) . '<br>' . $session->rate_comment : $session->comment !!}</small>
              </td>
              <td>
                <small>{{ $session->zoom_date }} ({{ $session->period }}M)</small>
              </td>
              <td>
                @if ($session->approved)
                  @if ($session->approved == 'Yes')
                    <span class="badge bg-label-success">{{ __('admin.Yes') }}</span>
                  @else
                    <span class="badge bg-label-danger">{{ __('admin.No') }}</span>
                  @endif
                @else
                  <span class="badge bg-label-warning">{{ __('admin.Waiting') }}</span>
                @endif
              </td>
              <td>
                <span class="badge bg-label-{{ $session->done ? 'success' : 'danger' }}">
                  {{ $session->done ? __('Yes') : __('No') }}
                </span>
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
    // Initialize sessions data table
    let sessionsTable = $('#tblSessions').DataTable({
      responsive: true,
      pageLength: 10,
      columnDefs: [{
        orderable: false,
        targets: [0, 1, 2]
      }],
      order: [[5, 'desc']], // Order by date/time column
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
        { extend: 'csv', text: '<i class="ti ti-file-text me-1"></i>CSV', className: 'd-none', title: `tblSessions_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] } },
        { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel', className: 'd-none', title: `tblSessions_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] } }
      ]
    });

    // Custom search function for filtering
    $.fn.dataTable.ext.search.push(
      function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tblSessions') {
          return true;
        }
                
        let row = sessionsTable.row(dataIndex).node();
        if (!row) return true;
        
        let cells = $(row).find('td');
        if (cells.length < 8) return true;

        let tblSessionsProgramFilter = $('#tblSessions_programFilter').val();
        let tblSessionsMentorFilter = $('#tblSessions_mentorFilter').val();
        let startupFilter = $('#tblSessions_startupFilter').val();
        let tblSessionsUserFilter = $('#tblSessions_userFilter').val();

        let programMatch = !tblSessionsProgramFilter || $(cells[3]).text().trim() === tblSessionsProgramFilter;
        let mentorMatch = !tblSessionsMentorFilter || $(cells[2]).text().trim().includes(tblSessionsMentorFilter);
        let startupMatch = !startupFilter || $(cells[0]).text().trim().includes(startupFilter);
        let userMatch = !tblSessionsUserFilter || $(cells[1]).text().trim().includes(tblSessionsUserFilter);

        let currentFilter = window.currentSessionFilter || 'all';
        let kpiMatch = true;
        
        if (currentFilter !== 'all' && currentFilter !== '' && currentFilter) {
          let approvedCell = $(cells[6]).html();
          let doneCell = $(cells[7]).html();
          
          switch(currentFilter) {
            case 'approved':
              kpiMatch = approvedCell && approvedCell.includes('bg-label-success');
              break;
            case 'disapproved':
              kpiMatch = approvedCell && approvedCell.includes('bg-label-danger');
              break;
            case 'new':
              kpiMatch = approvedCell && approvedCell.includes('bg-label-warning');
              break;
            case 'done':
              kpiMatch = doneCell && doneCell.includes('bg-label-success');
              break;
            case 'pending':
              kpiMatch = doneCell && doneCell.includes('bg-label-danger') && 
                         approvedCell && approvedCell.includes('bg-label-success');
              break;
          }
        }
        
        return programMatch && mentorMatch && startupMatch && userMatch && kpiMatch;
      }
    );

    // Function to update clear button visibility
    function updateClearButtonVisibility() {
      let hasFilter = $('#tblSessions_programFilter').val() || $('#tblSessions_mentorFilter').val() || $('#tblSessions_startupFilter').val() || $('#tblSessions_userFilter').val() || (window.currentSessionFilter && window.currentSessionFilter !== 'all');
      if (hasFilter) {
        $('#tblSessions_clearFilters').removeClass('d-none');
      } else {
        $('#tblSessions_clearFilters').addClass('d-none');
      }
    }

    // Trigger table redraw on filter change
    $('#tblSessions_programFilter, #tblSessions_mentorFilter, #tblSessions_startupFilter, #tblSessions_userFilter').on('change', function() {
      sessionsTable.draw();
      updateClearButtonVisibility();
    });

    // Clear filters button
    $('#tblSessions_clearFilters').on('click', function() {
      $('#tblSessions_programFilter, #tblSessions_mentorFilter, #tblSessions_startupFilter, #tblSessions_userFilter').val('');
      window.currentSessionFilter = 'all';
      $('.kpi-card').removeClass('active-filter');
      sessionsTable.draw();
      updateClearButtonVisibility();
    });

    // Initial check for clear button visibility
    updateClearButtonVisibility();


    // Global filter function for KPI cards
    window.filterSessionsByKPI = function(filter, element) {
      $('#tblSessions_programFilter, #tblSessions_mentorFilter, #tblSessions_startupFilter, #tblSessions_userFilter').val('');

      // Update current filter
      window.currentSessionFilter = filter;
      
      // Remove active class from all cards
      $('.kpi-card').removeClass('active-filter');
      
      // Add active class to clicked card
      $(element).addClass('active-filter');
      
      // Redraw table with new filter
      sessionsTable.draw();
      
      // Update clear button visibility
      updateClearButtonVisibility();
      
      // Scroll to table
      $('html, body').animate({
        scrollTop: $('#tblSessions').offset().top - 100
      }, 500);
    }

    // Global export sessions function
    window.exportSessions = function (format) {
      if (!sessionsTable) {
        releaseAlert('danger', { message: 'No data available to export' });
        return;
      }
      try {
        sessionsTable.button(`.buttons-${format}`).trigger();
      } catch (error) {
        console.error('Export error:', error);
        releaseAlert('danger', { message: 'Failed to export data. Please try again.' });
      }
    }
  });
</script>
