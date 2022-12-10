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
        Configure::read('app.jsNamespace') . ".Helper.initColorMode();" .
        Configure::read('app.jsNamespace') . ".Helper.showContent();" .
        Configure::read('app.jsNamespace') . ".Helper.initAnystretch();" .
        Configure::read('app.jsNamespace') . ".Admin.setMenuFixed();" .
        Configure::read('app.jsNamespace') . ".Helper.initMenu();" .
        Configure::read('app.jsNamespace') . ".Helper.initScrolltopButton();" .
        Configure::read('app.jsNamespace') . ".Mobile.autoOpenSidebarLeft();" .
        Configure::read('app.jsNamespace') . ".ModalLogout.init();"
]);

echo '<div id="home">';
echo $this->Flash->render();
echo $this->Flash->render('auth');
echo '<br /><br />';
$adminNameGreeting = __d('admin', 'to_the_admin_area');
if ($appAuth->isManufacturer()) {
    $adminNameGreeting = __d('admin', 'to_the_manufacturer_area');
}
echo '<h1>'.__d('admin', 'Welcome') . '<br />' . $adminNameGreeting . '</h1>';
echo $this->element('acceptUpdatedTermsOfUseForm');
echo '<br />';
echo '<div class="filter-container"></div>';
echo '<img id="installation-logo" src="/files/images/' . Configure::read('app.logoFileName') . '?' . filemtime(WWW_ROOT.'files'.DS.'images'.DS.Configure::read('app.logoFileName')) . '" />';
echo '<div class="sc"></div>';
echo '</div>';
