<?php

namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PurchasePriceProductAttributesTable extends AppTable
{

    public function initialize(array $config): void
    {
        $this->setTable('purchase_prices');
        parent::initialize($config);
        $this->setPrimaryKey('product_attribute_id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->greaterThanOrEqual('price', 0, __('The_price_needs_to_be_greater_or_equal_than_0.'));
        return $validator;
    }

    public function isPurchasePriceSet($entity): bool
    {
        $result = true;
        if (!empty($entity->unit_product_attribute) && $entity->unit_product_attribute->price_per_unit_enabled) {
            if (is_null($entity->unit_product_attribute->purchase_price_incl_per_unit)) {
                $result = false;
            }
        } else {
            if (empty($entity->purchase_price_product_attribute) || is_null($entity->purchase_price_product_attribute->price)) {
                $result = false;
            }
        }
        return $result;
    }
}
