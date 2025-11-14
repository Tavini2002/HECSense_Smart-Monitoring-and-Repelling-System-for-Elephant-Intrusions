!function($) {
    "use strict";

    var ChartJs = function() {};

    ChartJs.prototype.respChart = function(selector, type, data, options) {
        Chart.defaults.global.defaultFontColor = "rgba(255,255,255,0.5)";
        Chart.defaults.scale.gridLines.color = "rgba(165, 166, 173, 0.1)";

        var ctx = selector.get(0).getContext("2d");
        var container = $(selector).parent();

        $(window).resize(generateChart);

        function generateChart() {
            var ww = selector.attr('width', $(container).width());
            switch (type) {
                case 'Line':
                    new Chart(ctx, { type: 'line', data: data, options: options });
                    break;
                case 'Doughnut':
                    new Chart(ctx, { type: 'doughnut', data: data, options: options });
                    break;
                case 'Pie':
                    new Chart(ctx, { type: 'pie', data: data, options: options });
                    break;
                case 'Bar':
                    new Chart(ctx, { type: 'bar', data: data, options: options });
                    break;
                case 'Radar':
                    new Chart(ctx, { type: 'radar', data: data, options: options });
                    break;
                case 'PolarArea':
                    new Chart(ctx, { data: data, type: 'polarArea', options: options });
                    break;
            }
        }

        generateChart();
    };

    ChartJs.prototype.init = function() {
        var lineChart = {
            labels: datesInMonth, // Using the dates passed from Blade
            datasets: [
                {
                    label: "Actual Revenue",
                    fill: true,
                    lineTension: 0.5,
                    backgroundColor: "rgba(32, 212, 182, 0.2)",
                    borderColor: "#20d4b6",
                    pointBorderColor: "#20d4b6",
                    pointBackgroundColor: "rgba(32, 212, 182, 0.6)",
                    pointBorderWidth: 2,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "#20d4b6",
                    pointHoverBorderColor: "#1bb89e",
                    pointHoverBorderWidth: 2,
                    pointRadius: 4,
                    pointHitRadius: 10,
                    data: actualRevenueByDay // Using the revenue data passed from Blade
                },
                {
                    label: "Pending Revenue",
                    fill: true,
                    lineTension: 0.5,
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    borderColor: "#ff6384",
                    pointBorderColor: "#ff6384",
                    pointBackgroundColor: "rgba(255, 99, 132, 0.6)",
                    pointBorderWidth: 2,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "#ff6384",
                    pointHoverBorderColor: "#ff4b60",
                    pointHoverBorderWidth: 2,
                    pointRadius: 4,
                    pointHitRadius: 10,
                    data: expectedRevenueByDay // Using the expected revenue data passed from Blade
                }
            ]
        };

        var lineOpts = {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Revenue'
                    }
                }],
                xAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Day of the Month'
                    }
                }]
            }
        };

        this.respChart($("#lineChart"), 'Line', lineChart, lineOpts);
    };

    $.ChartJs = new ChartJs, $.ChartJs.Constructor = ChartJs;

}(window.jQuery),

// Initializing
function($) {
    "use strict";
    $.ChartJs.init();
}(window.jQuery);
