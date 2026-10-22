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

use Com\Tecnick\Barcode\Barcode;

class BarCodeTcpdfService extends AppTcpdfService
{

    /**
     * @var array<string, mixed>
     */
    public array $barCodeStyle = [
        'position' => '',
        'align' => 'C',
        'stretch' => false,
        'fitwidth' => true,
        'cellfitalign' => '',
        'border' => true,
        'hpadding' => 'auto',
        'vpadding' => 'auto',
        'fgcolor' => [0,0,0],
        'bgcolor' => false,
        'text' => true,
        'fontsize' => 8,
        'stretchtext' => 4
    ];

    public function __construct(string $orientation = 'P')
    {
        parent::__construct($orientation);
        $this->setFontSize(10);
    }

    /**
     * barcode image sized like the former tcpdf library: one pixel equals one point, downscaled to the cell width
     */
    public function getBarcodeImageTag(string $code, float $moduleWidth, int $height, float $cellWidth): string
    {
        $barcodeFactory = new Barcode();
        $modules = $barcodeFactory->getBarcodeObj('C39', $code)->getArray()['ncols'] + 1;
        $widthPx = (int) floor($modules * $moduleWidth);
        $barcode = $barcodeFactory->getBarcodeObj('C39', $code, $widthPx, $height);
        $scale = min(1, $cellWidth / $widthPx);
        $widthMm = $this->toUnit($widthPx * $scale);
        $heightMm = $this->toUnit($height * $scale);
        return '<img style="width:' . round($widthMm, 2) . 'mm;height:' . round($heightMm, 2) . 'mm;" src="data:image/png;base64,' . base64_encode($barcode->getPngData()) . '">';
    }

}
