<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
    'name' => __d('admin', 'Activities'),
    'options' => [
        'fa-icon' => 'fa-fw fa-eye'
    ]
];
$changedOrderedProductsMenuElement = [
    'slug' => $this->Slug->getActionLogsList().'/index/?types[]=order_detail_cancelled&types[]=order_detail_product_price_changed&types[]=order_detail_product_quantity_changed&types[]=order_detail_product_amount_changed&types[]=order_detail_customer_changed',
    'name' => __d('admin', 'Order_adaptions'),
    'options' => [
        'fa-icon' => 'fa-fw fa-times'
    ]
];
if ($this->Html->paymentIsCashless() && Configure::read('app.isDepositPaymentCashless')) {
    $paymentDepositCustomerAddedMenuElement = [
        'slug' => $this->Slug->getActionLogsList().'/index/?types[]=payment_deposit_customer_added',
        'name' => __d('admin', 'Deposit_returns'),
        'options' => [
            'fa-icon' => $this->Html->getFontAwesomeIconForCurrencyName(Configure::read('app.currencyName'))
        ]
    ];
}
$orderListsMenuElement = [
    'slug' => $this->Slug->getOrderLists(),
    'name' => __d('admin', 'Order_lists'),
    'options' => [
        'fa-icon' => 'fa-fw fa-book'
    ]
];
$orderDetailsGroupedByCustomerMenuElement = [
    'slug' => $this->Slug->getOrderDetailsList().'?groupBy=customer',
    'name' => __d('admin', 'Orders'),
    'options' => [
        'fa-icon' => 'fa-fw fa-shopping-cart'
    ]
];
$customerProfileMenuElement = [
    'slug' => $this->Slug->getCustomerProfile(),
    'name' => __d('admin', 'My_data'),
    'options' => [
        'fa-icon' => 'fa-fw fa-home'
    ]
];
$changePasswordMenuElement = [
    'slug' => $this->Slug->getChangePassword(),
    'name' => __d('admin', 'Change_password'),
    'options' => [
        'fa-icon' => 'fa-fw fa-key'
    ]
];
$blogPostsMenuElement = [
    'slug' => $this->Slug->getBlogPostListAdmin(),
    'name' => __d('admin', 'Blog_posts'),
    'options' => [
        'fa-icon' => 'fa-fw fa-file-alt'
    ]
];
$homepageAdministrationElement = [
    'slug' => $this->Slug->getPagesListAdmin(),
    'name' => __d('admin', 'Website_administration'),
    'options' => [
        'fa-icon' => 'fa-fw fa-pencil-alt'
    ]
];
$menu = [];
$logoHtml = '<img class="logo" src="/files/images/logo.jpg?' . filemtime(WWW_ROOT.'files'.DS.'images'.DS.'logo.jpg') . '" width="100%" />';
$menu[] = [
    'slug' => $this->Slug->getHome(),
    'name' => $logoHtml,
    'options' => []
];
$menu[] = [
    'slug' => $this->Slug->getHome(),
    'name' => __d('admin', 'Home'),
    'options' => [
        'fa-icon' => 'fa-fw fa-home'
    ]
];

if ($appAuth->isCustomer()) {
    if (isset($paymentDepositCustomerAddedMenuElement)) {
        $orderDetailsGroupedByCustomerMenuElement['children'][] = $paymentDepositCustomerAddedMenuElement;
    }
    $orderDetailsGroupedByCustomerMenuElement['children'][] = $changedOrderedProductsMenuElement;
    $menu[] = $orderDetailsGroupedByCustomerMenuElement;
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
    if (isset($paymentDepositCustomerAddedMenuElement)) {
        $orderDetailsGroupedByCustomerMenuElement['children'][] = $paymentDepositCustomerAddedMenuElement;
    }
    $orderDetailsGroupedByCustomerMenuElement['children'][] = $changedOrderedProductsMenuElement;
    $orderDetailsGroupedByCustomerMenuElement['children'][] = $orderListsMenuElement;
    $menu[] = $orderDetailsGroupedByCustomerMenuElement;
    $manufacturerMenu = [
        'slug' => '/admin/manufacturers',
        'name' => __d('admin', 'Manufacturers'),
        'options' => [
            'fa-icon' => 'fa-fw fa-leaf'
        ]
    ];
    $manufacturerMenu['children'][] = [
        'slug' => $this->Slug->getProductAdmin(),
        'name' => __d('admin', 'Products'),
        'options' => [
            'fa-icon' => 'fa-fw fa-tags'
        ]
    ];

    if (date('Y-m-d') > Configure::read('app.depositForManufacturersStartDate')) {
        $manufacturerMenu['children'][] = [
            'slug' => $this->Slug->getDepositList(),
            'name' => __d('admin', 'Deposit_account'),
            'options' => [
                'fa-icon' => 'fa-fw fa-recycle'
            ]
        ];
    }

    if ($appAuth->isSuperadmin() || ($appAuth->isAdmin() && Configure::read('app.showStatisticsForAdmins'))) {
        $manufacturerMenu['children'][] = [
            'slug' => $this->Slug->getStatistics(),
            'name' => __d('admin', 'Turnover_statistics'),
            'options' => [
                'fa-icon' => 'fa-fw fa-chart-bar'
            ]
        ];
    }
    
    $menu[] = $manufacturerMenu;

    $menu[] = [
        'slug' => $this->Slug->getCustomerListAdmin(),
        'name' => __d('admin', 'Members'),
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
    if (Configure::read('app.isBlogFeatureEnabled')) {
        $menu[] = $blogPostsMenuElement;
    }
    
    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getPagesListAdmin(),
        'name' => __d('admin', 'Pages'),
        'options' => [
            'fa-icon' => 'fa-fw fa-pencil-alt'
        ]
    ];

    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getCategoriesList(),
        'name' => __d('admin', 'Categories'),
        'options' => [
            'fa-icon' => 'fa-fw fa-list'
        ]
    ];
    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getAttributesList(),
        'name' => __d('admin', 'Attributes'),
        'options' => [
            'fa-icon' => 'fa-fw fa-chevron-circle-right'
        ]
    ];
    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getSlidersList(),
        'name' => __d('admin', 'Slideshow'),
        'options' => [
            'fa-icon' => 'fa-fw fa-image'
        ]
    ];

    if ($appAuth->isSuperadmin()) {
        $homepageAdministrationElement['children'][] = [
            'slug' => $this->Slug->getTaxesList(),
            'name' => __d('admin', 'Tax_rates'),
            'options' => [
                'fa-icon' => 'fa-fw fa-percent'
            ]
        ];
        $reportSlug = null;
        if (!$this->Html->paymentIsCashless() && Configure::read('app.isDepositPaymentCashless')) {
            $reportSlug = $this->Slug->getReport('deposit');
        }
        if ($this->Html->paymentIsCashless()) {
            $reportSlug = $this->Slug->getReport('product');
        }
        if ($reportSlug) {
            $homepageAdministrationElement['children'][] = [
                'slug' => $reportSlug,
                'name' => __d('admin', 'Financial_reports'),
                'options' => [
                    'fa-icon' => 'fa-fw fa-money-bill-alt'
                ]
            ];
        }
        $homepageAdministrationElement['children'][] = [
            'slug' => $this->Slug->getConfigurationsList(),
            'name' => __d('admin', 'Configurations'),
            'options' => [
                'fa-icon' => 'fa-fw fa-cogs'
            ]
        ];
    }

    $menu[] = $homepageAdministrationElement;
}

if ($appAuth->isManufacturer()) {
    $orderDetailsMenuElement = [
        'slug' => $this->Slug->getOrderDetailsList(),
        'name' => __d('admin', 'Orders'),
        'options' => [
            'fa-icon' => 'fa-fw fa-shopping-cart'
        ]
    ];
    $orderDetailsMenuElement['children'][] = $changedOrderedProductsMenuElement;
    $orderDetailsMenuElement['children'][] = $orderListsMenuElement;
    $menu[] = $orderDetailsMenuElement;
    $menu[] = [
        'slug' => $this->Slug->getProductAdmin(),
        'name' => __d('admin', 'My_products'),
        'options' => [
            'fa-icon' => 'fa-fw fa-tags'
        ]
    ];
    $profileMenu = [
        'slug' => $this->Slug->getManufacturerProfile(),
        'name' => __d('admin', 'My_profile'),
        'options' => [
            'fa-icon' => 'fa-fw fa-home'
        ]
    ];
    $optionsMenu = [
        'slug' => $this->Slug->getManufacturerMyOptions(),
        'name' => __d('admin', 'Configurations'),
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
                'name' => __d('admin', 'Deposit_account'),
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
    if (Configure::read('app.isBlogFeatureEnabled')) {
        $menu[] = $blogPostsMenuElement;
    }
    $menu[] = $actionLogsMenuElement;
    
    $menu[] = [
        'slug' => $this->Slug->getMyStatistics(),
        'name' => __d('admin', 'Turnover_statistics'),
        'options' => [
            'fa-icon' => 'fa-fw fa-chart-bar'
        ]
    ];
}

// for all users
$menu[] = $this->Menu->getAuthMenuElement($appAuth);

$footerHtml = '';
if ($appAuth->isManufacturer() && !empty($appAuth->manufacturer->customer) && !empty($appAuth->manufacturer->customer->address_customer)) {
    $footerHtml = '<b>'.__d('admin', 'Contact_person').'</b><br />' . $appAuth->manufacturer->customer->name . ', ' . $appAuth->manufacturer->customer->email. ', ' . $appAuth->manufacturer->customer->address_customer->phone_mobile;
}

echo $this->Menu->render($menu, [
    'id' => 'menu',
    'class' => 'vertical menu',
    'footer' => $footerHtml
]);
