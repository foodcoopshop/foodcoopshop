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

$menu = [];

$adminName = __('Admin_area');
$profileSlug = $this->Slug->getCustomerProfile();
$userName = $identity->name;

if ($identity->isManufacturer()) {
    $profileSlug = $this->Slug->getManufacturerProfile();
    $adminName = __('Manufacturer_area');
}

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".ColorMode.initToggle();"
]);
$menu[] = ['slug' => 'javascript:void(0)', 'name' => '', 'options' => ['fa-icon' => 'ok fa-fw far fa-moon', 'class' => ['color-mode-toggle']]];
if ($identity->isLoggedIn()) {
    if (!$orderCustomerService->isOrderForDifferentCustomerMode()) {
        $menu[] = ['slug' => $profileSlug, 'name' =>  $userName, 'options' => ['fa-icon' => 'ok fa-fw fa-user']];
    }
    if ($orderCustomerService->isOrderForDifferentCustomerMode()) {
        $menu[] = ['slug' => 'javascript:alert(\''.__('To_change_your_profile_please_stop_the_instant_order_mode.').'\');', 'name' =>  __('Signed_in') . ': ' . $userName];
    }
}
if ($identity->isLoggedIn() && 0 && !$identity->isCustomer() && !$orderCustomerService->isOrderForDifferentCustomerMode()) {
    $menu[1]['children'][] = ['slug' => $this->Slug->getAdminHome(), 'name' => $adminName, 'options' => ['fa-icon' => 'ok fa-fw fa-gear']];
}

if (0 && $identity->isCustomer()) {
    $menu[1]['children'] = $this->Menu->getCustomerMenuElements($identity);
}

if (!$orderCustomerService->isOrderForDifferentCustomerMode()) {

    $selfServiceMenuElement = null;
    if (0 && !$identity->isManufacturer() && Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && !Configure::read('appDb.FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED')) {
        $selfServiceMenuElement = [
            'slug' => $this->Slug->getSelfService(),
            'name' => __('Self_service'),
            'options' => [
                'fa-icon' => 'ok fa-fw fa-shopping-bag',
            ],
        ];
    }

    $authMenuElement = $this->Menu->getAuthMenuElement($identity);
    if ($identity->isLoggedIn()) {
        if (!is_null($selfServiceMenuElement)) {
            $menu[1]['children'][] = $selfServiceMenuElement;
        }
        $menu[1]['children'][] = $authMenuElement;
    } else {
        $menu[] = $authMenuElement;
        if (!is_null($selfServiceMenuElement)) {
            $menu[1]['children'][] = $selfServiceMenuElement;
        }
    }

}

echo $this->Menu->render($menu, ['id' => 'user-menu', 'class' => 'horizontal menu']);
