<?php
/**
 * custom.config.default.php
 *
 * - this file contains the specific configuration for your foodcoop
 * - configurations in config.php can be overriden in this file
 * - please rename it to "custom.config.php" to use it
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

return [
    'debug' => false,
    'EmailTransport' => [
        'default' => [
            'className' => 'Mail',
        ]
    ],
    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => 'you@localhost'
        ],
    ],
    'Datasources' => [
        'default' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'password',
            'database' => 'foodcoopshop_test',
            'port' => '8888',
        ],
        'test' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'password',
            'database' => 'foodcoopshop_test',
            'port' => '8888',
        ]
    ],

    /**
     * A random string used in security hashing methods.
     */
    'Security.salt' => 'b6OSgpEV0vA36P3PxjWigmbQc6J5CLhs3bSV89KK8m1IKkl8gJfp84Odz3gMdW9K',

    'app' => [
        /**
         * your host's name, eg. http://www.yourfoodcoop.com
         */
        'cakeServerName' => 'http://localhost',

        /**
         * cronjob needs to be activated too
         */
        'emailOrderReminderEnabled' => true,

        'outputStringReplacements' => [
            'This is a test' => 'This is another test',
        ],

        /**
         * valid options of array: 'cashless' or 'cash' (or both but this is not recommended)
         */
        'paymentMethods' => [
            'cashless'
        ],

        'helloCashAtCredentials' => [
            'username' => 'HELLO_CASH_USERNAME',
            'password' => 'HELLO_CASH_PASSWORD',
            'cashier_id' => 'HELLO_CASH_CASHIER_ID',
            'payment_type_cash' => 'Bar',
            'payment_type_cashless' => 'Kreditrechnung',
        ],

    ]
];
