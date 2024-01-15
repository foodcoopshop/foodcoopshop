<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;
use App\Services\SanitizeService;
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait EditPriceTrait {

    protected $Sanitize;

    public function editPrice()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $originalProductId = $this->getRequest()->getData('productId');

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes',
                'ProductAttributes.UnitProductAttributes',
                'UnitProducts',
                'Taxes',
            ]
        ])->first();

        if ($ids['attributeId'] > 0) {
            // override values for messages
            foreach ($oldProduct->product_attributes as $attribute) {
                if ($attribute->id_product_attribute != $ids['attributeId']) {
                    continue;
                }
                $oldProduct->name = $oldProduct->name . ' : ' . $attribute->product_attribute_combination->attribute->name;
                $oldProduct->price = $attribute->price;
                $oldProduct->unit_product = $attribute->unit_product_attribute;
            }
        }

        try {
            $this->Product->changePrice(
                [
                    [
                        $originalProductId => [
                            'gross_price' => $this->getRequest()->getData('price'),
                            'unit_product_price_incl_per_unit' => $this->getRequest()->getData('priceInclPerUnit'),
                            'unit_product_name' => $this->getRequest()->getData('priceUnitName'),
                            'unit_product_amount' => $this->getRequest()->getData('priceUnitAmount'),
                            'unit_product_quantity_in_units' => $this->getRequest()->getData('priceQuantityInUnits'),
                            'unit_product_price_per_unit_enabled' => $this->getRequest()->getData('pricePerUnitEnabled')
                        ]
                    ]
                ]
            );
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $price = Configure::read('app.numberHelper')->getStringAsFloat($this->getRequest()->getData('price'));

        $this->Flash->success(__d('admin', 'The_price_of_the_product_{0}_was_changed_successfully.', ['<b>' . $oldProduct->name . '</b>']));
        if (!empty($oldProduct->unit_product) && $oldProduct->unit_product->price_per_unit_enabled) {
            $oldPrice = Configure::read('app.pricePerUnitHelper')->getPricePerUnitBaseInfo($oldProduct->unit_product->price_incl_per_unit, $oldProduct->unit_product->name, $oldProduct->unit_product->amount);
        } else {
            $taxRate = $oldProduct->tax->rate ?? 0;
            $oldPrice = Configure::read('app.numberHelper')->formatAsCurrency($this->Product->getGrossPrice($oldProduct->price, $taxRate));
        }

        if ($this->getRequest()->getData('pricePerUnitEnabled')) {
            $newPrice = Configure::read('app.pricePerUnitHelper')->getPricePerUnitBaseInfo(Configure::read('app.numberHelper')->getStringAsFloat($this->getRequest()->getData('priceInclPerUnit')), $this->getRequest()->getData('priceUnitName'), $this->getRequest()->getData('priceUnitAmount'));
        } else {
            $newPrice = Configure::read('app.numberHelper')->formatAsCurrency($price);
        }

        $actionLogMessage = __d('admin', 'The_price_of_the_product_{0}_from_manufacturer_{1}_was_changed_from_{2}_to_{3}.', [
            '<b>' . $oldProduct->name . '</b>',
            '<b>' . $oldProduct->manufacturer->name . '</b>',
            $oldPrice,
            $newPrice
        ]);

        $this->ActionLog->customSave('product_price_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

}
