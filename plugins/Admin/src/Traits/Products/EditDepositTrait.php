<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;
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

trait EditDepositTrait 
{

    public function editDeposit(): ?Response
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
                'DepositProducts',
                'ProductAttributes.DepositProductAttributes',
                'ProductAttributes.ProductAttributeCombinations.Attributes'
            ]
        )->first();

        try {
            $productsTable->changeDeposit(
                [
                    [$originalProductId => $this->getRequest()->getData('deposit')]
                ]
            );
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $depositEntity = $oldProduct->deposit_product;
        $productName = $oldProduct->name;

        if ($ids['attributeId'] > 0) {
            $attributeName = '';
            foreach ($oldProduct->product_attributes as $attribute) {
                if ($attribute->id_product_attribute == $ids['attributeId']) {
                    $attributeName = $attribute->product_attribute_combination->attribute->name;
                    $depositEntity = $attribute->deposit_product_attribute;
                    break;
                }
            }
            $productName .= ' ('.__d('admin', 'Attribute').': '.$attributeName.')';
        }

        $oldDeposit = 0;
        if (!empty($depositEntity->deposit)) {
            $oldDeposit = $depositEntity->deposit;
        }
        $deposit = Configure::read('app.numberHelper')->getStringAsFloat($this->getRequest()->getData('deposit'));

        $actionLogMessage = __d('admin', 'The_deposit_of_the_product_{0}_was_changed_from_{1}_to_{2}.', [
            '<b>' . $productName . '</b>',
            Configure::read('app.numberHelper')->formatAsCurrency($oldDeposit),
            Configure::read('app.numberHelper')->formatAsCurrency($deposit)
        ]);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('product_deposit_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);

        $this->Flash->success($actionLogMessage);
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        return null;
    }

}
