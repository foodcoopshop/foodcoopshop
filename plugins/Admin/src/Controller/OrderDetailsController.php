<?php
declare(strict_types=1);

namespace Admin\Controller;

use Admin\Traits\OrderDetails\AddFeedbackTrait;
use Admin\Traits\OrderDetails\DeleteTrait;
use Admin\Traits\OrderDetails\EditCustomerTrait;
use Admin\Traits\OrderDetails\EditPickupDayCommentTrait;
use Admin\Traits\OrderDetails\EditPickupDayTrait;
use Admin\Traits\OrderDetails\EditProductAmountTrait;
use Admin\Traits\OrderDetails\EditProductNameTrait;
use Admin\Traits\OrderDetails\EditProductPriceTrait;
use Admin\Traits\OrderDetails\EditProductQuantityTrait;
use Admin\Traits\OrderDetails\EditProductsPickedUpTrait;
use Admin\Traits\OrderDetails\EditPurchasePriceTrait;
use Admin\Traits\OrderDetails\IndexTrait;
use Admin\Traits\OrderDetails\OrderForDifferentCustomerTrait;
use Admin\Traits\OrderDetails\ProfitTrait;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use App\Services\PdfWriter\OrderDetailsPdfWriterService;

/**
* FoodCoopShop - The open source software for your foodcoop
*
* Licensed under the GNU Affero General Public License version 3
* For full copyright and license information, please see LICENSE
* Redistributions of files must retain the above copyright notice.
*
* @since         FoodCoopShop 1.0.0
* @license       https://opensource.org/licenses/AGPL-3.0
* @author        Mario Rothauer <office@foodcoopshop.com>
* @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
* @link          https://www.foodcoopshop.com
*/
class OrderDetailsController extends AdminAppController
{

    use AddFeedbackTrait;
    use EditCustomerTrait;
    use EditPickupDayTrait;
    use EditPickupDayCommentTrait;
    use EditProductNameTrait;
    use EditProductAmountTrait;
    use EditProductPriceTrait;
    use EditProductQuantityTrait;
    use EditProductsPickedUpTrait;
    use EditPurchasePriceTrait;
    use DeleteTrait;
    use IndexTrait;
    use OrderForDifferentCustomerTrait;
    use ProfitTrait;

    protected $OrderDetail;

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'profit';
            case 'editPurchasePrice';
                return Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') && $this->identity->isSuperadmin();
                break;
            case 'editProductName':
                return $this->identity->isSuperadmin();
                break;
            case 'addFeedback';
                if (!Configure::read('appDb.FCS_FEEDBACK_TO_PRODUCTS_ENABLED')) {
                    return false;
                }
                if ($this->identity->isSuperadmin() || $this->identity->isAdmin()) {
                    return true;
                }
                if ($this->identity->isCustomer()) {
                    $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
                    $orderDetail = $this->OrderDetail->find('all', [
                        'conditions' => [
                            'OrderDetails.id_order_detail' => $this->getRequest()->getData('orderDetailId')
                        ]
                    ])->first();
                    if (!empty($orderDetail)) {
                        if ($orderDetail->id_customer == $this->identity->getId()) {
                            return true;
                        }
                    }
                }
                $this->sendAjaxError(new ForbiddenException(ACCESS_DENIED_MESSAGE));
                return false;
                break;
            case 'delete':
            case 'editProductPrice':
            case 'editProductAmount':
            case 'editProductQuantity':
            case 'editCustomer':
                if ($this->identity->isSuperadmin() || $this->identity->isAdmin()) {
                    return true;
                }
                if ($this->getRequest()->getParam('action') == 'editCustomer' && ($this->identity->isManufacturer() || $this->identity->isCustomer())) {
                    return false;
                }
                /*
                 * START customer/manufacturer OWNER check
                 * param orderDetailId / orderDetailIds is passed via ajaxCall
                 */
                if (!empty($this->getRequest()->getData('orderDetailIds'))) {
                    $accessAllowed = false;
                    foreach ($this->getRequest()->getData('orderDetailIds') as $orderDetailId) {
                        $accessAllowed |= $this->checkOrderDetailIdAccess($orderDetailId);
                    }
                    return $accessAllowed;
                }
                if (!empty($this->getRequest()->getData('orderDetailId'))) {
                    return $this->checkOrderDetailIdAccess($this->getRequest()->getData('orderDetailId'));
                }
                return false;
            case 'index':
                if ($this->identity->isCustomer() && !Configure::read('app.isCustomerAllowedToViewOwnOrders')) {
                    return false;
                }
                return true;
            default:
                return parent::isAuthorized($user);
                break;
        }
    }

    /**
     * @param int $orderDetailId
     * @return boolean
     */
    private function checkOrderDetailIdAccess($orderDetailId)
    {
        if ($this->identity->isCustomer() || $this->identity->isManufacturer()) {
            $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
            $orderDetail = $this->OrderDetail->find('all', [
                'conditions' => [
                    'OrderDetails.id_order_detail' => $orderDetailId
                ],
                'contain' => [
                    'Products'
                ]
            ])->first();
            if (!empty($orderDetail)) {
                if ($this->identity->isManufacturer() && $orderDetail->product->id_manufacturer == $this->identity->getManufacturerId()) {
                    return true;
                }
                if ($this->identity->isCustomer() && !Configure::read('isCustomerAllowedToModifyOwnOrders') && $orderDetail->id_customer == $this->identity->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function orderDetailsAsPdf()
    {
        $pickupDay = [$this->getRequest()->getQuery('pickupDay')];
        $order = $this->getRequest()->getQuery('order') ?? null;
        $pdfWriter = new OrderDetailsPdfWriterService();
        $pdfWriter->prepareAndSetData($this->identity, $pickupDay, $order);
        die($pdfWriter->writeInline());
    }

    public function setElFinderUploadPath($orderDetailId)
    {
        $this->RequestHandler->renderAs($this, 'json');

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('App.fullBaseUrl') . "/files/kcfinder/order_details/" . $orderDetailId,
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/order_details/" . $orderDetailId
        ];

        $this->set([
            'status' => true,
            'msg' => 'OK',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

}
