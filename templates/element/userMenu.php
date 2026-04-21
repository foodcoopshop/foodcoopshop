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
use App\Services\OrderCustomerService;

$menu = [];

$adminName = __('Admin_area');
$profileSlug = $this->Slug->getCustomerProfile();
$userName = $identity->name ?? '';

if ($identity !== null && $identity->isManufacturer()) {
    $profileSlug = $this->Slug->getManufacturerProfile();
    $adminName = __('Manufacturer_area');
}

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".ColorMode.initToggle();"
]);

if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $identity !== null) {
    $menu[] = [
        'slug' => '',
        'name' => '',
        'options' => [
            'class' => ['user-menu-search'],
            'content' => $this->element('productSearch', [
                'action' => __('route_search'),
                'placeholder' => __('Search'),
                'resetSearchUrl' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $this->Slug->getAllProducts(),
                'includeCategoriesDropdown' => false,
                'placement' => 'user-menu',
            ]),
        ],
    ];
}

$menu[] = ['slug' => 'javascript:void(0)', 'name' => '', 'options' => ['fa-icon' => 'ok fa-fw fas fa-moon', 'class' => ['color-mode-toggle']]];
$infoBoxContent = $this->element('globalNoDeliveryDayBox') . $this->element('infoBox');
if (!empty($infoBoxContent)) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".ModalText.init('#user-menu a.modal-link-info-box');"
    ]);
    $menu[] = ['slug' => 'javascript:void(0)', 'name' => 'Infos', 'options' => ['fa-icon' => 'ok fa-fw fas fa-info-circle', 'class' => ['modal-link-info-box'], 'data-element-selector' => '#modal-info-box-wrapper']];
}

if ($identity !== null && !$identity->isManufacturer()) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace').".ModalText.init('#user-menu a.modal-link-cart');"
    ]);
    $menu[] = ['slug' => 'javascript:void(0)', 'name' => 'Warenkorb', 'options' => ['fa-icon' => 'ok fa-fw fa fa-shopping-cart', 'class' => ['modal-link-cart'], 'data-element-selector' => '#modal-cart-wrapper']];
}

$loginMenuIndex = count($menu);
if ($identity !== null) {
    if (!OrderCustomerService::isOrderForDifferentCustomerMode()) {
        $menu[] = ['slug' => $profileSlug, 'name' =>  $userName, 'options' => ['fa-icon' => 'ok fa-fw fa-user']];
    } else {
        $menu[] = ['slug' => 'javascript:alert(\''.__('To_change_your_profile_please_stop_the_instant_order_mode.').'\');', 'name' =>  __('Signed_in') . ': ' . $this->request->getSession()->read('OriginalIdentity')->name];
    }
}

if ($identity !== null && !OrderCustomerService::isOrderForDifferentCustomerMode()) {
    $menu[$loginMenuIndex]['children'][] = ['slug' => $this->Slug->getAdminHome(), 'name' => $adminName, 'options' => ['fa-icon' => 'ok fa-fw fa-gear']];
    if ($identity->isCustomer()) {
        $menu[$loginMenuIndex]['children'] = array_merge($menu[$loginMenuIndex]['children'], $this->Menu->getCustomerMenuElements($identity));
    }
}

if (!OrderCustomerService::isOrderForDifferentCustomerMode()) {

    $selfServiceMenuElement = null;
    if (($identity === null || !$identity->isManufacturer()) && Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && !Configure::read('appDb.FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED')) {
        $selfServiceMenuElement = [
            'slug' => $this->Slug->getSelfService(),
            'name' => __('Self_service'),
            'options' => [
                'fa-icon' => 'ok fa-fw fa-shopping-bag',
            ],
        ];
    }

    $authMenuElement = $this->Menu->getAuthMenuElement($identity);
    if ($identity !== null) {
        if (!is_null($selfServiceMenuElement)) {
            $menu[$loginMenuIndex]['children'][] = $selfServiceMenuElement;
        }
        $menu[$loginMenuIndex]['children'][] = $authMenuElement;
    } else {
        $loginMenuIndex = count($menu);
        $menu[] = $authMenuElement;
        if (!is_null($selfServiceMenuElement)) {
            $menu[$loginMenuIndex]['children'][] = $selfServiceMenuElement;
        }
    }

}


echo $this->Menu->render($menu, ['id' => 'user-menu', 'class' => 'horizontal menu']);
