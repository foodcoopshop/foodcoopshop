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

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

if (! $appAuth->user() || $this->request->getParam('action') == 'iframeStartPage') {
    return;
}

// used multiple times...
$paymentProductMenuElement = $this->Menu->getPaymentProductMenuElement();
$paymentMemberFeeMenuElement = $this->Menu->getPaymentMemberFeeMenuElement();
$timebasedCurrencyPaymentForCustomersMenuElement = $this->Menu->getTimebasedCurrencyPaymentForCustomersMenuElement($appAuth);

$actionLogsMenuElement = [
    'slug' => $this->Slug->getActionLogsList(),
    'name' => 'Aktivitäten',
    'options' => [
        'fa-icon' => 'fa-fw fa-eye'
    ]
];
$cancelledArticlesMenuElement = [
    'slug' => $this->Slug->getActionLogsList().'/index/?type=order_detail_cancelled',
    'name' => 'Stornierte Produkte',
    'options' => [
        'fa-icon' => 'fa-fw fa-times'
    ]
];
$ordersMenuElement = [
    'slug' => $this->Slug->getOrdersList(),
    'name' => 'Bestellungen',
    'options' => [
        'fa-icon' => 'fa-fw fa-shopping-cart'
    ]
];
$orderDetailsMenuElement = [
    'slug' => $this->Slug->getOrderDetailsList(),
    'name' => 'Bestellte Produkte',
    'options' => [
        'fa-icon' => 'fa-fw fa-shopping-cart'
    ]
];
$customerProfileMenuElement = [
    'slug' => $this->Slug->getCustomerProfile(),
    'name' => 'Meine Daten',
    'options' => [
        'fa-icon' => 'fa-fw fa-home'
    ]
];
$changePasswordMenuElement = [
    'slug' => $this->Slug->getChangePassword(),
    'name' => 'Passwort ändern',
    'options' => [
        'fa-icon' => 'fa-fw fa-key'
    ]
];
$blogPostsMenuElement = [
    'slug' => $this->Slug->getBlogPostListAdmin(),
    'name' => 'Blog-Artikel',
    'options' => [
        'fa-icon' => 'fa-fw fa-file-alt'
    ]
];
$homepageAdministrationElement = [
    'slug' => $this->Slug->getPagesListAdmin(),
    'name' => 'Homepage-Verwaltung',
    'options' => [
        'fa-icon' => 'fa-fw fa-edit'
    ]
];
$menu = [];
$logoHtml = '<img class="logo" src="/files/images/logo.jpg" width="100%" />';
$menu[] = [
    'slug' => $this->Slug->getHome(),
    'name' => $logoHtml,
    'options' => []
];
$menu[] = [
    'slug' => $this->Slug->getHome(),
    'name' => 'Home',
    'options' => [
        'fa-icon' => 'fa-fw fa-home'
    ]
];

if ($appAuth->isCustomer()) {
    $ordersMenuElement['children'] = [
        $orderDetailsMenuElement,
        $cancelledArticlesMenuElement
    ];
    $menu[] = $ordersMenuElement;
    $menu[] = $customerProfileMenuElement;
    if (! empty($paymentProductMenuElement)) {
        $menu[]= $paymentProductMenuElement;
    }
    if (! empty($paymentMemberFeeMenuElement)) {
        $menu[]= $paymentMemberFeeMenuElement;
    }
    if (! empty($timebasedCurrencyPaymentForCustomersMenuElement)) {
        $menu[]= $timebasedCurrencyPaymentForCustomersMenuElement;
    }
    $menu[] = $changePasswordMenuElement;
    $menu[] = $actionLogsMenuElement;
}

if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
    $ordersMenuElement['children'] = [
        $orderDetailsMenuElement,
        $cancelledArticlesMenuElement,
        [
            'slug' => '/admin/lists/orderLists',
            'name' => 'Bestelllisten',
            'options' => [
                'fa-icon' => 'fa-fw fa-book'
            ]
        ]
    ];
    $menu[] = $ordersMenuElement;
    $manufacturerMenu = [
        'slug' => '/admin/manufacturers',
        'name' => 'Hersteller',
        'options' => [
            'fa-icon' => 'fa-fw fa-leaf'
        ]
    ];
    $manufacturerMenu['children'][] = [
        'slug' => $this->Slug->getProductAdmin(),
        'name' => 'Produkte',
        'options' => [
            'fa-icon' => 'fa-fw fa-tags'
        ]
    ];

    if (date('Y-m-d') > Configure::read('app.depositForManufacturersStartDate')) {
        $manufacturerMenu['children'][] = [
            'slug' => $this->Slug->getDepositList(),
            'name' => 'Pfandkonto',
            'options' => [
                'fa-icon' => 'fa-fw fa-recycle'
            ]
        ];
    }

    $menu[] = $manufacturerMenu;

    $menu[] = [
        'slug' => $this->Slug->getCustomerListAdmin(),
        'name' => 'Mitglieder',
        'options' => [
            'fa-icon' => 'fa-fw fa-user'
        ]
    ];
    $menu[] = $actionLogsMenuElement;
    if (! empty($paymentProductMenuElement)) {
        $customerProfileMenuElement['children'][] = $paymentProductMenuElement;
    }
    if (! empty($paymentMemberFeeMenuElement)) {
        $customerProfileMenuElement['children'][] = $paymentMemberFeeMenuElement;
    }    if (! empty($timebasedCurrencyPaymentForCustomersMenuElement)) {
        $customerProfileMenuElement['children'][] = $timebasedCurrencyPaymentForCustomersMenuElement;
    }
    $customerProfileMenuElement['children'][] = $changePasswordMenuElement;
    $menu[] = $customerProfileMenuElement;
    $menu[] = $blogPostsMenuElement;

    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getPagesListAdmin(),
        'name' => 'Seiten',
        'options' => [
            'fa-icon' => 'fa-fw fa-edit'
        ]
    ];

    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getCategoriesList(),
        'name' => 'Kategorien',
        'options' => [
            'fa-icon' => 'fa-fw fa-list'
        ]
    ];
    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getAttributesList(),
        'name' => 'Varianten',
        'options' => [
            'fa-icon' => 'fa-fw fa-chevron-circle-right'
        ]
    ];
    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getSlidersList(),
        'name' => 'Slideshow',
        'options' => [
            'fa-icon' => 'fa-fw fa-image'
        ]
    ];

    if ($appAuth->isSuperadmin()) {
        $homepageAdministrationElement['children'][] = [
            'slug' => $this->Slug->getTaxesList(),
            'name' => 'Steuersätze',
            'options' => [
                'fa-icon' => 'fa-fw fa-percent'
            ]
        ];
        // show deposit report also for cash configuration
        $reportSlug = $this->Slug->getReport('product');
        if (!$this->Html->paymentIsCashless()) {
            $reportSlug = $this->Slug->getReport('deposit');
        }
        $homepageAdministrationElement['children'][] = [
            'slug' => $reportSlug,
            'name' => 'Finanzberichte',
            'options' => [
                'fa-icon' => 'fa-fw fa-money-bill-alt'
            ]
        ];
        $homepageAdministrationElement['children'][] = [
            'slug' => $this->Slug->getConfigurationsList(),
            'name' => 'Einstellungen',
            'options' => [
                'fa-icon' => 'fa-fw fa-cogs'
            ]
        ];
    }

    $menu[] = $homepageAdministrationElement;
}

if ($appAuth->isManufacturer()) {
    $menu[] = $orderDetailsMenuElement;
    $menu[] = [
        'slug' => $this->Slug->getProductAdmin(),
        'name' => 'Meine Produkte',
        'options' => [
            'fa-icon' => 'fa-fw fa-tags'
        ]
    ];
    $menu[] = $cancelledArticlesMenuElement;
    $profileMenu = [
        'slug' => $this->Slug->getManufacturerProfile(),
        'name' => 'Mein Profil',
        'options' => [
            'fa-icon' => 'fa-fw fa-home'
        ]
    ];
    $optionsMenu = [
        'slug' => $this->Slug->getManufacturerMyOptions(),
        'name' => 'Einstellungen',
        'options' => [
            'fa-icon' => 'fa-fw fa-cogs'
        ]
    ];
    if (date('Y-m-d') > Configure::read('app.depositForManufacturersStartDate')) {
        $od = TableRegistry::getTableLocator()->get('OrderDetails');
        $sumDepositDelivered = $od->getDepositSum($appAuth->getManufacturerId(), false);
        if ($sumDepositDelivered[0]['sumDepositDelivered'] > 0) {
            $menu[] = [
                'slug' => $this->Slug->getMyDepositList(),
                'name' => 'Pfandkonto',
                'options' => [
                    'fa-icon' => 'fa-fw fa-recycle'
                ]
            ];
        }
    }
    $timebasedCurrencyPaymentForManufacturersMenuElement = $this->Menu->getTimebasedCurrencyPaymentForManufacturersMenuElement($appAuth);
    if (! empty($timebasedCurrencyPaymentForManufacturersMenuElement)) {
        $menu[]= $timebasedCurrencyPaymentForManufacturersMenuElement;
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
if ($appAuth->isManufacturer() && !empty($appAuth->manufacturer->customer) && !empty($appAuth->manufacturer->customer->address_customer)) {
    $footerHtml = '<b>Ansprechperson</b><br />' . $appAuth->manufacturer->customer->name . ', ' . $appAuth->manufacturer->customer->email. ', ' . $appAuth->manufacturer->customer->address_customer->phone_mobile;
}

echo $this->Menu->render($menu, [
    'id' => 'menu',
    'class' => 'vertical menu',
    'footer' => $footerHtml
]);
