<?php
/**
 * ReportsController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ReportsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin() && Configure::read('htmlHelper')->paymentIsCashless();
    }

    public function payments($paymentType)
    {
        $this->loadModel('CakePayment', 'Model');
        
        $dateFrom = Configure::read('timeHelper')->getFirstDayOfThisYear();
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);
        
        $dateTo = Configure::read('timeHelper')->getLastDayOfThisYear();
        if (! empty($this->params['named']['dateTo'])) {
            $dateTo = $this->params['named']['dateTo'];
        }
        $this->set('dateTo', $dateTo);
        
        $customerId = '';
        if (! empty($this->params['named']['customerId'])) {
            $customerId = $this->params['named']['customerId'];
        }
        $this->set('customerId', $customerId);
        
        $conditions = array(
            'CakePayment.type' => $paymentType
        );
        $conditions[] = 'DATE_FORMAT(CakePayment.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        $conditions[] = 'DATE_FORMAT(CakePayment.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('timeHelper')->formatToDbFormatDate($dateTo) . '\'';
        
        if ($customerId != '') {
            $conditions['CakePayment.id_customer'] = $customerId;
        }
        
        // exluce "empty_glasses" deposit payments for manufacturers
        $conditions[] = "((CakePayment.id_manufacturer > 0 && CakePayment.text = 'money') || CakePayment.id_manufacturer = 0)";
        
        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'CakePayment.date_add' => 'DESC'
            )
        ), $this->Paginator->settings);
        
        $payments = $this->Paginator->paginate('CakePayment');
        $this->set('payments', $payments);
        
        $this->set('customersForDropdown', $this->CakePayment->Customer->getForDropdown());
        $this->set('title_for_layout', 'Bericht: ' . Configure::read('htmlHelper')->getPaymentText($paymentType));
        $this->set('paymentType', $paymentType);
        $this->set('showTextColumn', in_array($paymentType, array(
            'member_fee',
            'deposit'
        )));
    }
}
