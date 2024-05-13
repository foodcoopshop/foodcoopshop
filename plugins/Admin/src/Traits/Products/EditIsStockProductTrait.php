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

trait EditIsStockProductTrait 
{

    public function editIsStockProduct()
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $originalProductId = $this->getRequest()->getData('productId');

        $ids = $this->Product->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $this->Product->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'StockAvailables',
                'Manufacturers',
                'ProductAttributes',
                'ProductAttributes.StockAvailables',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        )->first();

        try {
            $this->Product->changeIsStockProduct(
                [
                    [
                        $originalProductId => $this->getRequest()->getData('isStockProduct')
                    ]
                ]
            );
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $this->Flash->success(__d('admin', 'The_product_{0}_was_changed_successfully_to_a_stock_product.', ['<b>' . $oldProduct->name . '</b>']));

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
    }

}
