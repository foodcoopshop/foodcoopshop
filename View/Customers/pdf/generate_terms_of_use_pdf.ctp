<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

App::uses('FCS_TCPDF', 'Lib/Pdf');
$pdf = new FCS_TCPDF();
$pdf->SetLeftMargin(12);
$pdf->SetRightMargin(12);

$title = 'Nutzungsbedingungen';
$pdf->SetTitle($title);
$pdf->infoTextForFooter = $title;

$pdf->AddPage();

$html = $this->element('legal/termsOfUse');
$pdf->writeHTML($html, true, false, true, false, '');

echo $pdf->Output(StringComponent::createRandomString().'.pdf', $saveParam);
