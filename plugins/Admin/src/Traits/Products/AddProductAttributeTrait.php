<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

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

trait AddProductAttributeTrait 
{

    public function addProductAttribute($productId, $productAttributeId): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $oldProduct = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'Manufacturers'
            ]
        )->first();

        $productAttributesTable = $this->getTableLocator()->get('ProductAttributes');
        $productAttributesTable->add($productId, $productAttributeId);

        // get new data
        $newProduct = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'ProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        )->first();
        foreach ($newProduct->product_attributes as $attribute) {
            if ($attribute->product_attribute_combination->id_attribute == $productAttributeId) {
                $productAttributeIdForHighlighting = $attribute->product_attribute_combination->id_product_attribute;
            }
        }
        if (isset($productAttributeIdForHighlighting)) {
            $this->getRequest()->getSession()->write('highlightedRowId', $productId . '-' . $productAttributeIdForHighlighting);
        }

        if (isset($attribute)) {
            $actionLogMessage = __d('admin', 'The_attribute_{0}_for_the_product_{1}_from_manufacturer_{2}_was_successfully_created.', [
                '<b>' . $attribute->product_attribute_combination->attribute->name . '</b>',
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>'
            ]);
            $this->Flash->success($actionLogMessage);
            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave('product_attribute_added', $this->identity->getId(), $oldProduct->id_product, 'products', $actionLogMessage);
        }
        
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

}
