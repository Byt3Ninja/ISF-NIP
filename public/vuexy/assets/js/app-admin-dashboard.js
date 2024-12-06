/* Admin Dashboard */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  renderCompanySdgChart();
  renderDevStageChart();
  renderProjectDistributionChart();
  renderSectorBreakdownChart();
  renderUserDistributionChart();
  renderTargetMarketChart();
  renderProjectOverTimeStatusChart();
  renderTotalCompaniesBySectorChart();
});

const lang = document.documentElement.lang;
const colors = [
  '#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FFC300', 
  '#87CEEB', '#FFA500', '#FF6347', '#FF4500', '#FF0000', 
  '#FF1493', '#FF69B4', '#FFB6C1', '#FFC0CB', '#DB7093', 
  '#C71585', '#FF00FF', '#8A2BE2', '#4B0082', '#800080',
  '#9370DB', '#7B68EE', '#6A5ACD', '#483D8B', '#0000FF',
  '#0000CD', '#00008B', '#000080', '#4169E1', '#6495ED',
  '#1E90FF', '#4682B4', '#87CEFA', '#87CEEB', '#00BFFF',
  '#ADD8E6', '#DC143C', '#B22222', '#8B0000', '#FF00FF',
];

const shuffledColors = colors.sort(() => 0.5 - Math.random());

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

const getPieChartConfig = (labels, data) => {
  return {
    chart: { height: 380, type: 'pie' },
    labels,
    series: data,
    colors: shuffledColors,
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
};

const getBarChartConfig = (labels, data, precentage = false, max) => {
  return {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          data,
          backgroundColor: shuffledColors,
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
          min: 0,
          max: precentage ? 100 : max ? max : 20,
          grid: {
            color: borderColor,
            drawBorder: false,
            borderColor: borderColor
          },
          ticks: {
            stepSize: precentage ? 10 : 1,
            color: labelColor
          }
        }
      }
    }
  };
};

// Render Total Companies By Sector Chart
const renderTotalCompaniesBySectorChart = () => {
  const chartData = companiesBySectorData ? companiesBySectorData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];

  const labels = chartData.map(data => lang == 'ar' ? data.sector_ar : data.sector_en);
  const values = chartData.map(data => data.count);
  
  const chartDom = document.querySelector('#companyBySectorChart');
  const chartConfig = getBarChartConfig(labels, values, false, 50);

  if (chartDom) {
    new Chart(chartDom, chartConfig);
  }
}

// Render User Distribution Chart
const renderUserDistributionChart = () => {
  const chartData = userDistributionData ? userDistributionData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];

  const labels = chartData.map(data => lang == 'ar' ? data.label_ar : data.label_en);
  const values = chartData.map(data => data.count);

  const chartDom = document.querySelector('#userDistributionChart');
  const chartConfig = getBarChartConfig(labels, values, false, 50);

  if (chartDom) {
    new Chart(chartDom, chartConfig);
  }
}

// Render Project Over Time Status Chart
const renderProjectOverTimeStatusChart = () => {
  const labels = projectStatusData ? projectStatusData.map(pro => pro.cycle_title) : [];
  const values = projectStatusData ? projectStatusData.map(pro => pro.total_programs * 10) : [];
  const dates = projectStatusData ? projectStatusData.map(
    pro => {
      const date = new Date(pro.cycle_start_date);
      return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}`;
    }
  ) : [];

  // Project Status Over Time curve Chart
  const projectStatusChartEl = document.querySelector('#projectStatusOverTimeChart');
  const projectStatusChartConfig = {
    chart: {
      height: 345,
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
        data: values,
      }
    ],
    markers: {
      strokeWidth: 7,
      strokeOpacity: 1,
      strokeColors: [isDarkStyle ? '#fff' : '#000'],
      colors: [config.colors.warning]
    },
    dataLabels: {
      enabled: true,
      style: {
        colors: [isDarkStyle ? '#c0c0c0' : '#515365'],
        fontSize: '12px',
        fontWeight: 'bold'
      },
      formatter: function (val, opts) {
        return labels[opts.dataPointIndex] || ''; // Display cycle title as label
      }
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
      categories: dates,
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
  const chartData = targetMarketData ? targetMarketData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];

  const labels = chartData ? chartData.map(data => lang == 'ar' ? data.label_ar : data.label_en) : [];
  const values = chartData ? chartData.map(data => data.count) : [];

  const chartDom = document.querySelector('#targetMarketChart');
  const chartConfig = getBarChartConfig(labels, values, false, null);

  if (chartDom) {
    new Chart(chartDom, chartConfig);
  }
};

// Render Development Stage Chart
const renderDevStageChart = () => {
  const chartData = devStageData ? devStageData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];

  const labels = chartData.map(data => lang == 'ar' ? data.label_ar : data.label_en);
  const values = chartData.map(data => data.count);

  const charDom = document.querySelector('#devStageStackedBarChart');
  const chartConfig = getBarChartConfig(labels, values, false, null);

  if (charDom) {
    new Chart(charDom, chartConfig);
  }
};

// Render Company SDG Chart
const renderCompanySdgChart = () => {
  const labels = sdgData ? sdgData.map(data => lang == 'ar' ? data.sdg_ar : data.sdg_en) : [];
  const series = sdgData ? sdgData.map(data => data.total) : [];

  const chartDom = document.querySelector('#sdgCompaniesChart');

  const chartConfig = getBarChartConfig(labels, series);

  if (chartDom) {
    new Chart(chartDom, chartConfig);
  };
};

// Render Project Distribution Pie Chart
const renderProjectDistributionChart = () => {
  const chartData = projectsPieData ? projectsPieData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];
  
  const labels = chartData.map(data => data.label);
  const values = chartData.map(data => data.count);
  
  const chartDom = document.querySelector('#projectsPieChart');
  const chartConfig = getPieChartConfig(labels, values);

  if (typeof chartDom !== undefined && chartDom !== null) {
    const chart = new ApexCharts(chartDom, chartConfig);
    chart.render();
  }
};

// Render Sector Breakdown Chart
const renderSectorBreakdownChart = () => {
  const chartData = sectorBreakdownData ? sectorBreakdownData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];

  const labels = chartData.map(data => lang == 'ar' ? data.label_ar : data.label_en);
  const values = chartData.map(data => data.count);
  
  const chartDom = document.querySelector('#sectorBreakdownChart');
  const chartConfig = getPieChartConfig(labels, values);
  
  if (typeof chartDom !== undefined && chartDom !== null) {
    const chart = new ApexCharts(chartDom, chartConfig);
    chart.render();
  }
};
