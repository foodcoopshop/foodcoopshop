<?php
namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

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
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ActionLogsController extends AdminAppController
{

    public function beforeFilter(EventInterface $event)
    {
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $this->Product = TableRegistry::getTableLocator()->get('Products');
        parent::beforeFilter($event);
    }

    public function index()
    {
        $conditions = [];

        $dateFrom = date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime('-6 day'));
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'));
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $conditions[] = 'DATE_FORMAT(ActionLogs.date, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        $conditions[] = 'DATE_FORMAT(ActionLogs.date, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'';

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        }
        $this->set('customerId', $customerId);

        if ($customerId != '') {
            $conditions['ActionLogs.customer_id'] = $customerId;
        }

        $productId = '';
        if (! empty($this->getRequest()->getQuery('productId'))) {
            $productId = h($this->getRequest()->getQuery('productId'));
        }
        $this->set('productId', $productId);

        if ($productId != '') {
            $conditions[] =
                '((ActionLogs.object_id = ' . $productId . ' AND ActionLogs.object_type = "products") ' . 
                ' OR ' . 
                '(ActionLogs.object_type = "order_details"
                     AND ActionLogs.object_id IN (
                         SELECT id_order_detail
                         FROM fcs_order_detail od
                         WHERE od.product_id = ' . $productId .
                     ') ' . 
                 ')) ';
        }

        // manufacturers should only see their own product logs
        if ($this->AppAuth->isManufacturer()) {
            $conditions[] = '( (BlogPosts.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR Products.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR Payments.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR Manufacturers.id_manufacturer = ' . $this->AppAuth->getManufacturerId() . ') '.
                ' OR (ActionLogs.object_type = "order_details"
                     AND ActionLogs.object_id IN (
                         SELECT id_order_detail
                         FROM fcs_order_detail od
             			 INNER JOIN fcs_product p ON p.id_product = od.product_id
                         WHERE p.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                    ') '.
                ') '.
              ' OR (ActionLogs.customer_id = ' .$this->AppAuth->getUserId().') )';
        }

        // customers are only allowed to see their own data
        if ($this->AppAuth->isCustomer()) {
            $tmpCondition  =  '(';
                $tmpCondition .= 'Customers.id_customer = '.$this->AppAuth->getUserId();
                // order of first and lastname can be changed Configure::read('app.customerMainNamePart')
                $customerNameForRegex = $this->AppAuth->user('firstname') . ' ' . $this->AppAuth->user('lastname');
            if (Configure::read('app.customerMainNamePart') == 'lastname') {
                $customerNameForRegex = $this->AppAuth->user('lastname') . ' ' . $this->AppAuth->user('firstname');
            }
                $tmpCondition .= ' OR ActionLogs.text REGEXP \'' . $customerNameForRegex . '\'';
            $tmpCondition .= ')';
            $conditions[] = $tmpCondition;
            // never show cronjob logs for customers
            $conditions[] = 'ActionLogs.type NOT REGEXP \'^cronjob_\'';
        }

        $types = [];
        if (! empty($this->getRequest()->getQuery('types'))) {
            $types = h($this->getRequest()->getQuery('types'));
            if (!empty($types[0])) {
                $conditions['ActionLogs.type IN'] = $types;
            }
        }
        $this->set('types', $types);

        $query = $this->ActionLog->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Customers',
                'Products',
                'Manufacturers',
                'BlogPosts',
                'Payments'
            ]
        ]);
        $actionLogs = $this->paginate($query, [
            'sortWhitelist' => [
                'ActionLogs.type', 'ActionLogs.date', 'ActionLogs.text', 'Customers.' . Configure::read('app.customerMainNamePart')
            ],
            'order' => [
                'ActionLogs.date' => 'DESC'
            ]
        ])->toArray();
        foreach ($actionLogs as $actionLog) {
            if (!empty($actionLog->customer)) {
                $manufacturer = $this->Customer->getManufacturerRecord($actionLog->customer);
                if (!empty($manufacturer)) {
                    $actionLog->customer->manufacturer = $manufacturer;
                }
            }
        }
        $this->set('actionLogs', $actionLogs);

        $this->set('actionLogModel', $this->ActionLog);

        $titleForLayout = __d('admin', 'Activities');
        $this->set('title_for_layout', $titleForLayout);
    }
}
