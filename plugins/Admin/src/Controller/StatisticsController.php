<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Core\Configure;
use Admin\Traits\ManufacturerIdTrait;

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

class StatisticsController extends AdminAppController
{

    use ManufacturerIdTrait;

    public function myIndex()
    {
        $this->manufacturerId = $this->identity->getManufacturerId();
        $this->index();
        $this->render('index');
    }

    public function index()
    {
        $manufacturerId = (string) $this->getManufacturerId();

        $range = '';
        if (in_array('range', array_keys($this->getRequest()->getQueryParams()))) {
            $range = h($this->getRequest()->getQuery('range'));
        }
        $this->set('range', $range);

        $year = null;
        $lastMonths = null;
        if (preg_match('`year-`', $range)) {
            $year = preg_replace('`year-`', '', $range);
        }
        if (preg_match('`last-months-`', $range)) {
            $lastMonths = preg_replace('`last-months-`', '', $range);
            if (!in_array($lastMonths, [12,24])) {
                throw new \Exception($lastMonths . ' not valid as last-months parameter');
            }
        }

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $manufacturersForDropdown = [];
        if ($this->identity->isSuperadmin() || $this->identity->isAdmin()) {
            $manufacturersForDropdown = ['all' => __d('admin', 'All_manufacturers')];
        }
        $manufacturersForDropdown = array_merge($manufacturersForDropdown, $manufacturersTable->getForDropdown());
        $this->set('manufacturersForDropdown', $manufacturersForDropdown);
        $this->set('manufacturerId', $manufacturerId);

        $titleForLayout = Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') ? __d('admin', 'Turnover_and_profit_statistics') : __d('admin', 'Turnover_statistics');
        if ($manufacturerId == '') {
            $this->set('title_for_layout', $titleForLayout);
            return;
        }

        $conditions = [];
        if ($manufacturerId != 'all') {
            $conditions['Manufacturers.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Manufacturers.id_manufacturer > 0';
        }

        $manufacturers = $manufacturersTable->find('all', conditions: $conditions)->toArray();
        $this->set('manufacturers', $manufacturers);

        if ($manufacturerId != 'all') {
            $titleForLayout .=  ': ' . $manufacturers[0]->name;
        }
        $this->set('title_for_layout', $titleForLayout);

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $firstOrderYear = $orderDetailsTable->getFirstOrderYear($manufacturerId);
        $lastOrderYear = $orderDetailsTable->getLastOrderYear($manufacturerId);

        $rangesForDropdown = [
            '' => __d('admin', 'Total'),
            'last-months-12' => __d('admin', 'Last_{0}_months', [12]),
            'last-months-24' => __d('admin', 'Last_{0}_months', [24]),
        ];
        if ($lastOrderYear !== false && $firstOrderYear !== false) {
            $allYears = Configure::read('app.timeHelper')->getAllYearsUntilThisYear($lastOrderYear, $firstOrderYear);
            foreach($allYears as $y) {
                $rangesForDropdown['year-' . $y] = $y;
            }
        }
        $this->set('ranges', $rangesForDropdown);

        $excludeMemberFeeCondition = [];
        if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
            $excludeMemberFeeCondition = [
                'OrderDetails.product_id NOT IN' => explode(',', Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS'))
            ];
        }

        if ($lastMonths !== null) {
            $monthlySumProducts = $orderDetailsTable->getMonthlySumProductByManufacturer($manufacturerId, '');
            $firstDayOfLastOrderMonth = $orderDetailsTable->getFirstDayOfLastOrderMonth($manufacturerId);
            $monthlySumProducts = $orderDetailsTable->addLastMonthsCondition($monthlySumProducts, $firstDayOfLastOrderMonth, $lastMonths);
        } else {
            $monthlySumProducts = $orderDetailsTable->getMonthlySumProductByManufacturer($manufacturerId, $year);
        }

        if (!empty($excludeMemberFeeCondition)) {
            $monthlySumProducts->where($excludeMemberFeeCondition);
        }
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $monthlySumProducts->contain(['OrderDetailPurchasePrices']);
            $monthlySumProducts->select(['SumNetProfit' => 'SUM(OrderDetails.total_price_tax_excl) - SUM(OrderDetailPurchasePrices.total_price_tax_excl)']);
            $monthlySumProducts->select(['Surcharge' => '(SUM(OrderDetails.total_price_tax_excl) / SUM(OrderDetailPurchasePrices.total_price_tax_excl) * 100) - 100']);
            $monthlySumProducts->select(['SumTotalPaid' => $monthlySumProducts->func()->sum('OrderDetailPurchasePrices.total_price_tax_excl')]);
        }

        if (empty($monthlySumProducts->toArray())) {
            $this->set('xAxisData', []);
            return;
        }

        $monthsAndYear = Configure::read('app.timeHelper')->getAllMonthsUntilThisYear($lastOrderYear, $firstOrderYear);

        $monthsWithTurnoverMonthAndYear = $monthlySumProducts->all()->extract('MonthAndYear')->toArray();
        $monthsWithTurnoverSumTotalPaid = $monthlySumProducts->all()->extract('SumTotalPaid')->toArray();

        $monthsWithTurnoverSumNetProfit = $monthlySumProducts->all()->extract('SumTotalPaid')->toArray(); // dummy data which is not used
        $monthsWithTurnoverSurcharge = $monthlySumProducts->all()->extract('SumTotalPaid')->toArray(); // dummy data which is not used

        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $monthsWithTurnoverSumNetProfit = $monthlySumProducts->all()->extract('SumNetProfit')->toArray();
            $monthsWithTurnoverSurcharge = $monthlySumProducts->all()->extract('Surcharge')->toArray();
        }

        $xAxisData = array_values($monthsAndYear);
        $yAxisData = [];
        $yAxisData2 = [];
        $yAxisData3 = [];

        foreach($monthsAndYear as $monthKey => $monthString) {
            $foundIndex = array_search($monthKey, $monthsWithTurnoverMonthAndYear);
            if ($foundIndex !== false) {
                $yAxisData[] = $monthsWithTurnoverSumTotalPaid[$foundIndex];
                $yAxisData2[] = $monthsWithTurnoverSumNetProfit[$foundIndex];
                $yAxisData3[] = $monthsWithTurnoverSurcharge[$foundIndex];
            } else {
                $yAxisData[] = 0;
                $yAxisData2[] = 0;
                $yAxisData3[] = 'NaN';
            }
        }

        $xAxisDataWithYearSeparators = [];
        $yAxisDataWithYearSeparators = [];
        $yAxisData2WithYearSeparators = [];
        $yAxisData3WithYearSeparators = [];
        foreach($xAxisData as $i => $x) {
            $xAxisDataWithYearSeparators[] = $x;
            $yAxisDataWithYearSeparators[] = $yAxisData[$i];
            $yAxisData2WithYearSeparators[] = $yAxisData2[$i];
            $yAxisData3WithYearSeparators[] = $yAxisData3[$i];
            if (preg_match('/'.__d('admin', 'December').'/', $x)) {
                $xAxisDataWithYearSeparators[] = '';
                $yAxisDataWithYearSeparators[] = 0;
                $yAxisData2WithYearSeparators[] = 0;
                $yAxisData3WithYearSeparators[] = 'NaN';
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
        $yAxisData2WithYearSeparators = array_splice($yAxisData2WithYearSeparators, $firstIndexWithValue, $lastIndexWithValue * -1);
        $yAxisData3WithYearSeparators = array_splice($yAxisData3WithYearSeparators, $firstIndexWithValue, $lastIndexWithValue * -1);
        $this->set('xAxisDataBarChart', $xAxisDataWithYearSeparators);
        $this->set('yAxisDataBarChart', $yAxisDataWithYearSeparators);

        $totalNetProfit = array_sum($monthsWithTurnoverSumNetProfit);
        $totalTurnover = array_sum($monthsWithTurnoverSumTotalPaid);

        if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $yAxisData2WithYearSeparators = 0;
            $yAxisData3WithYearSeparators = 0;
        } else {
            $totalNetTurnover = $totalTurnover + $totalNetProfit;
            $this->set('totalNetTurnover', $totalNetTurnover);
            $purchasePriceProductsTable = $this->getTableLocator()->get('PurchasePriceProducts');
            $averageSurcharge = $purchasePriceProductsTable->calculateSurchargeBySellingPriceNet($totalNetTurnover, $totalTurnover);
            $this->set('averageSurcharge', $averageSurcharge);
        }

        $this->set('yAxisData2BarChart', $yAxisData2WithYearSeparators);
        $this->set('yAxisData3BarChart', $yAxisData3WithYearSeparators);
        $this->set('totalTurnover', $totalTurnover);
        $this->set('averageTurnover', array_sum($monthsWithTurnoverSumTotalPaid) / count($monthsWithTurnoverMonthAndYear));
        $this->set('totalNetProfit', $totalNetProfit);
        $this->set('averageNetProfit', array_sum($monthsWithTurnoverSumNetProfit) / count($monthsWithTurnoverMonthAndYear));

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

                if ($lastMonths !== null) {
                    $monthlySumProductsQuery = $orderDetailsTable->getMonthlySumProductByManufacturer($manufacturer->id_manufacturer, $year);
                    /** @phpstan-ignore-next-line */
                    $monthlySumProductsQuery = $orderDetailsTable->addLastMonthsCondition($monthlySumProductsQuery, $firstDayOfLastOrderMonth, $lastMonths);
                } else {
                    $monthlySumProductsQuery = $orderDetailsTable->getMonthlySumProductByManufacturer($manufacturer->id_manufacturer, $year);
                }

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
                $preparedData['label'] = $manufacturer->decoded_name;
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
                $backgroundColorPieChart[] = Configure::read('app.customThemeMainColor') . dechex((int) ($opacity * 2.56));
            }

            // start retrieving data for pie chart
            $this->set('labelsPieChart', $labelsPieChart);
            $this->set('dataPieChart', $dataPieChart);
            $this->set('backgroundColorPieChart', $backgroundColorPieChart);
        }
        // END prepare pie chart

    }
}
