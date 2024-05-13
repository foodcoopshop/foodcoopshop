<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;
use Cake\I18n\DateTime;

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

    public function editNewStatus($productId, $status)
    {
        $status = (int) $status;

        if (! in_array($status, [
            APP_OFF,
            APP_ON
        ])) {
            throw new \Exception('New status needs to be 0 or 1: ' . $status);
        }

        $this->Product = $this->getTableLocator()->get('Products');
        $product = $this->Product->find('all',
            conditions: [
                'Products.id_product' => $productId
            ],
            contain: [
                'Manufacturers'
            ]
        )->first();

        $product->created = DateTime::now();
        if ($status == APP_OFF) {
            $product->created = DateTime::now()->subDays((int) Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') + 1);
        }
        $this->Product->save($product);

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
        $this->ActionLog->customSave($actionLogType, $this->identity->getId(), $productId, 'products', $actionLogMessage);
        $this->getRequest()->getSession()->write('highlightedRowId', $productId);

        $this->redirect($this->referer());
    }

}
