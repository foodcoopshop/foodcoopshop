<?php
namespace App\Shell\Task;

use App\Lib\PdfWriter\InvoiceToCustomerPdfWriter;
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

class QueueGenerateInvoiceForCustomerTask extends QueueTask implements QueueTaskInterface {

    use UpdateActionLogTrait;

    public $timeout = 30;

    public $retries = 2;

    public $Customer;

    public $paidInCash = false;

    public function run(array $data, $jobId) : void
    {

        $customerId = $data['customerId'];
        $invoiceNumber = $data['invoiceNumber'];
        $invoiceDate = $data['invoiceDate'];
        $invoicePdfFile = $data['invoicePdfFile'];

        $this->Customer = $this->getTableLocator()->get('Customers');

        $pdfWriter = new InvoiceToCustomerPdfWriter();
        $data = $this->Customer->Invoices->getDataForCustomerInvoice($customerId);
        $pdfWriter->prepareAndSetData($data, $this->paidInCash, $invoiceNumber, $invoiceDate);

        $pdfWriter->setFilename($invoicePdfFile);
        $pdfWriter->writeFile();

    }

}
?>