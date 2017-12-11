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
Configure::write('app.cookieKey', '77LIqHJAoVS89iRp3QKcdXaAvZTTqRdiweyZo87CoY9c4fieao5KKPWcdS');

/**
 * A random string used in security hashing methods.
 */
Configure::write('Security.salt', 'b6OSgpEV0vA36P3PxjWigmbQc6J5CLhs3bSV89KK8m1IKkl8gJfp84Odz3gMdW9K');

/**
 * A random numeric string (digits only) used to encrypt/decrypt strings.
 */
Configure::write('Security.cipherSeed', '0461186178535041377302530808327621688296');

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
Configure::write('app.cakeServerName', 'http://www.foodcoopshop.localhost');

/**
 * array of email adresses that receive notifications after new member registrations
 */
Configure::write('app.registrationNotificationEmails', array());

/**
 * whether to apply a member fee to the members account balance
 */
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
