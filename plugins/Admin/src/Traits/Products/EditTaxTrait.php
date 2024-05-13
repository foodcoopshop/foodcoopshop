<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;
use Cake\Utility\Hash;

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

trait EditTaxTrait 
{

    public function editTax()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productId = (int) $this->getRequest()->getData('productId');
        $taxId = (int) $this->getRequest()->getData('taxId');

        try {

            $contain = [
                'Taxes',
                'ProductAttributes',
                'Manufacturers',
            ];
            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                $contain[] = 'PurchasePriceProducts.Taxes';
                $contain[] = 'ProductAttributes.PurchasePriceProductAttributes';
            }
            $oldProduct = $this->Product->find('all',
                conditions: [
                    'Products.id_product' => $productId
                ],
                contain: $contain,
            )->first();

            $this->Tax = $this->getTableLocator()->get('Taxes');
            $taxes = $this->Tax->find('all',
                conditions: [
                    'Taxes.deleted' => APP_OFF,
                ]
            )->toArray();
            $validTaxIds = Hash::extract($taxes, '{n}.id_tax');
            $validTaxIds[] = 0;
            if (!in_array($taxId, $validTaxIds)) {
                throw new \Exception('invalid taxId: ' . $taxId);
            }

            $changedTaxInfoForMessage = [];
            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                $purchasePriceTaxId = (int) $this->getRequest()->getData('purchasePriceTaxId');
                if (!in_array($purchasePriceTaxId, $validTaxIds)) {
                    throw new \Exception('invalid purchasePriceTaxId: ' . $purchasePriceTaxId);
                }
                $changedTaxInfoForMessage = $this->Product->PurchasePriceProducts->savePurchasePriceTax($purchasePriceTaxId, $productId, $oldProduct);
            }

            if (empty($oldProduct->tax)) {
                $oldProduct->tax = (object) [
                    'rate' => 0
                ];
            }

            if ($taxId != $oldProduct->id_tax) {
                $product2update = [
                    'id_tax' => $taxId,
                ];

                $this->Product->save(
                    $this->Product->patchEntity($oldProduct, $product2update)
                );

                $newTaxRate = 0;
                foreach($taxes as $tax) {
                    if ($taxId == $tax->id_tax) {
                        $newTaxRate = $tax->rate;
                        continue;
                    }
                }

                if (! empty($oldProduct->product_attributes)) {
                    // update net price of all attributes
                    foreach ($oldProduct->product_attributes as $attribute) {
                        $newNetPrice = $this->Product->getNetPriceForNewTaxRate($attribute->price, $oldProduct->tax->rate, $newTaxRate);
                        $this->Product->ProductAttributes->updateAll([
                            'price' => $newNetPrice
                        ], [
                            'id_product_attribute' => $attribute->id_product_attribute
                        ]);
                    }
                } else {
                    // update price of product without attributes
                    $newNetPrice = $this->Product->getNetPriceForNewTaxRate($oldProduct->price, $oldProduct->tax->rate, $newTaxRate);
                    $product2update = [
                        'price' => $newNetPrice
                    ];
                    $this->Product->save(
                        $this->Product->patchEntity($oldProduct, $product2update)
                    );
                }

                $oldTaxRate = 0;
                if (! empty($oldProduct->tax)) {
                    $oldTaxRate = $oldProduct->tax->rate;
                }

                $changedTaxInfoForMessage[] = [
                    'label' => Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') ? __d('admin', 'Selling_price') . ': ' : '',
                    'oldTaxRate' => $oldTaxRate,
                    'newTaxRate' => $newTaxRate,
                ];

            }

            if (!empty($changedTaxInfoForMessage)) {
                $messageString = __d('admin', 'The_tax_rate_of_product_{0}_from_manufacturer_{1}_was_changed_successfully.', [
                    '<b>' . $oldProduct->name . '</b>',
                    '<b>' . $oldProduct->manufacturer->name . '</b>',
                ]);
                foreach($changedTaxInfoForMessage as $info) {
                    $messageString .= '<br />';
                    if ($info['label'] != '') {
                        $messageString .= '<b>' . $info['label'] . '</b>';
                    }
                    $messageString .= __d('admin', 'From_{0}_to_{1}', [
                        Configure::read('app.numberHelper')->formatTaxRate($info['oldTaxRate']) . '%',
                        '<b>' . Configure::read('app.numberHelper')->formatTaxRate($info['newTaxRate']) . '%</b>',
                    ]);
                }
                $this->ActionLog->customSave('product_tax_changed', $this->identity->getId(), $productId, 'products', $messageString);
            } else {
                $messageString = __d('admin', 'Nothing_changed.');
            }
            $this->Flash->success($messageString);

            $this->getRequest()->getSession()->write('highlightedRowId', $productId);

            $this->set([
                'status' => 1,
                'msg' => __d('admin', 'Saving_successful.'),
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

    }

}
