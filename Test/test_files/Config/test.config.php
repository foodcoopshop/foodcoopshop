<?php
/**
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

Configure::write('test.loginEmailCustomer', 'fcs-demo-mitglied@mailinator.com');
Configure::write('test.customerId', 87);
Configure::write('test.loginEmailSuperadmin', 'fcs-demo-superadmin@mailinator.com');
Configure::write('test.superadminId', 92);
Configure::write('test.loginEmailManufacturer', 'fcs-demo-fleisch-hersteller@mailinator.com');
Configure::write('test.manufacturerId', 91); // id is id from table customer, use Customer::getManufacturerIdByCustomerId to obtain real manufacturerId

// password for all users is reset to test.loginPassword on Test::setUp()
Configure::write('test.loginPassword', 'foodcoopshop');
Configure::write('test.shopOrderTestUser', array(
    'email' => 'fcs-demo-mitglied@mailinator.com',
    'name' => 'Demo Mitglied'
));
