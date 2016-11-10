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

    public $manufacturerId;
    
    public function isAuthorized($user)
    {
        switch ($this->action) {
            case 'index':
            case 'detail':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
                break;
            case 'my_index':
            case 'my_detail':
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
        if (isset($this->request->named['manufacturerId'])) {
            $manufacturerId = $this->request->named['manufacturerId'];
        } if ($this->manufacturerId > 0) {
            $manufacturerId = $this->manufacturerId;
        }
        return $manufacturerId;
    }
    
    public function my_index()
    {
        $this->manufacturerId = $this->AppAuth->getManufacturerId();
        $this->index();
        $this->render('index');
    }
    
    public function my_detail($monthAndYear)
    {
        $this->manufacturerId = $this->AppAuth->getManufacturerId(); 
        $this->detail($monthAndYear);
        $this->render('detail');
    }
    
    public function index()
    {
        $manufacturerId = $this->getManufacturerId();
        
        $this->loadModel('Manufacturer');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());
        $this->set('manufacturerId', $manufacturerId);
        
        if ($manufacturerId == '') {
            $this->set('title_for_layout', 'Pfandkonto Übersicht');
            return;    
        }
        
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        $this->set('manufacturer', $manufacturer);
        
        $this->loadModel('OrderDetail');
        $this->loadModel('CakePayment');
        
        $orderState = Configure::read('htmlHelper')->getOrderStateIdsAsCsv();
        $this->set('orderState', $orderState);
        
        $depositsDelivered = $this->OrderDetail->getDepositSum($manufacturerId, true);
        $depositsReturned = $this->CakePayment->getMonthlyDepositSumByManufacturer($manufacturerId, true);
        
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
        
        $title = 'Pfandkonto für ';
        if ($this->AppAuth->isManufacturer()) {
            $title .= $manufacturer['Manufacturer']['name'];
        } 
        $this->set('title_for_layout', $title);
        
    }

    /**
     * @param string $monthAndYear
     */
    public function detail($monthAndYear)
    {
        
        $manufacturerId = $this->getManufacturerId();
        
        $this->loadModel('Manufacturer');
        $this->set('manufacturerId', $manufacturerId);
        
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        $this->set('manufacturer', $manufacturer);
        
        $this->loadModel('CakePayment');
        $payments = $this->CakePayment->getManufacturerDepositsByMonth($manufacturerId, $monthAndYear);
        
        $this->set('payments', $payments);
        
        if ($monthAndYear == '') {
            throw new MissingActionException('monthAndYear missing');
        }
        $monthAndYearExploded = explode('-', $monthAndYear);
        $year  = $monthAndYearExploded[0];
        $month = $monthAndYearExploded[1];
        $this->set('month', $month);
        $this->set('year', $year);
        $this->set('title_for_layout', 'Pfand-Rücknahme Detail für ' . $manufacturer['Manufacturer']['name']);
        
    }
    
}

?>