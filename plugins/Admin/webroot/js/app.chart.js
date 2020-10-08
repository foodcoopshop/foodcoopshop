/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.AppChart = {

    barChartOptions : {
        tension : 0.3,
        datasetStrokeWidth : 1,
        scaleOverride: true,
        scaleStartValue: 0,
        scaleGridLineColor: 'rgba(0,0,0,.15)',
        tooltips: {
            callbacks: {
                label: function(item, data) {
                    var value = data.datasets[item.datasetIndex].data[item.index];
                    return foodcoopshop.Helper.formatFloatAsCurrency(value);
                }
            }
        },
        legend: {
            display: false
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false
                },
                maxBarThickness: 40,
                ticks: {
                    autoSkip: false,
                    maxRotation: 90,
                    minRotation: 90
                }
            }],
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: function(value, index, values) {
                        return foodcoopshop.Helper.formatFloatAsCurrency(value);
                    }
                }
            }]
        }
    },

    pieChartOptions : {
        cutoutPercentage: 25,
        rotation: 10,
        legend: {
            display: false
        },
        tooltips: {
            callbacks: {
                label: function(item, data) {
                    var label = data.labels[item.index];
                    var value = data.datasets[item.datasetIndex].data[item.index];
                    return label + ': ' + foodcoopshop.Helper.formatFloatAsCurrency(value);
                }
            }
        },
        pieceLabel: [
            {
                render: 'label',
                fontSize: 14,
                textShadow: true,
                position: 'outside',
                fontColor: '#333',
                textMargin: 4,
            },
            {
                render: 'percentage',
                fontSize: 14,
                fontColor: '#fff',
                textShadow: true,
            }

        ],
    },

    lineChartOptions : {
        legend: {
            display: false
        },
        tooltips: {
            callbacks: {
                label: function(item, data) {
                    var value = data.datasets[item.datasetIndex].data[item.index];
                    return foodcoopshop.Helper.formatFloatAsCurrency(value);
                }
            }
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false
                },
            }],
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: function(value, index, values) {
                        return foodcoopshop.Helper.formatFloatAsCurrency(value);
                    }
                }
            }]
        }
    },

    initLineChart : function(xAxisData, yAxisData) {

        var lineChartData = {
            labels: xAxisData,
            datasets: [{
                data: yAxisData,
                fill: false,
                borderColor: 'rgba(113,159,65,.5)',
                pointBorderColor: 'rgba(113,159,65,1)',
                pointBackgroundColor: 'rgba(113,159,65,1)',
                pointRadius: 5
            }]
        };

        var ctx = $('#myLineChart').get(0).getContext('2d');
        var myNewChart = new Chart(ctx, {
            responsive : true,
            type: 'line',
            data: lineChartData,
            options: this.lineChartOptions
        });

    },

    initLineChartDepositOverview : function(xAxisData1, xAxisData2, yAxisData, xAxisData1Label, xAxisData2Label) {

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
                }
            ]
        };

        var lineChartOptions = this.lineChartOptions;
        lineChartOptions.legend.display = true;

        var ctx = $('#myLineChart').get(0).getContext('2d');
        var myNewChart = new Chart(ctx, {
            responsive : true,
            type: 'line',
            data: lineChartData,
            options: lineChartOptions
        });

    },

    initBarChart : function(xAxisData, yAxisData) {

        var barChartData = {
            labels: xAxisData,
            datasets: [{
                data: yAxisData,
                backgroundColor: 'rgba(113,159,65,.7)',
                hoverBackgroundColor: 'rgba(113,159,65,.5)'
            }]
        };

        var ctx = $('#myBarChart').get(0).getContext('2d');
        var myNewChart = new Chart(ctx, {
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
                borderColor: '#fff',
                backgroundColor: backgroundColorPieChart,
                hoverBackgroundColor: 'rgba(113,159,65,1)',
                borderWidth: 1,
            }],
            labels: labelsPieChart,
        };

        var ctx = $('#myPieChart').get(0).getContext('2d');
        var myNewChart = new Chart(ctx, {
            responsive : true,
            type: 'pie',
            data: pieChartData,
            options: this.pieChartOptions
        });

    }

};