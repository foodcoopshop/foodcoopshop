<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\Pdf;

use Cake\Core\Configure;
use TCPDF;

abstract class AppTcpdf extends TCPDF
{

    public $table = '';

    public $logoPath = ROOT . DS . 'webroot' . DS . 'files' . DS . 'images' . DS . 'logo-pdf.jpg';

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

        $this->SetCreator(Configure::read('appDb.FCS_APP_NAME'));
        $this->SetAuthor(Configure::read('appDb.FCS_APP_NAME'));
    }

    protected function drawLine()
    {
        $this->Line(0, $this->y, $this->w, $this->y);
    }
}
