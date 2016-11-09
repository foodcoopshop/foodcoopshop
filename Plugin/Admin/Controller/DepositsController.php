<?php
/**
 * DepositsController
*
* FoodCoopShop - The open source software for your foodcoop
*
* Licensed under The MIT License
* For full copyright and license information, please see the LICENSE.txt
* Redistributions of files must retain the above copyright notice.
*
* @since         FoodCoopShop 1.1.0
* @license       http://www.opensource.org/licenses/mit-license.php MIT License
* @author        Mario Rothauer <office@foodcoopshop.com>
* @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
* @link          https://www.foodcoopshop.com
*/
class DepositsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isManufacturer();
    }

    public function index()
    {

        $this->loadModel('OrderDetail');
        $this->loadModel('CakePayment');
        
        $orderState = Configure::read('htmlHelper')->getOrderStateIdsAsCsv();
        $this->set('orderState', $orderState);
        
        $depositsDelivered = $this->OrderDetail->getDepositSum($this->AppAuth->getManufacturerId(), true);
        $depositsReturned = $this->CakePayment->getMonthlyDepositSumByManufacturer($this->AppAuth->getManufacturerId(), true);
        
        // TODO add year 2020 on 31.12.2019
        $monthsAndYear = Configure::read('timeHelper')->getAllMonthsForYear(2019);
        $monthsAndYear = array_merge(Configure::read('timeHelper')->getAllMonthsForYear(2018), $monthsAndYear);
        $monthsAndYear = array_merge(Configure::read('timeHelper')->getAllMonthsForYear(2017), $monthsAndYear);
        $monthsAndYear = array_merge(Configure::read('timeHelper')->getAllMonthsForYear(2016), $monthsAndYear);
        $monthsAndYear = array_reverse($monthsAndYear);
        
        $deposits = array();
        $sumDepositsDelivered = 0;
        $sumDepositsReturned = 0;
        foreach($monthsAndYear as $monthAndYear => $monthAndYearAsString) {
            $recordFound = false;
            foreach($depositsDelivered as $depositDelivered) {
                if ($depositDelivered[0]['monthAndYear'] == $monthAndYear) {
                    $deliveredValue = $depositDelivered[0]['sumDepositDelivered'];
                    if ($deliveredValue > 0) {
                        $deposits[$monthAndYear]['delivered'] = $deliveredValue;
                        $sumDepositsDelivered += $deliveredValue;
                        $recordFound = true;
                    }
                    continue;
                }
            }
            foreach($depositsReturned as $depositReturned) {
                if ($depositReturned[0]['monthAndYear'] == $monthAndYear) {
                    $returnValue = $depositReturned[0]['sumDepositReturned'] * -1;
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
                $deposits[$monthAndYear]['dateFrom'] = '01.' . Configure::read('htmlHelper')->addLeadingZero($month) . '.' . $year;
                $deposits[$monthAndYear]['dateTo'] = Configure::read('timeHelper')->getLastDayOfGivenMonth($monthAndYear) . '.' . Configure::read('htmlHelper')->addLeadingZero($month) . '.' . $year;
            }
            
        }
        
        $this->set('sumDepositsDelivered', $sumDepositsDelivered);
        $this->set('sumDepositsReturned', $sumDepositsReturned);
        $this->set('deposits', $deposits);
        
        $this->set('title_for_layout', 'Pfand-Übersicht');
        
    }

}

?>