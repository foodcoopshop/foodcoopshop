<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Datasource\Exception\RecordNotFoundException;
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

trait AddTrait 
{

    public function add(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $manufacturerId = $this->getRequest()->getData('manufacturerId');
        $productName = $this->getRequest()->getData('name');
        $descriptionShort = $this->getRequest()->getData('descriptionShort');
        $description = $this->getRequest()->getData('description');
        $unity = $this->getRequest()->getData('unity');
        $isDeclarationOk = $this->getRequest()->getData('isDeclarationOk');
        $idStorageLocation = $this->getRequest()->getData('idStorageLocation');
        $barcode = $this->getRequest()->getData('barcode');

        // if logged user is manufacturer, then get param manufacturer id is NOT used
        // but logged user id for security reasons
        if ($this->identity->isManufacturer()) {
            $manufacturerId = $this->identity->getManufacturerId();
        }

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $manufacturer = $manufacturersTable->find('all',
            conditions: [
                $manufacturersTable->aliasField('id_manufacturer') => $manufacturerId,
            ]
        )->first();

        try {
            if (empty($manufacturer)) {
                throw new RecordNotFoundException('manufacturer not existing');
            }
            $productsTable = $this->getTableLocator()->get('Products');
            $productEntity =$productsTable->add(
                $manufacturer,
                $productName,
                $descriptionShort,
                $description,
                $unity,
                $isDeclarationOk,
                $idStorageLocation,
                $barcode,
            );
            if ($productEntity->hasErrors()) {
                throw new \Exception(join(' ',$productsTable->getAllValidationErrors($productEntity)));
            }
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $messageString = __d('admin', 'The_product_{0}_was_created_for_{1}.', [
            '<b>' . $productName . '</b>',
            '<b>' . $manufacturer->name . '</b>',
        ]);
        $this->Flash->success($messageString);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave('product_added', $this->identity->getId(), $productEntity->id_product, 'products', $messageString);

        $this->getRequest()->getSession()->write('highlightedRowId', $productEntity->id_product);

        $this->set([
            'status' => 1,
        ]);
        $this->viewBuilder()->setOption('serialize', ['status']);
        return null;

    }

}
