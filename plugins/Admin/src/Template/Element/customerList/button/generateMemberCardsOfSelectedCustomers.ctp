<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace').".Admin.initGenerateMemberCardsOfSelectedCustomersButton();"
    ]);
    echo '<a id="generateMemberCardsOfSelectedCustomersButton" class="btn btn-outline-light" href="javascript:void(0);"><i class="far fa-address-card"></i> ' . __d('admin', 'Generate_member_cards') . '</a>';
}

?>