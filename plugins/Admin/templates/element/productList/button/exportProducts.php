<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (!($identity->isSuperadmin() || $identity->isAdmin())) {
    return false;
}

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.initExportProductsButton();"
]);
echo '<a id="exportProductsButton" class="dropdown-item" href="javascript:void(0);"><i class="fa-fw fas fa-file-export ok"></i> ' . __d('admin', 'Export_{0}', [__d('admin', 'Stock_products')]) . '</a>';
