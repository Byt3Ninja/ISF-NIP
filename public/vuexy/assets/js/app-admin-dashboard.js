/* Admin Dashboard */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  renderDevStageChart();
  renderProjectDistributionChart();
  renderCompanySdgChart();
  renderUserDistributionChart();
  renderSectorBreakdownChart();
  renderTargetMarketChart();
  renderProjectOverTimeStatusChart();
  // renderTotalCompaniesBySectorChart();
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
// const renderTotalCompaniesBySectorChart = () => {
//   const chartData = totalStartupsPerOrganization ? totalStartupsPerOrganization?.filter(
//     data => data.label !== null && data.count > 0
//   ) : [];
//   const chartDataLabels = chartData.map(data => data.label);
//   const chartDataValues = chartData.map(data => data.count);
//   console.log('chartDataLabels', chartDataLabels)
//   console.log('chartDataValues', chartDataValues)
  // const chartDom = document.querySelector('#companyBySectorChart');
  // if (chartDom) {
  //   const chart = new Chart(chartDom, {
  //     type: 'bar',
  //     data: {
  //       labels: sectors,
  //       datasets: [
  //         {
  //           data: companies,
  //           backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1'],
  //           borderColor: 'transparent',
  //           maxBarThickness: 15,
  //           borderRadius: {
  //             topRight: 15,
  //             topLeft: 15
  //           }
  //         }
  //       ]
  //     },
  //     options: {
  //       responsive: true,
  //       maintainAspectRatio: false,
  //       animation: {
  //         duration: 500
  //       },
  //       plugins: {
  //         tooltip: {
  //           rtl: isRtl,
  //           backgroundColor: cardColor,
  //           titleColor: headingColor,
  //           bodyColor: legendColor,
  //           borderWidth: 1,
  //           borderColor: borderColor
  //         },
  //         legend: {
  //           display: false
  //         }
  //       },
  //       scales: {
  //         x: {
  //           grid: {
  //             color: borderColor,
  //             drawBorder: false,
  //             borderColor: borderColor
  //           },
  //           ticks: {
  //             color: labelColor
  //           }
  //         },
  //         y: {
  //           min: 2,
  //           max: 12,
  //           grid: {
  //             color: borderColor,
  //             drawBorder: false,
  //             borderColor: borderColor
  //           },
  //           ticks: {
  //             stepSize: 2,
  //             color: labelColor
  //           }
  //         }
  //       }
  //     }
  //   });
  // }
// }

// Render User Distribution Chart
const renderUserDistributionChart = () => {
  const chartData = userDistributionData ? userDistributionData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];
  const chartDataLabels = chartData.map(data => data.label);
  const chartDataValues = chartData.map(data => data.count);

  const chartDom = document.querySelector('#userDistributionChart');
  if (chartDom) {
    const chart = new Chart(chartDom, {
      type: 'bar',
      data: {
        labels: chartDataLabels,
        datasets: [
          {
            data: chartDataValues,
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
            min: 0,
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
    data => data.label !== null
  ) : [];

  const allLabels = chartData.reduce((acc, data) => {
    if (data.label) {
      const labels = JSON.parse(data.label);
      labels.forEach(label => {
        acc.push(label);
      });
    }
    return acc;
  }, []);

  const allValues = chartData.reduce((acc, data) => {
    if (data.label) {
      const labels = JSON.parse(data.label);
      labels.forEach(() => {
        acc.push(data.count * 10);
      });
    }
    return acc;
  }, []);

  const chartDom = document.querySelector('#targetMarketChart');
  if (chartDom) {
    const chart = new Chart(chartDom, {
      type: 'bar',
      data: {
        labels: allLabels,
        datasets: [
          {
            data: allValues,
            backgroundColor: [
              '#808390', '#7367f0', '#00bad1', '#007bff', '#FFC300',
              '#f8d7da', '#cce5ff', '#d4edda', '#fff3cd', '#d1ecf1',
            ],
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
            max: 100,
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              stepSize: 10,
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
  const chartData = devStageData ? devStageData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];
  const chartDataLabels = chartData.map(data => data.label);
  const chartDataValues = chartData.map(data => data.count * 10);

  const charDom = document.querySelector('#devStageStackedBarChart');
  if (charDom) {
    const chart = new Chart(charDom, {
      type: 'bar',
      data: {
        labels: chartDataLabels,
        datasets: [
          {
            data: chartDataValues,
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
            min: 0,
            max: 100,
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              stepSize: 10,
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
  if (sdgData) {
    const chartData = sdgData ? sdgData?.filter(
      data => data.sdg !== null
    ) : [];
  const chartDataLabels = chartData.reduce((acc, data) => {
    if (data.sdg) {
      let labels = [];
      try {
        labels = JSON.parse(data.sdg);
      } catch (e) {
        console.error('Invalid JSON string in data.sdg:', data.sdg);
      }
      labels.forEach(label => {
        acc.push(label);
      });
    }
    return acc;
  }, []);

  const chartDataValues = chartData.reduce((acc, data) => {
    if (data.sdg) {
      const labels = JSON.parse(data.sdg);
      labels.forEach(() => {
        acc.push(data.total);
      });
    }
    return acc;
  }, []);
  
    const chartDom = document.querySelector('#sdgCompaniesChart'),
    chartConfig = {
      chart: { height: 380, type: 'pie' },
      labels: chartDataLabels,
      series: chartDataValues,
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
  }
};

// Render Project Distribution Pie Chart
const renderProjectDistributionChart = () => {
  const chartData = projectsPieData ? projectsPieData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];
  const chartDataLabels = chartData.map(data => data.label);
  const chartDataValues = chartData.map(data => data.count);

  const chartDom = document.querySelector('#projectsPieChart'),
    chartConfig = {
      chart: { height: 380, type: 'pie' },
      labels: chartDataLabels,
      series: chartDataValues,
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
  const chartData = sectorBreakdownData ? sectorBreakdownData?.filter(
    data => data.label !== null && data.count > 0
  ) : [];
  const chartDataLabels = chartData.map(data => data.label);
  const chartDataValues = chartData.map(data => data.count);

  const chartDom = document.querySelector('#sectorBreakdownChart'),
    colors = ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FFC300'],
    chartConfig = {
      chart: { height: 380, type: 'donut' },
      labels: chartDataLabels,
      series: chartDataValues,
      colors,
      stroke: { show: true, width: 5, colors: ['#fff'] },
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
            height: 500, // Increase this value
            size: '75%' // Change this to a smaller value, e.g., '75%' or '70%'
          }
        }
      }
    };

  if (typeof chartDom !== undefined && chartDom !== null) {
    const chart = new ApexCharts(chartDom, chartConfig);
    chart.render();
  }
};
