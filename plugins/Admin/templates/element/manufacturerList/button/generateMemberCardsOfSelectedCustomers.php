<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace').".Admin.initGenerateMemberCardsOfSelectedCustomersButton();"
    ]);
    echo '<a id="generateMemberCardsOfSelectedCustomersButton" class="dropdown-item" href="javascript:void(0);"><i class="far fa-address-card"></i> ' . __d('admin', 'Generate_member_cards') . '</a>';
}

?>