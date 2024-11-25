/* Admin Dashboard */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  renderSectorBreakdownChart();
  renderProjectDistributionChart();
  renderDevStageChart();
  renderTargetMarketChart();
  renderProjectOverTimeStatusChart();
  renderCompanySdgChart();
  renderUserDistributionChart();
  renderTotalCompaniesBySectorChart();
});

let cardColor, headingColor, labelColor, borderColor, legendColor;

if (isDarkStyle) {
  cardColor = config.colors_dark.cardColor;
  headingColor = config.colors_dark.headingColor;
  labelColor = config.colors_dark.textMuted;
  legendColor = config.colors_dark.bodyColor;
  borderColor = config.colors_dark.borderColor;
} else {
  cardColor = config.colors.cardColor;
  headingColor = config.colors.headingColor;
  labelColor = config.colors.textMuted;
  legendColor = config.colors.bodyColor;
  borderColor = config.colors.borderColor;
}

const chartList = document.querySelectorAll('.chartjs');
chartList.forEach(function (chartListItem) {
  chartListItem.height = chartListItem.dataset.height;
});


const getChartData = (labels, data) => {
  if (labels && data) {
    return { labels, data };
  }
};

// Render Total Companies By Sector Chart
const renderTotalCompaniesBySectorChart = () => {
  const sectors = ['Technology', 'Healthcare', 'Finance', 'Education'];
  const companies = [4, 9, 5, 7];
  const chartDom = document.querySelector('#companyBySectorChart');
  if (chartDom) {
    const chart = new Chart(chartDom, {
      type: 'bar',
      data: {
        labels: sectors,
        datasets: [
          {
            data: companies,
            backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1'],
            borderColor: 'transparent',
            maxBarThickness: 15,
            borderRadius: {
              topRight: 15,
              topLeft: 15
            }
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 500
        },
        plugins: {
          tooltip: {
            rtl: isRtl,
            backgroundColor: cardColor,
            titleColor: headingColor,
            bodyColor: legendColor,
            borderWidth: 1,
            borderColor: borderColor
          },
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              color: labelColor
            }
          },
          y: {
            min: 2,
            max: 12,
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              stepSize: 2,
              color: labelColor
            }
          }
        }
      }
    });
  }
}

// Render User Distribution Chart
const renderUserDistributionChart = () => {
  const { labels, data } = getChartData(userDistributionLabels, userDistributionData);
  // console.log('labels', labels);
  const chartDom = document.querySelector('#userDistributionChart');
  if (chartDom) {
    const chart = new Chart(chartDom, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            data,
            backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1'],
            borderColor: 'transparent',
            maxBarThickness: 15,
            borderRadius: {
              topRight: 15,
              topLeft: 15
            }
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 500
        },
        plugins: {
          tooltip: {
            rtl: isRtl,
            backgroundColor: cardColor,
            titleColor: headingColor,
            bodyColor: legendColor,
            borderWidth: 1,
            borderColor: borderColor
          },
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              color: labelColor
            }
          },
          y: {
            min: 2,
            max: 100,
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              stepSize: 2,
              color: labelColor
            }
          }
        }
      }
    });
  }
}

// Render Target Market Chart
const renderProjectOverTimeStatusChart = () => {
  const projectStatusLabels = ['January', 'February', 'March', 'April', 'May', 'June', 'July'];
  const projectStatusData = [10, 20, 15, 25, 30, 35, 40];

  // Project Status Over Time curve Chart
  const projectStatusChartEl = document.querySelector('#projectStatusOverTimeChart');
  const projectStatusChartConfig = {
    chart: {
      height: 400,
      type: 'line',
      parentHeightOffset: 0,
      zoom: {
        enabled: false
      },
      toolbar: {
        show: false
      }
    },
    series: [
      {
        data: [280, 200, 220, 180, 270, 250, 70, 90, 200, 150, 160, 100, 150, 100, 50]
      }
    ],
    markers: {
      strokeWidth: 7,
      strokeOpacity: 1,
      strokeColors: [isDarkStyle ? '#fff' : '#000'],
      colors: [config.colors.warning]
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'straight'
    },
    colors: [config.colors.warning],
    grid: {
      borderColor: isDarkStyle ? '#555' : '#ddd',
      xaxis: {
        lines: {
          show: true
        }
      },
      padding: {
        top: -20
      }
    },
    tooltip: {
      custom: function ({ series, seriesIndex, dataPointIndex, w }) {
        return '<div class="px-3 py-2">' + '<span>' + series[seriesIndex][dataPointIndex] + '%</span>' + '</div>';
      }
    },
    xaxis: {
      categories: [
        '7/12',
        '8/12',
        '9/12',
        '10/12',
        '11/12',
        '12/12',
        '13/12',
        '14/12',
        '15/12',
        '16/12',
        '17/12',
        '18/12',
        '19/12',
        '20/12',
        '21/12'
      ],
      axisBorder: {
        show: false
      },
      axisTicks: {
        show: false
      },
      labels: {
        style: {
          colors: isDarkStyle ? '#c0c0c0' : '#515365',
          fontSize: '13px'
        }
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: isDarkStyle ? '#c0c0c0' : '#515365',
          fontSize: '13px'
        }
      }
    }
  };

  if (typeof projectStatusChartEl !== undefined && projectStatusChartEl !== null) {
    const projectStatusChart = new ApexCharts(projectStatusChartEl, projectStatusChartConfig);
    projectStatusChart.render();
  }
};

// Render Target Market Chart
const renderTargetMarketChart = () => {
  const { labels, data } = getChartData(targetMarketLabels, targetMarketData);
  const finalLabels = JSON.parse(labels[1]);
  const chartDom = document.querySelector('#targetMarketChart');

  if (chartDom) {
    const chart = new Chart(chartDom, {
      type: 'bar',
      data: {
        labels: finalLabels,
        datasets: [
          {
            data: data,
            backgroundColor: ['green', 'yellow'],
            borderColor: 'transparent',
            maxBarThickness: 15,
            borderRadius: {
              topRight: 15,
              topLeft: 15
            }
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 500
        },
        plugins: {
          tooltip: {
            rtl: isRtl,
            backgroundColor: cardColor,
            titleColor: headingColor,
            bodyColor: legendColor,
            borderWidth: 1,
            borderColor: borderColor
          },
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              color: labelColor
            }
          },
          y: {
            min: 2,
            max: 12,
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              stepSize: 2,
              color: labelColor
            }
          }
        }
      }
    });
  }
};

// Render Development Stage Chart
const renderDevStageChart = () => {
  const { labels, data } = getChartData(devStageLabels, devStageData);
  const finalLabels = labels.map(label => (!label ? 'others' : label));
  console.log('finalLabels', finalLabels);
  console.log('data', data);
  const charDom = document.querySelector('#devStageStackedBarChart');
  if (charDom) {
    const chart = new Chart(charDom, {
      type: 'bar',
      data: {
        labels: finalLabels,
        datasets: [
          {
            data: data,
            backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1'],
            borderColor: 'transparent',
            maxBarThickness: 15,
            borderRadius: {
              topRight: 15,
              topLeft: 15
            }
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 500
        },
        plugins: {
          tooltip: {
            rtl: isRtl,
            backgroundColor: cardColor,
            titleColor: headingColor,
            bodyColor: legendColor,
            borderWidth: 1,
            borderColor: borderColor
          },
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              color: labelColor
            }
          },
          y: {
            min: 2,
            max: 12,
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              stepSize: 2,
              color: labelColor
            }
          }
        }
      }
    });
  }
};

// Render Company SDG Chart
const renderCompanySdgChart = () => {
  const { labels, data } = getChartData(sdgLabels, sdgData);
  const finalLabels = JSON.parse(labels[1]);
  const chartDom = document.querySelector('#sdgCompaniesChart'),
  chartConfig = {
    chart: { height: 380, type: 'pie' },
    labels: finalLabels,
    series: [10, 20, 15, 25, 30, 35, 40, 12, 56, 41, 11, 12, 23, 46, 68, 79, 89],
    colors: ['#ffd9b4', '#ff7f50', '#ff6347', '#ff4500', '#ff0000', '#dc143c', '#b22222', '#8b0000', '#ff1493', '#ff69b4', '#ffb6c1', '#ffc0cb', '#db7093', '#c71585', '#ff00ff', '#ee82ee', '#dda0dd'],
    stroke: { show: false, curve: 'smooth' },
    legend: {
      show: true,
      position: 'bottom',
      markers: { offsetX: -3 },
      itemMargin: { vertical: 3, horizontal: 10 },
      labels: { colors: isDarkStyle ? '#c0c0c0' : '#515365', useSeriesColors: false }
    },
    plotOptions: {}
  };
  if (typeof chartDom !== undefined && chartDom !== null) {
    const chart = new ApexCharts(chartDom, chartConfig);
    chart.render();
  }
};

// Render Project Distribution Pie Chart
const renderProjectDistributionChart = () => {
  const { labels, data } = getChartData(projectsPieLabels, projectsPieData);
  const finalLabels = labels.map(label => (!label ? 'others' : label)),
    chartDom = document.querySelector('#projectsPieChart'),
    chartConfig = {
      chart: { height: 380, type: 'pie' },
      labels: finalLabels,
      series: data,
      colors: ['#87CEEB', '#FFA500', '#33FF57', '#3357FF', '#FF33A1', '#FFC300'],
      stroke: { show: false, curve: 'smooth' },
      legend: {
        show: true,
        position: 'bottom',
        markers: { offsetX: -3 },
        itemMargin: { vertical: 3, horizontal: 10 },
        labels: { colors: isDarkStyle ? '#c0c0c0' : '#515365', useSeriesColors: false }
      },
      plotOptions: {}
    };
  if (typeof chartDom !== undefined && chartDom !== null) {
    const chart = new ApexCharts(chartDom, chartConfig);
    chart.render();
  }
};

// Render Sector Breakdown Chart
const renderSectorBreakdownChart = () => {
  const { labels, data } = getChartData(sectorBreakdownLabels, sectorBreakdownData);

  const chartDom = document.querySelector('#sectorBreakdownChart'),
    colors = ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FFC300'],
    chartConfig = {
      chart: { height: 380, type: 'donut' },
      labels: labels.slice(0, 5),
      series: data.slice(0, 5).map(d => d * 10),
      colors,
      stroke: { show: true, width: 2, colors: ['#fff'] },
      legend: {
        show: true,
        position: 'bottom',
        markers: { offsetX: -3 },
        itemMargin: { vertical: 3, horizontal: 10 },
        labels: { colors: labelColor, useSeriesColors: false }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '85%'
          }
        }
      }
    };

  if (typeof chartDom !== undefined && chartDom !== null) {
    const chart = new ApexCharts(chartDom, chartConfig);
    chart.render();
  }
};
