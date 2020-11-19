<?php

namespace App\Test\TestCase\Traits;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait PrepareInvoiceDataTrait
{

    public function generateInvoice($customerId, $paidInCash)
    {
        $this->get('/admin/invoices/generate.pdf?customerId='.$customerId.'&paidInCash='.$paidInCash.'&currentDay=2018-02-02');
    }

    public function prepareOrdersAndPaymentsForInvoice($customerId)
    {

        $pickupDay = '2018-02-02';

        // add product with price pre unit
        $productIdA = 347; // forelle
        $productIdB = '348-11'; // rindfleisch + attribute
        $this->addProductToCart($productIdA, 1);
        $this->addProductToCart($productIdB, 3);
        $this->finishCart(1, 1, '', null, $pickupDay);

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $query = 'UPDATE ' . $this->OrderDetail->getTable().' SET pickup_day = :pickupDay WHERE id_order_detail IN(4,5);';
        $params = [
            'pickupDay' => $pickupDay,
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);

        $this->addPayment($customerId, 2.0, 'deposit', 0, '', $pickupDay);
        $this->addPayment($customerId, 3.2, 'deposit', 0, '', $pickupDay);

    }

}
