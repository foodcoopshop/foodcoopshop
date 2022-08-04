<?php
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

$menu = [];

$adminName = __('Admin_area');
$profileSlug = $this->Slug->getCustomerProfile();
$userName = $appAuth->user('firstname') . ' ' . $appAuth->user('lastname');
if (Configure::read('app.customerMainNamePart') == 'lastname') {
    $userName = $appAuth->user('lastname') . ' ' . $appAuth->user('firstname');
}
if ($appAuth->user('is_company')) {
    $userName = $appAuth->user('firstname');
}
if ($appAuth->isManufacturer()) {
    $profileSlug = $this->Slug->getManufacturerProfile();
    $adminName = __('Manufacturer_area');
    $userName = $appAuth->getManufacturerName();
}

if ($appAuth->user()) {
    if (!$appAuth->isOrderForDifferentCustomerMode()) {
        $menu[] = ['slug' => $profileSlug, 'name' =>  $userName, 'options' => ['fa-icon' => 'ok fa-fw fa-user']];
    }
    if ($appAuth->isOrderForDifferentCustomerMode()) {
        $menu[] = ['slug' => 'javascript:alert(\''.__('To_change_your_profile_please_stop_the_instant_order_mode.').'\');', 'name' =>  __('Signed_in') . ': ' . $userName];
    }
}

if ($appAuth->user() && !$appAuth->isCustomer() && !$appAuth->isOrderForDifferentCustomerMode()) {
    $menu[0]['children'][] = ['slug' => $this->Slug->getAdminHome(), 'name' => $adminName, 'options' => ['fa-icon' => 'ok fa-fw fa-gear']];
}

if ($appAuth->isCustomer()) {
    $menu[0]['children'] = $this->Menu->getCustomerMenuElements($appAuth);
}

if (!$appAuth->isOrderForDifferentCustomerMode()) {

    $selfServiceMenuElement = null;
    if (!$appAuth->isManufacturer() && Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && !Configure::read('appDb.FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED')) {
        $selfServiceMenuElement = [
            'slug' => $this->Slug->getSelfService(),
            'name' => __('Self_service'),
            'options' => [
                'fa-icon' => 'ok fa-fw fa-shopping-bag',
            ],
        ];
    }

    $authMenuElement = $this->Menu->getAuthMenuElement($appAuth);
    if ($appAuth->user()) {
        if (!is_null($selfServiceMenuElement)) {
            $menu[0]['children'][] = $selfServiceMenuElement;
        }
        $menu[0]['children'][] = $authMenuElement;
    } else {
        $menu[] = $authMenuElement;
        if (!is_null($selfServiceMenuElement)) {
            $menu[0]['children'][] = $selfServiceMenuElement;
        }
    }

}


echo $this->Menu->render($menu, ['id' => 'user-menu', 'class' => 'horizontal menu']);
