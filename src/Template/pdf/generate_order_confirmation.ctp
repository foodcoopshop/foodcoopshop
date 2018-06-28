<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Controller\Component\StringComponent;
use App\Lib\Pdf\AppTcpdf;
use Cake\Core\Configure;

$pdf = new AppTcpdf();
$pdf->SetLeftMargin(16);

if (!empty($manufacturers)) {
    $i = 0;

    foreach ($manufacturers as $manufacturerId => $details) {
        $pdf->AddPage();

        $pdf->infoTextForFooter = __('Order_overview') . ' ' . $details['Manufacturer']->name;

        $pdf->writeHTML('<h3>' . __('Order_of') . ' '. $appAuth->getUsername().'<br />' . __('placed_on') . ' '. $order->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLong')).'</h3>', true, false, true, false, '');
        $pdf->Ln(8);

        $pdf->writeHTML($this->MyHtml->getManufacturerImprint($details['Manufacturer'], 'pdf', false), true, false, true, false, '');
        $pdf->Ln(6);

        $widths = [
            45,
            270,
            45,
            45
        ];
        $headers = [
            __('Amount'),
            __('Product'),
            __('Price'),
            __('Deposit')
        ];

        $pdf->table .= '<table style="font-size:8px" cellspacing="0" cellpadding="1" border="1"><thead><tr>';

        $num_headers = count($headers);
        for ($j = 0; $j < $num_headers; ++ $j) {
            $pdf->table .= '<th style="font-weight:bold;background-color:#cecece" width="' . $widths[$j] . '">' . $headers[$j] . '</th>';
        }
        $pdf->table .= '</tr></thead>';

        $sumPrice = 0;
        $sumDeposit = 0;
        $sumQuantity = 0;
        $sumOrderDetailTax = 0;

        $manufacturerOrderDetails = [];
        foreach ($details['OrderDetails'] as $orderDetail) {
            if ($orderDetail->product->id_manufacturer != $manufacturerId) {
                continue;
            }
            $manufacturerOrderDetails[] = $orderDetail;
        }

        $k = 0;

        foreach ($manufacturerOrderDetails as $orderDetail) {
            $k++;
            $showSum = $k == count($manufacturerOrderDetails);

            $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';

            $quantityStyle = '';
            if ($orderDetail->product_amount > 1) {
                $quantityStyle = ' background-color:#cecece;';
            }
            $pdf->table .= '<td style="' . $quantityStyle . 'text-align: center;" width="' . $widths[0] . '">' . $orderDetail->product_amount . 'x</td>';
            $pdf->table .= '<td width="' . $widths[1] . '">' . $orderDetail->product_name . '</td>';
            $pdf->table .= '<td style="text-align: right;" width="' . $widths[2] . '">' . $this->MyNumber->formatAsCurrency($orderDetail->total_price_tax_incl) . '</td>';

            $deposit = $orderDetail->deposit;
            if ($deposit > 0) {
                $sumDeposit += $deposit;
                $deposit = $this->MyNumber->formatAsCurrency($deposit);
            } else {
                $deposit = '';
            }
            $pdf->table .= '<td style="text-align: right;" width="' . $widths[3] . '">' . $deposit . '</td>';

            $sumPrice += $orderDetail->total_price_tax_incl;
            $sumQuantity += $orderDetail->product_amount;

            $sumOrderDetailTax += $orderDetail->order_detail_tax->total_amount;

            $pdf->table .= '</tr>';

            if ($showSum) {
                $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';
                    $pdf->table .= '<td width="' . $widths[0] . '"></td>';
                    $pdf->table .= '<td width="' . $widths[1] . '"></td>';
                    $pdf->table .= '<td style="text-align:right;font-weight:bold;" width="' . $widths[2] . '"><p>' . $this->MyNumber->formatAsCurrency($sumPrice) . '</p></td>';
                if ($sumDeposit > 0) {
                    $sumDepositAsString = $this->MyNumber->formatAsCurrency($sumDeposit);
                } else {
                    $sumDepositAsString = '';
                }
                    $pdf->table .= '<td style="text-align:right;font-weight:bold;" width="' . $widths[3] . '"><p>' . $sumDepositAsString . '</p></td>';
                $pdf->table .= '</tr>';
                $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';
                    $pdf->table .= '<td colspan="2" style="text-align:right;" width="' . ($widths[0] + $widths[1]) . '"><h3>Gesamt</h3></td>';
                    $pdf->table .= '<td colspan="2" style="text-align:center;" width="' . ($widths[2] + $widths[3]) . '"><h3>' . $this->MyNumber->formatAsCurrency($sumPrice + $sumDeposit) . '</h3></td>';
                $pdf->table .= '</tr>';
            }
        }

        $pdf->renderTable();

        $pdf->writeHTML('<p>' . __('Prices_are_including_vat.') . '</p>', true, false, true, false, '');
        $pdf->Ln(3);
        $pdf->writeHTML('<p>' . __('Including_vat') . ': ' . $this->MyNumber->formatAsCurrency($sumOrderDetailTax) . '</p>', true, false, true, false, '');
    }
}

echo $pdf->Output(StringComponent::createRandomString().'.pdf', $saveParam);
