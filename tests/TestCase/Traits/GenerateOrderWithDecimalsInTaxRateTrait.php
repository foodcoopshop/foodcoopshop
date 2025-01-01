<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait GenerateOrderWithDecimalsInTaxRateTrait
{

    protected function generateOrderWithDecimalsInTaxRate($customerId): void
    {
        $taxesTable = $this->getTableLocator()->get('Taxes');
        $newTax = $taxesTable->save(
            $taxesTable->newEntity(
                [
                    'rate' => 8.4,
                ],
            )
        );

        $productToAdd = 340; // beuschl, no deposit
        $productsTable = $this->getTableLocator()->get('Products');
        $product = $productsTable->get($productToAdd);
        $product->id_tax = $newTax->id_tax;
        $productsTable->save($product);

        $this->addProductToCart($productToAdd, 3);
        $this->finishCart();

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->updateAll(
            ['pickup_day' => '2018-02-02'],
            ['id_customer' => $customerId]
        );

    }

}
