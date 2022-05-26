<?php
namespace App\Queue\Task;

use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Queue\Queue\Task;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class SendDeliveryNoteTask extends Task {

    use UpdateActionLogTrait;

    public $Manufacturer;

    public $OrderDetail;

    public $timeout = 30;

    public $retries = 2;

    public function run(array $data, $jobId) : void
    {

        $deliveryNoteFile = $data['deliveryNoteFile'];
        $manufacturerId = $data['manufacturerId'];
        $actionLogId = $data['actionLogId'];

        $this->Manufacturer = $this->loadModel('Manufacturers');
        $manufacturer = $this->Manufacturer->getManufacturerByIdForSendingOrderListsOrInvoice($manufacturerId);
        $invoicePeriodMonthAndYear = Configure::read('app.timeHelper')->getLastMonthNameAndYear();

        $email = new AppMailer();
        $email->viewBuilder()->setTemplate('Admin.send_delivery_note');
        $email->setTo($manufacturer->address_manufacturer->email)
        ->setAttachments([
            TMP . $deliveryNoteFile,
        ])
        ->setSubject(__('Delivery_note_for_{0}', [$invoicePeriodMonthAndYear]))
        ->setViewVars([
            'manufacturer' => $manufacturer,
            'invoicePeriodMonthAndYear' => $invoicePeriodMonthAndYear,
            'showManufacturerUnsubscribeLink' => true,
        ]);
        $email->send();

        $actionLogIdentifier = 'send-delivery-note-' . $manufacturer->id_manufacturer;
        $this->updateActionLog($actionLogId, $actionLogIdentifier, $jobId);

    }

}
?>