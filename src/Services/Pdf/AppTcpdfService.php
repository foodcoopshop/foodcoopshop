<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Pdf;

use App\Services\OutputFilter\OutputFilterService;
use Cake\Core\Configure;
use TCPDF;
use App\Controller\Component\StringComponent;
use Cake\View\Helper\TextHelper;

abstract class AppTcpdfService extends TCPDF
{

    public string $table = '';

    public bool $replaceEuroSign = true;

    public string $logoPath = ROOT . DS . 'webroot' . DS . 'files' . DS . 'images' . DS . 'logo-pdf.jpg';

    public TextHelper $textHelper;

    private ?string $html = '';

    public function setTextHelper($textHelper)
    {
        $this->textHelper = $textHelper;
    }

    public function writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
    {

        $html = StringComponent::removeEmojis($html);

        if (Configure::check('app.outputStringReplacements')) {
            $html = OutputFilterService::replace($html, Configure::read('app.outputStringReplacements'));
        }

        // in generate_order_confirmation.ctp::88 $this->MyNumber->formatAsCurrency leads to empty output
        // but in all other pdfs it works. this workaround helps
        if ($this->replaceEuroSign) {
            $html = preg_replace('/€/', '&euro;', $html);
        }
        $this->html .= $html;
        parent::writeHTML($html, $ln, $fill, $reseth, $cell, $align);
    }

    public function getHtml()
    {
        return $this->html;
    }

    public function renderTable()
    {
        $this->table .= '</table>';

        $this->writeHTML($this->table, true, false, true, false, '');

        // reset table
        $this->table = '';
    }

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {

        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        mb_internal_encoding('UTF-8');

        $this->FontFamily = 'freesans'; // freesans supports cyrillic characters

        $this->SetCreator(Configure::read('appDb.FCS_APP_NAME'));
        $this->SetAuthor(Configure::read('appDb.FCS_APP_NAME'));

    }

    protected function drawLine()
    {
        $this->Line(0, $this->y, $this->w, $this->y);
    }
}
