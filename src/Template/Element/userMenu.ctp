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

$menu = [];

$adminName = __('Admin_area');
$profileSlug = $this->Slug->getCustomerProfile();
$class = ['btn btn-success'];
$userName = $appAuth->user('firstname') . ' ' . $appAuth->user('lastname');
if ($appAuth->isManufacturer()) {
    $profileSlug = $this->Slug->getManufacturerProfile();
    $adminName = __('Manufacturer_area');
    $userName = $appAuth->getManufacturerName();
}
if ($appAuth->user()) {
    if (!$this->request->getSession()->check('Auth.shopOrderCustomer')) {
        $menu[] = ['slug' => $this->Slug->getAdminHome(), 'name' => $adminName, 'options' => ['class' => $class]];
        $menu[] = ['slug' => $profileSlug, 'name' =>  $userName];
    } else {
        $menu[] = ['slug' => 'javascript:alert(\'Um dein Profil zu ändern, beende bitte den Sofort-Bestellungsmodus.\');', 'name' =>  'Eingeloggt: ' . $userName];
    }
}
if (!$this->request->getSession()->check('Auth.shopOrderCustomer')) {
    $menu[] = $this->Menu->getAuthMenuElement($appAuth);
}
echo $this->Menu->render($menu, ['id' => 'user-menu', 'class' => 'horizontal menu']);
