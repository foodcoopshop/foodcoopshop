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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\Pdf;

use Cake\Core\Configure;
use TCPDF;

class AppTcpdf extends TCPDF
{

    public $headerRight;

    public $table = '';

    public $infoTextForFooter = '';
    
    public $textHelper;
    
    private $html = '';
    
    public function setTextHelper($textHelper)
    {
        $this->textHelper = $textHelper;
    }
    
    public function writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
    {
        // in generate_order_confirmation.ctp::88 $this->MyNumber->formatAsCurrency leads to empty output
        // but in all other pdfs it works. this workaround helps
        $html = preg_replace('/€/', '&euro;', $html);
        $this->html .= $html;
        parent::writeHTML($html, $ln, $fill, $reseth, $cell, $align);
    }
    
    public function outputHtml()
    {
        echo $this->html;
        exit(0);
    }

    public function renderTable()
    {
        $this->table .= '</table>';

        $this->writeHTML($this->table, true, false, true, false, '');

        // reset table
        $this->table = '';
    }
    
    public function renderTaxRatesTable($results)
    {
        
        $taxRates = [];
        foreach($results as $result)
        {
            @$taxRates[$result['TaxRate']]['sum_price_excl'] += $result['OrderDetailPriceExcl'];
            @$taxRates[$result['TaxRate']]['sum_tax'] += $result['OrderDetailTaxAmount'];
            @$taxRates[$result['TaxRate']]['sum_price_incl'] += $result['OrderDetailPriceIncl'];
        }
        
        if (count($taxRates) == 1) {
            return false;
        }
        
        ksort($taxRates);
        
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
            if ($taxRate != intval($taxRate)) {
                $formattedTaxRate = Configure::read('app.numberHelper')->formatAsDecimal($taxRate, 1);
            } else {
                $formattedTaxRate = Configure::read('app.numberHelper')->formatAsDecimal($taxRate, 0);
            }
            $html .= $formattedTaxRate . '%';
            $html .= '</td>';
            $html .= '<td align="right" width="'.$taxRatesTableColumnWidth.'">'. Configure::read('app.numberHelper')->formatAsDecimal($data['sum_price_excl']) . '</td>';
            $html .= '<td align="right" width="'.$taxRatesTableColumnWidth.'">'. Configure::read('app.numberHelper')->formatAsDecimal($data['sum_tax']) . '</td>';
            $html .= '<td align="right" width="'.$taxRatesTableColumnWidth.'">'. Configure::read('app.numberHelper')->formatAsDecimal($data['sum_price_incl']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $this->Ln(3);
        $this->writeHTML($html, true, false, true, false, '');
    }

    public function renderDetailedOrderList($results, $widths, $headers, $groupType, $onlyShowSums = false)
    {
        $this->table .= '<table style="font-size:8px" cellspacing="0" cellpadding="1" border="1"><thead><tr>';

        $isOrderList = $this->isOrderList($headers);

        // Header
        $num_headers = count($headers);
        for ($i = 0; $i < $num_headers; ++ $i) {
            $this->table .= '<th style="font-weight:bold;background-color:#cecece" width="' . $widths[$i] . '">' . $headers[$i] . '</th>';
        }
        $this->table .= '</tr></thead>';

        // add products to table
        $amountSum = 0;
        $priceInclSum = 0;
        $priceExclSum = 0;
        $taxSum = 0;
        $unitSum = [];
        $i = 0;

        foreach ($results as $result) {
            $amount = $result['OrderDetailAmount'];
            $priceIncl = $result['OrderDetailPriceIncl'];
            $priceExcl = $result['OrderDetailPriceExcl'];
            $tax = $result['OrderDetailTaxAmount'];
            $productName = $result['ProductName'];
            $customerName = $result['CustomerName'];
            $taxRate = $result['TaxRate'];
            
            if ($groupType == 'customer' && isset($lastCustomerName) && $lastCustomerName != $customerName) {
                $this->getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $lastCustomerName, $lastTaxRate, $lastUnitSum);
                // reset everything
                $amountSum = $priceExclSum = $taxSum = $priceInclSum = 0;
                $unitSum = [];
            }

            if ($groupType == 'product' && isset($lastProductName) && ($lastProductName != $productName || $lastTaxRate != $taxRate)) {
                $this->getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $lastProductName, $lastTaxRate, $lastUnitSum);
                // reset everything
                $amountSum = $priceExclSum = $taxSum = $priceInclSum = 0;
                $unitSum = [];
            }

            $amountSum += $amount;
            $priceInclSum += $priceIncl;
            $priceExclSum += $priceExcl;
            $taxSum += $tax;

            if ($result['OrderDetailUnitQuantityInUnits'] != '') {
                @$unitSum[$result['OrderDetailUnitUnitName']] += $result['OrderDetailUnitProductQuantityInUnits'];
            }

            if (! $onlyShowSums) {
                $this->table .= '<tr style="font-weight:normal;background-color:#ffffff;">';

                $indexForWidth = 0;
                $amountStyle = '';
                if ($amount > 1) {
                    $amountStyle = 'background-color: #cecece;';
                }
                $this->table .= '<td style="' . $amountStyle . '" align="right" width="' . $widths[$indexForWidth] . '">' . $amount . 'x</td>';

                $indexForWidth ++;

                $unity = '';
                if ($result['OrderDetailUnitQuantityInUnits'] > 0) {
                    if ($isOrderList) {
                        $unity = Configure::read('app.pricePerUnitHelper')->getQuantityInUnits(
                            true,
                            $result['OrderDetailUnitQuantityInUnits'],
                            $result['OrderDetailUnitUnitName'],
                            $amount
                        );
                    }
                }
                if (!$isOrderList) {
                    if ($result['OrderDetailUnitProductQuantityInUnits'] > 0) {
                        $unity = Configure::read('app.numberHelper')->formatUnitAsDecimal($result['OrderDetailUnitProductQuantityInUnits']) . ' ' . $result['OrderDetailUnitUnitName'];
                    }
                }

                if ($unity != '') {
                    $unity = ', ' . $unity;
                }
                $this->table .= '<td width="' . $widths[$indexForWidth] . '">' . $productName . $unity . '</td>';

                if (in_array(__('Price_excl.'), $headers)) {
                    $indexForWidth ++;
                    $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($priceExcl) . '</td>';
                }

                if (in_array(__('VAT'), $headers)) {
                    $indexForWidth ++;
                    $this->table .= '<td width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($tax) . ' (' . ($taxRate != intval($taxRate) ? Configure::read('app.numberHelper')->formatAsDecimal($taxRate, 1) : Configure::read('app.numberHelper')->formatAsDecimal($taxRate, 0)) . '%)</td>';
                }

                $indexForWidth ++;
                $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($priceIncl) . '</td>';
                
                if (in_array(__('Order_day'), $headers)) {
                    $indexForWidth ++;
                    $this->table .= '<td align="center" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.timeHelper')->formatToDateShort($result['OrderDetailCreated']) . '</td>';
                }
                
                if (in_array(__('Delivery_day'), $headers)) {
                    $indexForWidth ++;
                    $this->table .= '<td align="center" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.timeHelper')->formatToDateShort($result['OrderDetailPickupDay']) . '</td>';
                }
                
                $indexForWidth ++;
                $this->table .= '<td width="' . $widths[$indexForWidth] . '">' . $this->textHelper->truncate($result['CustomerName'], 27) . '</td>';

                $this->table .= '</tr>';
            }

            // very last row
            if ($i + 1 == count($results)) {
                if ($groupType == 'customer') {
                    $this->getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $customerName, $taxRate, $unitSum);
                }
                if ($groupType == 'product') {
                    $this->getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $productName, $taxRate, $unitSum);
                }
            }

            $lastProductName = $productName;
            $lastCustomerName = $customerName;
            $lastTaxRate = $taxRate;
            $lastUnitSum = $unitSum;

            $i ++;
        }
    }

    private function getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $lastObjectName, $taxRate = '', $unitSum)
    {
        $colspan = $this->getCorrectColspan($headers);

        // currently used for recognizing if sum-only-mode is used (invoices)
        $detailsHidden = false;
        if ($colspan == 2) {
            $detailsHidden = true;
        }

        $trStyles = ' style="background-color:#cecece;font-weight:bold;"';
        if ($detailsHidden) {
            $trStyles = '';
        }

        $this->table .= '<tr' . $trStyles . '>';

        $indexForWidth = 0;

        $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . $amountSum . 'x</td>';
        $indexForWidth ++;

        $unitSumString = '';
        if ($detailsHidden) {
            $unitSumString = Configure::read('app.pricePerUnitHelper')->getStringFromUnitSums($unitSum, ', ');
            if ($unitSumString != '') {
                $unitSumString = ', ' . $unitSumString;
            }
        }
        $this->table .= '<td width="' . $widths[$indexForWidth] . '">' . $lastObjectName . $unitSumString .  '</td>';
        $indexForWidth ++;

        if (in_array(__('Price_excl.'), $headers)) {
            $colspan --;
            $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($priceExclSum) . '</td>';
            $indexForWidth ++;
        }

        if (in_array(__('VAT'), $headers)) {
            $colspan --;
            $taxRateString = '';
            if ($detailsHidden) {
                $taxRateString = ' (' . ($taxRate != intval($taxRate) ? Configure::read('app.numberHelper')->formatAsDecimal($taxRate, 1) : Configure::read('app.numberHelper')->formatAsDecimal($taxRate, 0)) . '%)';
            }
            $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($taxSum) . $taxRateString . '</td>';
            $indexForWidth ++;
        }

        $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($priceInclSum) . '</td>';
        $indexForWidth ++;

        if ($colspan > 0) {
            $this->table .= '<td colspan="' . $colspan . '"></td>';
        }

        $this->table .= '</tr>';

        if (! $detailsHidden) {
            $this->table .= '<tr border="0"><td></td></tr>';
        }
    }

    public function isOrderList($headers)
    {
        return $this->getCorrectColspan($headers) == 3;
    }

    public function getCorrectColspan($headers)
    {
        $diff = 2;
        // first page of invoices does not contain column "member"
        if (! in_array(__('Member'), $headers)) {
            $diff = 3;
        }
        $colspan = count($headers) - $diff;
        return $colspan;
    }

    public function addLastSumRow($headers, $sumPriceExcl, $sumTax, $sumPriceIncl)
    {
        $colspan = $this->getCorrectColspan($headers);

        // currently used for recognizing if sum-only-mode is used (invoices)
        $detailsHidden = false;
        if ($colspan == 2) {
            $detailsHidden = true;
        }

        if ($detailsHidden) {
            $this->table .= '<tr><td></td></tr>';
        }

        $this->table .= '<tr style="font-size:12px;font-weight:bold;">';

        $this->table .= '<td></td>';
        $this->table .= '<td>' . __('Total_sum') . '</td>';

        if (in_array(__('Price_excl.'), $headers)) {
            $colspan --;
            $this->table .= '<td align="right">' . $sumPriceExcl . '</td>';
        }

        if (in_array(__('VAT'), $headers)) {
            $colspan --;
            $this->table .= '<td align="right">' . $sumTax . '</td>';
        }

        $this->table .= '<td align="right">' . $sumPriceIncl . '</td>';

        if ($colspan > 0) {
            $this->table .= '<td colspan="' . $colspan . '"></td>';
        }

        $this->table .= '</tr>';
    }

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        mb_internal_encoding('UTF-8');

        $this->SetCreator(Configure::read('appDb.FCS_APP_NAME'));
        $this->SetAuthor(Configure::read('appDb.FCS_APP_NAME'));
        $this->SetTopMargin(43);
        $this->SetRightMargin(0);
        $this->SetFontSize(10);
    }

    /**
     * parent class is overriden although it's name is Header() (capital letter)
     * php functions are case insensitive
     */
    public function header()
    {
        $this->SetY(4);

        $this->MultiCell(50, 0, '<img src="' . ROOT . DS . 'webroot' . DS . 'files' . DS . 'images' . DS . 'logo-pdf.jpg' . '">', 0, 'L', 0, 0, '', '', true, null, true);
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
        $this->SetY(- 15);
        $this->drawLine();
        $this->SetFontSize(10);
        $this->Cell(0, 10, $this->infoTextForFooter, 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $textForFooterRight = __('Page_{0}_of_{1}', [$this->getAliasNumPage(), $this->getAliasNbPages()]);
        $this->Cell(0, 10, $textForFooterRight, 0, false, 'R', 0, '', 0, false, 'T', 'M');
        $this->SetFontSize(12);
    }

    private function drawLine()
    {
        $this->Line(0, $this->y, $this->w, $this->y);
    }
}
