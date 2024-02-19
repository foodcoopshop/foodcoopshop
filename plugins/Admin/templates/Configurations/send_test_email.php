<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Helper.showContent();" .
        Configure::read('app.jsNamespace') . ".ModalLogout.init();" .
        Configure::read('app.jsNamespace') . ".Admin.setMenuFixed();" .
        Configure::read('app.jsNamespace') . ".Helper.initMenu();" . 
        Configure::read('app.jsNamespace') . ".ColorMode.init();"
]);

print_r($success);
