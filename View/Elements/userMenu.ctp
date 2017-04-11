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

$menu = array();

$adminSlug = '/admin';
$adminName = 'Admin-Bereich';
$profileSlug = $this->Slug->getCustomerProfile();
$class = array('btn btn-success');
$userName = $appAuth->user('firstname') . ' ' . $appAuth->user('lastname');
if ($appAuth->isManufacturer()) {
    $profileSlug = $this->Slug->getManufacturerProfile();
    $adminName = 'Hersteller-Bereich';
    $userName = $appAuth->getManufacturerName();
}
if ($appAuth->loggedIn()) {
    $menu[] = array('slug' => $adminSlug, 'name' => $adminName, 'options' => array('class' => $class));
    $menu[] = array('slug' => $profileSlug, 'name' =>  $userName);
}
$menu[] = $this->Menu->getAuthMenuElement($appAuth);
echo $this->Menu->render($menu, array('id' => 'user-menu', 'class' => 'horizontal menu'));
