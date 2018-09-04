<?php

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ReportsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        if (isset($this->getRequest()->getParam('pass')[0])) {
            switch ($this->getRequest()->getParam('pass')[0]) {
                // allow deposit for cash configuration
                case 'deposit':
                    return $this->AppAuth->isSuperadmin();
                    break;
            }
        }
        return $this->AppAuth->isSuperadmin() && Configure::read('app.htmlHelper')->paymentIsCashless();
    }

    public function payments($paymentType)
    {
        $this->Payment = TableRegistry::getTableLocator()->get('Payments');

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = $this->getRequest()->getQuery('dateFrom');
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = $this->getRequest()->getQuery('dateTo');
        }
        $this->set('dateTo', $dateTo);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = $this->getRequest()->getQuery('customerId');
        }
        $this->set('customerId', $customerId);

        $conditions = [
            'Payments.type' => $paymentType
        ];
        $conditions[] = 'DATE_FORMAT(Payments.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        $conditions[] = 'DATE_FORMAT(Payments.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'';

        if ($customerId != '') {
            $conditions['Payments.id_customer'] = $customerId;
        }

        // exluce "empty_glasses" deposit payments for manufacturers
        $conditions[] = "((Payments.id_manufacturer > 0 && Payments.text = 'money') || Payments.id_manufacturer = 0)";

        $query = $this->Payment->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Customers',
                'Manufacturers',
                'CreatedByCustomers',
                'ChangedByCustomers'
            ]
        ]);

        $payments = $this->paginate($query, [
            'order' => [
                'Payments.date_add' => 'DESC'
            ]
        ]);
        $this->set('payments', $payments);

        $this->set('customersForDropdown', $this->Payment->Customers->getForDropdown());
        $this->set('title_for_layout', __d('admin', 'Report') . ': ' . Configure::read('app.htmlHelper')->getPaymentText($paymentType));
        $this->set('paymentType', $paymentType);
        $this->set('showTextColumn', in_array($paymentType, array(
            'member_fee',
            'deposit'
        )));
    }
}
