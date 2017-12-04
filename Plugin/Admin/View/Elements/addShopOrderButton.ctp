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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

if ($appAuth->isAdmin() || $appAuth->isSuperadmin()) {
    $this->element('addScript', array(
        'script' =>
            Configure::read('app.jsNamespace') . ".Admin.initAddOrder('#add-order-button-wrapper .btn', " . date('N', time()).");"
    ));
    echo '<div id="add-order-button-wrapper" class="add-button-wrapper">';
    $options = array(
        'escape' => false
    );
    $options['class'] = 'btn btn-default';
    echo $this->Html->link('<i class="fa fa-shopping-cart fa-lg"></i> Sofort-Bestellung', 'javascript:void(0);', $options);
    echo $this->Form->input('shopOrderCustomerId', array(
        'type' => 'select',
        'label' => '',
        'class' => 'do-not-submit',
        'empty' => 'Mitglied auswählen...',
        'options' => $customers
    ));
    echo '</div>';
}
