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
use Cake\ORM\TableRegistry;

if ($identity === null || in_array($this->request->getParam('action'), ['iframeInstantOrder', 'iframeSelfServiceOrder'])) {
    return;
}

// used multiple times...
$paymentProductMenuElement = $this->Menu->getPaymentProductMenuElement();
$myFeedbackMenuElement = $this->Menu->getMyFeedbackMenuElement($identity);
$orderDetailsGroupedByCustomerMenuElement = $this->Menu->getOrderDetailsGroupByCustomerMenuElement();
$changedOrderedProductsMenuElement = $this->Menu->getChangedOrderedProductsMenuElement();
$customerProfileMenuElement = $this->Menu->getCustomerProfileMenuElement();
$actionLogsMenuElement = $this->Menu->getActionLogsMenuElement();
$changePasswordMenuElement = $this->Menu->getChangePasswordMenuElement();
$myInvoicesMenuElement = $this->Menu->getMyInvoicesMenuElement();

$orderListsMenuElement = [
    'slug' => $this->Slug->getOrderLists(),
    'name' => __d('admin', 'Order_lists'),
    'options' => [
        'fa-icon' => 'fa-fw ok fa-book'
    ]
];
$blogPostsMenuElement = [
    'slug' => $this->Slug->getBlogPostListAdmin(),
    'name' => __d('admin', 'Blog_posts'),
    'options' => [
        'fa-icon' => 'fa-fw ok fa-file-alt'
    ]
];
$homepageAdministrationElement = [
    'slug' => $this->Slug->getPagesListAdmin(),
    'name' => __d('admin', 'Website_administration'),
    'options' => [
        'fa-icon' => 'fa-fw ok fa-pencil-alt'
    ]
];
$menu = [];
$logoHtml = '<img class="logo" src="/files/images/' . Configure::read('app.logoFileName') . '?' . filemtime(WWW_ROOT.'files'.DS.'images'.DS.Configure::read('app.logoFileName')) . '" width="100%" />';
$menu[] = [
    'slug' => $this->Slug->getHome(),
    'name' => $logoHtml,
    'options' => []
];
$menu[] = [
    'slug' => $this->Slug->getHome(),
    'name' => __d('admin', 'Home'),
    'options' => [
        'fa-icon' => 'fa-fw ok fa-home'
    ]
];

if ($identity->isCustomer()) {
    $menu = array_merge($menu, $this->Menu->getCustomerMenuElements($identity));
}

if ($identity->isSuperadmin() || $identity->isAdmin()) {
    $orderDetailsGroupedByCustomerMenuElement['children'][] = $changedOrderedProductsMenuElement;
    $orderDetailsGroupedByCustomerMenuElement['children'][] = $orderListsMenuElement;

    if ($identity->isSuperadmin() && Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        $invoicesMenuElement = [
            'slug' => Configure::read('app.slugHelper')->getInvoices(),
            'name' => __d('admin', 'Invoices'),
            'options' => [
                'fa-icon' => 'fa-fw ok fa-file-invoice',
            ],
        ];
        $orderDetailsGroupedByCustomerMenuElement['children'][] = $invoicesMenuElement;
    }

    $menu[] = $orderDetailsGroupedByCustomerMenuElement;
    $manufacturerMenu = [
        'slug' => '/admin/manufacturers',
        'name' => __d('admin', 'Manufacturers'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-leaf'
        ]
    ];
    $manufacturerMenu['children'][] = [
        'slug' => $this->Slug->getProductAdmin(),
        'name' => __d('admin', 'Products'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-tags'
        ]
    ];

    if (Configure::read('app.isDepositEnabled') && date('Y-m-d') > Configure::read('app.depositForManufacturersStartDate')) {
        $manufacturerMenu['children'][] = [
            'slug' => $this->Slug->getDepositList(),
            'name' => __d('admin', 'Deposit_account'),
            'options' => [
                'fa-icon' => 'fa-fw ok fa-recycle'
            ]
        ];
    }

    if ($identity->isSuperadmin() || ($identity->isAdmin() && Configure::read('app.showStatisticsForAdmins'))) {
        $manufacturerMenu['children'][] = [
            'slug' => $this->Slug->getStatistics(),
            'name' => Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') ? __d('admin', 'Turnover_and_profit_statistics') : __d('admin', 'Turnover_statistics'),
            'options' => [
                'fa-icon' => 'fa-fw ok fa-chart-line'
            ]
        ];
    }

    $menu[] = $manufacturerMenu;

    $menu[] = [
        'slug' => $this->Slug->getCustomerListAdmin(),
        'name' => __d('admin', 'Members'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-user-group'
        ]
    ];
    $menu[] = $actionLogsMenuElement;
    if (! empty($paymentProductMenuElement)) {
        $customerProfileMenuElement['children'][] = $paymentProductMenuElement;
    }
    if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        $customerProfileMenuElement['children'][] = $myInvoicesMenuElement;
    }
    $customerProfileMenuElement['children'][] = $changePasswordMenuElement;
    if (!empty($myFeedbackMenuElement)) {
        $customerProfileMenuElement['children'][] = $myFeedbackMenuElement;
    }
    $menu[] = $customerProfileMenuElement;

    if (Configure::read('app.isBlogFeatureEnabled')) {
        $menu[] = $blogPostsMenuElement;
    }

    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getPagesListAdmin(),
        'name' => __d('admin', 'Pages'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-pencil-alt'
        ]
    ];

    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getCategoriesList(),
        'name' => __d('admin', 'Categories'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-list'
        ]
    ];
    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getAttributesList(),
        'name' => __d('admin', 'Attributes'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-chevron-circle-right'
        ]
    ];
    $homepageAdministrationElement['children'][] = [
        'slug' => $this->Slug->getSlidersList(),
        'name' => __d('admin', 'Slideshow'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-image'
        ]
    ];

    if ($identity->isSuperadmin()) {

        $reportSlug = null;

        if ($this->Html->paymentIsCashless()) {
            $reportSlug = $this->Slug->getReport('product');
        } else {
            if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
                $reportSlug = $this->Slug->getProfit();
            }
            if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
                $reportSlug = $this->Slug->getInvoices();
            }
        }
        if ($reportSlug) {
            $homepageAdministrationElement['children'][] = [
                'slug' => $reportSlug,
                'name' => __d('admin', 'Financial_reports'),
                'options' => [
                    'fa-icon' => 'fa-fw ok fa-money-bill-alt'
                ]
            ];
        }
        $homepageAdministrationElement['children'][] = [
            'slug' => $this->Slug->getConfigurationsList(),
            'name' => __d('admin', 'Configurations'),
            'options' => [
                'fa-icon' => 'fa-fw ok fa-cogs'
            ]
        ];

    }

    $menu[] = $homepageAdministrationElement;
}

if ($identity->isManufacturer()) {
    $orderDetailsMenuElement = [
        'slug' => $this->Slug->getOrderDetailsList(),
        'name' => __d('admin', 'Orders'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-shopping-cart'
        ]
    ];
    $orderDetailsMenuElement['children'][] = $changedOrderedProductsMenuElement;
    $orderDetailsMenuElement['children'][] = $orderListsMenuElement;
    $menu[] = $orderDetailsMenuElement;
    $menu[] = [
        'slug' => $this->Slug->getProductAdmin(),
        'name' => __d('admin', 'My_products'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-tags'
        ]
    ];
    $profileMenu = [
        'slug' => $this->Slug->getManufacturerProfile(),
        'name' => __d('admin', 'My_profile'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-home'
        ]
    ];
    $optionsMenu = [
        'slug' => $this->Slug->getManufacturerMyOptions(),
        'name' => __d('admin', 'Configurations'),
        'options' => [
            'fa-icon' => 'fa-fw ok fa-cogs'
        ]
    ];
    if (Configure::read('app.isDepositEnabled') && date('Y-m-d') > Configure::read('app.depositForManufacturersStartDate')) {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $sumDepositDelivered = $orderDetailsTable->getDepositSum($identity->getManufacturerId(), false);
        if ($sumDepositDelivered[0]['sumDepositDelivered'] > 0) {
            $menu[] = [
                'slug' => $this->Slug->getMyDepositList(),
                'name' => __d('admin', 'Deposit_account'),
                'options' => [
                    'fa-icon' => 'fa-fw ok fa-recycle'
                ]
            ];
        }
    }
    $profileMenu['children'][] = $changePasswordMenuElement;
    if (!empty($myFeedbackMenuElement)) {
        $profileMenu['children'][] = $myFeedbackMenuElement;
    }
    $menu[] = $profileMenu;
    $menu[] = $optionsMenu;
    if (Configure::read('app.isBlogFeatureEnabled')) {
        $menu[] = $blogPostsMenuElement;
    }
    $menu[] = $actionLogsMenuElement;

    if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        $orderDetailsTable = TableRegistry::getTableLocator()->get('OrderDetails');
        $firstOrderYear = $orderDetailsTable->getFirstOrderYear((string) $identity->getManufacturerId());
        if ($firstOrderYear !== false) {
            $menu[] = [
                'slug' => $this->Slug->getManufacturerInvoices(),
                'name' => __d('admin', 'Invoices'),
                'options' => [
                    'fa-icon' => 'fa-fw ok fa-file-invoice',
                    ]
            ];
        }
        $menu[] = [
            'slug' => $this->Slug->getMyStatistics(),
            'name' => __d('admin', 'Turnover_statistics'),
            'options' => [
                'fa-icon' => 'fa-fw ok fa-chart-bar',
            ]
        ];
    }
}

// for all users
$menu[] = $this->Menu->getAuthMenuElement($identity);

$footerHtml = '';
if ($identity->isManufacturer() && !empty($identity->getManufacturerCustomer()) && !empty($identity->getManufacturerCustomer()['address_customer'])) {
    $footerHtml = '<b>'.__d('admin', 'Contact_person').'</b><br />' . $identity->getManufacturerCustomer()['name'] . ', ' . $identity->getManufacturerCustomer()['email']. ', ' . $identity->getManufacturerCustomer()['address_customer']['phone_mobile'];
}
echo $this->Menu->render($menu, [
    'id' => 'menu',
    'class' => 'vertical menu',
    'footer' => $footerHtml
]);
