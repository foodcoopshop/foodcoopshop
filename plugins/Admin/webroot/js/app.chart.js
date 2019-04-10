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
        legend: {
            display: false
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false
                }   
            }],
            yAxes: []
        }        
    },    
        
    init : function(xAxisData, yAxisData) {
        
        var ctx = $("#myChart").get(0).getContext("2d");
        xAxisData = $.parseJSON(xAxisData);
        yAxisData = $.parseJSON(yAxisData);
        
//        for(y in yAxis) {
//            yAxis[y] = foodcoopshop.Helper.getStringAsFloat(yAxis[y]);
//        }
//        console.log(yAxis);
        
        var barChartData = {
            labels: xAxisData,
            datasets: [{
                data: yAxisData,
                backgroundColor: 'rgba(113,159,65,.7)',
            }]
        };

        var ctx = $("#myChart").get(0).getContext("2d");
          var myNewChart = new Chart(ctx, {
          type: 'bar',
          data: barChartData,
          options: this.chartOptions
       });        
    }
    
};