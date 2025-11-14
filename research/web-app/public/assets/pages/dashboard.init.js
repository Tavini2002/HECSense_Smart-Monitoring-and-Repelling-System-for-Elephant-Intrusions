
/*
 Template Name: Zegva - Responsive Bootstrap 4 Admin Dashboard
 Author: Themesdesign
 Website: www.themesdesign.in
 File: Dashboard init js
 */


!function($) {
    "use strict";
  
    var Dashboard = function() {};
  
    //creates Stacked chart
    Dashboard.prototype.createStackedChart = function(element, data, xkey, ykeys, labels, lineColors) {
        Morris.Bar({
            element: element,
            data: data,
            xkey: xkey,          // xkey should contain month names as strings
            ykeys: ykeys,
            stacked: true,
            labels: labels,
            hideHover: 'auto',
            barSizeRatio: 0.4,
            resize: true,
            gridLineColor: '#3a4657',
            barColors: lineColors,
            xLabelAngle: 45      // Angle the labels to fit more months
        });
    };
    
  
  
    //creates Donut chart
    Dashboard.prototype.createDonutChart = function(element, data, colors) {
        Morris.Donut({
            element: element,
            data: data,
            resize: true, //defaulted to true
            backgroundColor: '#2c3749',
            labelColor: '#dee2e6',
            colors: colors
        });
    },
  
    Dashboard.prototype.init = function() {
  
        // Replace static data with dynamic monthlyRevenueData from PHP
        var $stckedData = monthlyRevenueData;

        // Creating Stacked chart with the dynamic data
        this.createStackedChart('morris-bar-stacked', $stckedData, 'y', ['a'], ['Revenue'], ['#23cbe0']);
        
        //creating donut chart
        var $donutData = [
            {label: "Pending Revenue", value: total_pending_amount, color: '#fb4365'},  // Using PHP variable for Pending Income
            {label: "Settled Revenue", value: total_company_revenue, color: '#20d4b6'},  // Using PHP variable for Settled Income
        ];
        this.createDonutChart('morris-donut-example', $donutData, ['#fb4365', '#20d4b6']);

    },
    //init
    $.Dashboard = new Dashboard, $.Dashboard.Constructor = Dashboard
  }(window.jQuery),
  
  //initializing
  function($) {
    "use strict";
    $.Dashboard.init();
  }(window.jQuery);

  //   apex chart
  var options1= {
    chart: {
        type: 'area',
        height: 60,
        sparkline: {
            enabled: true
        }
    }
    ,
    series: [ {
        data: [24, 66, 42, 88, 62, 24, 45, 12, 36, 10]
    }
    ],
    stroke: {
        curve: 'smooth', width: 3
    }
    ,
    colors: ['#0e86e7'],
    tooltip: {
        fixed: {
            enabled: false
        }
        ,
        x: {
            show: false
        }
        ,
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        }
        ,
        marker: {
            show: false
        }
    }
}
new ApexCharts(document.querySelector("#chart1"), options1).render();
// 2
var options2= {
    chart: {
        type: 'area',
        height: 60,
        sparkline: {
            enabled: true
        }
    }
    ,
    series: [ {
        data: [54, 12, 24, 66, 42, 25, 44, 12, 36, 9]
    }
    ],
    stroke: {
        curve: 'smooth', width: 3
    }
    ,
    colors: ['#fbb131'],
    tooltip: {
        fixed: {
            enabled: false
        }
        ,
        x: {
            show: false
        }
        ,
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        }
        ,
        marker: {
            show: false
        }
    }
}
new ApexCharts(document.querySelector("#chart2"), options2).render();
// 3
var options3= {
    chart: {
        type: 'area',
        height: 60,
        sparkline: {
            enabled: true
        }
    }
    ,
    series: [ {
        data: [10, 36, 12, 44, 63, 24, 44, 12, 56, 24]
    }
    ],
    stroke: {
        curve: 'smooth', width: 3
    }
    ,
    colors: ['#23cbe0'],
    tooltip: {
        fixed: {
            enabled: false
        }
        ,
        x: {
            show: false
        }
        ,
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        }
        ,
        marker: {
            show: false
        }
    }
}
new ApexCharts(document.querySelector("#chart3"), options3).render();
//   4
var options4= {
    chart: {
        type: 'area',
        height: 60,
        sparkline: {
            enabled: true
        }
    }
    ,
    series: [ {
        data: [34, 66, 42, 33, 62, 24, 45, 16, 48, 10]
    }
    ],
    stroke: {
        curve: 'smooth', width: 3
    }
    ,
    colors: ['#fb4365'],
    tooltip: {
        fixed: {
            enabled: false
        }
        ,
        x: {
            show: false
        }
        ,
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        }
        ,
        marker: {
            show: false
        }
    }
}
new ApexCharts(document.querySelector("#chart4"), options4).render();