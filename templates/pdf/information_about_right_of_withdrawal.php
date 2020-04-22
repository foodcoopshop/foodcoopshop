<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use Cake\I18n\I18n;

$pdf->SetLeftMargin(12);
$pdf->SetRightMargin(12);

$title = __('Information_about_right_of_withdrawal');
if (isset($cart)) {
    $title .= ' ' . __('and_withdrawal_form');
}
$pdf->SetTitle($title);
$pdf->infoTextForFooter = $title;

$pdf->AddPage();

$html = $this->element('legal/'.I18n::getLocale().'/rightOfWithdrawalTerms');
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->infoTextForFooter = __('Information_about_right_of_withdrawal');

if (!empty($manufacturers)) {
    foreach ($manufacturers as $manufacturer) {
        $i = 0;

        foreach ($manufacturer as $product) {
            if ($i > 0) {
                continue;
            }

            $pdf->AddPage();

            $pdf->infoTextForFooter = __('Withdrawal_form'). ' ' . $product->manufacturer->name;

            $html = '<h1>'.__('Withdrawal_form').'</h1>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(8);

            $html = '<p>'.__('If_you_want_to_cancel_the_contract_please_fill_out_this_form_and_send_it_back.').'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(8);

            $html = $this->Html->getManufacturerImprint($product->manufacturer, 'pdf', true);
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(4);

            $html = '<p>'.__('Hereby_I_cancel_the_contract_about_the_purchase_of_following_goods_or_service.').'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);

            $html = '<p>____________________________________________________________________________________________</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);

            $html = '<p>'.__('Ordered_on').' (*): '.$cart['Cart']->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeLong')).'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');

            $html = '<p>'.__('Received_on').' (*): </p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);

            $html = '<p>'.__('Name_of_consumer(s)').': '.$appAuth->getUsername().'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');

            $customerAddress = $appAuth->user('AddressCustomers.address1');
            if ($appAuth->user('AddressCustomers.address2') != '') {
                $customerAddress .= ', '.$appAuth->user('AddressCustomers.address2');
            }
            $customerAddress .= ', '.$appAuth->user('AddressCustomers.postcode') . ' ' . $appAuth->user('AddressCustomers.city');
            $html = '<p>'.__('Address_of_consumer(s)').': '.$customerAddress.'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);

            $html = '<p>'.__('Signature_of_consumer_only_if_transmitted_on_paper.').'</p><br /><br />';
            $html .= '<p>___________________________________________________________</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln(3);

            $html = '<p>'.__('Date').': ______________________</p>';
            $pdf->Ln(8);

            $html .= '<p>(*) '.__('Strike_out_unnecessary_items.').'</p>';
            $pdf->writeHTML($html, true, false, true, false, '');

            $i++;
        }
    }
}
