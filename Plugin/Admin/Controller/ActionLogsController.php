<?php

App::uses('Customer', 'Model');

/**
 * ActionLogsController
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
class ActionLogsController extends AdminAppController
{

    public function beforeFilter()
    {
        $this->loadModel('CakeActionLog');
        $this->loadModel('Customer');
        $this->loadModel('Product');
        parent::beforeFilter();
    }

    public function index()
    {
        $conditions = array();

        $dateFrom = date('d.m.Y', strtotime('-6 day'));
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = date('d.m.Y');
        if (! empty($this->params['named']['dateTo'])) {
            $dateTo = $this->params['named']['dateTo'];
        }
        $this->set('dateTo', $dateTo);

        $conditions[] = 'DATE_FORMAT(CakeActionLog.date, \'%Y-%m-%d\') >= \'' . Configure::read('timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        $conditions[] = 'DATE_FORMAT(CakeActionLog.date, \'%Y-%m-%d\') <= \'' . Configure::read('timeHelper')->formatToDbFormatDate($dateTo) . '\'';

        $customerId = '';
        if (! empty($this->params['named']['customerId'])) {
            $customerId = $this->params['named']['customerId'];
        }
        $this->set('customerId', $customerId);

        if ($customerId != '') {
            $conditions['CakeActionLog.customer_id'] = $customerId;
        }

        $productId = '';
        if (! empty($this->params['named']['productId'])) {
            $productId = $this->params['named']['productId'];
        }
        $this->set('productId', $productId);

        if ($productId != '') {
            $conditions['CakeActionLog.object_type'] = "products";
            $conditions['CakeActionLog.object_id'] = $productId;
        }

        // manufacturers should only see their own product logs
        if ($this->AppAuth->isManufacturer()) {
            $conditions[] = '( (BlogPost.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR Product.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR CakePayment.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR Manufacturer.id_manufacturer = ' . $this->AppAuth->getManufacturerId() . ') '.
              ' OR (CakeActionLog.customer_id = ' .$this->AppAuth->getUserId().') )';
        }

        // customers are only allowed to see their own data
        if ($this->AppAuth->isCustomer()) {
            $tmpCondition  =  '(';
                $tmpCondition .= 'Customer.id_customer = '.$this->AppAuth->getUserId();
                // order of first and lastname can be changed Configure::read('app.customerMainNamePart')
                $customerNameForRegex = $this->AppAuth->user('firstname') . ' ' . $this->AppAuth->user('lastname');
            if (Configure::read('app.customerMainNamePart') == 'lastname') {
                $customerNameForRegex = $this->AppAuth->user('lastname') . ' ' . $this->AppAuth->user('firstname');
            }
                $tmpCondition .= ' OR CakeActionLog.text REGEXP \'' . $customerNameForRegex . '\'';
            $tmpCondition .= ')';
            $conditions[] = $tmpCondition;
            // never show cronjob logs for customers
            $conditions[] = 'CakeActionLog.type NOT REGEXP \'^cronjob_\'';
        }

        $type = '';
        if (! empty($this->params['named']['type'])) {
            $type = $this->params['named']['type'];
            $conditions[] = 'CakeActionLog.type = \'' . $type . '\'';
        }
        $this->set('type', $type);

        $this->Paginator->settings = array_merge($this->Paginator->settings, array(
            'conditions' => $conditions,
            'order' => array(
                'CakeActionLog.date' => 'DESC'
            )
        ));
        $actionLogs = $this->Paginator->paginate('CakeActionLog');
        foreach ($actionLogs as &$actionLog) {
            $manufacturer = $this->Customer->getManufacturerRecord($actionLog);
            if ($manufacturer) {
                $actionLog['Customer']['Manufacturer'] = $manufacturer['Manufacturer'];
            }
        }
        $this->set('actionLogs', $actionLogs);

        $this->set('actionLogModel', $this->CakeActionLog);

        if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isManufacturer()) {
            $this->set('customersForDropdown', $this->Customer->getForDropdown(true));
        }

        $titleForLayout = 'Aktivitäten';
        if (isset($this->CakeActionLog->types[$type])) {
            $titleForLayout .= ' | ' . $this->CakeActionLog->types[$type]['de'];
        }
        $this->set('title_for_layout', $titleForLayout);
    }
}
