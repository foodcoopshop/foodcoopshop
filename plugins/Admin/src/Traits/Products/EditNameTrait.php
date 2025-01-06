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

trait EditNameTrait 
{

    public function editName(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $productId = $this->getRequest()->getData('productId');

        $productsTable = $this->getTableLocator()->get('Products');
        $oldProduct = $productsTable->find('all',
            conditions: [
                $productsTable->aliasField('id_product') => $productId,
            ],
            contain: [
                'Manufacturers',
            ]
        )->first();

        try {
            $productsTable->changeName(
                [
                    [$productId => [
                        'name' => $this->getRequest()->getData('name'),
                        'description' => $this->getRequest()->getData('description'),
                        'description_short' => $this->getRequest()->getData('descriptionShort'),
                        'unity' => $this->getRequest()->getData('unity'),
                        'is_declaration_ok' => $this->getRequest()->getData('isDeclarationOk'),
                        'id_storage_location' => $this->getRequest()->getData('idStorageLocation'),
                        'barcode' => $this->getRequest()->getData('barcode'),
                    ]]
                ]
            );
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $this->Flash->success(__d('admin', 'The_product_was_changed_successfully.'));
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        if ($this->getRequest()->getData('name') != $oldProduct->name) {
            $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_was_renamed_to_{2}.', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                '<i>"' . $this->getRequest()->getData('name') . '"</i>'
            ]);
            $actionLogsTable->customSave('product_name_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);
        }
        if ($this->getRequest()->getData('unity') != $oldProduct->unity) {
            $actionLogMessage = __d('admin', 'The_unity_of_the_product_{0}_from_manufacturer_{1}_was_changed_to_{2}.', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                '<i>"' . $this->getRequest()->getData('unity') . '"</i>'
            ]);
            $actionLogsTable->customSave('product_unity_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);
        }
        if ($this->getRequest()->getData('description') != $oldProduct->description) {
            $actionLogMessage = __d('admin', 'The_description_of_the_product_{0}_from_manufacturer_{1}_was_changed:_{2}', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                '<div class="changed">' . $this->getRequest()->getData('description') . ' </div>'
            ]);
            $actionLogsTable->customSave('product_description_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);
        }
        if ($this->getRequest()->getData('descriptionShort') != $oldProduct->description_short) {
            $actionLogMessage = __d('admin', 'The_short_description_of_the_product_{0}_from_manufacturer_{1}_was_changed:_{2}', [
                '<b>' . $oldProduct->name . '</b>',
                '<b>' . $oldProduct->manufacturer->name . '</b>',
                '<div class="changed">' . $this->getRequest()->getData('descriptionShort') . ' </div>'
            ]);
            $actionLogsTable->customSave('product_description_short_changed', $this->identity->getId(), $productId, 'products', $actionLogMessage);
        }

        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);
        return null;
    }

}
