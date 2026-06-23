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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */


$html = $this->Html->link(
    '<i class="far fa-fw fa-clone"></i>',
    'javascript:void(0)',
    [
        'class' => 'btn btn-outline-light btn-clipboard-table',
        'title' => __('Copy_to_clipboard'),
        'style' => 'margin-right:10px;float:left;',
        'escape' => false,
    ]
);
$html .= '<table class="list tax-sum-table">';

$html .= '<tr>';
    $html .= '<th>' . __('Tax_rate_admin').'</th>';
    $html .= '<th>' . __('Sum') . ' ' . __('Net') . '</th>';
    $html .= '<th>' . __('Sum') . ' ' . __('VAT').'</th>';
    $html .= '<th>' . __('Sum') . ' ' . __('Gross') .'</th>';
$html .= '</tr>';

foreach($taxRates as $taxRate => $data) {
    $html .= '<tr>';
        $html .= '<td>';
            $taxRate = $this->Number->parseFloatRespectingLocale($taxRate);
            $formattedTaxRate = $this->Number->formatTaxRate($taxRate);
            $html .= $formattedTaxRate . '%';
        $html .= '</td>';
        $html .= '<td>'. $this->Number->formatAsDecimal($data['sum_price_excl']) . '</td>';
        $html .= '<td>'. $this->Number->formatAsDecimal($data['sum_tax']) . '</td>';
        $html .= '<td>'. $this->Number->formatAsDecimal($data['sum_price_incl']) . '</td>';
    $html .= '</tr>';
}

$html .= '<tr style="font-weight:bold;">';
$html .= '<td>'.__('Sum').'</td>';
$html .= '<td>'. $this->Number->formatAsDecimal($taxRatesSums['sum_price_excl']) . '</td>';
$html .= '<td>'. $this->Number->formatAsDecimal($taxRatesSums['sum_tax']) . '</td>';
$html .= '<td>'. $this->Number->formatAsDecimal($taxRatesSums['sum_price_incl']) . '</td>';

$html .= '</tr>';


$html .= '</table>';

echo $html;
