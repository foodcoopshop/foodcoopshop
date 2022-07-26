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
$class = ['btn btn-success'];
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

if ($appAuth->user() && !$appAuth->isOrderForDifferentCustomerMode()) {
    $menu[] = ['slug' => $this->Slug->getAdminHome(), 'name' => $adminName, 'options' => ['class' => $class]];
}
if ($appAuth->user()) {
    if (!$appAuth->isOrderForDifferentCustomerMode()) {
        $menu[] = ['slug' => $profileSlug, 'name' =>  $userName];
    }
    if ($appAuth->isOrderForDifferentCustomerMode()) {
        $menu[] = ['slug' => 'javascript:alert(\''.__('To_change_your_profile_please_stop_the_instant_order_mode.').'\');', 'name' =>  __('Signed_in') . ': ' . $userName];
    }
}

if (!$appAuth->isOrderForDifferentCustomerMode() && Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && !Configure::read('appDb.FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED')) {
    $menu[] = [
        'slug' => $this->Slug->getSelfService(),
        'name' => ' ' . __('Self_service'),
        'options' => [
            'fa-icon' => 'fa-fw fa-shopping-bag',
            'class' => ['btn btn-success']
        ]
    ];
}
if (!$appAuth->isOrderForDifferentCustomerMode()) {
    $menu[] = $this->Menu->getAuthMenuElement($appAuth);
}

echo $this->Menu->render($menu, ['id' => 'user-menu', 'class' => 'horizontal menu']);
