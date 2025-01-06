<?php
declare(strict_types=1);

namespace App\Model\Traits;

use Cake\Datasource\EntityInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait ProductAndAttributeEntityTrait
{

    public function getEntityToSaveByProductAttributeId($productAttributeId): EntityInterface
    {
        $entity2Save = $this->find('all',
            conditions: [
                'product_attribute_id' => $productAttributeId,
            ],
        )->first();
        if (empty($entity2Save)) {
            $entity2Save = $this->newEntity(['product_attribute_id' => $productAttributeId]);
        }
        return $entity2Save;
    }

    public function getEntityToSaveByProductId($productId): EntityInterface
    {
        $entity2Save = $this->find('all',
            conditions: [
                'product_id' => $productId,
            ],
        )->first();
        if (empty($entity2Save)) {
            $entity2Save = $this->newEntity(['product_id' => $productId]);
        }
        return $entity2Save;
    }

}