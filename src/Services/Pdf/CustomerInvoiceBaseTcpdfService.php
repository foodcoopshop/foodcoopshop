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
use App\Services\Pdf\Traits\FooterTrait;
use App\Services\Pdf\Traits\TaxSumTableTrait;

abstract class CustomerInvoiceBaseTcpdfService extends AppTcpdfService
{

    use FooterTrait;
    use TaxSumTableTrait;

    public bool $replaceEuroSign = false;

    /**
     * @var array<int, array<string, int|string>>
     */
    public array $headers = [];

    public function __construct(string $orientation = 'P')
    {
        parent::__construct($orientation);
        $this->setTopMargin(48);
        $this->setRightMargin(0);
        $this->setLeftMargin(18);
        $this->setFontSize(10);
        $this->setHeaders();
    }

    abstract public function setHeaders(): void;

    public function prepareTableHeader(): void
    {

        $this->table = '<table width="' . array_sum(array_column($this->headers, 'width')) . '" style="font-size:8px;border-collapse:collapse;" cellspacing="0" cellpadding="1"><thead><tr>';

        foreach($this->headers as $header) {
            $this->table .= '<th' . $this->getThinTableCellStyleAttribute('font-weight:bold;background-color:#cecece') . ' align="' . $header['align'] . '" width="' . $header['width'] . '">' . $header['name'] . '</th>';
        }
        $this->table .= '</tr></thead>';
    }

    /**
     * @param array<int, string> $values
     */
    protected function renderTableRow(array $values): void
    {
        $i = 0;
        foreach($values as $value) {
            $this->table .= '<td' . $this->getThinTableCellStyleAttribute() . ' align="' . $this->headers[$i]['align'] . '" width="' . $this->headers[$i]['width'] . '">' . $value . '</td>';
            $i++;
        }
    }

    protected function getPageHeader(int $pid): string
    {
        $leftMargin = $this->pageLeftMargins[$pid] ?? $this->leftMargin;
        $out = $this->getLogo($leftMargin, 4, 50);
        $headerRight = Configure::read('appDb.FCS_INVOICE_HEADER_TEXT');
        $lineCount = substr_count($headerRight, '<br />');
        if ($lineCount < 6) {
            $headerRight = str_repeat('<br />', 6 - $lineCount) . $headerRight;
        }
        $out .= $this->getHeaderRightText($headerRight, 194);
        $out .= $this->drawLine(36);
        return $out;
    }

}
