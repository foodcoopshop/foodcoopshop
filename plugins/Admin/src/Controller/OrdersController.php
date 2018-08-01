<?php

namespace Admin\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * OrdersController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class OrdersController extends AdminAppController
{

    public function editComment()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderId = $this->getRequest()->getData('orderId');
        $orderComment = htmlspecialchars_decode(strip_tags(trim($this->getRequest()->getData('orderComment')), '<strong><b>'));

        $this->Order = TableRegistry::getTableLocator()->get('Orders');
        $oldOrder = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'Customers'
            ]
        ])->first();

        $this->Order->save(
            $this->Order->patchEntity(
                $oldOrder,
                [
                    'comment' => $orderComment
                ]
            )
        );

        $this->Flash->success(__d('admin', 'The_comment_was_changed_successfully.'));

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('order_comment_changed', $this->AppAuth->getUserId(), $orderId, 'orders', __d('admin', 'The_comment_of_the_order_number_{0}_by_{1}_was_changed:', [$oldOrder->id_order, $oldOrder->customer->name]) . ' <div class="changed">' . $orderComment . ' </div>');

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }

    public function editDate()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $orderId = $this->getRequest()->getData('orderId');
        $date = $this->getRequest()->getData('date');

        $this->Order = TableRegistry::getTableLocator()->get('Orders');
        $oldOrder = $this->Order->find('all', [
            'conditions' => [
                'Orders.id_order' => $orderId
            ],
            'contain' => [
                'Customers'
            ]
        ])->first();

        $oldDate = $oldOrder->date_add;

        $this->Order->save(
            $this->Order->patchEntity(
                $oldOrder,
                [
                    'date_add' => $date
                ]
            )
        );

        $message = __d('admin', 'The_date_of_the_order_{0}_of_{1}_was_changed_to_{2}.', [
            $orderId,
            $oldOrder->customer->name,
            Configure::read('app.timeHelper')->formatToDateShort($date)
        ]);
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->ActionLog->customSave('orders_date_changed', $this->AppAuth->getUserId(), $orderId, 'orders', $message);

        $this->Flash->success($message);

        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ]));
    }
}
