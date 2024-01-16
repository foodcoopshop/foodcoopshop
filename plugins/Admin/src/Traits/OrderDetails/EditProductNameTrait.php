<?php
declare(strict_types=1);

namespace Admin\Traits\OrderDetails;

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

trait EditProductNameTrait {

    public function editProductName()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $orderDetailId = (int) $this->getRequest()->getData('orderDetailId');
        $productName = strip_tags(html_entity_decode($this->getRequest()->getData('productName')));

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $oldOrderDetail = $this->OrderDetail->find('all',
            conditions: [
                'OrderDetails.id_order_detail' => $orderDetailId,
            ],
        )->first();
        $oldName = $oldOrderDetail->product_name;

        try {
            $entity = $this->OrderDetail->patchEntity(
                $oldOrderDetail,
                ['product_name' => $productName],
                ['validate' => 'name'],
            );
            if ($entity->hasErrors()) {
                $errorMessages = $this->OrderDetail->getAllValidationErrors($entity);
                throw new \Exception(join('<br />', $errorMessages));
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $this->OrderDetail->save($entity);

        $message = __d('admin', 'The_name_of_the_ordered_product_{0}_was_successfully_changed_to_{1}.', [
            '<b>' . $oldName . '</b>',
            '<b>' . $productName . '</b>',
        ]);

        $this->Flash->success($message);

        $this->getRequest()->getSession()->write('highlightedRowId', $orderDetailId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}
