<?php
declare(strict_types=1);

namespace Admin\Traits\Products;


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

trait EditStatusTrait {

    public function editStatusBulk()
    {
        $this->RequestHandler->renderAs($this, 'json');

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $productIds = $this->request->getData('productIds');
        $status = (int) $this->request->getData('status');

        $data = [];
        foreach($productIds as $productId) {
            $productId = (int) $productId;
            $data[] = [$productId => $status];
        }

        try {

            $this->Product->changeStatus($data);
            $actionLogMessage = __d('admin', '{0,plural,=1{1_product_was} other{#_products_were}}_deactivated.', [
                count($productIds),
            ]);
            $actionLogType = 'product_set_inactive';
            if ($status) {
                $actionLogMessage = __d('admin', '{0,plural,=1{1_product_was} other{#_products_were}}_activated.', [
                    count($productIds),
                ]);
                $actionLogType = 'product_set_active';
            }
            $this->Flash->success($actionLogMessage);
            $this->ActionLog->customSave($actionLogType, $this->identity->getId(), 0, 'products', $actionLogMessage . '<br />Ids: ' . join(',', $productIds));

            $this->set([
                'status' => 1,
                'msg' => __d('admin', 'Saving_successful.'),
            ]);

            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

    }

    public function editStatus($productId, $status)
    {
        $this->Product->changeStatus(
            [
                [$productId => (int) $status]
            ]
        );

        $product = $this->Product->find('all', [
            'conditions' => [
                'Products.id_product' => $productId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_was_deactivated.', [
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>'
        ]);
        $actionLogType = 'product_set_inactive';
        if ($status) {
            $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_was_activated.', [
                '<b>' . $product->name . '</b>',
                '<b>' . $product->manufacturer->name . '</b>'
            ]);
            $actionLogType = 'product_set_active';
            $this->getRequest()->getSession()->write('highlightedRowId', $productId);
        }

        $this->Flash->success($actionLogMessage);

        $this->ActionLog->customSave($actionLogType, $this->identity->getId(), $productId, 'products', $actionLogMessage);

        $this->redirect($this->referer());
    }

}
