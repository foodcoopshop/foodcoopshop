<?php
namespace Admin\Controller;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;

/**
* FoodCoopShop - The open source software for your foodcoop
*
* Licensed under The MIT License
* For full copyright and license information, please see the LICENSE.txt
* Redistributions of files must retain the above copyright notice.
*
* @since         FoodCoopShop 1.1.0
* @license       https://opensource.org/licenses/mit-license.php MIT License
* @author        Mario Rothauer <office@foodcoopshop.com>
* @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
* @link          https://www.foodcoopshop.com
*/

class DepositsController extends AdminAppController
{

    public $manufacturerId;

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'overviewDiagram':
                return $this->AppAuth->isSuperadmin();
                break;
            case 'index':
            case 'detail':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
                break;
            case 'myIndex':
            case 'myDetail':
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
        $manufacturerId = '';
        if (!empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        } if ($this->manufacturerId > 0) {
            $manufacturerId = $this->manufacturerId;
        }
        return $manufacturerId;
    }

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
        $manufacturerDepositSumEmptyGlassesByCalendarWeek = $this->Payment->getManufacturerDepositSumEmptyGlassesByCalendarWeek();
        $preparedManufacturerData = [];
        foreach($manufacturerDepositSumEmptyGlassesByCalendarWeek as $week) {
            $week->YearWeekPrepared = str_replace('-', 'W', $week->YearWeek);
            $preparedManufacturerData[$week->YearWeek] = $week->SumAmount;
        }

        $customerDepositSumByCalendarWeek = $this->Payment->getCustomerDepositSumByCalendarWeek();
        $preparedCustomerData = [];
        foreach($customerDepositSumByCalendarWeek as $week) {
            $week->YearWeekPrepared = str_replace('-', 'W', $week->YearWeek);
            $preparedCustomerData[$week->YearWeek] = $week->SumAmount;
        }

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
        $yAxisDataLineChart= [];
        $manufacturerDepositSum = 0;
        $customerDepositSum = 0;
        $yearlyDeltas = [];
        foreach($allCalendarWeeksUntilNow as $calendarWeek) {

            $year = explode('-', $calendarWeek)[0];
            $yAxisDataLineChart[] = $calendarWeek;

            $manufacturerDeposit = $preparedManufacturerData[$calendarWeek] ?? $preparedManufacturerData[$calendarWeek] ?? 0;
            $xAxisData1LineChart[] = $manufacturerDeposit;
            $manufacturerDepositSum += $manufacturerDeposit;

            $customerDeposit = $preparedCustomerData[$calendarWeek] ?? $preparedCustomerData[$calendarWeek] ?? 0;
            $xAxisData2LineChart[]= $customerDeposit;
            $customerDepositSum += $customerDeposit;

            @$yearlyDeltas[$year] += $manufacturerDeposit - $customerDeposit;

        }

        $this->set('xAxisData1LineChart', $xAxisData1LineChart);
        $this->set('xAxisData2LineChart', $xAxisData2LineChart);
        $this->set('yAxisDataLineChart', $yAxisDataLineChart);

        $this->set('manufacturerDepositSum', $manufacturerDepositSum);
        $this->set('customerDepositSum', $customerDepositSum);
        $this->set('depositDelta', $manufacturerDepositSum - $customerDepositSum);
        $this->set('yearlyDeltas', $yearlyDeltas);

    }

    public function myIndex()
    {
        $this->manufacturerId = $this->AppAuth->getManufacturerId();
        $this->index();
        $this->render('index');
    }

    public function myDetail($monthAndYear)
    {
        $this->manufacturerId = $this->AppAuth->getManufacturerId();
        $this->detail($monthAndYear);
        $this->render('detail');
    }

    public function index()
    {
        $manufacturerId = $this->getManufacturerId();

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());
        $this->set('manufacturerId', $manufacturerId);

        if ($manufacturerId == '') {
            $this->set('title_for_layout', __d('admin', 'Deposit_account'));
            return;
        }

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();
        $this->set('manufacturer', $manufacturer);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Payment = $this->getTableLocator()->get('Payments');

        $orderStates = Configure::read('app.htmlHelper')->getOrderStateIds();
        $this->set('orderStates', $orderStates);

        $depositsDelivered = $this->OrderDetail->getDepositSum($manufacturerId, true);
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

        $title = 'Pfandkonto für ';
        if ($this->AppAuth->isManufacturer()) {
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

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturerId', $manufacturerId);

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
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
