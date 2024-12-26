<?php
declare(strict_types=1);

namespace Admin\Traits\Customers;

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait CreditBalanceSumTrait
{

    public function creditBalanceSum()
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

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $customerTable = $this->getTableLocator()->get('Customers');

        $paymentProductDelta = $customerTable->getProductBalanceForCustomers(APP_ON);
        $paymentDepositDelta = $customerTable->getDepositBalanceForCustomers(APP_ON);
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_activated_members'),
            'count' => count($customerTable->getCustomerIdsWithStatus(APP_ON)),
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => $paymentDepositDelta * -1,
            'payment_product_delta' => 0,
        ];

        $paymentProductDelta = $customerTable->getProductBalanceForCustomers(APP_OFF);
        $paymentDepositDelta = $customerTable->getDepositBalanceForCustomers(APP_OFF);
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_deactivated_members'),
            'count' => count($customerTable->getCustomerIdsWithStatus(APP_OFF)),
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => $paymentDepositDelta * -1,
            'payment_product_delta' => 0,
        ];

        $paymentProductDelta = $customerTable->getProductBalanceForDeletedCustomers();
        $paymentDepositDelta = $customerTable->getDepositBalanceForDeletedCustomers();
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_credits_of_deleted_members'),
            'count' => 0,
            'credit_balance' => $paymentProductDelta + $paymentDepositDelta,
            'payment_deposit_delta' => ($paymentDepositDelta * -1) + 0,
            'payment_product_delta' => 0,
        ];

        $paymentDepositDelta = $paymentsTable->getManufacturerDepositMoneySum();
        $customers[] = [
            'customer_type' => __d('admin', 'Sum_of_deposit_compensation_payments_for_manufactures'),
            'count' => 0,
            'credit_balance' => 0,
            'payment_deposit_delta' => ($paymentDepositDelta * -1) + 0,
            'payment_product_delta' => 0,
        ];

        $this->set('customers', $customers);

        $sums = [
            'credit_balance' => 0,
            'deposit_delta' => 0,
            'product_delta' => 0,
        ];
        foreach($customers as $customer) {
            $sums['credit_balance'] += $customer['credit_balance'];
            $sums['deposit_delta'] += $customer['payment_deposit_delta'];
            $sums['product_delta'] += $customer['payment_product_delta'];
        }

        $this->set('sums', $sums);

        $this->set('title_for_layout', __d('admin', 'Credit_and_deposit_balance'));
    }

}