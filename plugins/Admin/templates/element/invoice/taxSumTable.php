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
 * @author        Swoichha Adhikari
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */


$html = $this->Html->link(
    '<i class="far fa-fw fa-clipboard"></i>',
    'javascript:void(0)',
    [
        'class' => 'btn btn-outline-light btn-clipboard-table',
        'title' => __d('admin', 'Copy_to_clipboard'),
        'style' => 'margin-right:3px;float:left;',
        'escape' => false,
    ]
);
$html .= '<table class="list tax-sum-table">';

$html .= '<tr>';
    $html .= '<th>'.__d('admin', 'Tax_rate').'</th>';
    $html .= '<th>'.__d('admin', 'Sum_price_excl.').'</th>';
    $html .= '<th>'.__d('admin', 'Sum_tax').'</th>';
    $html .= '<th>'.__d('admin', 'Sum_price_incl.').'</th>';
$html .= '</tr>';

foreach($taxRates as $taxRate => $data) {
    $html .= '<tr>';
        $html .= '<td>';
            $formattedTaxRate = $this->Number->formatTaxRate($taxRate);
            $html .= $formattedTaxRate . '%';
        $html .= '</td>';
        $html .= '<td>'. $this->Number->formatAsDecimal($data['sum_price_excl']) . '</td>';
        $html .= '<td>'. $this->Number->formatAsDecimal($data['sum_tax']) . '</td>';
        $html .= '<td>'. $this->Number->formatAsDecimal($data['sum_price_incl']) . '</td>';
    $html .= '</tr>';
}

$html .= '<tr style="font-weight:bold;">';
$html .= '<td>'.__d('admin', 'Sum').'</td>';
$html .= '<td>'. $this->Number->formatAsDecimal($taxRatesSums['sum_price_excl']) . '</td>';
$html .= '<td>'. $this->Number->formatAsDecimal($taxRatesSums['sum_tax']) . '</td>';
$html .= '<td>'. $this->Number->formatAsDecimal($taxRatesSums['sum_price_incl']) . '</td>';

$html .= '</tr>';


$html .= '</table>';

echo $html;
