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
namespace App\Lib\Pdf;

use Cake\Core\Configure;

class CustomerInvoiceTcpdf extends AppTcpdf
{

    use FooterTrait;
    use TaxSumTableTrait;

    public $headerRight;

    public $replaceEuroSign = false;

    public $infoTextForFooter = '';

    public $headers = [];

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->SetTopMargin(48);
        $this->SetRightMargin(0);
        $this->SetLeftMargin(18);
        $this->SetFontSize(10);

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

    public function prepareTableHeader()
    {

        $this->table = '<table style="font-size:8px" cellspacing="0" cellpadding="1" border="1"><thead><tr>';

        foreach($this->headers as $header) {
            $this->table .= '<th style="font-weight:bold;background-color:#cecece" align="' . $header['align'] . '" width="' . $header['width'] . '">' . $header['name'] . '</th>';
        }
        $this->table .= '</tr></thead>';
    }

    public function prepareTableData($result, $sumPriceExcl, $sumPriceIncl, $sumTax)
    {

        foreach($result->active_order_details as $orderDetail) {

            $taxRate = $orderDetail->tax->rate ?? 0;
            $formattedTaxRate = Configure::read('app.numberHelper')->formatTaxRate($taxRate);

            // products
            $this->table .= '<tr style="font-weight:normal;">';
                $this->renderTableRow(
                    [
                        $orderDetail->product_amount . 'x',
                        $orderDetail->product_id  . ($orderDetail->product_attribute_id > 0 ? '-' . $orderDetail->product_attribute_id : ''),
                        $orderDetail->product_name,
                        $this->textHelper->truncate($orderDetail->product->manufacturer->name, 18),
                        Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_excl),
                        Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->order_detail_tax->total_amount) . ' (' . $formattedTaxRate  . '%)',
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

    private function renderTableRow($values)
    {
        $i = 0;
        foreach($values as $value) {
            $this->table .= '<td align="' . $this->headers[$i]['align'] . '" width="' . $this->headers[$i]['width'] . '">' . $value . '</td>';
            $i++;
        }
    }

    /**
     * parent class is overriden although it's name is Header() (capital letter)
     * php functions are case insensitive
     */
    public function header()
    {
        $this->SetY(4);

        $this->MultiCell(50, 0, '<img src="' . $this->logoPath . '">', 0, 'L', 0, 0, '', '', true, null, true);
        $this->setFontSize(10);

        $convertedHeaderRight = Configure::read('app.htmlHelper')->prepareDbTextForPDF(Configure::read('appDb.FCS_INVOICE_HEADER_TEXT'));

        // add additional line break on top if short address
        $lineCount = substr_count($convertedHeaderRight, "\n");
        if ($lineCount < 5) {
            $convertedHeaderRight = "\n" . $convertedHeaderRight;
        }

        $this->headerRight = $convertedHeaderRight;

        $this->MultiCell(145 - $this->lMargin, 0, $this->headerRight, 0, 'R', 0, 1, '', '', true);

        $this->SetY(36);
        $this->drawLine();
    }

}
