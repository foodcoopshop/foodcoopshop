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
    'debug' => true,
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
            'password' => '',
            'database' => 'foodcoopshop_test'
        ],
        'test' => [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'foodcoopshop_test'
        ]
    ],

    /**
     * A random string used in security hashing methods.
     */
    'Security.salt' => 'b6OSgpEV0vA36P3PxjWigmbQc6J5CLhs3bSV89KK8m1IKkl8gJfp84Odz3gMdW9K',

    'app' => [
        /**
         * please create a unique cookie key and put it here
         */
        'cookieKey' => '77LIqHJAoVS89iRp3QKcdXaAvZTTqRdiweyZo87CoY9c4fieao5KKPWcdS',

        /**
         * your host's name, eg. http://www.yourfoodcoop.com
         */
        'cakeServerName' => 'http://www.foodcoopshop.test',

        /**
         * whether to apply a member fee to the members account balance
         */
        'memberFeeEnabled' => true,

        /**
         * cronjob needs to be activated too
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
