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

    public $infoTextForFooter = '';

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
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
