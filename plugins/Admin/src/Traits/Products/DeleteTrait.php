<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Model\Table\OrderDetailsTable;
use Cake\I18n\DateTime;
use App\Model\Entity\OrderDetail;

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

trait DeleteTrait {

    protected OrderDetailsTable $OrderDetail;

    public function delete()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productIds = $this->getRequest()->getData('productIds');
        $products = $this->Product->find('all',
            conditions: [
                'Products.id_product IN' => $productIds
            ],
            contain: [
                'Manufacturers'
            ]
        );
        $preparedProductsForActionLog = [];
        foreach($products as $product) {
            $preparedProductsForActionLog[] = '<b>' . $product->name . '</b>: ID ' . $product->id_product . ',  ' . $product->manufacturer->name;
        }

        try {
            // check if open order exist
            $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
            $query = $this->OrderDetail->find('all',
                conditions: [
                    'OrderDetails.product_id IN' => $productIds,
                    'OrderDetails.order_state IN' => [
                        OrderDetail::STATE_OPEN,
                        OrderDetail::STATE_ORDER_LIST_SENT_TO_MANUFACTURER,
                    ],
                ],
                contain: [
                    'Products'
                ]
            );
            $query->select(
                [
                    'orderDetailsCount' => $query->func()->count('OrderDetails.product_id'),
                    'productName' => 'Products.name'
                ]
            );
            $query->group('OrderDetails.product_id');

            $errors = [];
            if ($query->count() > 0) {
                foreach($query as $orderDetail) {
                    $errors[] = __d('admin', 'The_product_{0}_has_{1,plural,=1{1_open_order} other{#_open_orders}}.',
                        [
                            '<b>' . $orderDetail->productName . '</b>',
                            $orderDetail->orderDetailsCount,
                        ]
                    );
                }
            }
            if (!empty($errors)) {
                $errorString = '<ul><li>' . join('</li><li>', $errors) . '</li></ul>';
                $errorString .= __d('admin', 'Please_try_again_as_soon_as_the_next_invoice_has_been_generated.');
                throw new \Exception($errorString);
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        // 1) set field active to -1
        $this->Product->updateAll([
            'active' => APP_DEL,
            'modified' => DateTime::now() // timestamp behavior does not work here...
        ], [
            'id_product IN' => $productIds
        ]);

        // 2) delete image
        foreach($productIds as $productId) {
            $this->Product->changeImage(
                [
                    [$productId => 'no-image']
                ]
            );
        }

        $message = __d('admin', '{0,plural,=1{1_product_was} other{#_products_were}}_deleted_successfully.', [
            count($productIds)
        ]);
        $this->Flash->success($message);
        $this->ActionLog->customSave('product_deleted', $this->identity->getId(), 0, 'products', $message . '<br />' . join('<br />', $preparedProductsForActionLog));

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

}
