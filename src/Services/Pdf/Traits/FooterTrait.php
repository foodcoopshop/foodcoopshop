<?php
declare(strict_types=1);

namespace App\Services\Pdf\Traits;

use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\I18n\I18n;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait FooterTrait
{

    /**
     * parent class is overriden although it's name is Header() (capital letter)
     * php functions are case insensitive
     */
    public function footer()
    {
        $this->SetY(-19);
        $this->drawLine();
        $this->SetFontSize(10);
        if (I18n::getLocale() == 'ru_RU') {
            $this->SetFont('freesans');
        }
        $this->Cell(0, 10, $this->infoTextForFooter , 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Ln(4);
        $now = new DateTime();
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
