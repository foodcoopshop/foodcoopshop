<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use App\Services\ProductQuantityService;


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

trait EditQuantityTrait
{

    public function editQuantity()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $originalProductId = $this->getRequest()->getData('productId');

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $this->Product->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'StockAvailables',
                'Manufacturers',
                'UnitProducts',
                'ProductAttributes',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.UnitProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
            ]
        )->first();

        $unitObject = $oldProduct->unit_product;
        if ($ids['attributeId'] > 0) {
            // override values for messages
            foreach ($oldProduct->product_attributes as $attribute) {
                if ($attribute->id_product_attribute != $ids['attributeId']) {
                    continue;
                }
                $oldProduct->name = $oldProduct->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                $oldProduct->stock_available = $attribute->stock_available;
                $unitObject = $attribute->unit_product_attribute;
            }
        }

        $oldStockAvailable = $oldProduct->stock_available->quantity;

        try {
            $object2save = [
                'quantity' => $this->getRequest()->getData('quantity'),
                'always_available' => $this->getRequest()->getData('alwaysAvailable'),
                'default_quantity_after_sending_order_lists' => $this->getRequest()->getData('defaultQuantityAfterSendingOrderLists'),
            ];
            if (in_array('quantityLimit', array_keys($this->getRequest()->getData()))) {
                $object2save['quantity_limit'] = $this->getRequest()->getData('quantityLimit');
            }
            if (in_array('soldOutLimit', array_keys($this->getRequest()->getData()))) {
                $object2save['sold_out_limit'] = $this->getRequest()->getData('soldOutLimit');
            }

            $this->Product->changeQuantity(
                [
                    [
                        $originalProductId => $object2save,
                    ],
                ],
            );
        } catch (\Exception $e) {
            pr($e->getMessage());
            return $this->sendAjaxError($e);
        }

        $this->Flash->success(__d('admin', 'The_amount_of_the_product_{0}_was_changed_successfully.', ['<b>' . $oldProduct->name . '</b>']));

        $entity = $this->Product->StockAvailables->patchEntity($oldProduct->stock_available, $object2save);

        if ($entity->isDirty()) {

            $productQuantityService = new ProductQuantityService();
            $isAmountBasedOnQuantityInUnits = $productQuantityService->isAmountBasedOnQuantityInUnits($oldProduct, $unitObject);

            $dirtyFieldsWithNewValues = [];
            $unitName = $unitObject->name ?? '';

            foreach($entity->getDirty() as $dirtyField) {

                $newValue = $entity->get($dirtyField);
                $originalValue = $oldProduct->stock_available->getOriginal($dirtyField);

                $originalValueToCompare = $originalValue !== null ? Configure::read('app.numberHelper')->formatAsDecimal($originalValue, 3) : null;
                $newValueToCompare = $newValue !== null ? Configure::read('app.numberHelper')->formatAsDecimal($newValue, 3) : null;

                if ($originalValueToCompare == $newValueToCompare) {
                    continue;
                }

                switch($dirtyField) {
                    case 'quantity':
                        $translatedFieldName = __d('admin', 'Available_quantity') . ': '
                            . __d('admin', 'Old_value') . ': <b>' . $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $oldStockAvailable, $unitName) . '</b> '
                            . __d('admin', 'New_value');
                        $newValue = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $newValue, $unitName);
                        break;
                    case 'always_available':
                        $translatedFieldName = __d('admin', 'Always_available');
                        $newValue = $newValue == 1 ? __d('admin', 'yes') : __d('admin', 'no');
                        break;
                    case 'default_quantity_after_sending_order_lists':
                        $translatedFieldName = __d('admin', 'Default_quantity_after_sending_order_lists');
                        $newValue = $newValue == '' ? __d('admin', 'empty') : $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $newValue, $unitName);
                        break;
                    case 'quantity_limit':
                        $translatedFieldName = __d('admin', 'Quantity_limit');
                        $newValue = $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $newValue, $unitName);
                        break;
                    case 'sold_out_limit':
                        $translatedFieldName = __d('admin', 'Sold_out_limit');
                        $newValue = $newValue == '' ? __d('admin', 'empty') : $productQuantityService->getFormattedAmount($isAmountBasedOnQuantityInUnits, $newValue, $unitName);
                        break;
                }
                if (isset($translatedFieldName)) {
                    $dirtyFieldsWithNewValues[] = $translatedFieldName . ': <b>' . $newValue . '</b>';
                }
            }

            if (!empty($dirtyFieldsWithNewValues)) {

                $changeReason = $this->getRequest()->getData('changeReason', '');
                if ($changeReason != '') {
                    $dirtyFieldsWithNewValues[] = __d('admin', 'Reason_for_change') . ': <b>' . $changeReason . '</b>';
                }

                $this->ActionLog->customSave(
                    'product_quantity_changed',
                    $this->identity->getId(),
                    $productId,
                    'products',
                    __d('admin', 'The_amount_of_the_product_{0}_from_manufacturer_{1}_was_changed:_{2}.', [
                        '<b>' . $oldProduct->name . '</b>',
                        '<b>' . $oldProduct->manufacturer->name . '</b>',
                        join(', ', $dirtyFieldsWithNewValues),
                    ])
                );
            }
        }
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}
