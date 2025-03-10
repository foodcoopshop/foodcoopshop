<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Http\Response;


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

    public function editIsStockProduct(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $originalProductId = $this->getRequest()->getData('productId');

        $productsTable = $this->getTableLocator()->get('Products');
        $ids = $productsTable->getProductIdAndAttributeId($originalProductId);
        $productId = $ids['productId'];

        $oldProduct = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
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
            $productsTable->changeIsStockProduct(
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
        return null;
    }

}
