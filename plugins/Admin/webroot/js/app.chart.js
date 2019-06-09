/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.AppChart = {
    
    chartOptions : {
        tension : 0.3,
        datasetStrokeWidth : 1,
        scaleOverride: true,
        scaleStartValue: 0,
        scaleGridLineColor: 'rgba(0,0,0,.15)',
        animation: false,
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
        
    init : function(xAxisData, yAxisData) {
        
        var ctx = $('#myChart').get(0).getContext('2d');
        xAxisData = $.parseJSON(xAxisData);
        yAxisData = $.parseJSON(yAxisData);
        
        var barChartData = {
            labels: xAxisData,
            datasets: [{
                data: yAxisData,
                backgroundColor: 'rgba(113,159,65,.7)',
                hoverBackgroundColor: 'rgba(113,159,65,.5)'
            }]
        };

        var ctx = $('#myChart').get(0).getContext('2d');
        var myNewChart = new Chart(ctx, {
            responsive : true,
            type: 'bar',
            data: barChartData,
            options: this.chartOptions
        });        
    }
    
};