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
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait DeleteImageTrait 
{

    /**
     * deletes both db entries and physical files (thumbs)
     */
    public function deleteImage($productId)
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productId = (int) $productId;

        if ($productId == 0 || $productId == '') {
            $message = 'Product ID not correct: ' . $productId;
            $this->log($message);
            $this->set([
                'status' => 0,
                'msg' => $message,
            ]);
            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
            return;
        }

        $productsTable = $this->getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
            ],
            contain: [
                'Images',
                'Manufacturers'
            ]
        )->first();

        $productsTable->changeImage(
            [
                [$productId => 'no-image']
            ]
        );

        $actionLogMessage = __d('admin', 'Image_ID_{0}_from_manufacturer_{1}_was_deleted_successfully_Product_{1}_Manufacturer_{2}.', [
            $product->image->id_image,
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>'
        ]);

        $this->Flash->success($actionLogMessage);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('product_image_deleted', $this->identity->getId(), $productId, 'products', $actionLogMessage);

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

}
