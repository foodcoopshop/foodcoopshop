<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Controller\Component\StringComponent;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait EditDefaultAttributeTrait 
{

    public function editDefaultAttribute($productId, $productAttributeId): void
    {
        $productId = (int) $productId;
        $productAttributeId = (int) $productAttributeId;

        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->setDefaultAttributeId($productId, $productAttributeId);

        $product = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
            ],
            contain: [
                'Manufacturers',
            ]
        )->first();

        $productAttributesTable = $this->getTableLocator()->get('ProductAttributes');
        $productAttribute = $productAttributesTable->find('all',
            conditions: [
                $productAttributesTable->aliasField('id_product_attribute') => $productAttributeId,
            ],
            contain: [
                'ProductAttributeCombinations.Attributes',
            ]
        )->first();

        $actionLogMessage = __d('admin', 'The_default_attribute_of_the_product_{0}_from_manufacturer_{1}_was_changed_to_{2}.', [
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>',
            '<b>' . $productAttribute->product_attribute_combination->attribute->name . '</b>'
        ]);
        $this->Flash->success($actionLogMessage);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('product_default_attribute_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);

        $this->redirect($this->referer());
    }

}
