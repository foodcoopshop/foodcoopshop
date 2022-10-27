<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (empty($products)) {
    return false;
}

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".ModalProductDelete.init();"
]);
echo '<a id="deleteSelectedProducts" class="btn btn-danger" href="javascript:void(0);"><i class="far fa-trash-alt"></i> ' . __d('admin', 'Delete_selected_products') . '</a>';
