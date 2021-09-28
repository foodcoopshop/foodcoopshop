<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".ModalSelfServiceOrderAdd.init('#add-self-service-order-button-wrapper .btn');"
]);
echo '<div id="add-self-service-order-button-wrapper" class="add-button-wrapper '.(isset($additionalClass) ? $additionalClass : '') . '">';
    $options = [
        'escape' => false
    ];
    $options['class'] = 'btn btn-outline-light';
    echo $this->Html->link('<i class="fas fa-shopping-bag ok"></i> '.__d('admin', 'Self_service_order'), 'javascript:void(0);', $options);
    echo $this->Form->control('', [
        'type' => 'select',
        'label' => '',
        'id' => 'selfServiceOrderCustomerId', //null as first param and id removes attribute name => field is not submitted
        'class' => 'do-not-submit',
        'empty' => __d('admin', 'chose_member...'),
    ]);
echo '</div>';
