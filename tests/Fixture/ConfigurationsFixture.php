<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\Fixture;

class ConfigurationsFixture extends AppFixture
{
    public string $table = 'fcs_configuration';

    public array $records = [
        [
            'active' => 1,
            'name' => 'FCS_PRODUCT_AVAILABILITY_LOW',
            'text' => 'Geringe Verfügbarkeit<br /><div class="small">Ab welcher verfügbaren Produkt-Menge soll beim Bestellen der Hinweis "(x verfügbar") angezeigt werden?',
            'value' => '10',
            'type' => 'number',
            'position' => 600,
        ],
        [
            'active' => 1,
            'name' => 'FCS_DAYS_SHOW_PRODUCT_AS_NEW',
            'value' => '7',
            'type' => 'number',
            'position' => 700,
        ],
        [
            'active' => 1,
            'name' => 'FCS_FOOTER_CMS_TEXT',
            'value' => NULL,
            'type' => 'textarea_big',
            'position' => 920,
        ],
        [
            'active' => 1,
            'name' => 'FCS_FACEBOOK_URL',
            'value' => 'https://www.facebook.com/FoodCoopShop/',
            'type' => 'text',
            'position' => 910,
        ],
        [
            'active' => 1,
            'name' => 'FCS_REGISTRATION_EMAIL_TEXT',
            'value' => '',
            'type' => 'textarea_big',
            'position' => 1700,
        ],
        [
            'active' => 1,
            'name' => 'FCS_RIGHT_INFO_BOX_HTML',
            'value' => '<h3>Abholzeiten</h3>',
            'type' => 'textarea_big',
            'position' => 1500,
        ],
        [
            'active' => 1,
            'name' => 'FCS_NO_DELIVERY_DAYS_GLOBAL',
            'value' => '',
            'type' => 'multiple_dropdown',
            'position' => 100,
        ],
        [
            'active' => 1,
            'name' => 'FCS_ACCOUNTING_EMAIL',
            'value' => 'fcs-demo-superadmin@mailinator.com',
            'type' => 'text',
            'position' => 1100,
        ],
        [
            'active' => 1,
            'name' => 'FCS_REGISTRATION_INFO_TEXT',
            'value' => 'Um bei uns zu bestellen musst du Vereinsmitglied sein.',
            'type' => 'textarea_big',
            'position' => 1600,
        ],
        [
            'active' => 1,
            'name' => 'FCS_SHOW_PRODUCTS_FOR_GUESTS',
            'value' => '0',
            'type' => 'boolean',
            'position' => 200,
        ],
        [
            'active' => 1,
            'name' => 'FCS_DEFAULT_NEW_MEMBER_ACTIVE',
            'value' => '0',
            'type' => 'boolean',
            'position' => 500,
        ],
        [
            'active' => 1,
            'name' => 'FCS_MINIMAL_CREDIT_BALANCE',
            'value' => '-100',
            'type' => 'number',
            'position' => 1250,
        ],
        [
            'active' => 1,
            'name' => 'FCS_BANK_ACCOUNT_DATA',
            'value' => 'Guthaben-Konto Testbank / IBAN: AT65 5645 4154 8748 8999 / BIC: ABC87878',
            'type' => 'text',
            'position' => 1300,
        ],
        [
            'active' => 1,
            'name' => 'FCS_DELIVERY_DETAILS_FOR_MANUFACTURERS',
            'value' => ', 15:00 bis 17:00 Uhr',
            'type' => 'text',
            'position' => 1200,
        ],
        [
            'active' => 1,
            'name' => 'FCS_BACKUP_EMAIL_ADDRESS_BCC',
            'value' => '',
            'type' => 'text',
            'position' => 1900,
        ],
        [
            'active' => 1,
            'name' => 'FCS_SHOW_FOODCOOPSHOP_BACKLINK',
            'value' => '1',
            'type' => 'boolean',
            'position' => 930,
        ],
        [
            'active' => 1,
            'name' => 'FCS_APP_NAME',
            'value' => 'FoodCoop Test',
            'type' => 'text',
            'position' => 50,
        ],
        [
            'active' => 1,
            'name' => 'FCS_APP_ADDRESS',
            'value' => 'Demostra&szlig;e 4<br />A-4564 Demostadt',
            'type' => 'textarea',
            'position' => 60,
        ],
        [
            'active' => 1,
            'name' => 'FCS_APP_EMAIL',
            'value' => 'demo-foodcoop@maillinator.com',
            'type' => 'text',
            'position' => 900,
        ],
        [
            'active' => 1,
            'name' => 'FCS_PLATFORM_OWNER',
            'value' => '',
            'type' => 'textarea',
            'position' => 90,
        ],
        [
            'active' => 1,
            'name' => 'FCS_ORDER_COMMENT_ENABLED',
            'value' => '1',
            'type' => 'boolean',
            'position' => 130,
        ],
        [
            'active' => 1,
            'name' => 'FCS_USE_VARIABLE_MEMBER_FEE',
            'value' => '0',
            'type' => 'readonly',
            'position' => 400,
        ],
        [
            'active' => 1,
            'name' => 'FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE',
            'value' => '0',
            'type' => 'readonly',
            'position' => 500,
        ],
        [
            'active' => 1,
            'name' => 'FCS_NETWORK_PLUGIN_ENABLED',
            'value' => '1',
            'type' => 'readonly',
            'position' => 500,
        ],
        [
            'active' => 1,
            'name' => 'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS',
            'value' => '0',
            'type' => 'boolean',
            'position' => 210,
        ],
        [
            'active' => 1,
            'name' => 'FCS_CURRENCY_SYMBOL',
            'text' => 'Währungssymbol',
            'value' => '€',
            'type' => 'readonly',
            'position' => 520,
        ],
        [
            'active' => 1,
            'name' => 'FCS_DEFAULT_LOCALE',
            'value' => 'de_DE',
            'type' => 'readonly',
            'position' => 550,
        ],
        [
            'active' => 1,
            'name' => 'FCS_FOODCOOPS_MAP_ENABLED',
            'value' => '1',
            'type' => 'boolean',
            'position' => 1280,
        ],
        [
            'active' => 1,
            'name' => 'FCS_WEEKLY_PICKUP_DAY',
            'value' => '5',
            'type' => 'readonly',
            'position' => 600,
        ],
        [
            'active' => 1,
            'name' => 'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA',
            'value' => '2',
            'type' => 'readonly',
            'position' => 650,
        ],
        [
            'active' => 1,
            'name' => 'FCS_ORDER_POSSIBLE_FOR_STOCK_PRODUCTS_IN_ORDERS_WITH_DELIVERY_RHYTHM',
            'value' => '1',
            'type' => 'boolean',
            'position' => 750,
        ],
        [
            'active' => 1,
            'name' => 'FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS',
            'value' => '0',
            'type' => 'boolean',
            'position' => 760,
        ],
        [
            'active' => 1,
            'name' => 'FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES',
            'value' => '1',
            'type' => 'readonly',
            'position' => 600,
        ],
        [
            'active' => 1,
            'name' => 'FCS_REGISTRATION_NOTIFICATION_EMAILS',
            'value' => 'fcs-demo-superadmin@mailinator.com',
            'type' => 'text',
            'position' => 550,
        ],
        [
            'active' => 1,
            'name' => 'FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED',
            'value' => '0',
            'type' => 'boolean',
            'position' => 3000,
        ],
        [
            'active' => 1,
            'name' => 'FCS_APP_ADDITIONAL_DATA',
            'value' => '',
            'type' => 'textarea',
            'position' => 80,
        ],
        [
            'active' => 1,
            'name' => 'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED',
            'value' => '1',
            'type' => 'boolean',
            'position' => 3100,
        ],
        [
            'active' => 1,
            'name' => 'FCS_CASHLESS_PAYMENT_ADD_TYPE',
            'value' => 'manual',
            'type' => 'dropdown',
            'position' => 1450,
        ],
        [
            'active' => 1,
            'name' => 'FCS_FEEDBACK_TO_PRODUCTS_ENABLED',
            'value' => '1',
            'type' => 'boolean',
            'position' => 3200,
        ],
        [
            'active' => 1,
            'name' => 'FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY',
            'value' => '0',
            'type' => 'readonly',
            'position' => 590,
        ],
        [
            'active' => 1,
            'name' => 'FCS_SEND_INVOICES_TO_CUSTOMERS',
            'value' => '0',
            'type' => 'readonly',
            'position' => 580,
        ],
        [
            'active' => 1,
            'name' => 'FCS_DEPOSIT_TAX_RATE',
            'value' => '20,00',
            'type' => 'readonly',
            'position' => 581,
        ],
        [
            'active' => 1,
            'name' => 'FCS_INVOICE_HEADER_TEXT',
            'value' => 'FoodCoop Test<br />Demostraße 4<br />A-4564 Demostadt<br />demo-foodcoop@maillinator.com',
            'type' => 'readonly',
            'position' => 582,
        ],
        [
            'active' => 1,
            'name' => 'FCS_MEMBER_FEE_PRODUCTS',
            'value' => '',
            'type' => 'multiple_dropdown',
            'position' => 3300,
        ],
        [
            'active' => 1,
            'name' => 'FCS_CHECK_CREDIT_BALANCE_LIMIT',
            'value' => '0',
            'type' => 'number',
            'position' => 1450,
        ],
        [
            'active' => 1,
            'name' => 'FCS_PURCHASE_PRICE_ENABLED',
            'value' => '0',
            'type' => 'readonly',
            'position' => 587,
        ],
        [
            'active' => 1,
            'name' => 'FCS_HELLO_CASH_API_ENABLED',
            'value' => '0',
            'type' => 'readonly',
            'position' => 588,
        ],
        [
            'active' => 1,
            'name' => 'FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS',
            'value' => '0',
            'type' => 'readonly',
            'position' => 589,
        ],
        [
            'active' => 1,
            'name' => 'FCS_INSTAGRAM_URL',
            'value' => '',
            'type' => 'text',
            'position' => 920,
        ],
        [
            'active' => 1,
            'name' => 'FCS_ALLOW_ORDERS_FOR_DELIVERY_RHYTHM_ONE_OR_TWO_WEEKS_ONLY_IN_WEEK_BEFORE_DELIVERY',
            'value' => '0',
            'type' => 'boolean',
            'position' => 3210,
        ],
        [
            'active' => 1,
            'name' => 'FCS_INVOICE_NUMBER_PREFIX',
            'value' => '',
            'type' => 'readonly',
            'position' => 586,
        ],
        [
            'active' => 1,
            'name' => 'FCS_TAX_BASED_ON_NET_INVOICE_SUM',
            'value' => '0',
            'type' => 'readonly',
            'position' => 585,
        ],
        [
            'active' => 1,
            'name' => 'FCS_NEWSLETTER_ENABLED',
            'value' => '0',
            'type' => 'boolean',
            'position' => 3400,
        ],
        [
            'active' => 1,
            'name' => 'FCS_USER_FEEDBACK_ENABLED',
            'value' => '0',
            'type' => 'boolean',
            'position' => 3500,
        ],
        [
            'active' => 1,
            'name' => 'FCS_HOME_TEXT',
            'value' => '',
            'type' => 'textarea_big',
            'position' => 1290,
        ],
        [
            'name' => 'FCS_SHOW_ONLY_PRODUCTS_FOR_NEXT_WEEK_FILTER_ENABLED',
            'active' => 1,
            'value' => 0,
            'type' => 'boolean',
            'position' => 3600,
        ]
    ];

}
?>