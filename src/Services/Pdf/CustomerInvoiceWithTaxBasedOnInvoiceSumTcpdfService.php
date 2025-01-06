<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Pdf;

use Cake\Core\Configure;

class CustomerInvoiceWithTaxBasedOnInvoiceSumTcpdfService extends CustomerInvoiceBaseTcpdfService
{

    public function setHeaders(): void
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
                'width' => 185,
            ],
            [
                'name' => __('Manufacturer'),
                'align' => 'left',
                'width' => 124,
            ],
            [
                'name' => __('Price_net'),
                'align' => 'right',
                'width' => 58,
            ],
            [
                'name' => __('Tax_rate'),
                'align' => 'right',
                'width' => 30,
            ],
            [
                'name' => __('Delivery_day'),
                'align' => 'right',
                'width' => 48,
            ],
        ];
    }

    public function prepareTableData($result, $sumPriceExcl, $sumPriceIncl, $sumTax): void
    {

        foreach($result->active_order_details as $orderDetail) {

            // products
            $this->table .= '<tr style="font-weight:normal;">';
            $this->renderTableRow(
                [
                    $orderDetail->product_amount . 'x',
                    $orderDetail->product_id  . ($orderDetail->product_attribute_id > 0 ? '-' . $orderDetail->product_attribute_id : ''),
                    $orderDetail->product_name,
                    $this->textHelper->truncate($orderDetail->product->manufacturer->name, 28),
                    Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_excl),
                    Configure::read('app.numberHelper')->formatTaxRate($orderDetail->tax_rate) . '%',
                    $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')),
                ]
                );
            $this->table .= '</tr>';

        }

        $depositTaxRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));

        // ordered deposit
        if ($result->ordered_deposit['deposit_incl'] != 0) {
            $this->table .= '<tr style="font-weight:normal;">';
            $this->renderTableRow(
                [
                    $result->ordered_deposit['deposit_amount'] . 'x',
                    '',
                    __('Delivered_deposit'),
                    '',
                    Configure::read('app.numberHelper')->formatAsCurrency($result->ordered_deposit['deposit_excl']),
                    Configure::read('app.numberHelper')->formatTaxRate($depositTaxRate) . '%',
                    '',
                ]
                );
            $this->table .= '</tr>';
        }

        $this->table .= '<tr style="font-size:12px;">';
            $this->table .= '<td colspan="7"></td>';
        $this->table .= '</tr>';

        $this->renderSumRow(__('Total_sum_net'), Configure::read('app.numberHelper')->formatAsCurrency($sumPriceExcl));
        $this->renderSumRow(__('Value_added_tax'), Configure::read('app.numberHelper')->formatAsCurrency($sumTax));
        $this->renderSumRow('<b style="font-size:12px;">' . __('Total_sum_gross') . '</b>', '<b style="font-size:12px;">' . Configure::read('app.numberHelper')->formatAsCurrency($sumPriceIncl) . '</b>');

    }

    private function renderSumRow($label, $value): void
    {
        $this->table .= '<tr>';
            $this->table .= '<td colspan="4" align="right">' . $label . '</td>';
            $this->table .= '<td align="' . $this->headers[4]['align'] . '" width="' . $this->headers[4]['width'] . '">' . $value . '</td>';
            $this->table .= '<td colspan="2"></td>';
        $this->table .= '</tr>';
    }

}
