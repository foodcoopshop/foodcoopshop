<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.7.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

if (!empty($products)) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace').".ModalProductDelete.init();"
    ]);
    echo '<a id="deleteSelectedProducts" class="btn btn-danger" href="javascript:void(0);"><i class="far fa-trash-alt"></i> ' . __d('admin', 'Delete_selected_products') . '</a>';
}

?>