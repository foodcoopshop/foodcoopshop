<?php
declare(strict_types=1);

namespace Admin\Traits\Payments;

use Cake\Core\Configure;
use App\Model\Entity\Payment;
use Cake\I18n\Date;
use Cake\Utility\Hash;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait ProductTrait
{

    protected array $allowedPaymentTypes = [];

    public function product(): void
    {

        $this->paymentType = Payment::TYPE_PRODUCT;
        $this->set('title_for_layout', __d('admin', 'Credit'));

        $this->allowedPaymentTypes = [
            Payment::TYPE_PRODUCT,
            Payment::TYPE_PAYBACK,
            Payment::TYPE_DEPOSIT,
        ];
        if (!Configure::read('app.htmlHelper')->paymentIsCashless()) {
            $this->allowedPaymentTypes = [
                Payment::TYPE_PRODUCT,
                Payment::TYPE_PAYBACK,
            ];
        }

        $this->preparePayments();
        $customersTable = $this->getTableLocator()->get('Customers');
        $this->set('creditBalance', $customersTable->getCreditBalance($this->getCustomerId()));

        if ($this->identity->isSuperadmin() && !Configure::read('app.configurationHelper')->isCashlessPaymentTypeManual()) {
            $personalTransactionCode = $customersTable->getPersonalTransactionCode($this->getCustomerId());
            $this->set('personalTransactionCode', $personalTransactionCode);
        }

    }

    /**
     * $this->customerId needs to be set in calling method
     */
    private function getCustomerId(): int|string
    {
        $customerId = '';
        if (!empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        } if (isset($this->customerId) && $this->customerId > 0) {
            $customerId = $this->customerId;
        }
        return $customerId;
    }

    private function preparePayments(): void
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $paymentsAssociation = $customersTable->getAssociation('Payments');
        $paymentsAssociation->setConditions(
            array_merge(
                $paymentsAssociation->getConditions(),
                ['type IN' => $this->allowedPaymentTypes]
            )
        );

        $customer = $customersTable->find('all',
        conditions: [
            $customersTable->aliasField('id_customer') => $this->getCustomerId(),
        ],
        contain: [
           'Payments'
        ])->first();

        $payments = [];
        if (!empty($customer->payments)) {
            foreach ($customer->payments as $payment) {
                $text = Configure::read('app.htmlHelper')->getPaymentText($payment->type);
                $text .= (! empty($payment->text) ? ': "' . $payment->text . '"' : '');

                $payments[] = [
                    'dateRaw' => $payment->date_add,
                    'date' => $payment->date_add->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                    'year' => $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Year')),
                    'amount' => $payment->amount,
                    'deposit' => 0,
                    'type' => $payment->type,
                    'text' => $text,
                    'payment_id' => $payment->id,
                    'approval' => $payment->approval,
                    'invoice_id' => $payment->invoice_id,
                    'approval_comment' => $payment->approval_comment
                ];
            }
        }

        if ($this->paymentType == Payment::TYPE_PRODUCT) {
            $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
            $orderDetailsGroupedByMonth = $orderDetailsTable->getMonthlySumProductByCustomer($this->getCustomerId());

            if (! empty($orderDetailsGroupedByMonth)) {
                foreach ($orderDetailsGroupedByMonth as $orderDetail) {
                    $monthAndYear = explode('-', $orderDetail['MonthAndYear']);
                    $monthAndYear[0] = (int) $monthAndYear[0];
                    $monthAndYear[1] = (int) $monthAndYear[1];
                    $dateFrom = Date::create($monthAndYear[0], $monthAndYear[1], 1);
                    $lastDayOfMonth = (int) Configure::read('app.timeHelper')->getLastDayOfGivenMonth($orderDetail['MonthAndYear']);
                    $dateTo = Date::create($monthAndYear[0], $monthAndYear[1], $lastDayOfMonth);
                    $payments[] = [
                        'dateRaw' => $dateFrom,
                        'date' => $dateFrom->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
                        'year' => $monthAndYear[0],
                        'amount' => $orderDetail['SumTotalPaid'] * - 1,
                        'deposit' => strtotime($dateFrom->i18nFormat(Configure::read('DateFormat.DatabaseWithTime'))) > strtotime(Configure::read('app.depositPaymentCashlessStartDate')) ? $orderDetail['SumDeposit'] * - 1 : 0,
                        'type' => 'order',
                        'text' => Configure::read('app.htmlHelper')->link(
                            __d('admin', 'Orders') . ' ' . Configure::read('app.timeHelper')->getMonthName($monthAndYear[1]) . ' ' . $monthAndYear[0],
                            '/admin/order-details/?pickupDay[]=' . $dateFrom->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) .
                            '&pickupDay[]=' . $dateTo->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) .
                            '&customerId=' . $this->getCustomerId(),
                            [
                                'title' => __d('admin', 'Show_order')
                            ]
                        ),
                        'payment_id' => null,
                    ];
                }
            }
        }

        $payments = Hash::sort($payments, '{n}.date', 'desc');
        $this->set('payments', $payments);
        $this->set('customerId', $this->getCustomerId());

        $this->set('column_title', $this->viewBuilder()->getVars()['title_for_layout']);

        $title = $this->viewBuilder()->getVars()['title_for_layout'];
        if ($this->getRequest()->getParam('action') == 'product') {
            $title .= ' '.__d('admin', 'of_{0}', [$customer->name]);
        }
        $this->set('title_for_layout', $title);

        $this->set('paymentType', $this->paymentType);
    }

}