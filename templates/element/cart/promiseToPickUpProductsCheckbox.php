<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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