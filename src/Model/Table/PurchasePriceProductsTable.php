<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Validation\Validator;
use Cake\Datasource\FactoryLocator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PurchasePriceProductsTable extends AppTable
{

    public function initialize(array $config): void
    {
        $this->setTable('purchase_prices');
        parent::initialize($config);
        $this->setPrimaryKey('product_id');
        $this->belongsTo('Taxes', [
            'foreignKey' => 'tax_id'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->greaterThanOrEqual('price', 0, __('The_price_needs_to_be_greater_or_equal_than_0.'));
        return $validator;
    }

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

    public function savePurchasePriceTax($taxId, $productId, $oldProduct): Array
    {
        $changedTaxInfoForMessage = [];
        $oldPurchasePriceTaxRate = 0;
        if (!empty($oldProduct->purchase_price_product) && !empty($oldProduct->purchase_price_product->tax)) {
            $oldPurchasePriceTaxRate = $oldProduct->purchase_price_product->tax->rate;
        }

        $tax = $this->Taxes->find('all', [
            'conditions' => [
                'Taxes.id_tax' => $taxId,
            ]
        ])->first();

        $taxRate = 0;
        if (! empty($tax)) {
            $taxRate = Configure::read('app.numberHelper')->formatTaxRate($tax->rate);
        }

        $entity2Save = $this->getEntityToSaveByProductId($productId);
        $patchedEntity = $this->patchEntity(
            $entity2Save,
            [
                'tax_id' => $taxId,
            ]
        );

        if ($patchedEntity->isDirty('tax_id')) {

            $this->Product = FactoryLocator::get('Table')->get('Products');

            if (! empty($oldProduct->product_attributes)) {
                // update net price of all attributes
                foreach ($oldProduct->product_attributes as $attribute) {
                    if (!empty($attribute->purchase_price_product_attribute)) {
                        $newNetPrice = $this->Product->getNetPriceForNewTaxRate($attribute->purchase_price_product_attribute->price, $oldPurchasePriceTaxRate, $taxRate);
                        $entity2Save = $this->getEntityToSaveByProductAttributeId($attribute->id_product_attribute);
                        $entity2Save->price = $newNetPrice;
                        $this->save($entity2Save);
                    }
                }
            } else {
                // update net price of main product
                if (!empty($oldProduct->purchase_price_product)) {
                    $newNetPrice = $this->Product->getNetPriceForNewTaxRate($oldProduct->purchase_price_product->price, $oldPurchasePriceTaxRate, $taxRate);
                    $patchedEntity->price = $newNetPrice;
                }
            }

            $changedTaxInfoForMessage[] = [
                'label' => __d('admin', 'Purchase_price') . ': ',
                'oldTaxRate' => $oldPurchasePriceTaxRate,
                'newTaxRate' => $taxRate,
            ];
            $this->save($patchedEntity);
        }

        return $changedTaxInfoForMessage;

    }


}
