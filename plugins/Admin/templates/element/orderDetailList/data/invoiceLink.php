<?php
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

use Cake\Core\Configure;

if ($groupBy == 'customer' && Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $appAuth->isSuperadmin()) {
    echo '<td>';
        echo $this->Html->link(
            '<i class="fas fa-fw ok fa-file-invoice"></i> ' . __d('admin', 'Invoice') . ': ' . $this->Number->formatAsCurrency($orderDetail['invoiceData']->sumPriceIncl),
            'javascript:void(0);',
            [
                'escape' => false,
                'class' => 'btn btn-outline-light invoice-for-customer-add-button ' . (!$orderDetail['invoiceData']->new_invoice_necessary ? 'disabled' : ''),
            ]
        );
    echo '</td>';
}

?>
