<?php
declare(strict_types=1);

namespace App\Services\Pdf\Traits;

use Cake\Core\Configure;
use Cake\I18n\DateTime;

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

    protected function getPageFooter(int $pid): string
    {
        $page = $this->page->getPage($pid);
        $posy = $page['height'] - 19;
        $out = $this->drawLine($posy);
        $footerText = $this->infoTextForFooter;
        $out .= $this->withFont(10, function () use ($page, $posy, $pid, $footerText): string {
            $now = new DateTime();
            $generatedOn = __('Generated_on_{0}', [
                $now->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLongWithSecs')),
            ]);
            $textRight = $generatedOn . ', ' . __('Page_{0}_of_{1}', [$pid + 1, '']);
            // the former tcpdf library aligned the text using the width of its page number aliases
            $aliasText = $generatedOn . ', ' . __('Page_{0}_of_{1}', ['{{:pnp:}}', '{{:ptp:}}']);
            $textX = $page['width'] - $this->rightMargin - $this->getStringWidth($aliasText);
            $totalX = $textX + $this->getStringWidth($textRight);
            $width = $page['width'] - $this->rightMargin;
            $out = $this->getTcpdfCellText($footerText, $this->leftMargin, $posy, 10, 'L', $width - $this->leftMargin);
            $out .= $this->getTcpdfCellText($textRight, $textX, $posy + 4, 10, 'L', $width - $textX);
            $out .= $this->withFont(10, function () use ($totalX, $posy, $width): string {
                return $this->getTcpdfCellText(\Com\Tecnick\Pdf\Page\Page::PAGE_TOT, $totalX, $posy + 4, 10, 'L', $width - $totalX);
            }, 'freesanslatin');
            return $out;
        });
        return $out;
    }

}
