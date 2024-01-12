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
