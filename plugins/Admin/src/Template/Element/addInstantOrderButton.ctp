<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if ($appAuth->isAdmin() || $appAuth->isSuperadmin() || $appAuth->isCustomer()) {
    $this->element('addScript', [
        'script' =>
            Configure::read('app.jsNamespace') . ".Admin.initAddInstantOrder('#add-instant-order-button-wrapper .btn');"
    ]);
    echo '<div id="add-instant-order-button-wrapper" class="add-button-wrapper">';
    $options = [
        'escape' => false
    ];
    $options['class'] = 'btn btn-outline-light';
    echo $this->Html->link('<i class="fas fa-shopping-cart ok"></i> '.__d('admin', 'Instant_order_for_today'), 'javascript:void(0);', $options);
    echo $this->Form->control(null, [
        'type' => 'select',
        'label' => '',
        'id' => 'instantOrderCustomerId', //null as first param and id removes attribute name => field is not submitted
        'class' => 'do-not-submit',
        'empty' => __d('admin', 'chose_member...'),
        'options' => $customers
    ]);
    echo '</div>';
}
