<?php
declare(strict_types=1);

namespace App\Services;

use Cake\Core\Configure;
use Cake\Controller\Exception\InvalidParameterException;

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

class ProductQuantityService
{
    
    public function isAmountBasedOnQuantityInUnits($product, $unitObject)
    {
        if (empty($product->manufacturer)) {
            throw new InvalidParameterException('manufacturer must not be empty');
        }

        return $product->is_stock_product &&
               $product->manufacturer->stock_management_enabled  &&
               (!empty($unitObject) && $unitObject->price_per_unit_enabled);
    }

    public function isAmountBasedOnQuantityInUnitsIncludingSelfServiceCheck($product, $unitObject)
    {
        return (new OrderCustomerService())->isSelfServiceMode() && $this->isAmountBasedOnQuantityInUnits($product, $unitObject);
    }

    public function getCombinedAmount($existingCartProduct, $orderedQuantityInUnits)
    {
        $combinedAmount = $orderedQuantityInUnits;
        if ($existingCartProduct) {
            $combinedAmount = $existingCartProduct['productQuantityInUnits'] + $orderedQuantityInUnits;
        }
        return $combinedAmount;
    }

    public function getFormattedAmount($isAmountBasedOnQuantityInUnits, $amount, $unitName)
    {
        if ($isAmountBasedOnQuantityInUnits) {
            return Configure::read('app.numberHelper')->formatUnitAsDecimal($amount) .  ' ' . $unitName;
        }
        return  Configure::read('app.numberHelper')->formatAsDecimal($amount, 0);
}


}