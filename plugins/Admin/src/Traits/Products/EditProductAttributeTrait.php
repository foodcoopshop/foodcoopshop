<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Controller\Component\StringComponent;
use Cake\Http\Response;

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

trait EditProductAttributeTrait 
{

    public function editProductAttribute(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productId = h($this->getRequest()->getData('productId'));
        $productAttributeId = h($this->getRequest()->getData('productAttributeId'));
        $deleteProductAttribute = h($this->getRequest()->getData('deleteProductAttribute'));
        $barcode = $this->getRequest()->getData('barcode') ?? '';
        $barcode = StringComponent::removeSpecialChars(strip_tags(trim($barcode)));

        $productsTable = $this->getTableLocator()->get('Products');
        $oldProduct = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
            ],
            contain: [
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
            ]
        )->first();

        $attributeName = '';
        foreach ($oldProduct->product_attributes as $attribute) {
            if ($attribute->product_attribute_combination->id_product_attribute == $productAttributeId) {
                $attributeName = $attribute->product_attribute_combination->attribute->name;
                break;
            }
        }

        if ($deleteProductAttribute) {
            $productAttributesTable = $this->getTableLocator()->get('ProductAttributes');
            $productAttributesTable->deleteProductAttribute($productId, $productAttributeId);
            $actionLogMessage = __d('admin', 'The_attribute_{0}_of_the_product_{1}_from_manufacturer_{2}_was_successfully_deleted.', [
                '<b>' . $attributeName . '</b>',
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
            ]);
            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave('product_attribute_deleted', $this->identity->getId(), $productId, 'products', $actionLogMessage);
            $this->getRequest()->getSession()->write('highlightedRowId', $productId);
        } else {
            try {
                $barcodeProductAttributesTable = $this->getTableLocator()->get('BarcodeProductAttributes');
                $entity2Save = $barcodeProductAttributesTable->getEntityToSaveByProductAttributeId($productAttributeId);
                $entity2Save = $barcodeProductAttributesTable->patchEntity(
                    $entity2Save,
                    [
                        'barcode' => $barcode,
                        'product_attribute_id' => $productAttributeId,
                    ],
                    [
                        'validate' => true,
                    ]);
                if ($entity2Save->hasErrors()) {
                    throw new \Exception(join(' ', $productsTable->getAllValidationErrors($entity2Save)));
                }
                $barcodeProductAttributesTable->save($entity2Save);
            } catch (\Exception $e) {
                return $this->sendAjaxError($e);
            }
            $actionLogMessage = __d('admin', 'The_attribute_{0}_of_the_product_{1}_from_manufacturer_{2}_was_changed_successfully.', [
                '<b>' . $attributeName . '</b>',
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
            ]);
            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave('product_attribute_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);
            $this->getRequest()->getSession()->write('highlightedRowId', $productId . '-' . $productAttributeId);
        }
        $this->Flash->success($actionLogMessage);
        $this->set([
            'status' => 1,
            'msg' => 'success',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        return null;
    }

}
