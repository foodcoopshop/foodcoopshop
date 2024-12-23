<?php
declare(strict_types=1);

namespace Admin\Controller;

use Admin\Traits\ManufacturerIdTrait;
use App\Model\Table\OrderDetailsTable;
use App\Model\Table\PaymentsTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use App\Model\Entity\Payment;

/**
* FoodCoopShop - The open source software for your foodcoop
*
* Licensed under the GNU Affero General Public License version 3
* For full copyright and license information, please see LICENSE
* Redistributions of files must retain the above copyright notice.
*
* @since         FoodCoopShop 1.1.0
* @license       https://opensource.org/licenses/AGPL-3.0
* @author        Mario Rothauer <office@foodcoopshop.com>
* @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
* @link          https://www.foodcoopshop.com
*/

class DepositsController extends AdminAppController
{

    use ManufacturerIdTrait;

    protected PaymentsTable $Payment;
    protected OrderDetailsTable $OrderDetail;

    public $manufacturerId;

    public function overviewDiagram()
    {
        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $this->set('title_for_layout', __d('admin', 'Deposit_overview'));

        $this->Payment = $this->getTableLocator()->get('Payments');
        $manufacturerDepositSumEmptyGlassesByCalendarWeek = $this->Payment->getManufacturerDepositSumByCalendarWeekAndType(Payment::TEXT_EMPTY_GLASSES);
        $preparedManufacturerEmptyGlassesData = [];
        foreach($manufacturerDepositSumEmptyGlassesByCalendarWeek as $week) {
            $week->YearWeekPrepared = str_replace('-', 'W', $week->YearWeek);
            $preparedManufacturerEmptyGlassesData[$week->YearWeek] = $week->SumAmount;
        }

        $manufacturerDepositSumMoneyByCalendarWeek = $this->Payment->getManufacturerDepositSumByCalendarWeekAndType(Payment::TEXT_MONEY);
        $preparedManufacturerMoneyData = [];
        foreach($manufacturerDepositSumMoneyByCalendarWeek as $week) {
            $week->YearWeekPrepared = str_replace('-', 'W', $week->YearWeek);
            $preparedManufacturerMoneyData[$week->YearWeek] = $week->SumAmount;
        }

        $customerDepositSumByCalendarWeek = $this->Payment->getCustomerDepositSumByCalendarWeek();
        $preparedCustomerData = [];
        foreach($customerDepositSumByCalendarWeek as $week) {
            $week->YearWeekPrepared = str_replace('-', 'W', $week->YearWeek);
            $preparedCustomerData[$week->YearWeek] = $week->SumAmount;
        }

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $depositsDeliveredByYear = $this->OrderDetail->getDepositSum(false, 'year');

        if (empty($manufacturerDepositSumEmptyGlassesByCalendarWeek) && empty($customerDepositSumByCalendarWeek)) {
            return;
        }

        if (empty($customerDepositSumByCalendarWeek)) {
            $firstWeek = strtotime($manufacturerDepositSumEmptyGlassesByCalendarWeek[0]->YearWeekPrepared);
        }
        if (empty($manufacturerDepositSumEmptyGlassesByCalendarWeek)) {
            $firstWeek = strtotime($customerDepositSumByCalendarWeek[0]->YearWeekPrepared);
        }

        if (!isset($firstWeek)) {
            $firstWeek = strtotime($customerDepositSumByCalendarWeek[0]->YearWeekPrepared);
            if (strtotime($customerDepositSumByCalendarWeek[0]->YearWeekPrepared) > strtotime($manufacturerDepositSumEmptyGlassesByCalendarWeek[0]->YearWeekPrepared)) {
                $firstWeek = strtotime($manufacturerDepositSumEmptyGlassesByCalendarWeek[0]->YearWeekPrepared);
            }
        }

        $allCalendarWeeksUntilNow = Configure::read('app.timeHelper')->getAllCalendarWeeksUntilNow($firstWeek);
        $xAxisData1LineChart = [];
        $xAxisData2LineChart = [];
        $xAxisData3LineChart = [];
        $yAxisDataLineChart= [];
        $manufacturerEmptyGlassesSum = 0;
        $manufacturerMoneySum = 0;
        $yearlyManufacturerEmptyGlasses = [];
        $yearlyManufacturerMoney = [];
        $yearlyDepositsDelivered = [];
        $yearlyOverallDeltas = [];
        $years = [];

        foreach($allCalendarWeeksUntilNow as $calendarWeek) {

            $year = explode('-', $calendarWeek)[0];
            $years[] = $year;

            $yAxisDataLineChart[] = $calendarWeek;

            $manufacturerEmptyGlasses = $preparedManufacturerEmptyGlassesData[$calendarWeek] ?? 0;
            $xAxisData1LineChart[] = $manufacturerEmptyGlasses;

            $customerDeposit = $preparedCustomerData[$calendarWeek] ?? 0;
            $xAxisData2LineChart[]= $customerDeposit;

            $manufacturerMoney = $preparedManufacturerMoneyData[$calendarWeek] ?? 0;
            $xAxisData3LineChart[] = $manufacturerMoney;

            if (!isset($yearlyManufacturerEmptyGlasses[$year])) {
                $yearlyManufacturerEmptyGlasses[$year] = 0;
            }
            if (!isset($yearlyManufacturerMoney[$year])) {
                $yearlyManufacturerMoney[$year] = 0;
            }
            if (!isset($yearlyOverallDeltas[$year])) {
                $yearlyOverallDeltas[$year] = 0;
            }
            $yearlyManufacturerEmptyGlasses[$year] += $manufacturerEmptyGlasses;
            $yearlyManufacturerMoney[$year] += $manufacturerMoney;

            $yearlyOverallDeltas[$year] += $manufacturerEmptyGlasses + $manufacturerMoney;

        }

        $depositsDeliveredSum = 0;
        foreach($depositsDeliveredByYear as $depositDelivered) {
            $year = $depositDelivered['Year'];
            if (!isset($yearlyDepositsDelivered[$year])) {
                $yearlyDepositsDelivered[$year] = 0;
            }
            if (!isset($yearlyOverallDeltas[$year])) {
                $yearlyOverallDeltas[$year] = 0;
            }
            $yearlyDepositsDelivered[$year] = $depositDelivered['sumDepositDelivered'];
            $yearlyOverallDeltas[$year] -= $depositDelivered['sumDepositDelivered'];
        }

        $this->set('xAxisData1LineChart', $xAxisData1LineChart);
        $this->set('xAxisData2LineChart', $xAxisData2LineChart);
        $this->set('xAxisData3LineChart', $xAxisData3LineChart);
        $this->set('yAxisDataLineChart', $yAxisDataLineChart);

        $manufacturerEmptyGlassesSum = array_sum($preparedManufacturerEmptyGlassesData);
        $this->set('manufacturerEmptyGlassesSum', $manufacturerEmptyGlassesSum);
        $manufacturerMoneySum = array_sum($preparedManufacturerMoneyData);
        $this->set('manufacturerMoneySum', $manufacturerMoneySum);
        $depositsDeliveredSum = array_sum($yearlyDepositsDelivered);
        $this->set('depositsDeliveredSum', $depositsDeliveredSum);
        $overallDeltaSum = ($depositsDeliveredSum - $manufacturerEmptyGlassesSum - $manufacturerMoneySum) * -1;
        $this->set('overallDeltaSum', $overallDeltaSum);

        $this->set('yearlyManufacturerEmptyGlasses', $yearlyManufacturerEmptyGlasses);
        $this->set('yearlyManufacturerMoney', $yearlyManufacturerMoney);
        $this->set('yearlyDepositsDelivered', $yearlyDepositsDelivered);
        $this->set('yearlyOverallDeltas', $yearlyOverallDeltas);

        $this->set('years', array_unique($years));

        $customersTable = $this->getTableLocator()->get('Customers');
        $paymentDepositDelta = $customersTable->getDepositBalanceForCustomers(APP_ON);
        $paymentDepositDelta += $customersTable->getDepositBalanceForCustomers(APP_OFF);
        $paymentDepositDelta += $customersTable->getDepositBalanceForDeletedCustomers();
        $paymentDepositDelta = $paymentDepositDelta * -1 - $manufacturerMoneySum;
        $this->set('paymentDepositDelta', $paymentDepositDelta);

        $differenceToOpenDepositDemands = $overallDeltaSum + $paymentDepositDelta;
        $this->set('differenceToOpenDepositDemands', $differenceToOpenDepositDemands);

    }

    public function myIndex()
    {
        $this->manufacturerId = $this->identity->getManufacturerId();
        $this->index();
        $this->render('index');
    }

    public function myDetail($monthAndYear)
    {
        $this->manufacturerId = $this->identity->getManufacturerId();
        $this->detail($monthAndYear);
        $this->render('detail');
    }

    public function index()
    {
        $manufacturerId = $this->getManufacturerId();
        if ($manufacturerId == 'all') {
            $manufacturerId = '';
        }

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $manufacturersTable->getForDropdown());
        $this->set('manufacturerId', $manufacturerId);

        if ($manufacturerId == '') {
            $this->set('title_for_layout', __d('admin', 'Deposit_account'));
            return;
        }

        $manufacturer = $manufacturersTable->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();
        $this->set('manufacturer', $manufacturer);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Payment = $this->getTableLocator()->get('Payments');

        $orderStates = Configure::read('app.htmlHelper')->getOrderStateIds();
        $this->set('orderStates', $orderStates);

        $depositsDelivered = $this->OrderDetail->getDepositSum($manufacturerId, 'month');
        $depositsReturned = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturerId, true);

        $monthsAndYear = Configure::read('app.timeHelper')->getAllMonthsUntilThisYear(date('Y'), 2016);
        $monthsAndYear = array_reverse($monthsAndYear);

        $deposits = [];
        $sumDepositsDelivered = 0;
        $sumDepositsReturned = 0;
        foreach ($monthsAndYear as $monthAndYear => $monthAndYearAsString) {
            $recordFound = false;
            foreach ($depositsDelivered as $depositDelivered) {
                if ($depositDelivered['monthAndYear'] == $monthAndYear) {
                    $deliveredValue = $depositDelivered['sumDepositDelivered'];
                    if ($deliveredValue > 0) {
                        $deposits[$monthAndYear]['delivered'] = $deliveredValue;
                        $sumDepositsDelivered += $deliveredValue;
                        $recordFound = true;
                    }
                    continue;
                }
            }
            foreach ($depositsReturned as $depositReturned) {
                if ($depositReturned['monthAndYear'] == $monthAndYear) {
                    $returnValue = $depositReturned['sumDepositReturned'] * -1;
                    $deposits[$monthAndYear]['returned'] = $returnValue;
                    $sumDepositsReturned += $returnValue;
                    $recordFound = true;
                    continue;
                }
            }

            if (!$recordFound) {
                // remove empty months
                unset($monthsAndYear[$monthAndYear]);
            } else {
                $deposits[$monthAndYear]['monthAndYearAsString'] = $monthAndYearAsString;
                $monthAndYearExploded = explode('-', $monthAndYear);
                $year  = $monthAndYearExploded[0];
                $month = $monthAndYearExploded[1];
                $deposits[$monthAndYear]['dateFrom'] = '01.' . Configure::read('app.htmlHelper')->addLeadingZero($month) . '.' . $year;
                $deposits[$monthAndYear]['dateTo'] = Configure::read('app.timeHelper')->getLastDayOfGivenMonth($monthAndYear) . '.' . Configure::read('app.htmlHelper')->addLeadingZero($month) . '.' . $year;
            }
        }

        $this->set('sumDepositsDelivered', $sumDepositsDelivered);
        $this->set('sumDepositsReturned', $sumDepositsReturned);
        $this->set('deposits', $deposits);

        $title = __d('admin', 'Deposit_account') . ' ' . __d('admin', 'for') . ' ';
        if ($this->identity->isManufacturer()) {
            $title .= $manufacturer->name;
        }
        $this->set('title_for_layout', $title);
    }

    /**
     * @param string $monthAndYear
     */
    public function detail($monthAndYear)
    {

        $manufacturerId = $this->getManufacturerId();

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturerId', $manufacturerId);

        $manufacturer = $manufacturersTable->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();
        $this->set('manufacturer', $manufacturer);

        $this->Payment = $this->getTableLocator()->get('Payments');
        $payments = $this->Payment->getManufacturerDepositsByMonth($manufacturerId, $monthAndYear);

        $this->set('payments', $payments);

        if ($monthAndYear == '') {
            throw new RecordNotFoundException('monthAndYear missing');
        }
        $monthAndYearExploded = explode('-', $monthAndYear);
        $year  = $monthAndYearExploded[0];
        $month = $monthAndYearExploded[1];
        $this->set('month', $month);
        $this->set('year', $year);
        $this->set('title_for_layout', __d('admin', 'Deposit_take_back_detail_for') . ' ' . $manufacturer->name);
    }
}
