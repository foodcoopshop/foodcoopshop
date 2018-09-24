<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!Configure::read('app.promiseToPickUpProductsCheckboxEnabled')) {
    return false;
}

echo $this->Form->control('Carts.promise_to_pickup_products', [
    'label' => __('I_promise_to_pick_up_the_products_on_the_selected_pickup_day_and_to_pay_in_cash_in_the_shop.'),
    'type' => 'checkbox',
    'escape' => false
]);
?>