<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers;

use App\Services\PdfWriter\InvoiceToManufacturerPdfWriterService;
use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait GetInvoiceTrait
{

    public function getInvoice()
    {
        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        $dateTo = h($this->getRequest()->getQuery('dateTo'));

        $manufacturer = $this->Manufacturer->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();

        $newInvoiceNumber = 'xxx';

        $pdfWriter = new InvoiceToManufacturerPdfWriterService();
        $pdfWriter->prepareAndSetData($manufacturerId, $dateFrom, $dateTo, $newInvoiceNumber, [], '', 'xxx', $manufacturer->anonymize_customers);
        if (isset($pdfWriter->getData()['productResults']) && empty($pdfWriter->getData()['productResults'])) {
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }

        if (!empty($this->request->getQuery('outputType')) && $this->request->getQuery('outputType') == 'html') {
            return $this->response->withStringBody($pdfWriter->writeHtml());
        }

        $invoicePdfFile = Configure::read('app.htmlHelper')->getInvoiceLink($manufacturer->name, $manufacturerId, date('Y-m-d'), $newInvoiceNumber);
        $invoicePdfFile = explode(DS, $invoicePdfFile);
        $invoicePdfFile = end($invoicePdfFile);
        $invoicePdfFile = substr($invoicePdfFile, 11);
        $invoicePdfFile = $this->request->getQuery('dateFrom'). '-' . $this->request->getQuery('dateTo') . '-' . $invoicePdfFile;
        $pdfWriter->setFilename($invoicePdfFile);

        die($pdfWriter->writeInline());
    }

}