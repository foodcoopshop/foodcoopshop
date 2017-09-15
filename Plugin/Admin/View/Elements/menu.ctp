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

if (! $appAuth->loggedIn() || $this->action == 'iframeStartPage') {
    return;
}
?>

<?php

// used multiple times...
$paymentProductMenuElement = $this->Menu->getPaymentProductMenuElement();
$paymentMemberFeeMenuElement = $this->Menu->getPaymentMemberFeeMenuElement();
$actionLogsMenuElement = array(
    'slug' => '/admin/action_logs',
    'name' => 'Aktivitäten',
    'options' => array(
        'fa-icon' => 'fa-fw fa-eye'
    )
);
$cancelledArticlesMenuElement = array(
    'slug' => '/admin/action_logs/index/type:order_detail_cancelled',
    'name' => 'Stornierte Produkte',
    'options' => array(
        'fa-icon' => 'fa-fw fa-remove'
    )
);
$ordersMenuElement = array(
    'slug' => '/admin/orders',
    'name' => 'Bestellungen',
    'options' => array(
        'fa-icon' => 'fa-fw fa-shopping-cart'
    )
);
$orderDetailsMenuElement = array(
    'slug' => '/admin/order_details',
    'name' => 'Bestellte Produkte',
    'options' => array(
        'fa-icon' => 'fa-fw fa-shopping-cart'
    )
);
$customerProfileMenuElement = array(
    'slug' => $this->Slug->getCustomerProfile(),
    'name' => 'Meine Daten',
    'options' => array(
        'fa-icon' => 'fa-fw fa-home'
    )
);
$changePasswordMenuElement = array(
    'slug' => $this->Slug->getChangePassword(),
    'name' => 'Passwort ändern',
    'options' => array(
        'fa-icon' => 'fa-fw fa-key'
    )
);
$blogPostsMenuElement = array(
    'slug' => $this->Slug->getBlogPostListAdmin(),
    'name' => 'Blog-Artikel',
    'options' => array(
        'fa-icon' => 'fa-fw fa-file-text'
    )
);
$homepageAdministrationElement = array(
    'slug' => $this->Slug->getPagesListAdmin(),
    'name' => 'Homepage-Verwaltung',
    'options' => array(
        'fa-icon' => 'fa-fw fa-pencil-square-o'
    )
);

$menu = array();
$logoHtml = '<img class="logo" src="/files/images/logo.jpg" width="100%" />';
$menu[] = array(
    'slug' => $this->Slug->getHome(),
    'name' => $logoHtml,
    'options' => array()
);
$menu[] = array(
    'slug' => $this->Slug->getHome(),
    'name' => 'Home',
    'options' => array(
        'fa-icon' => 'fa-fw fa-home'
    )
);

if ($appAuth->isCustomer()) {
    $ordersMenuElement['children'] = array(
        $orderDetailsMenuElement,
        $cancelledArticlesMenuElement
    );
    $menu[] = $ordersMenuElement;
    $menu[] = $customerProfileMenuElement;
    if (! empty($paymentProductMenuElement)) {
        $menu[]= $paymentProductMenuElement;
    }
    if (! empty($paymentMemberFeeMenuElement)) {
        $menu[]= $paymentMemberFeeMenuElement;
    }
    $menu[] = $changePasswordMenuElement;
    $menu[] = $actionLogsMenuElement;
}

if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
    $ordersMenuElement['children'] = array(
        $orderDetailsMenuElement,
        $cancelledArticlesMenuElement,
        array(
            'slug' => '/admin/lists/orderLists',
            'name' => 'Bestelllisten',
            'options' => array(
                'fa-icon' => 'fa-fw fa-book'
            )
        )
    );
    $menu[] = $ordersMenuElement;
    $manufacturerMenu = array(
        'slug' => '/admin/manufacturers',
        'name' => 'Hersteller',
        'options' => array(
            'fa-icon' => 'fa-fw fa-leaf'
        )
    );
    $manufacturerMenu['children'][] = array(
        'slug' => $this->Slug->getProductAdmin(),
        'name' => 'Produkte',
        'options' => array(
            'fa-icon' => 'fa-fw fa-tags'
        )
    );

    if (date('Y-m-d') > Configure::read('app.depositForManufacturersStartDate')) {
        $manufacturerMenu['children'][] = array(
            'slug' => $this->Slug->getDepositList(),
            'name' => 'Pfandkonto',
            'options' => array(
                'fa-icon' => 'fa-fw fa-recycle'
            )
        );
    }

    $menu[] = $manufacturerMenu;

    $menu[] = array(
        'slug' => $this->Slug->getCustomerListAdmin(),
        'name' => 'Mitglieder',
        'options' => array(
            'fa-icon' => 'fa-fw fa-male'
        )
    );
    $menu[] = $actionLogsMenuElement;
    if (! empty($paymentProductMenuElement)) {
        $customerProfileMenuElement['children'][] = $paymentProductMenuElement;
    }
    if (! empty($paymentMemberFeeMenuElement)) {
        $customerProfileMenuElement['children'][] = $paymentMemberFeeMenuElement;
    }
    $customerProfileMenuElement['children'][] = $changePasswordMenuElement;
    $menu[] = $customerProfileMenuElement;
    $menu[] = $blogPostsMenuElement;

    $homepageAdministrationElement['children'][] = array(
        'slug' => $this->Slug->getCategoriesList(),
        'name' => 'Kategorien',
        'options' => array(
            'fa-icon' => 'fa-fw fa-list'
        )
    );
    $homepageAdministrationElement['children'][] = array(
        'slug' => $this->Slug->getAttributesList(),
        'name' => 'Varianten',
        'options' => array(
            'fa-icon' => 'fa-fw fa-chevron-circle-right'
        )
    );
    $homepageAdministrationElement['children'][] = array(
        'slug' => $this->Slug->getSlidersList(),
        'name' => 'Slideshow',
        'options' => array(
            'fa-icon' => 'fa-fw fa-image'
        )
    );

    if ($appAuth->isSuperadmin()) {
        $homepageAdministrationElement['children'][] = array(
            'slug' => $this->Slug->getTaxesList(),
            'name' => 'Steuersätze',
            'options' => array(
                'fa-icon' => 'fa-fw fa-percent'
            )
        );
        // show deposit report also for cash configuration
        $reportSlug = $this->Slug->getReport('product');
        if (!$this->Html->paymentIsCashless()) {
            $reportSlug = $this->Slug->getReport('deposit');
        }
        $homepageAdministrationElement['children'][] = array(
            'slug' => $reportSlug,
            'name' => 'Finanzberichte',
            'options' => array(
                'fa-icon' => 'fa-fw fa-money'
            )
        );
        $homepageAdministrationElement['children'][] = array(
            'slug' => $this->Slug->getConfigurationsList(),
            'name' => 'Einstellungen',
            'options' => array(
                'fa-icon' => 'fa-fw fa-cogs'
            )
        );
    }

    $menu[] = $homepageAdministrationElement;
}

if ($appAuth->isManufacturer()) {
    $menu[] = $orderDetailsMenuElement;
    $menu[] = array(
        'slug' => $this->Slug->getProductAdmin(),
        'name' => 'Meine Produkte',
        'options' => array(
            'fa-icon' => 'fa-fw fa-tags'
        )
    );
    $menu[] = $cancelledArticlesMenuElement;
    $profileMenu = array(
        'slug' => $this->Slug->getManufacturerProfile(),
        'name' => 'Mein Profil',
        'options' => array(
            'fa-icon' => 'fa-fw fa-home'
        )
    );
    $optionsMenu = array(
        'slug' => $this->Slug->getManufacturerMyOptions(),
        'name' => 'Einstellungen',
        'options' => array(
            'fa-icon' => 'fa-fw fa-cogs'
        )
    );
    if (date('Y-m-d') > Configure::read('app.depositForManufacturersStartDate')) {
        $od = ClassRegistry::init('OrderDetail');
        $sumDepositDelivered = $od->getDepositSum($appAuth->getManufacturerId(), false);
        if ($sumDepositDelivered[0][0]['sumDepositDelivered'] > 0) {
            $menu[] = array(
                'slug' => $this->Slug->getMyDepositList(),
                'name' => 'Pfandkonto',
                'options' => array(
                    'fa-icon' => 'fa-fw fa-recycle'
                )
            );
        }
    }
    $profileMenu['children'][] = $changePasswordMenuElement;
    $menu[] = $profileMenu;
    $menu[] = $optionsMenu;
    $menu[] = $blogPostsMenuElement;
    $menu[] = $actionLogsMenuElement;
}

// for all users
$menu[] = $this->Menu->getAuthMenuElement($appAuth);

$footerHtml = '';
if ($appAuth->isManufacturer() && !empty($appAuth->manufacturer['Customer']) && !empty($appAuth->manufacturer['Customer']['AddressCustomer'])) {
    $footerHtml = '<b>Ansprechperson</b><br />' . $appAuth->manufacturer['Customer']['firstname'] . ' ' . $appAuth->manufacturer['Customer']['lastname'] . ', ' . $appAuth->manufacturer['Customer']['email']. ', ' . $appAuth->manufacturer['Customer']['AddressCustomer']['phone_mobile'];
}

echo $this->Menu->render($menu, array(
    'id' => 'menu',
    'class' => 'vertical menu',
    'footer' => $footerHtml
));



