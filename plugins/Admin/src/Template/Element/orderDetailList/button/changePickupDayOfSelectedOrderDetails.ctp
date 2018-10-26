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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if ($deposit == '' && $groupBy == '' && count($orderDetails) > 0) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace').".Admin.initChangePickupDayOfSelectedProductsButton();"
    ]);
    echo '<a id="changePickupDayOfSelectedProductsButton" class="btn btn-outline-light" href="javascript:void(0);"><i class="fa fa-calendar"></i> ' . __d('admin', 'Change_pickup_day') . '</a>';
}

?>