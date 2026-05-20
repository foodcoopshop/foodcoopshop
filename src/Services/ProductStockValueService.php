<?php
declare(strict_types=1);

namespace App\Services;

use Cake\Core\Configure;
use stdClass;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductStockValueService
{

    public function getPrice(stdClass $product): float
    {
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            return $this->getPurchasePriceNet($product);
        }
        return $this->getSellingPriceGross($product);
    }

    public function getPricePerUnit(stdClass $product): float|string
    {
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            return $product->unit->purchase_price_incl_per_unit ?? 0;
        }
        return $product->unit->price_incl_per_unit ?? 0;
    }

    public function getSellingPriceGross(stdClass $product): float
    {
        $sellingPriceGross = $product->gross_price;
        if ($product->unit && $product->unit->price_per_unit_enabled) {
            $sellingPriceGross = $product->unit->price_incl_per_unit;
        }
        return (float) $sellingPriceGross;
    }

    public function getPurchasePriceNet(stdClass $product): float
    {
        $purchasePriceNet = $product->purchase_net_price ?? 0;
        if ($product->unit && $product->unit->price_per_unit_enabled) {
            $purchasePriceNet = $product->unit->purchase_price_incl_per_unit ?? 0;
        }
        return (float) $purchasePriceNet;
    }

    public function getStockValue(stdClass $product): float|int
    {
        return $this->calculateStockValue(
            $product,
            $this->getPrice($product),
            $product->stock_available->quantity,
            $this->getPricePerUnit($product),
        );
    }

    public function calculateStockValue(stdClass $product, float $price, float|string $availableQuantity, float|string $pricePerUnit): float|int
    {
        if ($availableQuantity <= 0) {
            return 0;
        }

        if ($product->unit && $product->unit->price_per_unit_enabled) {
            $price = Configure::read('app.pricePerUnitHelper')->getPricePerUnit($pricePerUnit, $product->unit->quantity_in_units, $product->unit->amount);
        }

        return $price * $availableQuantity;
    }

}