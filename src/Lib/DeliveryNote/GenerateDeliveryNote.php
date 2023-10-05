<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\DeliveryNote;
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateDeliveryNote {

    public function getSpreadsheet($orderDetails)
    {

        $headlines = [
            [
                'name' => __('Amount'),
                'alignment' => 'right',
            ],
            [
                'name' => __('Product'),
                'alignment' => 'left',
                'width' => 60,
            ],
            [
                'name' => __('net_per_piece_abbr'),
                'alignment' => 'right',
                'width' => 9,
            ],
            [
                'name' => __('Weight'),
                'alignment' => 'right',
            ],
            [
                'name' => __('Unit'),
                'alignment' => 'left',
            ],
            [
                'name' => __('Tax_rate'),
                'alignment' => 'right',
                'width' => 10,
            ],
            [
                'name' => __('net'),
                'alignment' => 'right',
            ],
            [
                'name' => __('VAT'),
                'alignment' => 'right',
            ],
            [
                'name' => __('gross'),
                'alignment' => 'right',
            ],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $column = 1;
        foreach($headlines as $headline) {
            $sheet->setCellValueByColumnAndRow($column, 1, $headline['name']);
            $this->setAlignmentForCell($sheet, $column, 1, $headline['alignment']);
            $this->setBoldForCell($sheet, $column, 1);
            if (isset($headline['width'])) {
                $sheet->getColumnDimensionByColumn($column)->setWidth($headline['width']);
            }
            $column++;
        }

        $totalSumAmount = 0;
        $totalSumPurchasePriceNet = 0;
        $totalSumPurchasePriceTax = 0;
        $totalSumPurchasePriceGross = 0;

        $defaultTaxArray = [
            'sum_price_net' => 0,
            'sum_tax' => 0,
            'sum_price_gross' => 0,
        ];
        $taxRates = [];

        $row = 2;
        foreach($orderDetails as $orderDetail) {

            $totalSumAmount += $orderDetail->SumAmount;
            $totalSumPurchasePriceNet += $orderDetail->SumPurchasePriceNet;
            $totalSumPurchasePriceTax += $orderDetail->SumPurchasePriceTax;
            $totalSumPurchasePriceGross += $orderDetail->SumPurchasePriceGross;

            $netPerPiece = '';
            if ($orderDetail->Unit == '') {
                $netPerPiece = round($orderDetail->SumPurchasePriceNet / $orderDetail->SumAmount, 2);
            }

            $taxRate = $orderDetail->PurchasePriceTaxRate;
            if (!isset($taxRates[$taxRate])) {
                $taxRates[$taxRate] = $defaultTaxArray;
            }
            $taxRates[$taxRate]['sum_price_net'] += $orderDetail->SumPurchasePriceNet;
            $taxRates[$taxRate]['sum_tax'] += $orderDetail->SumPurchasePriceTax;
            $taxRates[$taxRate]['sum_price_gross'] += $orderDetail->SumPurchasePriceGross;

            $sheet->setCellValueByColumnAndRow(1, $row, $orderDetail->SumAmount);
            $sheet->setCellValueByColumnAndRow(2, $row, html_entity_decode($orderDetail->ProductName));
            $sheet->setCellValueByColumnAndRow(3, $row, $netPerPiece);
            $this->setNumberFormatForCell($sheet, 3, $row);
            $sheet->setCellValueByColumnAndRow(4, $row, $orderDetail->SumWeight);
            $sheet->setCellValueByColumnAndRow(5, $row, $orderDetail->Unit);
            $sheet->setCellValueByColumnAndRow(6, $row, $orderDetail->PurchasePriceTaxRate);
            $sheet->setCellValueByColumnAndRow(7, $row, $orderDetail->SumPurchasePriceNet);
            $this->setNumberFormatForCell($sheet, 7, $row);
            $sheet->setCellValueByColumnAndRow(8, $row, $orderDetail->SumPurchasePriceTax);
            $this->setNumberFormatForCell($sheet, 8, $row);
            $sheet->setCellValueByColumnAndRow(9, $row, $orderDetail->SumPurchasePriceGross);
            $this->setNumberFormatForCell($sheet, 9, $row);
            $row++;
        }

        // add row with sums
        $row++;
        $sheet->setCellValueByColumnAndRow(1, $row, $totalSumAmount);
        $this->setBoldForCell($sheet, 1, $row);

        $sheet->setCellValueByColumnAndRow(7, $row, $totalSumPurchasePriceNet);
        $this->setNumberFormatForCell($sheet, 7, $row);
        $this->setBoldForCell($sheet, 7, $row);

        $sheet->setCellValueByColumnAndRow(8, $row, $totalSumPurchasePriceTax);
        $this->setNumberFormatForCell($sheet, 8, $row);
        $this->setBoldForCell($sheet, 8, $row);

        $sheet->setCellValueByColumnAndRow(9, $row, $totalSumPurchasePriceGross);
        $this->setNumberFormatForCell($sheet, 9, $row);
        $this->setBoldForCell($sheet, 9, $row);

        if (count($taxRates) > 1) {

            ksort($taxRates);

            // add rows for sums / tax rates
            $row++;
            $row++;
            $sheet->setCellValueByColumnAndRow(2, $row, __('Tax_rates_overview_table'));
            foreach($taxRates as $taxRate => $trt) {
                $sheet->setCellValueByColumnAndRow(6, $row, $taxRate);
                $sheet->setCellValueByColumnAndRow(7, $row, $trt['sum_price_net']);
                $this->setNumberFormatForCell($sheet, 7, $row);
                $sheet->setCellValueByColumnAndRow(8, $row, $trt['sum_tax']);
                $this->setNumberFormatForCell($sheet, 8, $row);
                $sheet->setCellValueByColumnAndRow(9, $row, $trt['sum_price_gross']);
                $this->setNumberFormatForCell($sheet, 9, $row);
                $row++;
            }
        }

        $row++;
        $row++;
        $sheet->setCellValueByColumnAndRow(2, $row, __('All_amounts_in_{0}.', [Configure::read('app.currencyName')]));

        return $spreadsheet;

    }

    public function writeSpreadsheetAsFile($spreadsheet, $dateFrom, $dateTo, $manufacturerName)
    {
        $writer = new Xlsx($spreadsheet);
        $filename = __('Delivery_note') . '-' . $dateFrom . '-' . $dateTo . '-' .StringComponent::slugify($manufacturerName) . '-' . StringComponent::slugify(Configure::read('appDb.FCS_APP_NAME')) . '.xlsx';
        $writer->save(TMP . $filename);
        return $filename;
    }

    public function deleteTmpFile($filename) {
        unlink(TMP . $filename);
    }

    protected function setAlignmentForCell($sheet, $column, $row, $alignment)
    {
        $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setHorizontal($alignment);
    }

    protected function setBoldForCell($sheet, $column, $row)
    {
        $sheet->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
    }

    protected function setNumberFormatForCell($sheet, $column, $row)
    {
        $sheet->getStyleByColumnAndRow($column, $row)->getNumberFormat() ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    }

}