<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Pdf;

use Cake\Core\Configure;
use App\Services\Pdf\Traits\FooterTrait;
use App\Services\Pdf\Traits\TaxSumTableTrait;

class ListTcpdfService extends AppTcpdfService
{

    use FooterTrait;
    use TaxSumTableTrait;

    public string $headerRight;

    public string $infoTextForFooter = '';

    public ?string $html;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->SetTopMargin(43);
        $this->SetRightMargin(0);
        $this->SetFontSize(10);
    }

    public function prepareTaxSumData($results): array|false
    {

        $taxRates = [];
        foreach($results as $result) {
            if (!isset($taxRates[$result['TaxRate']])) {
                $taxRates[$result['TaxRate']] = [
                    'sum_price_excl' => 0,
                    'sum_tax' => 0,
                    'sum_price_incl' => 0,
                ];
            }
            $taxRates[$result['TaxRate']]['sum_price_excl'] += $result['OrderDetailPriceExcl'];
            $taxRates[$result['TaxRate']]['sum_tax'] += $result['OrderDetailTaxAmount'];
            $taxRates[$result['TaxRate']]['sum_price_incl'] += $result['OrderDetailPriceIncl'];
        }

        if (count($taxRates) == 1) {
            return false;
        }

        ksort($taxRates);

        return $taxRates;

    }

    public function renderDetailedOrderList($results, $widths, $headers, $groupType, $onlyShowSums = false): void
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
        $showPricePerUnitMessage = false;
        $i = 0;

        foreach ($results as $result) {
            $amount = $result['OrderDetailAmount'];
            $priceIncl = $result['OrderDetailPriceIncl'];
            $priceExcl = $result['OrderDetailPriceExcl'];
            $tax = $result['OrderDetailTaxAmount'];
            $productName = $result['ProductName'];
            // invoices can also be generated for past date ranges where deleted members would cause an error
            $customerName = $result['CustomerName'] ?? __('Deleted_member');
            $taxRate = $result['TaxRate'];
            $showPricePerUnitSign = false;
            $showUnitSum = false;

            if ($groupType == 'customer' 
                && isset($lastCustomerName)
                && isset($lastUnitSum)
                && isset($lastTaxRate)
                && $lastCustomerName != $customerName) {
                $this->getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $lastCustomerName, $lastUnitSum, $lastTaxRate, $showPricePerUnitMessage, $showUnitSum);
                // reset everything
                $amountSum = $priceExclSum = $taxSum = $priceInclSum = 0;
                $showPricePerUnitMessage = false;
                $unitSum = [];
            }

            if ($groupType == 'product'
                && isset($lastProductName)
                && isset($lastTaxRate)
                && isset($lastUnitSum)
                && ($lastProductName != $productName || $lastTaxRate != $taxRate)) {
                $showUnitSum = true;
                $this->getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $lastProductName, $lastUnitSum, $lastTaxRate, $showPricePerUnitMessage, $showUnitSum);
                // reset everything
                $amountSum = $priceExclSum = $taxSum = $priceInclSum = 0;
                $showPricePerUnitMessage = false;
                $unitSum = [];
            }

            $amountSum += $amount;
            $priceInclSum += $priceIncl;
            $priceExclSum += $priceExcl;
            $taxSum += $tax;

            if ($result['OrderDetailUnitQuantityInUnits'] != '') {
                if (!isset($unitSum[$result['OrderDetailUnitUnitName']])) {
                    $unitSum[$result['OrderDetailUnitUnitName']] = 0;
                }
                $unitSum[$result['OrderDetailUnitUnitName']] += $result['OrderDetailUnitProductQuantityInUnits'];
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
                        $showPricePerUnitSign = true;
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
                    $this->table .= '<td width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($tax) . ' (' . Configure::read('app.numberHelper')->formatTaxRate($taxRate) . '%)</td>';
                }

                if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                    $indexForWidth ++;
                    $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($priceIncl) . ($showPricePerUnitSign ? '*' : '') . '</td>';
                }

                if (in_array(__('Order_day'), $headers)) {
                    $indexForWidth ++;
                    $this->table .= '<td align="center" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.timeHelper')->formatToDateShort($result['OrderDetailCreated']) . '</td>';
                }

                if (in_array(__('Delivery_day'), $headers)) {
                    $indexForWidth ++;
                    $this->table .= '<td align="center" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.timeHelper')->formatToDateShort($result['OrderDetailPickupDay']) . '</td>';
                }

                $indexForWidth ++;
                // invoices can also be generated for past date ranges where deleted members would cause an error
                if ($result['CustomerName']) {
                    $customerNameForColumn = $this->textHelper->truncate($result['CustomerName'], 27);
                } else {
                    $customerNameForColumn = __('Deleted_Member');
                }
                $this->table .= '<td width="' . $widths[$indexForWidth] . '">' . $customerNameForColumn . '</td>';

                $this->table .= '</tr>';
            }

            // very last row
            if ($i + 1 == count($results)) {
                if ($groupType == 'customer') {
                    $showUnitSum = false;
                    $this->getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $customerName, $unitSum, $taxRate, $showPricePerUnitMessage, $showUnitSum);
                }
                if ($groupType == 'product') {
                    $showUnitSum = true;
                    $this->getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $productName, $unitSum, $taxRate, $showPricePerUnitMessage, $showUnitSum);
                }
            }

            $lastProductName = $productName;
            $lastCustomerName = $customerName;
            $lastTaxRate = $taxRate;
            $lastUnitSum = $unitSum;
            $showPricePerUnitMessage |= $showPricePerUnitSign;

            $i ++;
        }
    }

    private function getInvoiceGenerateSum($amountSum, $priceExclSum, $taxSum, $priceInclSum, $headers, $widths, $lastObjectName, $unitSum, $taxRate = '', $showPricePerUnitMessage=false, $showUnitSum=false): void
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
        if ($showUnitSum) {
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
                $taxRateString = ' (' . Configure::read('app.numberHelper')->formatTaxRate($taxRate) . '%)';
            }
            $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($taxSum) . $taxRateString . '</td>';
            $indexForWidth ++;
        }

        if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $this->table .= '<td align="right" width="' . $widths[$indexForWidth] . '">' . Configure::read('app.numberHelper')->formatAsDecimal($priceInclSum) . '</td>';
        }
        $indexForWidth ++;

        if ($colspan > 0) {
            $this->table .= '<td colspan="' . $colspan . '">' . ($showPricePerUnitMessage ? ' * ' . __('Price_per_weight') : '') . '</td>';
        }

        $this->table .= '</tr>';

        if (! $detailsHidden) {
            $this->table .= '<tr border="0"><td></td></tr>';
        }
    }

    public function isOrderList($headers): bool
    {
        return $this->getCorrectColspan($headers) == 3;
    }

    public function getCorrectColspan($headers): int
    {
        $diff = 2;
        if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            $diff = 1;
        }
        // first page of invoices does not contain column "member"
        if (! in_array(__('Member'), $headers)) {
            $diff = 3;
        }
        $colspan = count($headers) - $diff;
        return $colspan;
    }

    public function addLastSumRow($headers, $sumAmount, $sumPriceExcl, $sumTax, $sumPriceIncl): void
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

        if (in_array(__('Amount'), $headers)) {
            $colspan --;
            $this->table .= '<td align="right">' . $sumAmount . 'x</td>';
        }

        $this->table .= '<td>' . __('Total_sum') . '</td>';

        if (in_array(__('Price_excl.'), $headers)) {
            $colspan --;
            $this->table .= '<td align="right">' . $sumPriceExcl . '</td>';
        }

        if (in_array(__('VAT'), $headers)) {
            $colspan --;
            $this->table .= '<td align="right">' . $sumTax . '</td>';
        }

        if (is_null($sumPriceIncl)) {
            $colspan++;
        } else {
            $this->table .= '<td align="right">' . $sumPriceIncl . '</td>';
        }

        if ($colspan > 0) {
            $this->table .= '<td colspan="' . $colspan . '"></td>';
        }

        $this->table .= '</tr>';
    }

    /**
     * parent class is overriden although it's name is Header() (capital letter)
     * php functions are case insensitive
     */
    public function header(): void
    {
        $this->SetY(4);

        $this->MultiCell(50, 0, '<img src="' . $this->logoPath . '">', 0, 'L', false, 0, null, null, true, 0, true);
        $this->setFontSize(10);

        $convertedHeaderRight = '<br />'.Configure::read('appDb.FCS_APP_NAME').'<br />'.Configure::read('appDb.FCS_APP_ADDRESS').'<br />'.Configure::read('appDb.FCS_APP_EMAIL');

        // add additional line break on top if short address
        $lineCount = substr_count($convertedHeaderRight, "\n");
        if ($lineCount < 5) {
            $convertedHeaderRight = '<br />' . $convertedHeaderRight;
        }

        $this->headerRight = $convertedHeaderRight;

        $this->MultiCell(145 - $this->lMargin, 0, $this->headerRight, 0, 'R', false, 1, null, null, true, 0, true);

        $this->SetY(36);
        $this->drawLine();
    }

}
