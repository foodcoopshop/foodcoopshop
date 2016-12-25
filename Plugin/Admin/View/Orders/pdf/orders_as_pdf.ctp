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

App::uses('FCS_TCPDF', 'Lib/Pdf');
$pdf = new FCS_TCPDF();
$pdf->SetLeftMargin(16);
$pdf->AddPage();
$pdf->infoTextForFooter = 'Bestellungen';

$j = 1;
foreach ($orders as $order) {
    
    $pdf->Ln(5);
    $pdf->writeHTML('<h2>' . $order['Customer']['name'] . '</h2>', true, false, true, false, '');
    $pdf->writeHTML('<h3>Bestellung vom ' . $this->Time->formatToDateNTimeLong($order['Order']['date_add']) . '</h3>', true, false, true, false, '');
    
    $pdf->Ln(5);
    
    $widths = array(
        45,
        270,
        100,
        45,
        45
    );
    $headers = array(
        'Anzahl',
        'Artikelname',
        'Hersteller',
        'Preis',
        'Pfand'
    );
    
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
    foreach ($order['OrderDetails'] as $orderDetail) {
        
        $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';
        
        $quantityStyle = '';
        if ($orderDetail['product_quantity'] > 1) {
            $quantityStyle = ' background-color:#cecece;';
        }
        $pdf->table .= '<td style="' . $quantityStyle . 'text-align: center;"; width="' . $widths[0] . '">' . $orderDetail['product_quantity'] . 'x</td>';
        $pdf->table .= '<td width="' . $widths[1] . '">' . $orderDetail['product_name'] . '</td>';
        $pdf->table .= '<td width="' . $widths[2] . '">' . $orderDetail['Product']['Manufacturer']['name'] . '</td>';
        $pdf->table .= '<td style="text-align: right"; width="' . $widths[3] . '">' . $this->Html->formatAsEuro($orderDetail['total_price_tax_incl']) . '</td>';
        
        $deposit = $orderDetail['deposit'];
        if ($deposit > 0) {
            $sumDeposit += $deposit;
            $deposit = $this->Html->formatAsEuro($deposit);
        } else {
            $deposit = '';
        }
        $pdf->table .= '<td style="text-align: right"; width="' . $widths[4] . '">' . $deposit . '</td>';
        
        $sumPrice += $orderDetail['total_price_tax_incl'];
        $sumQuantity += $orderDetail['product_quantity'];
        
        $pdf->table .= '</tr>';
        
        if ($i == count($order['OrderDetails'])) {
            $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';
                $pdf->table .= '<td width="' . $widths[0] . '"></td>';
                $pdf->table .= '<td width="' . $widths[1] . '"></td>';
                $pdf->table .= '<td width="' . $widths[2] . '"></td>';
                $pdf->table .= '<td style="text-align: right"; width="' . $widths[3] . '"><p>' . $this->Html->formatAsEuro($sumPrice) . '</p></td>';
                if ($sumDeposit > 0) {
                    $sumDepositAsString = $this->Html->formatAsEuro($sumDeposit);
                } else {
                    $sumDepositAsString = '';
                }
                $pdf->table .= '<td style="text-align: right"; width="' . $widths[4] . '"><p>' . $sumDepositAsString . '</p></td>';
            $pdf->table .= '</tr>';
            $pdf->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';
                $pdf->table .= '<td colspan="3" style="text-align:right;" width="' . ($widths[0] + $widths[1] + $widths[2]) . '"><h3>Gesamt</h3></td>';
                $pdf->table .= '<td colspan="2" style="text-align:center"; width="' . ($widths[3] + $widths[4]) . '"><h3>' . $this->Html->formatAsEuro($sumPrice + $sumDeposit) . '</h3></td>';
            $pdf->table .= '</tr>';
        }
        
        $i ++;
    }
    
    $pdf->renderTable();
    
    $pdf->Ln(5);
    $html = '<p>Vielen Dank, dass du bei uns bestellst!</p>';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    if ($j < count($orders)) {
        $pdf->AddPage();
    }
    
    $j ++;
}

echo $pdf->Output();

?>