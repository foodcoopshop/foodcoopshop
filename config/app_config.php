<?php
/**
 * app.config.php
 * this file contains the main configuration for foodcoopshop
 *
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

define('APP_ON', 1);
define('APP_OFF', 0);
define('APP_DEL', -1);

define('ORDER_STATE_ORDER_PLACED', 3);
define('ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER', 10);
define('ORDER_STATE_BILLED_CASHLESS', 11);
define('ORDER_STATE_BILLED_CASH', 12);

define('CUSTOMER_GROUP_MEMBER', 3);
define('CUSTOMER_GROUP_ADMIN', 4);
define('CUSTOMER_GROUP_SUPERADMIN', 5);

define('MAX_CART_PRODUCT_AMOUNT', 250);
define('PHONE_REGEX', '/^[0-9 ()+-\/]{7,20}$/');
define('ZIP_REGEX', '/^[0-9]{4,5}$/');
define('BIC_REGEX', '/^[a-z]{6}[2-9a-z][0-9a-np-z]([a-z0-9]{3}|x{3})?$/i');
define('HTTPS_REGEX', '/^https\:\/\//');
// copied from Cake/Utility/Validation.php with additional $ at the end
define('HOSTNAME_REGEX', '/(?:[_\p{L}0-9][-_\p{L}0-9]*\.)*(?:[\p{L}0-9][-\p{L}0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,})$/');

return [
    'Email' => [
        'default' => [
            'emailFormat' => 'html',
            'transport' => 'default',
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ],
    ],
    'Security' => [
        'salt_for_unit_tests' => 'afe52441c09f053e0c0c0f23f3a73cfe6202f7e4a8a64891477a2d290fccf75d'
    ],
    'app' => [
        'jsNamespace' => 'foodcoopshop',
        'filesDir' => DS . 'files',
        'tmpWwwDir' => DS . 'tmp',
        'uploadedImagesDir' => DS . 'files' . DS . 'images',
        'uploadedFilesDir' => DS . 'files' . DS . 'files',
        'customerImagesDir' => ROOT . DS . 'files_private'. DS . 'customers',
        'folder_invoices' => ROOT . DS . 'files_private'. DS . 'invoices',
        'folder_order_lists' => ROOT . DS . 'files_private' . DS .'order_lists',

        /**
         * all the default values in this block can be overwritten in the manufacturer settings
         */
        'defaultSendOrderList' => 1,
        'defaultSendInvoice' => 1,
        'defaultTaxId' => 2,
        'defaultSendInstantOrderNotification' => 1,
        'defaultSendOrderedProductDeletedNotification' => 1,
        'defaultSendOrderedProductPriceChangedNotification' => 1,
        'defaultSendOrderedProductAmountChangedNotification' => 1,
        'isDepositEnabled' => true,
        'isDepositPaymentCashless' => true,
        'depositPaymentCashlessStartDate' => '2016-01-01',
        'depositForManufacturersStartDate' => '2016-01-01',

        /**
         * not used since v2.4
         * sendOrderListsWeekday is replaced by the new database setting FCS_WEEKLY_PICKUP_DAY
         * and can be removed after successful migration of 20190218101915_IndividualSendOrderListDay.php
         */
        'sendOrderListsWeekday' => 3,

        /**
         * should names of members be shown as "John Doe" or "Doe John"
         * options:
         * - firstname
         * - lastname
         */
        'customerMainNamePart' => 'firstname',

        'showManufacturerImprint' => true,
        'termsOfUseEnabled' => true,
        'generalTermsAndConditionsEnabled' => true,
        'rightOfWithdrawalEnabled' => true,
        'promiseToPickUpProductsCheckboxEnabled' => false,
        'showPaymentInfoText' => true,
        'showManufacturerListAndDetailPage' => true,
        'showPickupPlaceInfo' => true,
        'showTaxSumTableOnOrderDetailPdf' => false,
        'selfServiceModeAutoLogoutDesktopEnabled' => true,

        /**
         * id of the category "all products"
         */
        'categoryAllProducts' => 20,

        /**
         * image upload sizes and suffixes
         */
        'productImageSizes' => [
            '150' => ['suffix' => '-home_default'],      // list page
            '358' => ['suffix' => '-large_default'],     // detail page
            '800' => ['suffix' => '-thickbox_default']   // lightbox
        ],
        'blogPostImageSizes' => [
            '170' => ['suffix' => '-home-default'],     // detail / list page
            '800' => ['suffix' => '-single-default']    // lightbox
        ],
        'customerImageSizes' => [
            '200' => ['suffix' => '-small'],  // list page
            '800' => ['suffix' => '-large'],  // lightbox
            '1200' => ['suffix' => '-xxl']    // lightbox
        ],
        'manufacturerImageSizes' => [
            '200' => ['suffix' => '-medium_default'],  // detail / list page
            '800' => ['suffix' => '-large_default']    // lightbox
        ],
        'categoryImageSizes' => [
            '717' => ['suffix' => '-category_default'] // detail AND lightbox
        ],
        'sliderImageSizes' => [
            '908' => ['suffix' => '-slider'] // detail AND lightbox
        ],
        'tmpUploadImagesDir' => DS . 'tmp' . DS . 'images',
        'tmpUploadFilesDir' => DS . 'tmp' . DS . 'files',

        'countryId' => 2, // austria: 2, germany: 1

        /**
         * if you work on windows, change to e.g
         * 'C:\\Programme\\xampp\\mysql\\bin\\mysqldump.exe'
         */
        'mysqlDumpCommand' => 'mysqldump',

        /**
         * if you work on windows, change to e.g
         * 'C:\\Programme\\xampp\\mysql\\bin\\mysql.exe'
         */
        'mysqlCommand' => 'mysql',

        /**
         * date of the last update of terms of use
         */
        'termsOfUseLastUpdate' => '2016-11-28',

        'implementedLocales' => ['de_DE', 'en_US', 'pl_PL'],

        // allows to use another color theme for the frontend, can be any valid css color code
        'customFrontendColorTheme' => '#719f41',

        'isBlogFeatureEnabled' => true,

        'applyOrdersNotYetBilledCheckOnDeletingCustomers' => true,

        'isCustomerAllowedToModifyOwnOrders' => true,

        'showStatisticsForAdmins' => true,

        'sendEmailWhenOrderDetailQuantityOrPriceChanged' => true,

        'configurationHelper' => new App\View\Helper\ConfigurationHelper(new Cake\View\View()),
        'htmlHelper' => new App\View\Helper\MyHtmlHelper(new Cake\View\View()),
        'timeHelper' => new App\View\Helper\MyTimeHelper(new Cake\View\View()),
        'numberHelper' => new App\View\Helper\MyNumberHelper(new Cake\View\View()),
        'slugHelper' => new App\View\Helper\SlugHelper(new Cake\View\View()),
        'timebasedCurrencyHelper' => new App\View\Helper\TimebasedCurrencyHelper(new Cake\View\View()),
        'pricePerUnitHelper' => new App\View\Helper\PricePerUnitHelper(new Cake\View\View())
    ],
    'DateFormat' => [
        'Database' => 'yyyy-MM-dd',
        'DatabaseWithTime' => 'yyyy-MM-dd HH:mm:ss',
        'DatabaseAlt' => 'Y-m-d',
        'DatabaseWithTimeAlt' => 'Y-m-d H:i:s',
        'DatabaseWithTimeAndMicrosecondsAlt' => 'Y-m-d H:i:s.u',
        'DateWithTimeForFilename' => 'Y-m-d_H-i-s'
    ]
];
