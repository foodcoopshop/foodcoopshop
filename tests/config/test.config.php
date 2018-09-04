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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

Configure::write('test.loginEmailCustomer', 'fcs-demo-mitglied@mailinator.com');
Configure::write('test.customerId', 87);
Configure::write('test.loginEmailAdmin', 'fcs-demo-admin@mailinator.com');
Configure::write('test.adminId', 88);
Configure::write('test.loginEmailSuperadmin', 'fcs-demo-superadmin@mailinator.com');
Configure::write('test.superadminId', 92);

// id is id from table customer, use Customer::getManufacturerIdByCustomerId to obtain real manufacturerId
Configure::write('test.loginEmailVegetableManufacturer', 'fcs-demo-gemuese-hersteller@mailinator.com');
Configure::write('test.vegetableManufacturerId', 89);
Configure::write('test.loginEmailMeatManufacturer', 'fcs-demo-fleisch-hersteller@mailinator.com');
Configure::write('test.meatManufacturerId', 91);
Configure::write('test.loginEmailMilkManufacturer', 'fcs-demo-milch-hersteller@mailinator.com');
Configure::write('test.meatMilkManufacturerId', 90);

// password for all users is reset to test.loginPassword on Test::setUp()
Configure::write('test.loginPassword', 'foodcoopshop');
