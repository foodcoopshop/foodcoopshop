<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use App\Model\Entity\OrderDetail;
use App\Model\Entity\Payment;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait PrepareAndTestInvoiceDataTrait
{

    public function generateInvoice($customerId, $paidInCash): void
    {
        $this->get('/admin/invoices/generate.pdf?customerId='.$customerId.'&paidInCash='.$paidInCash.'&currentDay=2018-02-02');
    }

    public function prepareOrdersAndPaymentsForInvoice($customerId): void
    {

        $pickupDay = '2018-02-02';

        // add product with price pre unit
        $productIdA = 347; // forelle
        $productIdB = '348-11'; // rindfleisch + attribute
        $this->addProductToCart($productIdA, 1);
        $this->addProductToCart($productIdB, 3);
        $this->finishCart(1, 1, '', null, $pickupDay);

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailEntityA = $orderDetailsTable->get(4);
        $orderDetailEntityA->pickup_day = $pickupDay;
        $orderDetailsTable->save($orderDetailEntityA);

        $orderDetailEntityB = $orderDetailsTable->get(5);
        $orderDetailEntityB->pickup_day = $pickupDay;
        $orderDetailsTable->save($orderDetailEntityB);

        $this->addCustomerPayment($customerId, 2.0, Payment::TYPE_DEPOSIT, true);
        $this->addCustomerPayment($customerId, 3.2, Payment::TYPE_DEPOSIT, true);

    }

    public function doAssertInvoiceTaxes($data, $taxRate, $excl, $tax, $incl): void
    {
        $this->assertEquals($data->tax_rate, $taxRate);
        $this->assertEquals(round($data->total_price_tax_excl, 2), $excl);
        $this->assertEquals(round($data->total_price_tax, 2), $tax);
        $this->assertEquals(round($data->total_price_tax_incl, 2), $incl);
    }

    public function getAndAssertOrderDetailsAfterCancellation($orderDetailIds): void
    {

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_order_detail IN' => $orderDetailIds,
            ],
        )->toArray();
        $this->assertEquals(5, count($orderDetails));

        foreach($orderDetails as $orderDetail) {
            $this->assertNull($orderDetail->id_invoice);
            $this->assertEquals($orderDetail->order_state, OrderDetail::STATE_OPEN);
            $this->assertTrue($orderDetail->total_price_tax_excl >= 0);
            $this->assertTrue($orderDetail->total_price_tax_incl >= 0);
            $this->assertTrue($orderDetail->tax_unit_amount >= 0);
            $this->assertTrue($orderDetail->tax_total_amount >= 0);
        }

    }

    public function getAndAssertPaymentsAfterCancellation($paymentIds): void
    {

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payments = $paymentsTable->find('all',
            conditions: [
                'Payments.id IN' => $paymentIds,
            ],
        )->toArray();
        $this->assertEquals(2, count($payments));

        foreach($payments as $payment) {
            $this->assertNull($payment->invoice_id);
            $this->assertTrue($payment->amount >= 0);
        }

    }

    public function getAndAssertOrderDetailsAfterInvoiceGeneration($invoiceId, $expectedCount): void
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $orderDetailsTable->find('all',
            conditions: [
                'OrderDetails.id_invoice' => $invoiceId,
            ],
        )->toArray();

        $this->assertEquals($expectedCount, count($orderDetails));
        foreach($orderDetails as $orderDetail) {
            $this->assertEquals($orderDetail->order_state, OrderDetail::STATE_BILLED_CASHLESS);
        }
    }

    public function getAndAssertPaymentsAfterInvoiceGeneration($customerId): void
    {
        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payments = $paymentsTable->getCustomerDepositNotBilled($customerId);

        foreach($payments as $payment) {
            $this->assertEquals($payment->id_invoice, 1);
        }
    }

}
