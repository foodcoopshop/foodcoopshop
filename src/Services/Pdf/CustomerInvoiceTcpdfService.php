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
namespace App\Services\Pdf;

use Cake\Core\Configure;

class CustomerInvoiceTcpdfService extends CustomerInvoiceBaseTcpdfService
{

    public function setHeaders()
    {
        $this->headers = [
            [
                'name' => '',
                'align' => 'right',
                'width' => 18,
            ],
            [
                'name' => __('ID'),
                'align' => 'right',
                'width' => 42,
            ],
            [
                'name' => __('Product'),
                'align' => 'left',
                'width' => 142,
            ],
            [
                'name' => __('Manufacturer'),
                'align' => 'left',
                'width' => 81,
            ],
            [
                'name' => __('Price_excl.'),
                'align' => 'right',
                'width' => 58,
            ],
            [
                'name' => __('VAT'),
                'align' => 'right',
                'width' => 58,
            ],
            [
                'name' => __('Price_incl.'),
                'align' => 'right',
                'width' => 58,
            ],
            [
                'name' => __('Delivery_day'),
                'align' => 'right',
                'width' => 48,
            ],
        ];
    }

    public function prepareTableData($result, $sumPriceExcl, $sumPriceIncl, $sumTax)
    {

        foreach($result->active_order_details as $orderDetail) {

            $formattedTaxRate = Configure::read('app.numberHelper')->formatTaxRate($orderDetail->tax_rate);

            // products
            $this->table .= '<tr style="font-weight:normal;">';
            $this->renderTableRow(
                [
                    $orderDetail->product_amount . 'x',
                    $orderDetail->product_id  . ($orderDetail->product_attribute_id > 0 ? '-' . $orderDetail->product_attribute_id : ''),
                    $orderDetail->product_name,
                    $this->textHelper->truncate($orderDetail->product->manufacturer->name, 18),
                    Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_excl),
                    Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->tax_total_amount) . ' (' . $formattedTaxRate  . '%)',
                    Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_incl),
                    $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')),
                ]
                );
            $this->table .= '</tr>';

        }

        $depositTaxRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));
        $formattedDepositTaxRate = Configure::read('app.numberHelper')->formatTaxRate($depositTaxRate);

        // ordered deposit
        if ($result->ordered_deposit['deposit_incl'] != 0) {
            $this->table .= '<tr style="font-weight:normal;font-style:italic;">';
            $this->renderTableRow(
                [
                    $result->ordered_deposit['deposit_amount'] . 'x',
                    '',
                    __('Delivered_deposit'),
                    '',
                    Configure::read('app.numberHelper')->formatAsCurrency($result->ordered_deposit['deposit_excl']),
                    Configure::read('app.numberHelper')->formatAsCurrency($result->ordered_deposit['deposit_tax']) . ' (' . $formattedDepositTaxRate . '%)',
                    Configure::read('app.numberHelper')->formatAsCurrency($result->ordered_deposit['deposit_incl']),
                    '',
                ]
                );
            $this->table .= '</tr>';
        }

        // returned deposit
        if ($result->returned_deposit['deposit_incl'] != 0) {
            $this->table .= '<tr style="font-weight:normal;font-style:italic;">';
            $this->renderTableRow(
                [
                    $result->returned_deposit['deposit_amount'] . 'x',
                    '',
                    __('Payment_type_deposit_return'),
                    '',
                    Configure::read('app.numberHelper')->formatAsCurrency($result->returned_deposit['deposit_excl']),
                    Configure::read('app.numberHelper')->formatAsCurrency($result->returned_deposit['deposit_tax']) . ' (' . $formattedDepositTaxRate . '%)',
                    Configure::read('app.numberHelper')->formatAsCurrency($result->returned_deposit['deposit_incl']),
                    '',
                ]
                );
            $this->table .= '</tr>';
        }

        // total sum
        $this->table .= '<tr style="font-size:12px;">';
        $this->table .= '<td align="' . $this->headers[0]['align'] . '" width="' . $this->headers[0]['width'] . '"></td>';
        $this->table .= '<td align="' . $this->headers[1]['align'] . '" width="' . $this->headers[1]['width'] . '"></td>';
        $this->table .= '<td style="font-weight:bold;" align="' . $this->headers[2]['align'] . '" width="' . $this->headers[2]['width'] . '">' . __('Total_sum') . '</td>';
        $this->table .= '<td align="' . $this->headers[3]['align'] . '" width="' . $this->headers[3]['width'] . '"></td>';
        $this->table .= '<td align="' . $this->headers[4]['align'] . '" width="' . $this->headers[4]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($sumPriceExcl) . '</td>';
        $this->table .= '<td align="' . $this->headers[5]['align'] . '" width="' . $this->headers[5]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($sumTax) . '</td>';
        $this->table .= '<td style="font-weight:bold;"  align="' . $this->headers[6]['align'] . '" width="' . $this->headers[6]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($sumPriceIncl) . '</td>';
        $this->table .= '<td align="' . $this->headers[7]['align'] . '" width="' . $this->headers[7]['width'] . '"></td>';
        $this->table .= '</tr>';

    }

}
