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

        $productsTable = $this->getTableLocator()->get('Products');

        $stockAvailable = $this->getTableLocator()->get('StockAvailables');
        $categoryProduct = $this->getTableLocator()->get('CategoryProducts');
        $units = $this->getTableLocator()->get('Units');

        $productId = $this->getRequest()->getData('productId');
        $copyOptions = $this->getRequest()->getData('copyOptions');
        $product = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
            ],
            contain: [
                'Manufacturers'
            ]
        )->first();

        // copy the data
        // need to check if $product-id is in units table
        // if yes put all the new id's there with the correct values.
        $copies = [];
        $productsTable->add();
        for ($i = 0; $i < $copyOptions['amount']; $i++) {
            $copy = $productsTable->newEntity([
                'id_manufacturer' => $product->id_manufacturer,
                'id_tax' =>  $product->id_tax,
                'id_price' =>  $product->id_price,
                'name' =>  $product->name,
                'unity' =>  $product->unity,
                'is_declaration_ok' =>  $product->is_declaration_ok,
                'is_stock_product' =>  $product->is_stock_product,
                'delivery_rhythm_type' =>  $product->delivery_rhythm_type,
                'delivery_rhythm_count' =>  $product->delivery_rhythm_count,
                'delivery_rhythm_first_delivery_day' => $product->delivery_rhythm_first_delivery_day,
                'delivery_rhythm_order_possible_units'  =>  $product->delivery_rhythm_order_possible_units,
                'delivery_rhythm_send_order_list_week'  =>  $product->delivery_rhythm_send_order_list_week,
                'delivery_rhythm_send_order_list_day'  =>  $product->delivery_rhythm_send_order_list_day,
                'created' => DateTime::now(),
                'modified' => DateTime::now(),
                'new' => DateTime::now(),
            ]);
            $copy = $productsTable->save($copy);;
            $copies[] = $copy;



        }

        $preparedProductForActionLog = [];
        $preparedProductForActionLog[] = '<b>' . $product->name . '</b>: ID ' . $product->id_product . ',  ' . $product->manufacturer->name;

//        $cronjobsTable = $this->getTableLocator()->get('Cronjobs');
//        if ($cronjobsTable->isInvoiceCronjobActive()) {
//            try {
//                // check if open order exist
//                $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
//                $query = $orderDetailsTable->find('all',
//                    conditions: [
//                        $orderDetailsTable->aliasField('product_id') => $productId,
//                        $orderDetailsTable->aliasField('order_state IN') => [
//                            OrderDetail::STATE_OPEN,
//                            OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER,
//                        ],
//                    ],
//                    contain: [
//                        'Products',
//                    ]
//                );
//                $query->select(
//                    [
//                        'orderDetailsCount' => $query->func()->count('OrderDetails.product_id'),
//                        'productName' => 'Products.name'
//                    ]
//                );
//                $query->groupBy('OrderDetails.product_id');
//
//                $errors = [];
//                if ($query->count() > 0) {
//                    foreach ($query as $orderDetail) {
//                        $errors[] = __d('admin', 'The_product_{0}_has_{1,plural,=1{1_open_order} other{#_open_orders}}.',
//                            [
//                                '<b>' . $orderDetail->productName . '</b>',
//                                $orderDetail->orderDetailsCount,
//                            ]
//                        );
//                    }
//                }
//                if (!empty($errors)) {
//                    $errorString = '<ul><li>' . join('</li><li>', $errors) . '</li></ul>';
//                    $errorString .= __d('admin', 'Please_try_again_as_soon_as_the_next_invoice_has_been_generated.');
//                    throw new \Exception($errorString);
//                }
//            } catch (\Exception $e) {
//                return $this->sendAjaxError($e);
//            }
//        }


        $message = __d('admin', 'product_was_copied_successfully.');

        $this->Flash->success($message);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('product_deleted', $this->identity->getId(), 0, 'products', $message . '<br />' . join('<br />', $preparedProductForActionLog));

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        return null;

    }

}
