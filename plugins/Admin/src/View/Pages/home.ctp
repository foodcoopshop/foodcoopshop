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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$this->element('addScript', [
    'script' =>
        Configure::read('AppConfig.jsNamespace') . ".Helper.showContent();" .
        Configure::read('AppConfig.jsNamespace') . ".Helper.initAnystretch();" .
        Configure::read('AppConfig.jsNamespace') . ".Admin.setMenuFixed();" .
        Configure::read('AppConfig.jsNamespace') . ".Helper.initMenu();" .
        Configure::read('AppConfig.jsNamespace') . ".Helper.initScrolltopButton();" .
        Configure::read('AppConfig.jsNamespace') . ".Mobile.autoOpenSidebarLeft();" .
        Configure::read('AppConfig.jsNamespace') . ".Helper.initLogoutButton();"
]);

echo '<div id="home">';
echo $this->Session->flash();
echo '<br /><br />';
$adminName = 'Admin-Bereich';
if ($appAuth->isManufacturer()) {
    $adminName = 'Hersteller-Bereich';
}
echo '<h1>Willkommen <br />im ' . $adminName . '</h1>';
echo $this->element('acceptUpdatedTermsOfUseForm');
echo '<br />';
echo '<div class="filter-container"></div>';
echo '<img id="installation-logo" src="/files/images/logo.jpg" />';
echo '<div class="sc"></div>';
echo '</div>';
