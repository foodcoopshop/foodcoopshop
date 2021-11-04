<?php
namespace App\Queue\Task;

use App\Mailer\AppMailer;
use App\Lib\HelloCash\HelloCash;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\FrozenTime;
use Queue\Queue\Task;

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

class SendInvoiceToCustomerTask extends Task {

    public $timeout = 30;

    public $retries = 2;

    public $Invoice;

    public function run(array $data, $jobId) : void
    {

        $customerName = $data['customerName'];
        $customerEmail = $data['customerEmail'];
        $creditBalance = $data['creditBalance'];
        $invoicePdfFile = $data['invoicePdfFile'];
        $invoiceNumber = $data['invoiceNumber'];
        $invoiceDate = $data['invoiceDate'];
        $invoiceId = $data['invoiceId'];
        $isCancellationInvoice = (bool) $data['isCancellationInvoice'];
        $originalInvoiceId = $data['originalInvoiceId'] ?? $invoiceId;

        $subject = __('Invoice_number_abbreviataion_{0}_{1}', [$invoiceNumber, $invoiceDate]);
        $emailTemplate = 'Admin.send_invoice_to_customer';
        if ($isCancellationInvoice) {
            $emailTemplate = 'Admin.send_cancellation_invoice_to_customer';
            $subject = __('Cancellation_invoice_number_abbreviataion_{0}_{1}', [$invoiceNumber, $invoiceDate]);
        }

        $email = new AppMailer();
        $email->fallbackEnabled = false;
        $email->viewBuilder()->setTemplate($emailTemplate);
        $email->setTo($customerEmail)
        ->setSubject($subject)
        ->setViewVars([
            'customerName' => $customerName,
            'creditBalance' => $creditBalance,
        ]);

        if (!empty($invoicePdfFile)) {
            $email->addAttachments([$invoicePdfFile]);
        } else {
            $helloCash = new HelloCash();
            $attachmentPrefix = __('Invoice');
            if ($isCancellationInvoice) {
                $attachmentPrefix = __('Cancellation_invoice');
            }
            $email->addAttachments([
                str_replace(' ', '_', $attachmentPrefix) . '_' . $invoiceNumber . '.pdf' => [
                    'data' => $helloCash->getInvoice($originalInvoiceId, $isCancellationInvoice)->getStringBody(),
                    'mimetype' => 'application/pdf',
                ],
            ]);
        }

        $email->send();

        $this->Invoice = FactoryLocator::get('Table')->get('Invoices');
        $invoiceEntity = $this->Invoice->patchEntity(
            $this->Invoice->get($invoiceId), [
            'email_status' => FrozenTime::now(),
        ]);
        $this->Invoice->save($invoiceEntity);

    }

}
?>