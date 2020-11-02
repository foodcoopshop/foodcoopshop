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
use Cake\I18n\FrozenTime;

class CustomerInvoiceTcpdf extends AppTcpdf
{

    public $headerRight;

    public $replaceEuroSign = false;

    public $infoTextForFooter = '';

    public $headers = [];

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->SetTopMargin(43);
        $this->SetRightMargin(0);
        $this->SetFontSize(10);

        $this->headers = [
            [
                'name' => __('Amount'),
                'align' => 'right',
                'width' => 33,
            ],
            [
                'name' => __('Product'),
                'align' => 'left',
                'width' => 226,
            ],
            [
                'name' => __('Price_excl.'),
                'align' => 'right',
                'width' => 55,
            ],
            [
                'name' => __('VAT'),
                'align' => 'right',
                'width' => 55,
            ],
            [
                'name' => __('Tax_rate'),
                'align' => 'right',
                'width' => 45,
            ],
            [
                'name' => __('Price_incl.'),
                'align' => 'right',
                'width' => 58,
            ],
            [
                'name' => __('Delivery_day'),
                'align' => 'right',
                'width' => 58,
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
            if ($taxRate != intval($taxRate)) {
                $formattedTaxRate = Configure::read('app.numberHelper')->formatAsDecimal($taxRate, 1);
            } else {
                $formattedTaxRate = Configure::read('app.numberHelper')->formatAsDecimal($taxRate, 0);
            }

            $this->table .= '<tr style="font-weight:normal;">';
                $this->table .= '<td align="' . $this->headers[0]['align'] . '" width="' . $this->headers[0]['width'] . '">' . $orderDetail->product_amount . 'x</td>';
                $this->table .= '<td align="' . $this->headers[1]['align'] . '" width="' . $this->headers[1]['width'] . '">' . $orderDetail->product_name . '</td>';
                $this->table .= '<td align="' . $this->headers[2]['align'] . '" width="' . $this->headers[2]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_excl) . '</td>';
                $this->table .= '<td align="' . $this->headers[3]['align'] . '" width="' . $this->headers[3]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->order_detail_tax->total_amount) . '</td>';
                $this->table .= '<td align="' . $this->headers[4]['align'] . '" width="' . $this->headers[4]['width'] . '">' . $formattedTaxRate  . '%' . '</td>';
                $this->table .= '<td align="' . $this->headers[5]['align'] . '" width="' . $this->headers[5]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->total_price_tax_incl) . '</td>';
                $this->table .= '<td align="' . $this->headers[6]['align'] . '" width="' . $this->headers[6]['width'] . '">' . $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . '</td>';
            $this->table .= '</tr>';

            if ($orderDetail->deposit > 0) {
                $this->table .= '<tr style="font-weight:normal;font-style:italic;">';
                    $this->table .= '<td align="' . $this->headers[0]['align'] . '" width="' . $this->headers[0]['width'] . '"></td>';
                    $this->table .= '<td align="' . $this->headers[1]['align'] . '" width="' . $this->headers[1]['width'] . '">+ ' . __('Deposit') . '</td>';
                    $this->table .= '<td align="' . $this->headers[2]['align'] . '" width="' . $this->headers[2]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->deposit_net) . '</td>';
                    $this->table .= '<td align="' . $this->headers[3]['align'] . '" width="' . $this->headers[3]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->deposit_tax) . '</td>';
                    $this->table .= '<td align="' . $this->headers[4]['align'] . '" width="' . $this->headers[4]['width'] . '">' . '20%' . '</td>';
                    $this->table .= '<td align="' . $this->headers[5]['align'] . '" width="' . $this->headers[5]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($orderDetail->deposit) . '</td>';
                    $this->table .= '<td align="' . $this->headers[6]['align'] . '" width="' . $this->headers[6]['width'] . '">' . $orderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . '</td>';
                $this->table .= '</tr>';
            }

        }

        $this->table .= '<tr style="font-size:12px;">';
            $this->table .= '<td align="' . $this->headers[0]['align'] . '" width="' . $this->headers[0]['width'] . '"></td>';
            $this->table .= '<td style="font-weight:bold;" align="' . $this->headers[1]['align'] . '" width="' . $this->headers[1]['width'] . '">' . __('Total_sum') . '</td>';
            $this->table .= '<td align="' . $this->headers[2]['align'] . '" width="' . $this->headers[2]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($sumPriceExcl) . '</td>';
            $this->table .= '<td align="' . $this->headers[3]['align'] . '" width="' . $this->headers[3]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($sumTax) . '</td>';
            $this->table .= '<td align="' . $this->headers[4]['align'] . '" width="' . $this->headers[4]['width'] . '"></td>';
            $this->table .= '<td style="font-weight:bold;"  align="' . $this->headers[5]['align'] . '" width="' . $this->headers[5]['width'] . '">' . Configure::read('app.numberHelper')->formatAsCurrency($sumPriceIncl) . '</td>';
            $this->table .= '<td align="' . $this->headers[6]['align'] . '" width="' . $this->headers[6]['width'] . '"></td>';
        $this->table .= '</tr>';

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

        $convertedHeaderRight = '<br />'.Configure::read('appDb.FCS_APP_NAME').'<br />'.Configure::read('appDb.FCS_APP_ADDRESS').'<br />'.Configure::read('appDb.FCS_APP_EMAIL');
        $convertedHeaderRight = Configure::read('app.htmlHelper')->prepareDbTextForPDF($convertedHeaderRight);

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

    /**
     * parent class is overriden although it's name is Header() (capital letter)
     * php functions are case insensitive
     */
    public function footer()
    {
        $this->SetY(-19);
        $this->drawLine();
        $this->SetFontSize(10);
        $this->Cell(0, 10, $this->infoTextForFooter , 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Ln(4);
        $now = new FrozenTime();
        $textForFooterRight =
        __('Generated_on_{0}', [
            $now->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs'))
        ])
        . ', ' .
        __('Page_{0}_of_{1}', [
            $this->getAliasNumPage(), $this->getAliasNbPages()
        ]);
        $this->Cell(0, 10, $textForFooterRight, 0, false, 'R', 0, '', 0, false, 'T', 'M');
        $this->SetFontSize(12);
    }

}
