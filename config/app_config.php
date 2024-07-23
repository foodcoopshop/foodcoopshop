<?php
declare(strict_types=1);

/**
 * app.config.php
 * this file contains the main configuration for foodcoopshop
 *
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

define('APP_ON', 1);
define('APP_OFF', 0);
define('APP_DEL', -1);

define('PHONE_REGEX', '/^[0-9 ()+-\/]{7,20}$/');
define('EMAIL_REGEX', '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,10})+/i');
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
        'testDebug' => false, // for debugging - keeps test data in test database after test 
        'jsNamespace' => 'foodcoopshop',
        'filesDir' => DS . 'files',
        'tmpWwwDir' => DS . 'tmp',
        'allowedImageMimeTypes' => [
            'JPG' => 'image/jpeg', // keep jpg as first element for best performance
            'PNG' => 'image/png',
            'GIF'=> 'image/gif',
            'WEBP'=> 'image/webp',
        ],
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
        'defaultTaxIdPurchasePrice' => 2,
        'defaultSendInstantOrderNotification' => 1,
        'defaultSendOrderedProductDeletedNotification' => 1,
        'defaultSendOrderedProductPriceChangedNotification' => 1,
        'defaultSendOrderedProductAmountChangedNotification' => 1,
        'isDepositEnabled' => true,
        'depositPaymentCashlessStartDate' => '2016-01-01',
        'depositForManufacturersStartDate' => '2016-01-01',

        /**
         * 'Raiffeisen' OR 'Volksbank' OR 'Sparkasse' OR 'GlsBank'
         */
        'bankNameForCreditSystem' => 'Raiffeisen',

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
        'showPaymentInfoText' => true,
        'showManufacturerListAndDetailPage' => true,
        'showPickupPlaceInfo' => true,
        'selfServiceModeAutoLogoutDesktopEnabled' => true,
        'selfServiceModeShowOnlyStockProducts' => true,
        'selfServiceModeAutoGenerateInvoice' => true,
        'selfServiceLoginCustomers' => [
            // [
            //     'id' => 1,
            //     'label' => 'City A',
            //     'customerId' => 33,
            // ],
        ],
        'showOrderedProductsTotalAmountInCatalog' => false,
        'applyOpenOrderCheckForOrderReminder' => true,
        'changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled' => false,

        /**
         * id of the category "all products"
         */
        'categoryAllProducts' => 20,

        'logoFileName' => 'logo.png',
        'logoWidth' => 220, //int
        'logoMaxHeight' => 120,  // int or string 'auto'

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
         * date of the last update of terms of use
         */
        'termsOfUseLastUpdate' => '2016-11-28',

        'implementedLocales' => ['de_DE', 'en_US', 'ru_RU'],

        // must be a valid 6-digit hex code
        'customThemeMainColor' => '#719f41',

        'isBlogFeatureEnabled' => true,

        'applyOrdersNotYetBilledCheckOnDeletingCustomers' => true,

        'applyPaymentsOkCheckOnDeletingCustomers' => true,

        'isCustomerAllowedToModifyOwnOrders' => true,

        'isCustomerAllowedToViewOwnOrders' => true,

        'isZeroTaxEnabled' => true,

        'showTaxInOrderConfirmationEmail' => true,

        // additionalTextForInvoice - for invoices to customers (not available if hello cash is used!)
        'additionalTextForInvoice' => '',

        'showStatisticsForAdmins' => true,

        'sendEmailWhenOrderDetailQuantityChanged' => true,

        // if set, a paypal.me-link is added to the invoice-to-customer email
        'paypalMeUsername' => '',

        'helloCashRestEndpoint' => 'https://api.hellocash.business/api/v1',

        'helloCashAtCredentials' => [
            'token' => '',
            'cashier_id' => 0,
            'payment_type_cash' => 'Bar',
            'payment_type_cashless' => 'Guthaben-System',
        ],

        'configurationHelper' => new App\View\Helper\ConfigurationHelper(new Cake\View\View()),
        'htmlHelper' => new App\View\Helper\MyHtmlHelper(new Cake\View\View()),
        'timeHelper' => new App\View\Helper\MyTimeHelper(new Cake\View\View()),
        'numberHelper' => new App\View\Helper\MyNumberHelper(new Cake\View\View()),
        'slugHelper' => new App\View\Helper\SlugHelper(new Cake\View\View()),
        'pricePerUnitHelper' => new App\View\Helper\PricePerUnitHelper(new Cake\View\View())
    ],
    'DateFormat' => [
        'Database' => 'yyyy-MM-dd',
        'DatabaseWithTime' => 'yyyy-MM-dd HH:mm:ss',
        'DatabaseAlt' => 'Y-m-d',
        'DatabaseWithTimeAlt' => 'Y-m-d H:i:s',
        'DatabaseWithTimeAndMicrosecondsAlt' => 'Y-m-d H:i:s.u',
        'DateWithTimeForFilename' => 'Y-m-d_H-i-s',
    ],
];
