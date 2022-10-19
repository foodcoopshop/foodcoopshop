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
namespace App\Lib\Pdf;

use Cake\Core\Configure;

abstract class CustomerInvoiceBaseTcpdf extends AppTcpdf implements CustomerInvoiceTcpdfInterface
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
        $this->setHeaders();
    }

    public function prepareTableHeader()
    {

        $this->table = '<table style="font-size:8px" cellspacing="0" cellpadding="1" border="1"><thead><tr>';

        foreach($this->headers as $header) {
            $this->table .= '<th style="font-weight:bold;background-color:#cecece" align="' . $header['align'] . '" width="' . $header['width'] . '">' . $header['name'] . '</th>';
        }
        $this->table .= '</tr></thead>';
    }

    protected function renderTableRow($values)
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

        $convertedHeaderRight = Configure::read('appDb.FCS_INVOICE_HEADER_TEXT');

        // add additional line break(s) on top
        $lineCount = substr_count($convertedHeaderRight, '<br />');
        if ($lineCount < 6) {
            $convertedHeaderRight = str_repeat('<br />', 6 - $lineCount) . $convertedHeaderRight;
        }

        $this->headerRight = $convertedHeaderRight;

        $this->MultiCell(145 - $this->lMargin, 0, $this->headerRight, 0, 'R', 0, 1, '', '', true, null, true);

        $this->SetY(36);
        $this->drawLine();
    }

}
