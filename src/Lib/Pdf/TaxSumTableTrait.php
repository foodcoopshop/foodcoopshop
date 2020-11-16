<?php

namespace App\Lib\Pdf;

use Cake\Core\Configure;

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
trait TaxSumTableTrait
{

    public function renderTaxSumTable($taxRates)
    {

        if (!$taxRates) {
            return;
        }

        $taxRatesTableColumnWidth = 85;

        $this->Ln(3);
        $html = '<p><b>'.__('Tax_rates_overview_table').'</b></p>';
        $this->Ln(3);
        $this->writeHTML($html, true, false, true, false, '');

        $html = '<table border="1" cellspacing="0" cellpadding="1" style="font-size:8px">';

        $html .= '<tr style="font-weight:bold;background-color:#cecece">';
        $html .= '<th align="right" width="'.$taxRatesTableColumnWidth.'">'.__('Tax_rate').'</th>';
        $html .= '<th align="right" width="'.$taxRatesTableColumnWidth.'">'.__('Sum_price_excl.').'</th>';
        $html .= '<th align="right" width="'.$taxRatesTableColumnWidth.'">'.__('Sum_tax').'</th>';
        $html .= '<th align="right" width="'.$taxRatesTableColumnWidth.'">'.__('Sum_price_incl.').'</th>';
        $html .= '</tr>';

        foreach($taxRates as $taxRate => $data) {
            $html .= '<tr>';
            $html .= '<td align="right" width="'.$taxRatesTableColumnWidth.'">';

            $formattedTaxRate = Configure::read('app.numberHelper')->formatTaxRate($taxRate);
            $html .= $formattedTaxRate . '%';

            $html .= '</td>';
            $html .= '<td align="right" width="'.$taxRatesTableColumnWidth.'">'. Configure::read('app.numberHelper')->formatAsCurrency($data['sum_price_excl']) . '</td>';
            $html .= '<td align="right" width="'.$taxRatesTableColumnWidth.'">'. Configure::read('app.numberHelper')->formatAsCurrency($data['sum_tax']) . '</td>';
            $html .= '<td align="right" width="'.$taxRatesTableColumnWidth.'">'. Configure::read('app.numberHelper')->formatAsCurrency($data['sum_price_incl']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $this->Ln(3);
        $this->writeHTML($html, true, false, true, false, '');

    }

}
