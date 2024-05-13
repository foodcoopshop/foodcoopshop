<?php
declare(strict_types=1);

namespace Admin\Traits\Products;

use Cake\Core\Configure;

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

trait CalculateSellingChargeWithSurchargeTrait 
{

    public function calculateSellingPriceWithSurcharge()
    {

        $this->request = $this->request->withParam('_ext', 'json');
        $productIds = $this->getRequest()->getData('productIds');

        $surcharge = Configure::read('app.numberHelper')->getStringAsFloat($this->getRequest()->getData('surcharge'));
        if ($surcharge < 0) {
            throw new \Exception(__d('admin', 'Surcharge_needs_to_be_greater_than_0.'));
        }

        try {
            $result = $this->Product->PurchasePriceProducts->getSellingPricesWithSurcharge($productIds, $surcharge);
            $this->Product->changePrice($result['pricesToChange']);
        } catch (\Exception $e) {
            return $this->sendAjaxError($e);
        }

        $message = __d('admin', 'The_selling_price_net_was_set_to:_{0}_of_purchase_price_net', [
            '<b>' . Configure::read('app.numberHelper')->formatAsPercent($surcharge) . '</b>',
        ]);
        $this->Flash->success($message);
        $this->ActionLog->customSave('product_price_changed', $this->identity->getId(), 0, 'products', $message . '<br />' . join('<br />', $result['preparedProductsForActionLog']));

        $this->set([
            'status' => 1,
            'msg' => 'ok',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

}
