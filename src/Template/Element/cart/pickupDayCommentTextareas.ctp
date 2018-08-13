<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
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
    
    $formattedPickupDay = $pickupDay->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
    
    if (count($pickupDay->getErrors()) > 0 || !empty($pickupDay->comment)) {
        $this->element('addScript', ['script' =>
            "$('.toggle-link-" . $formattedPickupDay . "').trigger('click');"
        ]);
    }
    
    $message =  __('Write_message_to_pick_up_team_for_{0}?',
        [$pickupDay->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'))]
    );
    echo $this->Html->link(
        '<i class="fa"></i> ' . $message,
        'javascript:void(0);',
        [
            'class' => 'toggle-link toggle-link-' . $formattedPickupDay,
            'title' => $message,
            'escape' => false
        ]
    );
    echo '<div class="toggle-content pickup-day-comment">';
    echo $this->Form->hidden('Carts.pickup_day_entities.'.$i.'.customer_id');
    echo $this->Form->control('Carts.pickup_day_entities.'.$i.'.comment', [
        'type' => 'textarea',
        'placeholder' => __('Placeholder_message_pickup_day_comment.'),
        'label' => ''
    ]);
    echo '</div>';
    $i++;
}

?>