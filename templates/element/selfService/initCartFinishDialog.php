<?php
declare(strict_types=1);

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if (!Configure::read('app.selfServiceEasyModeEnabled')) {
    $this->element('addScript', ['script' => Configure::read('app.jsNamespace').".Cart.initCartFinish();"]);
    return;
}

$title = '';
$html = '';
$dialogButtons = [];
$selfServicePaymentTypes = Configure::read('app.selfServicePaymentTypes');

if (empty($selfServicePaymentTypes)) {
    $title = __('Confirm_self_service_purchase_dialog') .'?';
    $html = '<p>' . __('Confirm_self_service_purchase') . '</p>';
    $dialogButtons[] = [
        'classes' => 'btn-success',
        'title' => __('Confirm_self_service_purchase_button'),
        'faIcon' => 'fa-fw fas fa-check',
        'isCloseButton' => null
    ];
} else {
    $title = __('Choose_paymenttype_for_self_service_purchase_dialog');
    $html = '<p>' . __('Confirm_self_service_purchase_with_paymenttypes') . '</p>';
    foreach($selfServicePaymentTypes as $selfServicePaymentType) {
        $dialogButtons[] = [
            'classes' => 'btn-success no-auto-bind',
            'title' => $selfServicePaymentType['payment_type'],
            'faIcon' => 'fa-fw fas fa-check',
            'isCloseButton' => true,
            'value' => $selfServicePaymentType['payment_text']
        ];
    }
}

$dialogButtons[] = [
    'classes' => 'btn-outline-light',
    'title' => __('Deny_self_service_purchase_button'),
    'faIcon' => null,
    'isCloseButton' => true
];

$this->element('addScript', ['script' => Configure::read('app.jsNamespace').".ModalSelfServiceConfirmDialog.init('$title', '$html', '".json_encode($dialogButtons)."');" ]);
