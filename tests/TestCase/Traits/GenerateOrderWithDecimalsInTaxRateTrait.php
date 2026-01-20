<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use App\Test\Fixture\ProductsFixture;

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

    protected function generateOrderWithDecimalsInTaxRate(int $customerId): void
    {
        $taxesTable = $this->getTableLocator()->get('Taxes');
        $newTax = $taxesTable->save(
            $taxesTable->newEntity(
                [
                    'rate' => 8.4,
                ],
            )
        );

        $productsTable = $this->getTableLocator()->get('Products');
        $product = $productsTable->get(ProductsFixture::ID_LUNG_STEW);
        $product->id_tax = $newTax->id_tax;
        $productsTable->save($product);

        $this->addProductToCart(ProductsFixture::ID_LUNG_STEW, 3);
        $this->finishCart();

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->updateAll(
            ['pickup_day' => '2018-02-02'],
            ['id_customer' => $customerId]
        );

    }

}
