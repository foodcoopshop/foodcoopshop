<?php
/**
 * - this file contains the specific configuration for your foodcoop
 * - configurations in config.php can be overriden in this file
 * - please rename it to "custom_config.php" to use it
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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

return [
    'debug' => false,
    'EmailTransport' => [
        'default' => [
            'className' => 'Smtp',
            'host' => '',
            'port' => 25,
            'timeout' => 30,
            'username' => '',
            'password' => '',
            'client' => null,
            'tls' => null,
        ]
    ],
    'Email' => [
        'default' => [
            'from' => [],
        ]
    ],
    'Datasources' => [
        'default' => [
            'host' => '',
            'username' => '',
            'password' => '',
            'database' => '',
            'prefix' => 'fcs_'
        ]
    ],

    /**
     * A random string used in security hashing methods.
     */
    'Security' => [
        'salt' => ''
    ],

    /**
     * locale can be overriden here, attention: "App" namespace (not "app")
     * since v2.1 FoodCoopShop is multilingual
     * @see app_config.php:implementedLocales for valid locales
     */
//     'App' => [
//         'defaultLocale' => 'en_US'
//     ],

    'app' => [
        /**
         * please create a unique cookie key and put it here
         * can be removed in v3
         */
        'cookieKey' => '',

        /**
         * not used since v2.4
         * deliveryDayDelta is replaced by the new database setting FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA
         * and can be removed in your custom_config.php after successful migration of 20190218101915_IndividualSendOrderListDay.php
         */
        //'deliveryDayDelta' => 2,
        
        'discourseSsoEnabled' => false,

        /**
         * A random string used for Discourse SSO
         */
        'discourseSsoSecret' => '',

        /**
         * optional: message that is displayed in the dialog where order-detail status can be changed (/admin/order-details)
         */
        'additionalOrderStatusChangeInfo' => '',

        /**
         * your host's name, eg. http://www.yourfoodcoop.com
         */
        'cakeServerName' => '',

        /**
         * not used since v2.4
         * registrationNotificationEmails is replaced by the new database setting FCS_REGISTRATION_NOTIFICATION_EMAILS
         * and can be removed in your custom_config.php after successful migration of 20190305183508_ConfigurationOptimizations.php
         */
        //'registrationNotificationEmails' => [],

        /**
         * whether to apply a member fee to the members account balance
         */
        'memberFeeEnabled' => false,

        /**
         * cronjob needs to be activated / deactivated too if you change emailOrderReminderEnabled
         * @see https://foodcoopshop.github.io/en/cronjobs
         */
        'emailOrderReminderEnabled' => true,

        /**
         * valid options of array: 'cashless' or 'cash' (or both but this is not recommended)
         */
        'paymentMethods' => [
            'cashless'
        ]

    ]
];
