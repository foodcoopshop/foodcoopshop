<?php
/**
 * credentials.default.php
 * please rename this file to "credentials.php" to use it
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
    'app' => [
        /**
         * the cronjobs for sending order lists and invoices need the credentials to a valid superadmin account
         */
        'adminEmail' => 'fcs-demo-superadmin@mailinator.com',
        'adminPassword' => 'foodcoopshop',

        /**
         * set to true if you want to receive debug emails when exceptions are triggered
         */
        'emailErrorLoggingEnabled' => false,
    ]
];
