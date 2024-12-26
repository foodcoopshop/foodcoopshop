<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */


use Cake\Core\Configure;

if (!Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
    return false;
}

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace') . ".Helper.bindToggleLinks();"
]);

$i = 0;
foreach($cart->pickup_day_entities as $pickupDay) {

    // pickup_day is empty when set to delivery-rhythm-triggered-delivery-break
    if (empty($pickupDay->pickup_day)) {
        continue;
    }

    $formattedPickupDay = $pickupDay->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));

    if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY') || 
        /** @phpstan-ignore-next-line */
        count($pickupDay->getErrors()) > 0 || 
        !empty($pickupDay->comment)) {
        $this->element('addScript', ['script' =>
            "$('.toggle-link-" . $formattedPickupDay . "').trigger('click');"
        ]);
    }

    if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        $message = __('Write_message_to_pick_up_team_for_{0}?',
            [
                $pickupDay->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')),
            ],
        );
    } else {
        $message = __('Write_message_to_{0}_for_{1}?',
            [
                Configure::read('appDb.FCS_APP_NAME'),
                $pickupDay->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')),
            ],
        );
    }

    if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
        $message = __('Write_message_to_your_order.');
    }
    echo $this->Html->link(
        '<i class="fa"></i> ' . $message,
        'javascript:void(0);',
        [
            'class' => 'toggle-link toggle-link-' . $formattedPickupDay,
            'title' => $message,
            'escape' => false,
        ]
    );
    echo '<div class="toggle-content pickup-day-comment">';
    echo $this->Form->hidden('Carts.pickup_day_entities.'.$i.'.customer_id');
    echo $this->Form->hidden('Carts.pickup_day_entities.'.$i.'.pickup_day', ['value' => $formattedPickupDay]);

    if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        $placeholderMessage = __('Placeholder_message_pickup_day_comment.');
    } else {
        $placeholderMessage = '';
    }
    if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
        $placeholderMessage = '';
    }
    echo $this->Form->control('Carts.pickup_day_entities.'.$i.'.comment', [
        'type' => 'textarea',
        'placeholder' => $placeholderMessage,
        'label' => ''
    ]);
    echo '</div>';
    $i++;
}

?>