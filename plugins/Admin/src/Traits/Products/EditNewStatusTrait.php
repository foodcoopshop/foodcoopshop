<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use App\Services\SanitizeService;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\I18n\Date;
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

trait EditNewStatusTrait
{

    public function editNewStatusBulk(): ?Response
    {
        $this->request = $this->request->withParam('_ext', 'json');

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

        $productIds = $this->request->getData('productIds');
        $status = (int) $this->request->getData('status');

        $data = [];
        foreach($productIds as $productId) {
            $productId = (int) $productId;
            $data[] = [$productId => $status];
        }

        try {

            $productsTable = $this->getTableLocator()->get('Products');
            $productsTable->changeNewStatus($data);
            $actionLogMessage = __d('admin', '{0,plural,=1{1_product_was} other{#_products_were}}_unmarked_as_new.', [
                count($productIds),
            ]);
            $actionLogType = 'product_set_to_old';
            if ($status) {
                $actionLogMessage = __d('admin', '{0,plural,=1{1_product_was} other{#_products_were}}_marked_as_new.', [
                    count($productIds),
                ]);
                $actionLogType = 'product_set_to_new';
            }
            $this->Flash->success($actionLogMessage);
            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), 0, 'products', $actionLogMessage . '<br />Ids: ' . join(',', $productIds));

            $this->set([
                'status' => 1,
                'msg' => __d('admin', 'Saving_successful.'),
            ]);

            $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }
        return null;

    }

    public function editNewStatus(int $productId, int $status): Response
    {

        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->changeNewStatus(
            [
                [$productId => $status]
            ]
        );

        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ],
            contain: [
                'Manufacturers',
            ]
        )->first();

        $actionLogType = 'product_set_to_old';
        $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_is_not_shown_as_new_any_more.', [
            '<b>' . $product->name . '</b>',
            '<b>' . $product->manufacturer->name . '</b>'
        ]);
        if ($status) {
            $actionLogMessage = __d('admin', 'The_product_{0}_from_manufacturer_{1}_is_shown_as_new_from_now_on_for_the_next_{2}_days.', [
                '<b>' . $product->name . '</b>',
                '<b>' . $product->manufacturer->name . '</b>',
                Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW')
            ]);
            $actionLogType = 'product_set_to_new';
        }

        $this->Flash->success($actionLogMessage);
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $productId, 'products', $actionLogMessage);
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        return $this->redirect($this->referer());
    }

}
