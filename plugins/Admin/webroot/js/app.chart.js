/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.AppChart = {

    color: '#cccccc', // default color

    getFontColor: function() {
        return foodcoopshop.Helper.getColorMode() == 'dark' ? '#CDCDCD' : '#333333';
    },

    getGridColor: function() {
        return foodcoopshop.Helper.getColorMode() == 'dark' ? '#696969' : '#dfdfdf';
    },

    barChartOptions : {
        plugins: {
            legend: {
                display: true
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        var formattedValue;
                        if (ctx.datasetIndex == 2) {
                            formattedValue = foodcoopshop.Helper.formatFloatAsString(ctx.parsed.y) + '%';
                        } else {
                            formattedValue = foodcoopshop.Helper.formatFloatAsCurrency(ctx.parsed.y);
                        }
                        return formattedValue;
                    }
                }
            }
        },
        datasetStrokeWidth : 1,
        scaleOverride: true,
        scaleStartValue: 0,
        scaleGridLineColor: 'rgba(0,0,0,.15)',
        scales: {
            x: {
                stacked: true,
                grid: {
                    display: false
                },
                ticks: {
                    autoSkip: false,
                    maxRotation: 90,
                    minRotation: 90,
                    labelOffset: -5
                }
            },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return foodcoopshop.Helper.formatFloatAsCurrency(value);
                    },
                },
                grid: {}
            },
        }
    },

    pieChartOptions : {
        cutout: 65,
        aspectRatio: 5 / 3,
        layout: {
            padding: 50
        },
        rotation: -30,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return foodcoopshop.Helper.formatFloatAsCurrency(ctx.parsed);
                    }
                }
            },
            legend: {
                display: false
            },
            datalabels: {
                formatter: function(value, ctx) {
                    var sum = 0;
                    var dataArr = ctx.chart.data.datasets[0].data;
                    dataArr.map(function(data) {
                        sum += data;
                    });
                    var percentage = (value * 100 / sum).toFixed(0);
                    if (percentage > 2) {
                        return percentage + '%';
                    }
                    return '';
                },
                color: '#ccc',
                labels: {
                    title: {
                        font: {
                            size: 15
                        }
                    }
                }
            }
        }
    },

    lineChartOptions : {
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return foodcoopshop.Helper.formatFloatAsCurrency(ctx.parsed.y);
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {}
            },
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return foodcoopshop.Helper.formatFloatAsCurrency(value);
                    }
                },
                grid: {}
            }
        }
    },

    setColor : function(color) {
        this.color = color;
    },

    initLineChart : function(xAxisData, yAxisData) {

        var lineChartData = {
            labels: xAxisData,
            datasets: [{
                lineTension : 0.15,
                data: yAxisData,
                fill: false,
                borderColor: this.color + '80', //.5 alpha
                pointBorderColor: this.color,
                pointBackgroundColor: this.color,
                pointRadius: 5
            }],
        };

        this.lineChartOptions.scales.x.ticks.color = this.getFontColor();
        this.lineChartOptions.scales.y.ticks.color = this.getFontColor();
        this.lineChartOptions.scales.y.grid.color = this.getGridColor();

        var ctx = $('#myLineChart').get(0).getContext('2d');
        new Chart(ctx, {
            responsive : true,
            type: 'line',
            data: lineChartData,
            options: this.lineChartOptions
        });

    },

    initLineChartDepositOverview : function(xAxisData1, xAxisData2, xAxisData3, yAxisData, xAxisData1Label, xAxisData2Label, xAxisData3Label) {

        var lineChartData = {
            labels: yAxisData,
            datasets: [
                {
                    data: xAxisData1,
                    label: xAxisData1Label,
                    fill: false,
                    borderColor: 'rgba(113,159,65,.7)',
                    pointBorderColor: 'rgba(113,159,65,1)',
                    pointBackgroundColor: 'rgba(113,159,65,1)',
                    pointRadius: 1,
                    tension: 0,
                    borderWidth: 1
                },
                {
                    data: xAxisData2,
                    label: xAxisData2Label,
                    fill: false,
                    borderColor: 'rgba(106,90,205,.7)',
                    pointBorderColor: 'rgba(106,90,205,1)',
                    pointBackgroundColor: 'rgba(106,90,205,1)',
                    pointRadius: 1,
                    tension: 0,
                    borderWidth: 1
                },
                {
                    data: xAxisData3,
                    label: xAxisData3Label,
                    fill: false,
                    borderColor: 'rgba(255,165,0,.7)',
                    pointBorderColor: 'rgba(255,165,0,1)',
                    pointBackgroundColor: 'rgba(255,165,0,1)',
                    pointRadius: 1,
                    tension: 0,
                    borderWidth: 1
                }
            ]
        };

        var lineChartOptions = this.lineChartOptions;
        lineChartOptions.plugins.legend.display = true;

        var ctx = $('#myLineChart').get(0).getContext('2d');
        new Chart(ctx, {
            responsive : true,
            type: 'line',
            data: lineChartData,
            options: lineChartOptions
        });

    },

    initBarChart : function(xAxisData, yAxisData, yAxisData2, yAxisData3, yAxisLabel, yAxisLabel2, yAxisLabel3) {

        var barChartData = {
            labels: xAxisData,
            datasets: [{
                label: yAxisLabel,
                data: yAxisData,
                maxBarThickness: 40,
                backgroundColor: this.color + 'B3', //.7 alpha
                hoverBackgroundColor: this.color + '4C' //.5 alpha
            }]
        };

        if (yAxisData2 != 0) {
            barChartData.datasets.push(
                {
                    label: yAxisLabel2,
                    data: yAxisData2,
                    maxBarThickness: 40,
                    backgroundColor: this.color + '80', //.5 alpha
                    hoverBackgroundColor: this.color + '4C' //.3 alpha
                }
            );
        }

        if (yAxisData3 != 0) {
            barChartData.datasets.push(
                {
                    label: yAxisLabel3,
                    data: yAxisData3,
                    lineTension : 0.15,
                    borderDash: [5, 5],
                    borderWidth: 1,
                    pointBorderColor: this.color,
                    pointBackgroundColor: this.color,
                    pointRadius: 5,
                    backgroundColor: this.color,
                    borderColor: this.color,
                    hoverBackgroundColor: this.color + '4C', //.3 alpha
                    yAxisID: 'y1',
                    type: 'line',
                }
            );

            this.barChartOptions.scales.y1 = {
                type: 'linear',
                display: true,
                position: 'right',
                ticks: {
                    callback: function(value, index, values) {
                        return foodcoopshop.Helper.formatFloatAsString(value) + '%';
                    }
                },
                grid: {
                  display: false,
                },
            };
            this.barChartOptions.scales.x.ticks.color = this.getFontColor();
            this.barChartOptions.scales.y.ticks.color = this.getFontColor();
            this.barChartOptions.scales.y1.ticks.color = this.getFontColor();
            this.barChartOptions.plugins.legend.labels = {color: this.getFontColor()};
            this.barChartOptions.scales.y.grid.color = this.getGridColor();
        }

        var ctx = $('#myBarChart').get(0).getContext('2d');
        new Chart(ctx, {
            responsive : true,
            type: 'bar',
            data: barChartData,
            options: this.barChartOptions
        });

    },

    initPieChart : function(dataPieChart, labelsPieChart, backgroundColorPieChart) {

        var pieChartData = {
            datasets: [{
                data: dataPieChart,
                borderColor: '#CDCDCD',
                backgroundColor: backgroundColorPieChart,
                hoverBackgroundColor: this.color,
                borderWidth: 1,
                datalabels: {
                    labels: {
                        outer: {
                            align: 'end',
                            anchor: 'end',
                            color: foodcoopshop.Helper.getColorMode() == 'dark' ? '#CDCDCD' : '#333333',
                            font: {
                                size: 15
                            },
                            formatter: function(value, ctx) {
                                var sum = 0;
                                var dataArr = ctx.chart.data.datasets[0].data;
                                dataArr.map(function(data) {
                                    sum += data;
                                });
                                var percentage = (value * 100 / sum);
                                if (percentage > 0.7) {
                                    return ctx.chart.data.labels[ctx.dataIndex];
                                }
                                return '';
                            },
                            offset: 15,
                        }
                    }
                }

            }],
            labels: labelsPieChart,
        };

        var ctx = $('#myPieChart').get(0).getContext('2d');
        new Chart(ctx, {
            plugins: [ChartDataLabels],
            responsive : true,
            type: 'pie',
            data: pieChartData,
            options: this.pieChartOptions
        });

    }

};