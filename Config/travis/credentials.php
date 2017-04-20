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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

/**
 * the cronjobs for sending order lists and invoices need the credentials to a valid superadmin account
 */
Configure::write('app.adminEmail', 'fcs-demo-superadmin@mailinator.com');
Configure::write('app.adminPassword', 'foodcoopshop');

/**
 * set to true if you want to receive debug emails when exceptions are triggered
 */
Configure::write('app.emailErrorLoggingEnabled', false);

/**
 * FROM address
 * emails are sent FROM this email configuration if app.emailErrorLoggingEnabled is set to true
 */
// Configure::write('debugEmailConfig', array(
//     'host' => 'mail.example.com',
//     'port' => 25,
//     'username' => 'example@example.com',
//     'password' => 'my-password',
//     'transport' => 'Smtp',
//     'from' => array(
//         'example@example.com' => 'Example'
//     ),
//     'emailFormat' => 'html'
// ));

/**
 * TO address
 * when exceptions are triggered, emails are sent TO this email address
 */
// Configure::write('app.debugEmail', 'mail@example.com');

/**
 * this email address
 * - receives the test email of url: /email /admin/configurations/sendTestEmail
 * - must have a valid customer record (which is never shown in customer list)
 * - receives the database dumps of BackupDatabase Shell
 */
// Configure::write('app.hostingEmail', 'mail@example.com');

/**
 * when the main email config in email.php is wrong, you can define this fallback email config
 * to send the emails despite a wrong main config
 * if you don't want to use the email fallback, leave it commented
 */
// Configure::write('fallbackEmailConfig', array(
// 'host' => 'mail.example.com',
// 'port' => 25,
// 'username' => 'example@example.com',
// 'password' => 'my-password',
// 'transport' => 'Smtp',
// 'from' => array('example@example.com' => 'Example'),
// 'emailFormat' => 'html'
// ));

/**
 * for easy debugging on live systems
 * just add ?debug=2&pass=<app.debugPasswordForUrl> to any url and the debug mode is turned on
 */
// Configure::write('app.debugPasswordForUrl', 'my-password');
