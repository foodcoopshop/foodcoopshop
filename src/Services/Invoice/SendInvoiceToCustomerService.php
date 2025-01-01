<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Services\Invoice;

use App\Services\HelloCash\HelloCashService;
use App\Mailer\AppMailer;

class SendInvoiceToCustomerService
{

    public string $customerName;
    public string $customerEmail;
    public float $creditBalance;
    public string $invoicePdfFile;
    public string $invoiceNumber;
    public string $invoiceDate;
    public int $invoiceId;
    public float $invoiceSumPriceIncl;
    public mixed $paidInCash;
    public bool $isCancellationInvoice;
    public ?int $originalInvoiceId;

    public function run(): void
    {

        $customerName = $this->customerName;
        $customerEmail = $this->customerEmail;
        $creditBalance = $this->creditBalance;
        $invoicePdfFile = $this->invoicePdfFile;
        $invoiceNumber = $this->invoiceNumber;
        $invoiceDate = $this->invoiceDate;
        $invoiceId = $this->invoiceId;
        $invoiceSumPriceIncl = $this->invoiceSumPriceIncl;
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
            'invoiceSumPriceIncl' => $invoiceSumPriceIncl,
            'customerName' => $customerName,
            'creditBalance' => $creditBalance,
        ]);

        if (!empty($invoicePdfFile)) {
            $email->addAttachments([$invoicePdfFile]);
        } else {
            $helloCashService = new HelloCashService();
            $attachmentPrefix = __('Invoice');
            if ($isCancellationInvoice) {
                $attachmentPrefix = __('Cancellation_invoice');
            }
            $email->addAttachments([
                str_replace(' ', '_', $attachmentPrefix) . '_' . $invoiceNumber . '.pdf' => [
                    'data' => $helloCashService->getInvoice($originalInvoiceId, $isCancellationInvoice),
                    'mimetype' => 'application/pdf',
                ],
            ]);
        }
        $email->afterRunParams = [
            'invoiceId' => $invoiceId,
        ];
        $email->addToQueue();

    }

}
