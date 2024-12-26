<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;
use Authorization\Policy\ResultInterface;
use Authorization\IdentityInterface;
use Cake\ORM\TableRegistry;

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
class OrderDetailsPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if ($identity === null) {
            return false;
        }

        switch ($request->getParam('action')) {
            case 'profit';
            case 'editPurchasePrice';
                return Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && $identity->isSuperadmin();
            case 'initInstantOrder':
            case 'initSelfServiceOrder':
            case 'iframeInstantOrder':
            case 'iframeSelfServiceOrder':
                if ($identity->isSuperadmin() || $identity->isAdmin()) {
                    return true;
                }
                if ($identity->isCustomer() && !Configure::read('app.isCustomerAllowedToEditOwnOrders')) {
                    return true;
                }
                return false;
            case 'editProductName':
                return $identity->isSuperadmin();
            case 'addFeedback';
                if (!Configure::read('appDb.FCS_FEEDBACK_TO_PRODUCTS_ENABLED')) {
                    return false;
                }
                if ($identity->isSuperadmin() || $identity->isAdmin()) {
                    return true;
                }
                if ($identity->isCustomer()) {
                    $orderDetailTable = TableRegistry::getTableLocator()->get('OrderDetails');
                    $orderDetail = $orderDetailTable->find('all',
                        conditions:  [
                            'OrderDetails.id_order_detail' => $request->getData('orderDetailId')
                        ]
                    )->first();
                    if (!empty($orderDetail)) {
                        if ($orderDetail->id_customer == $identity->getId()) {
                            return true;
                        }
                    }
                }
                return false;
            case 'setElFinderUploadPath':
                return $identity !== null && !$identity->isManufacturer();
            case 'delete':
            case 'editProductPrice':
            case 'editProductAmount':
            case 'editProductQuantity':
            case 'editCustomer':
            case 'editPickupDay':
                if ($identity->isSuperadmin() || $identity->isAdmin()) {
                    return true;
                }
                if ($request->getParam('action') == 'editCustomer' && ($identity->isManufacturer() || $identity->isCustomer())) {
                    return false;
                }
                /*
                 * START customer/manufacturer OWNER check
                 * param orderDetailId / orderDetailIds is passed via ajaxCall
                 */
                if (!empty($request->getData('orderDetailIds'))) {
                    $accessAllowed = false;
                    foreach ($request->getData('orderDetailIds') as $orderDetailId) {
                        $accessAllowed |= $this->checkOrderDetailIdAccess((int) $orderDetailId, $identity);
                    }
                    return (bool) $accessAllowed;
                }
                if (!empty($request->getData('orderDetailId'))) {
                    return $this->checkOrderDetailIdAccess((int) $request->getData('orderDetailId'), $identity);
                }
                return false;
            case 'index':
                if ($identity->isCustomer() && !Configure::read('app.isCustomerAllowedToViewOwnOrders')) {
                    return false;
                }
                return true;
            default:
                return $identity !== null;
        }

    }

    private function checkOrderDetailIdAccess(int $orderDetailId, $identity): bool
    {
        if ($identity->isCustomer() || $identity->isManufacturer()) {
            $orderDetailTable = TableRegistry::getTableLocator()->get('OrderDetails');
            $orderDetail = $orderDetailTable->find('all',
                conditions: [
                    'OrderDetails.id_order_detail' => $orderDetailId,
                ],
                contain: [
                    'Products'
                ]
            )->first();
            if (!empty($orderDetail)) {
                if ($identity->isManufacturer() && $orderDetail->product->id_manufacturer == $identity->getManufacturerId()) {
                    return true;
                }
                if ($identity->isCustomer() && !Configure::read('isCustomerAllowedToModifyOwnOrders') && $orderDetail->id_customer == $identity->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

}