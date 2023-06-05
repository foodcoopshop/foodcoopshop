<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".ModalIOrderForDifferentCustomerAdd.init('#add-instant-order-button-wrapper .btn');"
]);
echo '<div id="add-instant-order-button-wrapper" class="add-button-wrapper">';
    $options = [
        'escape' => false
    ];
    $options['class'] = 'btn btn-outline-light';
    echo $this->Html->link('<i class="fas fa-shopping-cart ok"></i> '.__d('admin', 'Instant_order_for_today'), 'javascript:void(0);', $options);
    echo $this->Form->control('', [
        'type' => 'select',
        'label' => '',
        'id' => 'orderCustomerId', //null as first param and id removes attribute name => field is not submitted
        'class' => 'do-not-submit',
        'empty' => __d('admin', 'chose_member...'),
    ]);
echo '</div>';
