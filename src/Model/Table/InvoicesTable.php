<?php

namespace App\Model\Table;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class InvoicesTable extends AppTable
{
    public function getNextInvoiceNumber($invoices)
    {
        $invoiceNumber = 1;
        if (! empty($invoices)) {
            $invoiceNumber = $invoices[0]->invoice_number + 1;
        }
        $newInvoiceNumber = $this->formatInvoiceNumber($invoiceNumber);
        return $newInvoiceNumber;
    }
    
    /**
     * turns eg 24 into 0024
     * @param int $invoiceNumber
     */
    private function formatInvoiceNumber($invoiceNumber)
    {
        return str_pad($invoiceNumber, 4, '0', STR_PAD_LEFT);
    }
    
}
