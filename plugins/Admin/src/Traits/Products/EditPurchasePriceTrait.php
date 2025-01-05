<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;
use App\Services\SanitizeService;
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

trait EditPurchasePriceTrait 
{

    public function editPurchasePrice(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $originalProductId = $this->getRequest()->getData('productId');
        $purchaseGrossPrice = $this->getRequest()->getData('purchasePrice');
        $purchaseGrossPrice = Configure::read('app.numberHelper')->getStringAsFloat($purchaseGrossPrice);

        $productsTable = $this->getTableLocator()->get('Products');
        $ids = $productsTable->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ],
            contain: [
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'ProductAttributes.UnitProductAttributes',
                'UnitProducts',
                'PurchasePriceProducts.Taxes',
                'ProductAttributes.PurchasePriceProductAttributes',
            ],
        )->first();

        try {

            if (empty($oldProduct)) {
                throw new \Exception('product not existing: id ' . $productId);
            }

            if (empty($oldProduct->purchase_price_product)) {
                $oldProduct->purchase_price_product = (object) ['price' => 0];
            }

            $taxRate = 0;
            if (!empty($oldProduct->purchase_price_product->tax)) {
                $taxRate = $oldProduct->purchase_price_product->tax->rate;
            }

            $purchasePriceProductsTable = $this->getTableLocator()->get('PurchasePriceProducts');
            $purchasePriceEntity2Save = $purchasePriceProductsTable->getEntityToSaveByProductId($ids['productId']);
            $purchaseTable = $purchasePriceProductsTable;
            $unitTable = $this->getTableLocator()->get('UnitProducts');

            if ($ids['attributeId'] > 0) {
                // override values
                foreach ($oldProduct->product_attributes as $attribute) {
                    if ($attribute->id_product_attribute != $ids['attributeId']) {
                        continue;
                    }
                    $oldProduct->name = $oldProduct->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                    $oldPrice = 0;
                    if (!empty($attribute->purchase_price_product_attribute)) {
                        $oldPrice = $attribute->purchase_price_product_attribute->price;
                    }
                    $oldProduct->purchase_price_product->price = $oldPrice;
                    $oldProduct->unit_product = $attribute->unit_product_attribute;
                    $purchasePriceEntity2Save = $purchasePriceProductsTable->getEntityToSaveByProductAttributeId($ids['attributeId']);
                    $purchaseTable = $this->getTableLocator()->get('PurchasePriceProductAttributes');
                    $unitTable = $this->getTableLocator()->get('UnitProductAttributes');
                }
            }

            if (!empty($oldProduct->unit_product) && $oldProduct->unit_product->price_per_unit_enabled) {
                $entity2Save = clone $oldProduct->unit_product;
                $patchedEntity = $unitTable->patchEntity(
                    $entity2Save,
                    [
                        'purchase_price_incl_per_unit' => $purchaseGrossPrice,
                    ],
                );
                if ($patchedEntity->hasErrors()) {
                    throw new \Exception(join(' ', $unitTable->getAllValidationErrors($patchedEntity)));
                }
                $unitTable->save($patchedEntity);
                $oldPrice = Configure::read('app.pricePerUnitHelper')->getPricePerUnitBaseInfo($oldProduct->unit_product->purchase_price_incl_per_unit ?? 0, $oldProduct->unit_product->name, $oldProduct->unit_product->amount);
                $newPrice = Configure::read('app.pricePerUnitHelper')->getPricePerUnitBaseInfo($purchaseGrossPrice, $oldProduct->unit_product->name, $oldProduct->unit_product->amount);
            } else {
                $purchasePrice2Save = $productsTable->getNetPrice($purchaseGrossPrice, $taxRate);
                $patchedEntity = $purchaseTable->patchEntity(
                    $purchasePriceEntity2Save,
                    [
                        'price' => $purchasePrice2Save,
                    ],
                );
                if ($patchedEntity->hasErrors()) {
                    throw new \Exception(join(' ', $productsTable->getAllValidationErrors($patchedEntity)));
                }
                $purchaseTable->save($patchedEntity);
                $oldPrice = Configure::read('app.numberHelper')->formatAsCurrency($productsTable->getGrossPrice($oldProduct->purchase_price_product->price, $taxRate));
                $newPrice = Configure::read('app.numberHelper')->formatAsCurrency($purchaseGrossPrice);
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $messageString = __d('admin', 'Nothing_changed.');
        if ($oldPrice != $newPrice) {
            $messageString = __d('admin', 'The_purchase_price_of_the_product_{0}_was_changed_successfully.', ['<b>' . $oldProduct->name . '</b>']);
            $actionLogMessage = __d('admin', 'The_purchase_price_of_the_product_{0}_from_manufacturer_{1}_was_changed_from_{2}_to_{3}.', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                $oldPrice,
                $newPrice,
            ]);
            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave('product_purchase_price_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);
        }
        $this->Flash->success($messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);
        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        return null;

    }

}
