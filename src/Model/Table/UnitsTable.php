<?php
namespace App\Model\Table;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class UnitsTable extends AppTable
{

    public function saveUnits($productId, $productAttributeId, $pricePerUnitEnabled, $priceInclPerUnit, $name, $quantityInUnits) {
        
        if ($productAttributeId > 0) {
            $productId = 0;
        }
        
        $idCondition = [
            'id_product' => $productId,
            'id_product_attribute' => $productAttributeId
        ];
        
        $entity = $this->find('all', [
            'conditions' => $idCondition
        ])->first();
        
        if (empty($entity)) {
            $entity = $this->newEntity($idCondition);
        }
        
        if ($pricePerUnitEnabled == 0 && $priceInclPerUnit == -1  && $name == '' && $quantityInUnits == -1) {
            $this->deleteAll($idCondition);
        } else {
            $patchedEntity = $this->patchEntity($entity, [
                'price_per_unit_enabled' => $pricePerUnitEnabled,
                'price_incl_per_unit' => $priceInclPerUnit,
                'name' => $name,
                'quantity_in_units' => $quantityInUnits
            ]);
            $this->save($patchedEntity);
        }
    }

}
