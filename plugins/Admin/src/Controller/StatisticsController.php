<?php
namespace Admin\Controller;
use Cake\Core\Configure;

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

class StatisticsController extends AdminAppController
{

    public $manufacturerId;

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'index':
                return $this->AppAuth->isSuperadmin() || ($this->AppAuth->isAdmin() && Configure::read('app.showStatisticsForAdmins'));
                break;
            case 'myIndex':
                return $this->AppAuth->isManufacturer();
                break;
            default:
                return $this->AppAuth->isManufacturer();
                break;
        }
    }

    /**
     * $this->manufacturerId needs to be set in calling method
     * @return int
     */
    private function getManufacturerId()
    {
        $manufacturerId = 'all';
        if (!empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        }
        if ($this->manufacturerId > 0) {
            $manufacturerId = $this->manufacturerId;
        }
        return $manufacturerId;
    }

    public function myIndex()
    {
        $this->manufacturerId = $this->AppAuth->getManufacturerId();
        $this->index();
        $this->render('index');
    }

    public function index()
    {
        $manufacturerId = $this->getManufacturerId();

        $year = '';
        if (!empty($this->getRequest()->getQuery('year'))) {
            $year = h($this->getRequest()->getQuery('year'));
        }
        $this->set('year', $year);

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $manufacturersForDropdown = [];
        if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
            $manufacturersForDropdown = ['all' => __d('admin', 'All_manufacturers')];
        }
        $manufacturersForDropdown = array_merge($manufacturersForDropdown, $this->Manufacturer->getForDropdown());
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->set('manufacturerId', $manufacturerId);

        if ($manufacturerId == '') {
            $this->set('title_for_layout', __d('admin', 'Turnover_statistics'));
            return;
        }

        $conditions = [];
        if ($manufacturerId != 'all') {
            $conditions['Manufacturers.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Manufacturers.id_manufacturer > 0';
        }

        $manufacturers = $this->Manufacturer->find('all', [
            'conditions' => $conditions
        ])->toArray();
        $this->set('manufacturers', $manufacturers);

        $titleForLayout = __d('admin', 'Turnover_statistics');
        if ($manufacturerId != 'all') {
            $titleForLayout .=  ' ' . $manufacturers[0]->name;
        }
        $this->set('title_for_layout', $titleForLayout);

        $this->set('years', Configure::read('app.timeHelper')->getAllYearsUntilThisYear(date('Y'), 2014));

        $excludeMemberFeeCondition = [];
        if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
            $excludeMemberFeeCondition = [
                'OrderDetails.product_id NOT IN' => explode(',', Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS'))
            ];
        }

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $monthlySumProducts = $this->OrderDetail->getMonthlySumProductByManufacturer($manufacturerId, $year);
        if (!empty($excludeMemberFeeCondition)) {
            $monthlySumProducts->where($excludeMemberFeeCondition);
        }
        if (empty($monthlySumProducts->toArray())) {
            $this->set('xAxisData', []);
            return;
        }

        $monthsAndYear = Configure::read('app.timeHelper')->getAllMonthsUntilThisYear(date('Y'), 2014);

        $monthsWithTurnoverMonthAndYear = $monthlySumProducts->extract('MonthAndYear')->toArray();
        $monthsWithTurnoverSumTotalPaid = $monthlySumProducts->extract('SumTotalPaid')->toArray();

        $xAxisData = array_values($monthsAndYear);
        $yAxisData = [];

        foreach($monthsAndYear as $monthKey => $monthString) {
            $foundIndex = array_search($monthKey, $monthsWithTurnoverMonthAndYear);
            if ($foundIndex !== false) {
                $yAxisData[] = $monthsWithTurnoverSumTotalPaid[$foundIndex];
            } else {
                $yAxisData[] = 0;
            }
        }

        $xAxisDataWithYearSeparators = [];
        $yAxisDataWithYearSeparators = [];
        foreach($xAxisData as $i => $x) {
            $xAxisDataWithYearSeparators[] = $x;
            $yAxisDataWithYearSeparators[] = $yAxisData[$i];
            if (preg_match('/'.__d('admin', 'December').'/', $x)) {
                $xAxisDataWithYearSeparators[] = '';
                $yAxisDataWithYearSeparators[] = 0;
            }
        }

        $firstIndexWithValue = 0;
        foreach($yAxisDataWithYearSeparators as $index => $y) {
            if ($y > 0) {
                $firstIndexWithValue = $index;
                break;
            }
        }

        $lastIndexWithValue = 0;
        $reversedYAxisDate = array_reverse($yAxisDataWithYearSeparators);
        foreach($reversedYAxisDate as $index => $y) {
            if ($y > 0) {
                $lastIndexWithValue = $index;
                break;
            }
        }

        $xAxisDataWithYearSeparators = array_splice($xAxisDataWithYearSeparators, $firstIndexWithValue, $lastIndexWithValue * -1);
        $yAxisDataWithYearSeparators = array_splice($yAxisDataWithYearSeparators, $firstIndexWithValue, $lastIndexWithValue * -1);

        $this->set('xAxisDataBarChart', $xAxisDataWithYearSeparators);
        $this->set('yAxisDataBarChart', $yAxisDataWithYearSeparators);
        $this->set('totalTurnover', array_sum($monthsWithTurnoverSumTotalPaid));
        $this->set('averageTurnover', array_sum($monthsWithTurnoverSumTotalPaid) / count($monthsWithTurnoverMonthAndYear));

        // START prepare line chart
        if ($year == '') {
            $xAxisDataLineChart = [];
            $yAxisDataLineChart = [];
            $i = 0;
            $j = 0;
            foreach($xAxisDataWithYearSeparators as $x) {
                $yearFromString = (int) substr($x, -4);
                if (!in_array($yearFromString, $xAxisDataLineChart) && $yearFromString > 0) {
                    $xAxisDataLineChart[] = $yearFromString;
                    $i++;
                }
                if (!isset($yAxisDataLineChart[$i-1])) {
                    $yAxisDataLineChart[$i-1] = 0;
                }
                $yAxisDataLineChart[$i-1] += $yAxisDataWithYearSeparators[$j];
                $j++;
            }

            $this->set('xAxisDataLineChart', $xAxisDataLineChart);
            $this->set('yAxisDataLineChart', $yAxisDataLineChart);
        }
        // END prepare line chart


        // START prepare pie chart
        if ($manufacturerId == 'all') {
            $data = [];
            foreach($manufacturers as $manufacturer) {

                $monthlySumProductsQuery = $this->OrderDetail->getMonthlySumProductByManufacturer($manufacturer->id_manufacturer, $year);
                if (!empty($excludeMemberFeeCondition)) {
                    $monthlySumProductsQuery->where($excludeMemberFeeCondition);
                }
                $monthlySumProducts = 0;
                foreach($monthlySumProductsQuery as $monthlySum) {
                    $monthlySumProducts += $monthlySum->SumTotalPaid;
                }

                if ($monthlySumProducts == 0) {
                    continue;
                }

                $preparedData = [];
                $preparedData['sum'] = $monthlySumProducts;
                $preparedData['label'] = html_entity_decode($manufacturer->name);
                $data[] = $preparedData;

            }

            arsort($data);

            $labelsPieChart = [];
            $dataPieChart = [];
            $backgroundColorPieChart = [];
            $i = 0;
            foreach($data as $d) {
                $i++;
                $labelsPieChart[] = $d['label'];
                $dataPieChart[] = $d['sum'];
                $opacity = 100 - ($i * 4);
                if ($opacity < 20) {
                    $opacity = 20;
                }
                $backgroundColorPieChart[] = 'rgba(113,159,65,0.'.$opacity.')';
            }

            // start retrieving data for pie chart
            $this->set('labelsPieChart', $labelsPieChart);
            $this->set('dataPieChart', $dataPieChart);
            $this->set('backgroundColorPieChart', $backgroundColorPieChart);
        }
        // END prepare pie chart

    }
}
