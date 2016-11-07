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
        
        $depositsDelivered = $this->OrderDetail->getDepositSum($this->AppAuth->getManufacturerId());
        $depositsReturned = $this->CakePayment->getMonthlyDepositSumByManufacturer($this->AppAuth->getManufacturerId());
        
        $months = Configure::read('timeHelper')->getAllMonthsForYear(2017);
        $months = array_merge(Configure::read('timeHelper')->getAllMonthsForYear(2016), $months);
        $months = array_reverse($months);
        
        $deposits = array();
        $sumDepositsDelivered = 0;
        $sumDepositsReturned = 0;
        foreach($months as $month => $monthAsString) {
            $recordFound = false;
            foreach($depositsDelivered as $depositDelivered) {
                if ($depositDelivered[0]['month'] == $month) {
                    $deliveredValue = $depositDelivered[0]['sumDepositDelivered'];
                    $deposits[$month]['delivered'] = $deliveredValue;
                    $sumDepositsDelivered += $deliveredValue;
                    $recordFound = true;
                    continue;
                }
            }
            foreach($depositsReturned as $depositReturned) {
                if ($depositReturned[0]['month'] == $month) {
                    $returnValue = $depositReturned[0]['sumDepositReturned'] * -1;
                    $deposits[$month]['returned'] = $returnValue;
                    $sumDepositsReturned += $returnValue;
                    $recordFound = true;
                    continue;
                }
            }
            
            if (!$recordFound) {
                // remove empty months
                unset($months[$month]);
            } else {
                $deposits[$month]['monthName'] = $monthAsString;
            }
            
        }
        
        $this->set('sumDepositsDelivered', $sumDepositsDelivered);
        $this->set('sumDepositsReturned', $sumDepositsReturned);
        $this->set('deposits', $deposits);
        
        $this->set('title_for_layout', 'Pfand-Übersicht');
        
    }

}

?>