<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\I18n\I18n;

$pdf->SetLeftMargin(12);
$pdf->SetRightMargin(12);

$title = __('Terms_and_conditions');
$pdf->SetTitle($title);
$pdf->infoTextForFooter = $title;

$pdf->AddPage();

$html = $this->element('legal/'.I18n::getLocale().'/' . $this->Html->getLegalTextsSubfolder() . '/generalTermsAndConditions');
$pdf->writeHTML($html, true, false, true, false, '');
