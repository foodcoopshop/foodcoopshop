<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Lib\Invoice;

use App\Lib\HelloCash\HelloCash;
use App\Mailer\AppMailer;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\FrozenTime;

class SendInvoiceToCustomer
{

    public function run()
    {

        $customerName = $this->customerName;
        $customerEmail = $this->customerEmail;
        $creditBalance = $this->creditBalance;
        $invoicePdfFile = $this->invoicePdfFile;
        $invoiceNumber = $this->invoiceNumber;
        $invoiceDate = $this->invoiceDate;
        $invoiceId = $this->invoiceId;
        $paidInCash = $this->paidInCash;
        $isCancellationInvoice = (bool) $this->isCancellationInvoice;
        $originalInvoiceId = $this->originalInvoiceId ?? $invoiceId;

        $subject = __('Invoice_number_abbreviataion_{0}_{1}', [$invoiceNumber, $invoiceDate]);
        $emailTemplate = 'Admin.send_invoice_to_customer';
        if ($isCancellationInvoice) {
            $emailTemplate = 'Admin.send_cancellation_invoice_to_customer';
            $subject = __('Cancellation_invoice_number_abbreviataion_{0}_{1}', [$invoiceNumber, $invoiceDate]);
        }

        $email = new AppMailer();
        $email->viewBuilder()->setTemplate($emailTemplate);
        $email->setTo($customerEmail)
        ->setSubject($subject)
        ->setViewVars([
            'paidInCash' => $paidInCash,
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
        $email->afterRunParams = [
            'invoiceId' => $invoiceId,
        ];
        $email->send();

    }

}
