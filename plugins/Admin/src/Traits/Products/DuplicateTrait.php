<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\I18n\DateTime;
use App\Model\Entity\OrderDetail;
use Cake\Http\Response;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Martin Hatlauf <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait DuplicateTrait
{

    public function duplicate(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productId = $this->getRequest()->getData('productId');
        $copyAmount = $this->getRequest()->getData('copyAmount');

        $productId = 60;
        $copyAmount = 1;

        $productsTable = $this->getTableLocator()->get('Products');
        $stockAvailableTable = $this->getTableLocator()->get('StockAvailables');
        $categoryProductTable = $this->getTableLocator()->get('CategoryProducts');
        $unitsTable = $this->getTableLocator()->get('Units');
        $purchasePricesTable = $this->getTableLocator()->get('PurchasePriceProducts');
        $depositsTable = $this->getTableLocator()->get('DepositProducts');

        $productOg = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
            ],
        )->first();

        $stockAvailableOg = $stockAvailableTable->find('all',
            conditions: [
                $stockAvailableTable->aliasField('id_product') => $productId,
                $stockAvailableTable->aliasField('id_product_attribute') => APP_OFF,
            ],
        )->first();

        $unitOg = $unitsTable->find('all',
            conditions: [
                $unitsTable->aliasField('id_product') => $productId,
            ],
        )->first();

        $purchasePriceOg = $purchasePricesTable->find('all',
            conditions: [
                $purchasePricesTable->aliasField('product_id') => $productId,
                $purchasePricesTable->aliasField('product_attribute_id') => APP_OFF,
            ],
        )->first();

        $categoriesOg = $categoryProductTable->find('all',
            conditions: [
                $categoryProductTable->aliasField('id_product') => $productId,
            ],
        );

        $depositOg = $depositsTable->find('all',
            conditions: [
                $depositsTable->aliasField('id_product') => $productId,
            ],
        )->first();


        $maxCopyNumber = 1;
        $preExistingCopies = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('name LIKE') => __d('admin', 'Copy_({0})_of_{1}', [
                    '%',
                    $productOg->name,
                ])
            ],
        );
//        dd(
//            [
//                $stockAvailableTable,
//                $stockAvailableOg,
//            ]
//        );
        $copies = [];
        for ($i = 0; $i < $copyAmount; $i++) {
            $productCopy = $productsTable->newEntity(
                [
                    'id_manufacturer' => $productOg->id_manufacturer,
                    'id_tax' => $productOg->id_tax,
                    'id_price' => $productOg->id_price,
                    'name' => __d('admin', 'Copy_({0})_of_{1}', [
                        ($maxCopyNumber + $i),
                        $productOg->name,
                    ]),
                    'unity' => $productOg->unity,
                    'is_declaration_ok' => $productOg->is_declaration_ok,
                    'is_stock_product' => $productOg->is_stock_product,

                    'delivery_rhythm_type' => $productOg->delivery_rhythm_type,
                    'delivery_rhythm_count' => $productOg->delivery_rhythm_count,
                    'delivery_rhythm_first_delivery_day' => $productOg->delivery_rhythm_first_delivery_day,
                    'delivery_rhythm_order_possible_units' => $productOg->delivery_rhythm_order_possible_units,
                    'delivery_rhythm_send_order_list_week' => $productOg->delivery_rhythm_send_order_list_week,
                    'delivery_rhythm_send_order_list_day' => $productOg->delivery_rhythm_send_order_list_day,

                    'created' => DateTime::now(),
                    'modified' => DateTime::now(),
                    'new' => DateTime::now(),
                ],
                [
                    'validate' => false
                ],
            );
            $productCopy = $productsTable->save($productCopy);;

            $stockAvailableCopy = $stockAvailableTable->newEntity(
                [
                    'id_product' => $productCopy->id_product,
                    'quantity' => $stockAvailableOg->quantity,
                    'quantity_limit' => $stockAvailableOg->quantity_limit,
                    'sold_out_limit' => $stockAvailableOg->sold_out_limit,
                    'always_available' => $stockAvailableOg->always_available,
                    'default_quantity_after_sending_order_lists' => $stockAvailableOg->default_quantity_after_sending_order_lists,
                ],
                [
                    'validate' => false
                ],
            );
            $stockAvailableCopy = $stockAvailableTable->save($stockAvailableCopy);

            if (!empty($unitOg)) {
                $unitCopy = $unitsTable->newEntity(
                    [
                        'id_product' => $productCopy->id_product,
                        'price_incl_per_unit' => $unitOg->price,
                        'name' => $unitOg->name,
                        'amount' => $unitOg->amount,
                        'price_per_unit_enabled' => $unitOg->price_per_unit_enabled,
                        'quantity_in_units' => $unitOg->quantity_in_units,
                        'use_weight_as_amount' => $unitOg->use_weight_as_amount,
                    ],
                    [
                        'validate' => false
                    ],
                );
                $unitCopy = $unitsTable->save($unitCopy);
            }

            $purchasePriceCopy = $purchasePricesTable->newEntity(
                [
                    'product_id' => $productCopy->id_product,
                    'tax_id' => $purchasePriceOg->id_tax,
                    'price' => $purchasePriceOg->price,
                ],
                [
                    'validate' => false
                ],
            );
            $purchasePriceOg = $purchasePricesTable->save($purchasePriceCopy);

            foreach ($categoriesOg as $categoryOg) {
                $categoryCopy = $categoryProductTable->newEntity(
                    [
                        'id_category' => $categoryOg->id_category,
                        'id_product' => $productCopy->id_product,
                    ],
                    [
                        'validate' => false
                    ],
                );
                $categoryCopy = $categoryProductTable->save($categoryCopy);
            }

            if (!empty($depositOg)) {
                $depositCopy = $depositsTable->newEntity(
                    [
                        'id_product' => $productCopy->id_product,
                        'deposit' => $depositOg->deposit,
                    ],
                    [
                        'validate' => false
                    ],
                );
                $depositCopy = $depositsTable->save($depositCopy);
            }


            $copies[] = $productCopy;
        }

        $preparedProductForActionLog = [];
        foreach ($copies as $productCopy) {
            $preparedProductForActionLog[] = '<b>' . $productCopy->name . '</b>: ID ' . $productCopy->id_product;
        }

        $message = __d('admin', 'product_was_copied_successfully.');

        $this->Flash->success($message);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('product_copied', $this->identity->getId(), 0, 'products', $message . '<br />' . join('<br />', $preparedProductForActionLog));

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        return null;

    }

}
