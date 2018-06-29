<?php
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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Lib\Pdf\AppTcpdf;
use Cake\Core\Configure;

$pdf = new AppTcpdf();
$pdf->SetLeftMargin(16);
$pdf->AddPage();
$pdf->infoTextForFooter = __d('admin', 'Orders');

$j = 1;
foreach ($orders as $order) {
    $pdf->Ln(5);
    $pdf->writeHTML('<h2>' . $order->customer->name . '</h2>', true, false, true, false, '');
    $pdf->writeHTML('<h3>'.__d('admin', 'Order_from') . ' ' . $order->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLong')) . '</h3>', true, false, true, false, '');

    if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED') && $order->comment != '') {
        $pdf->SetRightMargin(16);
        $pdf->Ln(2);
        $pdf->writeHTML('<p><b>'.__d('admin', 'Comment').': </b>' . $order->comment. '</p>', true, false, true, false, '');
    }

    $pdf->Ln(5);

    $widths = [
        45,
        270,
        100,
        45,
        45
    ];
    $headers = [
        __d('admin', 'Amount'),
        __d('admin', 'Product'),
        __d('admin', 'Manufacturer'),
        __d('admin', 'Price'),
        __d('admin', 'Deposit')
    ];

    $pdf->table .= '<table style="font-size:8px" cellspacing="0" cellpadding="1" border="1"><thead><tr>';

    $num_headers = count($headers);
    for ($i = 0; $i < $num_headers; ++ $i) {
        $pdf->table .= '<th style="font-weight:bold;background-color:#cecece" width="' . $widths[$i] . '">' . $headers[$i] . '</th>';
    }
    $pdf->table .= '</tr></thead>';

    $sumPrice = 0;
    $sumDeposit = 0;
    $sumQuantity = 0;
    $i = 1;
    $usesQuantityInUnits = 0;
    foreach ($order->order_details as $orderDetail) {
        $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';

        $quantityStyle = '';
        if ($orderDetail['product_amount'] > 1) {
            $quantityStyle = ' background-color:#cecece;';
        }
        $pdf->table .= '<td style="' . $quantityStyle . 'text-align: center;"; width="' . $widths[0] . '">' . $orderDetail->product_amount . 'x</td>';

        $unity = '';
        if (!empty($orderDetail->order_detail_unit)) {
            $unity = Configure::read('app.pricePerUnitHelper')->getQuantityInUnits(
                true,
                $orderDetail->order_detail_unit->quantity_in_units,
                $orderDetail->order_detail_unit->unit_name,
                $orderDetail->product_amount
            );
            if ($unity != '') {
                $unity = ', ' . $unity;
            }
        }
        $pdf->table .= '<td width="' . $widths[1] . '">' . $orderDetail->product_name . $unity . '</td>';

        $pdf->table .= '<td width="' . $widths[2] . '">' . $orderDetail->product->manufacturer->name . '</td>';

        $priceStyle = '';
        if (!empty($orderDetail->order_detail_unit)) {
            $priceStyle = ' background-color:#cecece;';
        }

        $pdf->table .= '<td style="' . $priceStyle . 'text-align: right"; width="' . $widths[3] . '">';
        $pdf->table .= $this->Number->formatAsCurrency($orderDetail->total_price_tax_incl);
        if (!empty($orderDetail->order_detail_unit)) {
            $pdf->table .= ' *';
            $usesQuantityInUnits++;
        }
        $pdf->table .= '</td>';
        $deposit = $orderDetail->deposit;
        if ($deposit > 0) {
            $sumDeposit += $deposit;
            $deposit = $this->Number->formatAsCurrency($deposit);
        } else {
            $deposit = '';
        }
        $pdf->table .= '<td style="text-align: right"; width="' . $widths[4] . '">' . $deposit . '</td>';

        $sumPrice += $orderDetail['total_price_tax_incl'];
        $sumQuantity += $orderDetail['product_amount'];

        $pdf->table .= '</tr>';

        if ($i == count($order->order_details)) {
            $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';
                $pdf->table .= '<td width="' . $widths[0] . '"></td>';
                $pdf->table .= '<td width="' . $widths[1] . '"></td>';
                $pdf->table .= '<td width="' . $widths[2] . '"></td>';
                $pdf->table .= '<td style="text-align: right;" width="' . $widths[3] . '">' . $this->Number->formatAsCurrency($sumPrice) . '</td>';
            if ($sumDeposit > 0) {
                $sumDepositAsString = $this->Number->formatAsCurrency($sumDeposit);
            } else {
                $sumDepositAsString = '';
            }
                $pdf->table .= '<td style="text-align: right;" width="' . $widths[4] . '">' . $sumDepositAsString . '</td>';
            $pdf->table .= '</tr>';
            $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';
                $pdf->table .= '<td colspan="3" style="font-size:10px;font-weight:bold;text-align:right;" width="' . ($widths[0] + $widths[1] + $widths[2]) . '">Gesamt</td>';
                $pdf->table .= '<td colspan="2" style="font-size:10px;font-weight:bold;text-align:center;" width="' . ($widths[3] + $widths[4]) . '">' . $this->Number->formatAsCurrency($sumPrice + $sumDeposit) . '</td>';
            $pdf->table .= '</tr>';
        }

        $i ++;
    }

    $pdf->renderTable();

    if ($usesQuantityInUnits > 0) {
        $html = '<p>* '.__('The_delivered_weight_will_eventually_be_adapted_which_means_the_price_can_change_slightly.').'</p>';
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    $pdf->Ln(5);
    if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && Configure::read('app.manufacturerComponensationInfoText') != '') {
        $html = '<p>'.Configure::read('app.manufacturerComponensationInfoText').'</p>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(2);
    }

    $html = '<p>'.__d('admin', 'Thank_you_very_much_for_delivering_your_products_to_us!').'</p>';
    $pdf->writeHTML($html, true, false, true, false, '');

    if ($j < $orders->count()) {
        $pdf->AddPage();
    }

    $j ++;
}

echo $pdf->Output();
