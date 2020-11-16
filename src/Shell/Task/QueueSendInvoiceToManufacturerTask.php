<?php
namespace App\Shell\Task;

use App\Mailer\AppMailer;
use Cake\Core\Configure;
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

class QueueSendInvoiceToManufacturerTask extends QueueTask implements QueueTaskInterface {

    use UpdateActionLogTrait;

    public $timeout = 30;

    public $retries = 2;

    public function run(array $data, $jobId) : void
    {

        $manufacturerId = $data['manufacturerId'];
        $invoicePdfFile = $data['invoicePdfFile'];
        $invoiceNumber = $data['invoiceNumber'];
        $actionLogId = $data['actionLogId'];

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $manufacturer = $this->Manufacturer->getManufacturerByIdForSendingOrderListsOrInvoice($manufacturerId);

        $invoicePeriodMonthAndYear = Configure::read('app.timeHelper')->getLastMonthNameAndYear();

        $email = new AppMailer();
        $email->fallbackEnabled = false;
        $email->viewBuilder()->setTemplate('Admin.send_invoice_to_manufacturer');
        $email->setTo($manufacturer->address_manufacturer->email)
        ->setAttachments([
            $invoicePdfFile,
        ])
        ->setSubject(__('Invoice_number_abbreviataion_{0}_{1}', [$invoiceNumber, $invoicePeriodMonthAndYear]))
        ->setViewVars([
            'manufacturer' => $manufacturer,
            'invoicePeriodMonthAndYear' => $invoicePeriodMonthAndYear,
            'showManufacturerUnsubscribeLink' => true
        ]);
        $email->send();

        $identifier = 'send-invoice-' . $manufacturer->id_manufacturer;
        $this->updateActionLog($actionLogId, $identifier, $jobId);

    }

}
?>