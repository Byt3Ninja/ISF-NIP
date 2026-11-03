@extends('layouts.admin')

@section('style')
<!-- Custom CSS (Combined & Refined) -->
<style>
  .chart-container {
    position: relative;
    min-height: 280px;
    width: 100%;
    margin-top: 1rem;
  }

  canvas {
    max-width: 100%;
  }

  .export-buttons {
    margin-bottom: 1rem;
    text-align: left;
  }

  /* Align left in LTR context */
  .export-buttons .btn {
    font-size: 0.85rem;
  }

  html[dir="rtl"] .export-buttons {
    text-align: left;
  }

  /* Override for RTL */

  /* --- Report Specific Adjustments (Example) --- */
  /* Evaluator Status Card (Final Eval Report) */
  .evaluator-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 0.5rem;
    background-color: #f8f9fa;
  }

  .evaluator-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #adb5bd;
    flex-shrink: 0;
  }

  .evaluator-info {
    flex-grow: 1;
  }

  .evaluator-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
    font-size: 1rem;
  }

  .evaluator-status {
    font-size: 0.85rem;
    color: #6c757d;
  }

  .evaluator-status .text-success,
  .evaluator-status .text-danger {
    font-weight: bold;
  }

  .evaluator-progress .progress {
    height: 6px;
    margin-top: 5px;
  }
</style>
@stop

@php
  $dashboardTabs = collect([
    ['summary-tab', '#summary-tab-pane', 'fas fa-file-alt', __('admin.Program Summary'), false],
    ['performance-tab', '#performance-tab-pane', 'fas fa-chart-line', __('admin.Startup Performance'), false],
    ['mentorship-tab', '#mentorship-tab-pane', 'fas fa-people-arrows', __('admin.Mentorship & Advisory'), false],
    ['workshops-tab', '#workshops-tab-pane', 'fas fa-person-chalkboard', __('admin.Workshops'), false],
    ['final-eval-tab', '#final-eval-tab-pane', 'fas fa-trophy', __('admin.Final Evaluation'), false],
  ])->map(function ($tab) {
    return [
      'id'     => $tab[0],
      'target' => $tab[1],
      'icon'   => $tab[2],
      'label'  => $tab[3],
      'active' => $tab[4],
    ];
  });

  // Get array of sector company counts as strings for charting, filtering out null/undefined and < 1
  $sectorCounts = array_values(
    $sectorsData->filter(function($sector) {
      return !is_null($sector->companies_count) && $sector->companies_count >= 1;
    })->map(function($sector) {
      return (string) $sector->companies_count;
    })->toArray()
  );

  $sectorLabels = array_values(
    $sectorsData->filter(function($sector) {
      $name = app()->getLocale() == 'en' ? $sector->name_en : $sector->name_ar;
      return !is_null($name) && $sector->companies_count >= 1;
    })->map(function($sector) {
      return (string) (app()->getLocale() == 'en' ? $sector->name_en : $sector->name_ar);
    })->toArray()
  );
  @endphp

@section('content')

<div class="">
  <div class="">

    <div class="d-flex align-items-center mb-4">
      <i class="ti ti-dashboard text-primary fs-3 me-2 d-none d-md-inline"></i>
      <h4 class="fw-bold mb-0">
        {{ __('admin.Program Report') }} - <br class="d-md-none" /> {{ $cycle->title }}
      </h4>
    </div>

    <div class="card card-body">
      <!-- Tab Navigation -->
      <ul class="nav nav-tabs border-bottom mb-8 pb-4" id="mainDashboardTabs" role="tablist">
        @foreach($dashboardTabs as $tab)
          <li class="nav-item" role="presentation">
            <button 
                class="nav-link {{ $tab['active'] ? 'active' : '' }}"
                id="{{ $tab['id'] }}"
                data-bs-toggle="tab"
                data-bs-target="{{ $tab['target'] }}"
                type="button"
                role="tab"
                aria-controls="{{ ltrim($tab['target'], '#') }}"
                aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
            >
                <i class="{{ $tab['icon'] }} me-2"></i> {{ $tab['label'] }}
            </button>
          </li>
        @endforeach
      </ul>


      <!-- Tab Content -->
      <div class="tab-content p-0" id="mainDashboardTabsContent">
        @foreach($dashboardTabs as $tab)
          <div class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}" id="{{ ltrim($tab['target'], '#') }}" role="tabpanel" aria-labelledby="{{ $tab['id'] }}">
            <!-- Tab content goes here -->
            @if($tab['id'] === 'summary-tab')
              @include('admin.programs.partials._program_summary_tab')
            @elseif($tab['id'] === 'performance-tab')
              @include('admin.programs.partials._startup_performance_tab')
            @elseif($tab['id'] === 'mentorship-tab')
              @include('admin.programs.partials._mentorship_advisory_tab')
            @elseif($tab['id'] === 'workshops-tab')
              @include('admin.programs.partials._workshops_tab')
            @elseif($tab['id'] === 'final-eval-tab')
              @include('admin.programs.partials._final_evaluation_tab')
            @endif
          </div>
        @endforeach
      </div>
      <!-- /tab-content -->
    </div>
  </div>
  <!-- /container-fluid -->
@stop

@section('scripts')
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stop


@section('pagescripts')
  <!-- Combined & Organized JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const tabKey = 'mainDashboardActiveTab';
      const tabEl = document.getElementById('mainDashboardTabs');

      // Get stored active tab ID or default to first tab's ID
      let activeTabId = localStorage.getItem(tabKey);
      if (!activeTabId && tabEl) {
        const firstTabBtn = tabEl.querySelector('button[data-bs-toggle="tab"]');
        if (firstTabBtn) {
            activeTabId = firstTabBtn.id;
        }
      }

      // Show the stored (or default) active tab
      if (activeTabId) {
        const tabTriggerEl = document.querySelector(`#${activeTabId}`);
        if (tabTriggerEl) {
            const tab = new bootstrap.Tab(tabTriggerEl);
            tab.show();
        }
      }

      // Save active tab ID on tab change
      if (tabEl) {
        tabEl.querySelectorAll('button[data-bs-toggle="tab"]').forEach(button => {
            button.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem(tabKey, event.target.id);
            });
        });
      }
    });
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const currentLanguage = document.documentElement.lang || 'en';
      const ctx = document.getElementById('summaryStatusDistributionChart').getContext('2d');
      const existingChart = Chart.getChart('summaryStatusDistributionChart');
      if (existingChart) {
          existingChart.destroy();
      }
      const chart = new Chart(ctx, {
        type: 'doughnut', 
        data: {
          labels: currentLanguage === 'ar' ? ['أعلى التقييمات', 'أعلى الأداء', 'أعلى التحسيسات', 'لم يتم التقييم'] : ['Highest Rated', 'Good Performance', 'Needs Improvement', 'Not Evaluated'],
          datasets: [{
            label: 'Status Distribution',
            data: @json(array_values($statusDistribution)),
            backgroundColor: [
                '#28A745', // Highest Rated
                '#39DA8A', // Good Performance
                '#DC3545', // Needs Improvement
                '#616060', // Not Evaluated
            ],
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'right',
            },
          },
        }
      });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      let summaryStatusChart = null;
            const chartFont = { family: 'Cairo' };
            const chartColors = ['#0d6efd', '#198754', '#ffc107', '#6f42c1', '#fd7e14', '#dc3545', '#0dcaf0'];
            const chartBorderColors = chartColors.map(color => color.replace('0.8', '1'));

             // --- Helper function to get data from a table within a specific pane ---
             function getDataFromTable(paneSelector, rowMapper) {
                const tableRows = document.querySelectorAll(`${paneSelector} .table tbody tr`);
                return Array.from(tableRows).map(rowMapper).filter(item => item !== null);
             }

            // ==============================================
            // 1. Startup Performance Report Logic
            // ==============================================
            function setupPerformanceReport() {
                const performanceData = getDataFromTable('#performance-tab-pane', row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length < 6) return null;
                    const progressText = cells[4].querySelector('.progress-bar') ? cells[4].querySelector('.progress-bar').textContent : '0%';
                    return {
                        name: cells[0].textContent.trim(), sector: cells[1].textContent.trim(), stage: cells[2].textContent.trim(),
                        rating: parseFloat(cells[3].textContent.split('/')[0]), progress: parseInt(progressText.replace('%', '')), notes: cells[5].textContent.trim()
                    };
                });

                // Chart 1: Sector Distribution (Chart at the start, legend at the end)
                const ctxPerfSector = document.getElementById('perfSectorDistributionChart')?.getContext('2d');
                if(ctxPerfSector) {
                  // Use actual startup count (not sum of slices, since a company can belong to multiple sectors)
                  const totalStartups = @json($totalStartups);

                  // Custom plugin to show total in center
                  const centerTextPlugin = {
                  id: 'centerText',
                  afterDraw(chart) {
                    const {ctx, chartArea: {left, right, top, bottom, width, height}} = chart;
                    ctx.save();
                    ctx.font = 'bold 1.2rem Cairo, Arial, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#212529';
                    const centerX = left + width / 2;
                    const centerY = top + height / 2;
                    ctx.fillText(totalStartups, centerX, centerY - 8);
                    ctx.font = '0.9rem Cairo, Arial, sans-serif';
                    ctx.fillStyle = '#6c757d';
                    const label = document.documentElement.lang === 'ar' ? 'إجمالي الشركات' : 'Total Startups';
                    ctx.fillText(label, centerX, centerY + 14);
                    ctx.restore();
                  }
                  };

                  new Chart(ctxPerfSector, {
                  type: 'doughnut',
                  data: {
                    labels: @json($sectorLabels),
                    datasets: [{
                    label: '{{ __('admin.company_counts') }}',
                    data: @json($sectorCounts),
                    backgroundColor: chartColors,
                    borderColor: chartBorderColors,
                    borderWidth: 1
                    }]
                  },
                  options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                    legend: {
                      position: 'right', // Move legend to the end (right)
                      labels: { font: chartFont }
                    },
                    tooltip: {
                      bodyFont: chartFont,
                      titleFont: chartFont
                    }
                    }
                  },
                  plugins: [centerTextPlugin]
                  });
                }

                // Chart 2: Progress by Stage
                const stages = ['الفكرة', 'النموذج الأولي', 'السوق', 'النمو', 'التوسع'];
                const stageAvgProgress = stages.map(stage => {
                    const startupsInStage = performanceData.filter(s => s.stage === stage);
                    if (startupsInStage.length === 0) return 0;
                    return Math.round(startupsInStage.reduce((sum, s) => sum + s.progress, 0) / startupsInStage.length);
                });
                const activeStages = stages.filter((stage, index) => stageAvgProgress[index] > 0 || performanceData.some(s => s.stage === stage));
                const activeStageAvgProgress = stageAvgProgress.filter((avg, index) => avg > 0 || performanceData.some(s => s.stage === stages[index]));
                const ctxPerfProgress = document.getElementById('perfProgressByStageChart')?.getContext('2d');
                if(ctxPerfProgress) {
                     new Chart(ctxPerfProgress, {
                        type: 'bar', data: { labels: activeStages, datasets: [{ label: 'متوسط التقدم (%)', data: activeStageAvgProgress, backgroundColor: chartColors, borderColor: chartBorderColors, borderWidth: 1, barThickness: 40 }] },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100, ticks: { font: chartFont, callback: v => v + '%' } }, x: { ticks: { font: chartFont } } }, plugins: { legend: { display: false }, tooltip: { bodyFont: chartFont, titleFont: chartFont, callbacks: { label: c => c.dataset.label + ': ' + c.parsed.y + '%' } } } }
                    });
                }

                // Filter Simulation
                // document.querySelector('.performance-filter-form')?.addEventListener('submit', e => { e.preventDefault(); console.log('Performance Filter Submitted'); alert('محاكاة: تم تطبيق فلتر الأداء!'); });
            }

            // ==============================================
            // 2. Mentorship Report Logic
            // ==============================================
      function setupMentorshipReport() {
              const mentorNames = @json($mentorNamesChart1);
              const mentorSessions = @json($mentorSessions); 
              const mentorAvgRatings = @json($mentorAvgRatings);

              const mentorSessionsArray = Object.values(mentorSessions);
              const mentorAvgRatingsArray = Object.values(mentorAvgRatings);
              const mentorRatingLabels = Object.keys(mentorAvgRatings);

       const chartColors = [
           '#FF5733', '#33FF57', '#3357FF', '#FF33A6', '#F5E050',
           '#50F5E0', '#E050F5', '#A6FF33', '#33FFF5', '#F55033'
        ].slice(0, mentorNames.length);

    // Chart 1: Top 5 Mentors
    const ctx1 = document.getElementById('mentorSessionsChart');
    new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: mentorNames,
        datasets: [{
          label: '{{ __('admin.Number_of_sessions') }}',
          data: mentorSessionsArray,
          backgroundColor: chartColors,
          borderColor: chartColors,
          borderWidth: 1,
          barThickness: 40 // Fixed bar width
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
            }
          },
          x: {
            title: {
              display: true,
            }
          }
        },
        plugins: {
          title: {
            display: true,
            font: {
              size: 16
            }
          },
          legend: {
            display: false
          }
        }
      }
    });

    // Chart 2: Average Ratings Per Mentor
    const ctx2 = document.getElementById('mentorRatingChart');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: mentorRatingLabels,
            datasets: [{
                data: mentorAvgRatingsArray,
                backgroundColor: chartColors,
                borderColor: chartColors,
                borderWidth: 1,
                barThickness: 40 // Fixed bar width
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    title: {
                        display: true,
                    }
                },
                x: {
                    title: {
                        display: true,
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    font: {
                        size: 16
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });
}
          // ==============================================
            // 3. Workshops Report Logic
          // ==============================================
             function setupWorkshopsReport() {
               const zoomWorkshops = @json($zoomWorkshops);
               const inPersonWorkshops = @json($inPersonWorkshops);
               const uploadedMaterials = @json($uploadedMaterials); 

            // Chart 1: Workshop Type Distribution
              const workshopTypeCounts = {
                'sessions': zoomWorkshops,
                'offline': inPersonWorkshops,
                'online': uploadedMaterials
            };

      const ctxWsType = document.getElementById('workshopTypeChart')?.getContext('2d');
       if (ctxWsType) {
        const currentLanguage = document.documentElement.lang || 'en';
        new Chart(ctxWsType, {
            type: 'pie',
            data: {
                // labels: ['Live Training', 'In-Person', 'Online'],
                labels: currentLanguage === 'ar' ? ['تدريب مباشر', 'حضوري', 'عبر الإنترنت'] : ['Live Training', 'In-Person', 'Online'],
                datasets: [{
                    label: '{{ __('admin.workshop_count') }}',
                    data: [
                        workshopTypeCounts.sessions,
                        workshopTypeCounts.offline,
                        workshopTypeCounts.online
                    ],
                    backgroundColor: [chartColors[6], chartColors[2], chartColors[4]],
                    borderColor: [chartBorderColors[6], chartBorderColors[2], chartBorderColors[4]],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: chartFont }
                    },
                    tooltip: {
                        bodyFont: chartFont,
                        titleFont: chartFont
                    }
                }
            }
        });
    }

        // Chart 2: Average Rating per Trainer (Example Data)
                  const trainerData = getDataFromTable('#workshops-tab-pane .workshop-details-table', row => {
                        const cells = row.querySelectorAll('td');
                        if (cells.length < 4) return null;
                        return { trainer: cells[1].textContent.trim(), rating: parseFloat(cells[3].textContent.split('/')[0]) };
                  });
                  const trainers = [...new Set(trainerData.map(w => w.trainer))];
                  const trainerAvgRatings = trainers.map(trainer => {
                        const workshopsByTrainer = trainerData.filter(w => w.trainer === trainer);
                        const totalRating = workshopsByTrainer.reduce((sum, w) => sum + w.rating, 0);
                        return workshopsByTrainer.length > 0 ? (totalRating / workshopsByTrainer.length).toFixed(1) : 0;
                  });
                 const ctxTrainerRating = document.getElementById('trainerRatingChart')?.getContext('2d');
                 if(ctxTrainerRating) {
                     new Chart(ctxTrainerRating, {
                        type: 'bar', data: { labels: trainers, datasets: [{ label: 'متوسط التقييم (من 5)', data: trainerAvgRatings, backgroundColor: chartColors, borderColor: chartBorderColors, borderWidth: 1 }] },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: false, min: 4.0, max: 5, ticks: { font: chartFont } }, x: { ticks: { font: chartFont } } }, plugins: { legend: { display: false }, tooltip: { bodyFont: chartFont, titleFont: chartFont } } }
                    });
                 }
             }

            // ==============================================
            // 4. Final Evaluation Report Logic
            // ==============================================
            function setupFinalEvalReport() {
        const finalEvalData = getDataFromTable('#final-eval-tab-pane .final-eval-table', row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 5) return null;
            const scoreText = cells[2].textContent.trim();
            const scoreMatch = scoreText.match(/(\d+(\.\d+)?)\s*\/\s*100/);
            return {
                name: cells[1].textContent.trim(),
                score: scoreMatch ? parseFloat(scoreMatch[1]) : null,
                status: cells[4].querySelector('.status-badge') ? cells[4].querySelector('.status-badge').textContent.trim() : null
            };
        });

        // Chart: Score Distribution
        const scoreBins = @json($scoreDistribution);
        finalEvalData.forEach(item => {
            if (item.score !== null) {
                for (const bin of scoreBins) {
                    if (item.score >= bin.min && item.score <= bin.max) {
                        bin.count++;
                        break;
                    }
                }
            }
        });

        const ctxFeScoreDist = document.getElementById('finalEvalScoreDistributionChart')?.getContext('2d');
        if (ctxFeScoreDist) {
            new Chart(ctxFeScoreDist, {
                type: 'bar',
                data: {
                    labels: scoreBins.map(bin => bin.label),
                    datasets: [{
                        label: '{{ __('admin.company_counts') }}',
                        data: scoreBins.map(bin => bin.count),
                        backgroundColor: ['#198754', '#0d6efd', '#0dcaf0', '#ffc107', '#dc3545'],
                        borderColor: chartBorderColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { font: chartFont, stepSize: 1 } },
                        x: { ticks: { font: chartFont } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { bodyFont: chartFont, titleFont: chartFont }
                    }
                }
            });
        }
    }

             // ==============================================
             // 5. Program Summary Report Logic
             // ==============================================
              function setupSummaryReport() {
                    const finalEvalData = getDataFromTable('#final-eval-tab-pane .final-eval-table', row => { // Get data again for summary
                        const cells = row.querySelectorAll('td');
                        if (cells.length < 4) return null;
                         const scoreText = cells[1].textContent.trim();
                         const scoreMatch = scoreText.match(/(\d+(\.\d+)?)\s*\/\s*100/);
                         return {
                            score: scoreMatch ? parseFloat(scoreMatch[1]) : null,
                            status: cells[3].querySelector('.status-badge') ? cells[3].querySelector('.status-badge').textContent.trim() : null
                        };
                    });
                    const scores = finalEvalData.map(s => s.score).filter(s => s !== null);
                    const evaluatedCount = scores.length;
                    const avgScore = evaluatedCount > 0 ? (scores.reduce((a, b) => a + b, 0) / evaluatedCount).toFixed(1) : '--';

                    // Update KPI
                    // document.getElementById('summaryAvgFinalScore').textContent = avgScore;

                    // Status Distribution Chart
                    const statusCounts = { 'Highest Rated': 1, 'Good Performance': 2, 'Needs Improvement': 4 };
                    finalEvalData.forEach(item => {
                        if (item.status && statusCounts.hasOwnProperty(item.status)) {
                            statusCounts[item.status]++;
                        } else if (item.status && item.status.includes('Good')) { // Handle variations like "Good Performance" vs "Good"
                             statusCounts['Good Performance']++;
                        } else if (item.status && item.status.includes('Improvement')) {
                             statusCounts['Needs Improvement']++;
                        } else if (item.status && item.status.includes('Highest')) {
                              statusCounts['Highest Rated']++;
                        }
                    });

                    const ctxSummaryStatus = document.getElementById('summaryStatusDistributionChart')?.getContext('2d');
                    if(ctxSummaryStatus) {
                        const existingChart = Chart.getChart('summaryStatusDistributionChart');
                        if (existingChart) {
                            existingChart.destroy();
                        }
                        summaryStatusChart = new Chart(ctxSummaryStatus, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(statusCounts),
                                datasets: [{
                                    label: 'عدد الشركات',
                                    data: Object.values(statusCounts),
                                    backgroundColor: [chartColors[1], chartColors[0], chartColors[5]], // Success, Primary, Danger
                                    borderColor: chartBorderColors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            font: chartFont,
                                            padding: 20,
                                            usePointStyle: true,
                                            pointStyle: 'circle'
                                        }
                                    },
                                    tooltip: {
                                        enabled: true,
                                        bodyFont: chartFont,
                                        titleFont: chartFont,
                                        backgroundColor: 'rgba(0,0,0,0.8)',
                                        padding: 12,
                                        cornerRadius: 6,
                                        displayColors: true,
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a,b) => a+b, 0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return `${label}: ${value} (${percentage}%)`;
                                            }
                                        }
                                    }
                                },
                                layout: {
                                    padding: 20
                                },
                                cutout: '60%',
                                radius: '90%',
                                animation: {
                                    animateRotate: true,
                                    animateScale: true
                                }
                            }
                        });
                    } else {
                        console.error('Could not get canvas context for summary status chart');
                    }

                 // Update summary list counts
                  document.getElementById('summaryTopCompanies').innerHTML = `<i class="fas fa-trophy"></i> Highest Rated: ${statusCounts['Highest Rated']} companies`;
                  document.getElementById('summaryGoodCompanies').innerHTML = `<i class="fas fa-thumbs-up"></i> Good Performance: ${statusCounts['Good Performance']} companies`;
                  document.getElementById('summaryImprovementCompanies').innerHTML = `<i class="fas fa-flag"></i> Needs Improvement: ${statusCounts['Needs Improvement']} companies`;
              }

            // --- Initialize all reports ---
            setupPerformanceReport();
            setupMentorshipReport();
            setupWorkshopsReport();
            setupFinalEvalReport();
            setupSummaryReport(); // Run summary last as it might depend on other data
        });
  </script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Initialize DataTable
    DataTableInit(".table");
  });

  // Helper to create export dropdown HTML
  function createExportDropdown(table) {
    const dropdownHtml = `
      <div class="dropdown ms-2 d-inline-block">
        <button class="btn btn-sm btn-label-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
          @lang('admin.Export')
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#" data-type="csv">CSV</a></li>
          <li><a class="dropdown-item" href="#" data-type="excel">Excel</a></li>
        </ul>
      </div>
    `;

    const container = $(table.table().container()).find('.dataTables_filter');
    container.append(dropdownHtml);

    container.find('.dropdown-item').on('click', function (e) {
      e.preventDefault();
      const type = $(this).data('type');

      if (type === 'csv') table.button('.buttons-csv').trigger();
      if (type === 'excel') table.button('.buttons-excel').trigger();
    });
  }

  // DataTable Initialization with RTL support and export buttons
  function DataTableInit(selector, options = {}) {
    const isRTL = document.documentElement.dir === "rtl";

    const defaultOptions = {
      responsive: true,
      processing: true,
      serverSide: false,
      pageLength: 10,
      language: {
        url: isRTL
          ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
          : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",
      },
      dom:
        "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'fB>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
      buttons: [
        { extend: 'csvHtml5', className: 'd-none' },
        { extend: 'excelHtml5', className: 'd-none' }
      ],

      initComplete: function () {
        createExportDropdown(this.api());

        if (isRTL) $(selector).addClass("datatable-rtl");
      },
    };

    const finalOptions = { ...defaultOptions, ...options };
    return $(selector).DataTable(finalOptions);
  }
</script>
@stop
