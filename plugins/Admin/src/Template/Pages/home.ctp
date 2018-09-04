<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Helper.showContent();" .
        Configure::read('app.jsNamespace') . ".Helper.initAnystretch();" .
        Configure::read('app.jsNamespace') . ".Admin.setMenuFixed();" .
        Configure::read('app.jsNamespace') . ".Helper.initMenu();" .
        Configure::read('app.jsNamespace') . ".Helper.initScrolltopButton();" .
        Configure::read('app.jsNamespace') . ".Mobile.autoOpenSidebarLeft();" .
        Configure::read('app.jsNamespace') . ".Helper.initLogoutButton();"
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
echo '<img id="installation-logo" src="/files/images/logo.jpg" />';
echo '<div class="sc"></div>';
echo '</div>';
