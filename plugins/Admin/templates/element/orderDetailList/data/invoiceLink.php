<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if ($groupBy == 'customer' && Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $identity->isSuperadmin()) {
    echo '<td class="invoice">';

        $invoiceText = __d('admin', 'Invoice') . ': <span class="invoice-amount">' . $this->Number->formatAsCurrency($orderDetail['invoiceData']->sumPriceIncl) . '</span>';
        if (!$orderDetail['invoiceData']->new_invoice_necessary) {
            $invoiceText = __d('admin', 'Invoice_cannot_be_generated');
        }
        $invoicesForTitle = '<span style="width:100%;float:left;margin-bottom:10px;"><b>' . $orderDetail['name'] . '</b></span>';
        if (empty($orderDetail['latestInvoices'])) {
            $invoicesForTitle .= '<span style="width:100%;float:left;">' . __d('admin', 'No_invoices_available.') . '</span>';
        } else {
            $invoicesForTitle .= '<ul style="border-bottom:1px solid #ccc;padding-bottom:10px;">';
        }
        foreach($orderDetail['latestInvoices'] as $invoice) {
            $invoiceRow = $invoice->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort2'));
            if ($this->Html->paymentIsCashless()) {
                $invoiceRow .=  ' / <b>' . ($invoice->paid_in_cash ? __d('admin', 'Paid_in_cash') : __d('admin', 'Credit')) . '</b>';
            }
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

        if ($this->Html->paymentIsCashless()) {
            $this->element('addScript', [
                'script' => Configure::read('app.jsNamespace').".Admin.loadGetCreditBalance(" . $orderDetail['customer_id'] . ");"
            ]);
            $invoicesForTitle .= '<p class="credit-balance-wrapper">';
            $invoicesForTitle .= '<span style="float:left;margin-top:6px;margin-right:5px;">' . __d('admin', 'Credit') . ': </span>';
                $invoicesForTitle .= $this->Html->link(
                    '<i class="fas fa-circle-notch fa-spin"></i>',
                    $this->Slug->getCreditBalance($orderDetail['customer_id']),
                    [
                        'class' => 'btn btn-outline-light',
                        'id' => 'credit-balance-' . $orderDetail['customer_id'],
                        'title' => __d('admin', 'Show_credit'),
                        'style' => 'text-decoration:none ! important;',
                        'escape' => false,
                    ]
                );
                $invoicesForTitle .= '<br /><span class="float:left;margin-right:10px;">' . __d('admin', 'Check_credit_reminder') . ': ';
                $invoicesForTitle .= $this->Html->link(
                    $orderDetail['invoiceData']->check_credit_reminder_enabled ? '<i class="fas fa-check-circle ok"></i>' : '<i class="fas fa-minus-circle not-ok"></i>',
                    $this->Slug->getCustomerEdit($orderDetail['customer_id']),
                    [
                        'class' => 'btn btn-outline-light',
                        'title' => __d('admin', 'Check_credit_reminder'),
                        'style' => 'text-decoration:none ! important;',
                        'escape' => false,
                    ]
                );

                $invoicesForTitle .= '</span>';
            $invoicesForTitle .= '</p>';
        }

        // use wrapper as tooltipster and jquery-hover do not work on disabled elements
        echo '<span class="latest-invoices-tooltip-wrapper" id="latest-invoices-tooltip-wrapper-' . $orderDetail['customer_id'] . '" title="' . h($invoicesForTitle) . '">';
            echo $this->Html->link(
                '<i class="fas fa-fw ok fa-file-invoice"></i> ' . $invoiceText,
                'javascript:void(0);',
                [
                    'escape' => false,
                    'class' => 'btn btn-outline-light invoice-for-customer-add-button ' . (!$orderDetail['invoiceData']->new_invoice_necessary ? 'disabled' : ''),
                    'id' => 'invoice-for-customer-add-button-' . $orderDetail['customer_id'],
                ]
            );
        echo '</span>';
    echo '</td>';
}

?>
