<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ActionLogsController extends AdminAppController
{

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
        if ($this->identity->isManufacturer()) {
            $conditions[] = '( (BlogPosts.id_manufacturer = ' . $this->identity->getManufacturerId() .
                ' OR Products.id_manufacturer = ' . $this->identity->getManufacturerId() .
                ' OR Payments.id_manufacturer = ' . $this->identity->getManufacturerId() .
                ' OR Manufacturers.id_manufacturer = ' . $this->identity->getManufacturerId() . ') '.
                ' OR (ActionLogs.object_type = "order_details"
                     AND ActionLogs.object_id IN (
                         SELECT id_order_detail
                         FROM fcs_order_detail od
                          INNER JOIN fcs_product p ON p.id_product = od.product_id
                         WHERE p.id_manufacturer = ' . $this->identity->getManufacturerId() .
                    ') '.
                ') '.
              ' OR (ActionLogs.customer_id = ' .$this->identity->getId().') )';

            if ($this->identity->getManufacturerAnonymizeCustomers()) {
                $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
                $conditions['ActionLogs.type NOT IN'] = $actionLogsTable->getHiddenTypesForManufacturersWithEnabledAnonymization();
            }

        }

        // customers are only allowed to see their own data
        if ($this->identity->isCustomer()) {
            $customerNameForRegex = $this->identity->name;
            $conditions[] = [
                'OR' => [
                    'Customers.id_customer' => $this->identity->getId(),
                    'ActionLogs.text REGEXP' => preg_quote($customerNameForRegex), // preg_quote needed to escape special characters like *
                ],
            ];

            // never show cronjob logs for customers
            $conditions['ActionLogs.type NOT REGEXP'] = '^cronjob_';
        }

        $types = [];
        if (! empty($this->getRequest()->getQuery('types'))) {
            $types = h($this->getRequest()->getQuery('types'));
            if (!empty($types[0])) {
                $conditions['ActionLogs.type IN'] = $types;
            }
        }
        $this->set('types', $types);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $query = $actionLogsTable->find('all',
        conditions: $conditions,
        contain: [
            'Customers',
            'Products',
            'Manufacturers',
            'BlogPosts',
            'Payments'
        ]);

        $query->where(function (QueryExpression $exp) use ($dateFrom, $dateTo) {
            $exp->gte('DATE_FORMAT(ActionLogs.date, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom));
            $exp->lte('DATE_FORMAT(ActionLogs.date, \'%Y-%m-%d\')', Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo));
            return $exp;
        });

        $actionLogs = $this->paginate($query, [
            'sortableFields' => [
                'ActionLogs.id', 'ActionLogs.type', 'ActionLogs.date', 'ActionLogs.text', 'Customers.' . Configure::read('app.customerMainNamePart')
            ],
            'order' => [
                'ActionLogs.id' => 'DESC',
            ]
        ]);
        $customersTable = $this->getTableLocator()->get('Customers');
        foreach ($actionLogs as $actionLog) {
            if (!empty($actionLog->customer)) {
                $manufacturer = $customersTable->getManufacturerRecord($actionLog->customer);
                if (!empty($manufacturer)) {
                    $actionLog->customer->manufacturer = $manufacturer;
                }
            }
        }
        $this->set('actionLogs', $actionLogs);
        $this->set('actionLogsTable', $actionLogsTable);

        $titleForLayout = __d('admin', 'Activities');
        $this->set('title_for_layout', $titleForLayout);
    }
}
