<?php

namespace App\Model\Traits;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait ProductAndAttributeEntityTrait
{

    public function getEntityToSaveByProductAttributeId($productAttributeId)
    {
        $entity2Save = $this->find('all', [
            'conditions' => [
                'product_attribute_id' => $productAttributeId,
            ],
        ])->first();
        if (empty($entity2Save)) {
            $entity2Save = $this->newEntity(['product_attribute_id' => $productAttributeId]);
        }
        return $entity2Save;
    }

    public function getEntityToSaveByProductId($productId)
    {
        $entity2Save = $this->find('all', [
            'conditions' => [
                'product_id' => $productId,
            ],
        ])->first();
        if (empty($entity2Save)) {
            $entity2Save = $this->newEntity(['product_id' => $productId]);
        }
        return $entity2Save;
    }

}