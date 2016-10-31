<?php
/**
 * custom.config.default.php
 * 
 * - this file contains the specific configuration for your foodcoop
 * - configurations in app.config.php can be overriden in this file
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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

/**
 * please create a unique cookie key and put it here
 */
Configure::write('app.cookieKey', '');

/**
 * A random string used in security hashing methods.
 */
Configure::write('Security.salt', '');

/**
 * A random numeric string (digits only) used to encrypt/decrypt strings.
 */
Configure::write('Security.cipherSeed', '');


/**
 * the name of your organisation, will be used in page title, email footer
 * and on many other places
 */
Configure::write('app.name', 'Demo Foodcoop');

/**
 * page title suffix for document title
 * please leave it as it is if you do not know what it is
 */
Configure::write('app.titleSuffix', Configure::read('app.name'));

/**
 * address configuration (for displaying address in generated pdfs and the footer of the homepage
 * ATTENTION:
 * - last element needs to be a valid email address!
 * - line separator must be \n
 */
$addressForPdf = "\n\nFoodCoopShop Demo";
$addressForPdf .= "\nDemostraße 4";
$addressForPdf .= "\n4644 Demostadt";
$addressForPdf .= "\nmail@example.com";
Configure::write('app.addressForPdf', $addressForPdf);

/**
 * defines how many days after the app.sendOrderListsWeekday the products are delivered
 * app.sendOrderListsWeekday is defined in app.config.php and preset to 3, i.e. "wednesday"
 */
Configure::write('app.deliveryDayDelta', 2);

/**
 * optional: message that is displayed in the dialog where order status can be changed (/admin/orders)
 */
Configure::write('app.additionalOrderStatusChangeInfo', '');

/**
 * your host's name, eg. http://www.yourfoodcoop.com
 */
Configure::write('app.cakeServerName', '');

/**
 * array of email adresses that receive notifications after new member registrations
 */
Configure::write('app.registrationNotificationEmails', array());

Configure::write('app.memberFeeEnabled', true);

/**
 * cronjob needs to be activated too
 */
Configure::write('app.emailOrderReminderEnabled', true);

/**
 * valid options of array: 'cashless' or 'cash' (or both but this is not recommended)
 */
Configure::write('app.paymentMethods', array(
    'cashless'
));

?>