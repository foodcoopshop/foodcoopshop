<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Core\Configure;
use Authorization\Exception\ForbiddenException;

class OrderDetailsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        switch ($request->getParam('action')) {
            case 'profit';
            case 'editPurchasePrice';
                return Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && $identity->isSuperadmin();
                break;
            case 'editProductName':
                return $identity->isSuperadmin();
                break;
            case 'addFeedback';
                if (!Configure::read('appDb.FCS_FEEDBACK_TO_PRODUCTS_ENABLED')) {
                    return false;
                }
                if ($identity->isSuperadmin() || $identity->isAdmin()) {
                    return true;
                }
                if ($identity->isCustomer()) {
                    $orderDetailTable = FactoryLocator::get('Table')->get('OrderDetails');
                    $orderDetail = $orderDetailTable->find('all', [
                        'conditions' => [
                            'OrderDetails.id_order_detail' => $request->getData('orderDetailId')
                        ]
                    ])->first();
                    if (!empty($orderDetail)) {
                        if ($orderDetail->id_customer == $identity->getId()) {
                            return true;
                        }
                    }
                }
                return false;
                break;
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
                return false;
                break;
        }

    }

    private function checkOrderDetailIdAccess(int $orderDetailId, $identity): bool
    {
        if ($identity->isCustomer() || $identity->isManufacturer()) {
            $orderDetailTable = FactoryLocator::get('Table')->get('OrderDetails');
            $orderDetail = $orderDetailTable->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId,
                ],
                'contain' => [
                    'Products'
                ]
            ])->first();
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