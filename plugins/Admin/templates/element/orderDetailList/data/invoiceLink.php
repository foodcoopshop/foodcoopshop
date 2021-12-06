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
    echo '<td class="invoice">';

        $invoiceText = __d('admin', 'Invoice') . ': <span class="invoice-amount">' . $this->Number->formatAsCurrency($orderDetail['invoiceData']->sumPriceIncl) . '</span>';
        if (!$orderDetail['invoiceData']->new_invoice_necessary) {
            $invoiceText = __d('admin', 'Invoice_cannot_be_generated');
        }
        $invoicesForTitle = '<span style="width:100%;float:left;margin-bottom:10px;"><b>' . $orderDetail['name'] . ': </b>' . __d('admin', 'Latest_invoices') . '</span>';
        if (empty($orderDetail['latestInvoices'])) {
            $invoicesForTitle .= '<span style="width:100%;float:left;">' . __d('admin', 'No_invoices_available.') . '</span>';
        } else {
            $invoicesForTitle .= '<ul>';
        }
        foreach($orderDetail['latestInvoices'] as $invoice) {
            $invoiceRow = $invoice->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLong2'));
            $invoiceRow .=  ' / <b>' . ($invoice->paid_in_cash ? __d('admin', 'Paid_in_cash') : __d('admin', 'Credit')) . '</b>';
            $invoiceRow .= ' / ' . $this->Number->formatAsCurrency($invoice->total_sum_price_incl);
            $invoiceRowClass = '';
            if ($invoice->total_sum_price_incl < 0) {
                $invoiceRowClass = 'negative';
            }
            $invoicesForTitle .= '<li class="' . $invoiceRowClass . '">' . $invoiceRow . '</li>';
        }
        if (!empty($orderDetail['latestInvoices'])) {
            $invoicesForTitle .= '</ul>';
        }

        $invoicesForTitle .= '<p class="credit-balance-wrapper">';
        $invoicesForTitle .= '<span style="float:left;margin-top:6px;margin-right:5px;">' . __d('admin', 'Credit') . ': </span>';
            $invoicesForTitle .= $this->Html->link(
                '<span class="'.($orderDetail['creditBalance'] < 0 ? 'negative' : '').'">' . $this->Number->formatAsCurrency($orderDetail['creditBalance']) . '</span>',
                $this->Slug->getCreditBalance($orderDetail['customer_id']),
                [
                    'class' => 'btn btn-outline-light',
                    'title' => __d('admin', 'Show_credit'),
                    'style' => 'text-decoration:none ! important;',
                    'escape' => false,
                ]
            );
        $invoicesForTitle .= '</p>';

        // use wrapper as tooltipster does not work on disabled elements
        echo '<span class="latest-invoices-tooltip-wrapper" title="' . h($invoicesForTitle) . '">';
            echo $this->Html->link(
                '<i class="fas fa-fw ok fa-file-invoice"></i> ' . $invoiceText,
                'javascript:void(0);',
                [
                    'escape' => false,
                    'class' => 'btn btn-outline-light invoice-for-customer-add-button ' . (!$orderDetail['invoiceData']->new_invoice_necessary ? 'disabled' : ''),
                ]
            );
        echo '</span>';
    echo '</td>';
}

?>
