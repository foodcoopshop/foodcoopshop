<?php
namespace App\Shell\Task;

use App\Mailer\AppMailer;
use Queue\Shell\Task\QueueTask;
use Queue\Shell\Task\QueueTaskInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class QueueSendOrderListTask extends QueueTask implements QueueTaskInterface {


    use UpdateActionLogTrait;

    public $timeout = 30;

    public $retries = 2;

    public function run(array $data, $jobId) : void
    {

        $productPdfFile = $data['productPdfFile'];
        $customerPdfFile = $data['customerPdfFile'];
        $pickupDayFormated = $data['pickupDayFormated'];
        $manufacturerId = $data['manufacturerId'];
        $orderDetailIds = $data['orderDetailIds'];
        $pickupDayFormated = $data['pickupDayFormated'];
        $actionLogId = $data['actionLogId'];

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $manufacturer = $this->Manufacturer->getManufacturerByIdForSendingOrderLists($manufacturerId);

        $ccRecipients = $this->Manufacturer->getOptionSendOrderListCc($manufacturer->send_order_list_cc);

        $email = new AppMailer();
        $email->fallbackEnabled = false;
        $email->viewBuilder()->setTemplate('Admin.send_order_list');
        $email->setTo($manufacturer->address_manufacturer->email)
        ->setAttachments([
            $productPdfFile,
            $customerPdfFile,
        ])
        ->setSubject(__('Order_lists_for_the_day') . ' ' . $pickupDayFormated)
        ->setViewVars([
            'manufacturer' => $manufacturer,
            'showManufacturerUnsubscribeLink' => true,
        ]);
        if (!empty($ccRecipients)) {
            $email->setCc($ccRecipients);
        }
        $email->send();

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->OrderDetail->updateOrderState(null, null, [ORDER_STATE_ORDER_PLACED], ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER, $manufacturer->id_manufacturer, $orderDetailIds);

        $identifier = 'send-order-list-' . $manufacturer->id_manufacturer . '-' . $pickupDayFormated;
        $this->updateActionLog($actionLogId, $identifier, $jobId);

    }

}
?>