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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

define('APP_ON', 1);
define('APP_OFF', 0);
define('APP_DEL', -1);

define('ORDER_STATE_OPEN', 3);
define('ORDER_STATE_CLOSED', 5);
define('ORDER_STATE_CASH_FREE', 1);
define('ORDER_STATE_CASH', 2);
define('ORDER_STATE_CANCELLED', 6);

define('CUSTOMER_GROUP_MEMBER', 3);
define('CUSTOMER_GROUP_ADMIN', 4);
define('CUSTOMER_GROUP_SUPERADMIN', 5);

define('PASSWORD_REGEX', '/^([^\\s]){6,32}$/');
define('PHONE_REGEX', '/^[0-9 ()+-\/]{7,20}$/');
define('ZIP_REGEX', '/^[0-9]{4,5}$/');
define('IBAN_REGEX', '/^([a-zA-Z]\s?){2}(([0-9]){18}|([0-9]){22})$/'); // austria and germany supported
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
    'app' => [
        'jsNamespace' => 'foodcoopshop',
        'visibleOrderStates' => [
            ORDER_STATE_OPEN => 'offen',
            ORDER_STATE_CASH_FREE => 'abgeschlossen',
        ],
        'filesDir' => DS . 'files',
        'tmpWwwDir' => DS.'tmp',
        'uploadedImagesDir' => DS . 'files' . DS . 'images',
        'folder_invoices' => ROOT . DS . 'files_private'. DS . 'invoices',
        'folder_order_lists' => ROOT . DS. 'files_private' . DS .'order_lists',
        'folder_invoices_with_current_year_and_month' => ROOT . DS . 'files_private'. DS . 'invoices'.DS.date('Y').DS.date('m'),
        'folder_order_lists_with_current_year_and_month' => ROOT . DS . 'files_private' . DS .'order_lists'.DS.date('Y').DS.date('m'),
        
        'manufacturerComponensationInfoText' => 'Die Bestellung beinhaltet den variablen Mitgliedsbeitrag.',
        /**
         * all the default values in this block can be overwritten in the manufacturer settings
         */
        'defaultSendOrderList' => true,
        'defaultSendInvoice' => true,
        'defaultTaxId' => 2,
        'defaultBulkOrdersAllowed' => false,
        'defaultSendShopOrderNotification' => true,
        'defaultSendOrderedProductDeletedNotification' => true,
        'defaultSendOrderedProductPriceChangedNotification' => true,
        'defaultSendOrderedProductQuantityChangedNotification' => true,
        'isDepositPaymentCashless' => true,
        'depositPaymentCashlessStartDate' => '2016-01-01',
        'depositForManufacturersStartDate' => '2016-01-01',
                
        /**
         * adds a link to the manufacturer admin to generate and send the order list on click
         * can be useful, if e.g. a member forgot to order and the order lists are already sent
         * @deprecated - do not use this option any more, will be removed in next version
         */
        'allowManualOrderListSending' => false,
        /**
         * weekday on which the weekly cronjob "SendOrderList" is called
         * the available options (in combination with deliveryDayDelta) can be found in Test/Case/View/Helper/MyTimeHelperTest.php
         */
        'sendOrderListsWeekday' => 3,
        
        /**
         * should names of members be shown as "John Doe" or "Doe John"
         * options:
         * - firstname
         * - lastname
         */
        'customerMainNamePart' => 'firstname',
        
        /**
         * id of the category "all products"
         */
        'categoryAllProducts' => 20,
        
        /**
         * @deprecated - do not use this option any more, will be removed in next version
         */
        'memberFeeFlexibleEnabled' => false,
        
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
        'manufacturerImageSizes' => [
            '200' => ['suffix' => '-medium_default'],  // detail / list page
            '800' => ['suffix' => '-large_default']    // lightbox
        ],
        'categoryImageSizes' => [
            '717' => ['suffix' => '-category_default'] // detail AND lightbox
        ],
        'sliderImageSizes' => [
            '905' => ['suffix' => '-slider'] // detail AND lightbox
        ],
        'tmpUploadImagesDir' => DS.'tmp' . DS . 'images',
        
        'langId' => 1,
        'shopId' => 1,
        'countryId' => 2, // austria: 2, germany: 1
        
        /**
         * if you work on windows, change to e.g
         * 'C:\\Programme\\xampp\\mysql\\bin\\mysqldump.exe'
         */
        'mysqlDumpCommand' => 'mysqldump',
        
        /**
         * date of the last update of terms of use
         */
        'termsOfUseLastUpdate' => '2016-11-28',
        
        'htmlHelper' => new App\View\Helper\MyHtmlHelper(new Cake\View\View()),
        'timeHelper' => new App\View\Helper\MyTimeHelper(new Cake\View\View()),
        'slugHelper' => new App\View\Helper\SlugHelper(new Cake\View\View())
    ],
    'DateFormat' => [
        'Database' => 'yyyy-MM-dd',
        'de' => [
            'DateShort' => 'dd.MM.yy',
            'DateLong' =>  'dd. MMMM y',
            'DateLong2' => 'dd.MM.yyyy',
            'DateNTimeShort' => 'dd.MM.y HH:mm',
            'DateNTimeLongWithSecs' => 'dd.MM.y HH:mm:ss',
            'TimeShort' => 'HH:mm',
            'DateNTimeForDatepicker' => 'dd.MM.yyyy HH:mm'
        ]
    ]
];