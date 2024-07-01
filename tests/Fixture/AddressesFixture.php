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

class AddressesFixture extends AppFixture
{
    public string $table = 'fcs_address';

    public array $records = [
        [
            'id_address' => 153,
            'id_customer' => 87,
            'id_manufacturer' => 0,
            'lastname' => 'Mitglied',
            'firstname' => 'Demo',
            'address1' => 'Demostrasse 4',
            'address2' => '',
            'postcode' => '4644',
            'city' => 'Scharnstein',
            'comment' => '',
            'phone' => '',
            'phone_mobile' => '0664/000000000',
            'email' => 'fcs-demo-mitglied@mailinator.com',
            'date_add' => '2014-12-02 12:19:31',
            'date_upd' => '2014-12-02 12:19:31',
        ],
        [
            'id_address' => 154,
            'id_customer' => 88,
            'id_manufacturer' => 0,
            'lastname' => 'Admin',
            'firstname' => 'Demo',
            'address1' => 'Demostrasse 4',
            'address2' => '',
            'postcode' => '4644',
            'city' => 'Scharnstein',
            'comment' => 'test',
            'phone' => '',
            'phone_mobile' => '0600/000000',
            'email' => 'fcs-demo-admin@mailinator.com',
            'date_add' => '2014-12-02 12:28:44',
            'date_upd' => '2014-12-02 12:28:44',
        ],
        [
            'id_address' => 173,
            'id_customer' => 0,
            'id_manufacturer' => 4,
            'lastname' => 'Fleisch-Hersteller',
            'firstname' => 'Demo',
            'address1' => 'Demostrasse 4',
            'address2' => '',
            'postcode' => '4644',
            'city' => 'Scharnstein',
            'comment' => '',
            'phone' => '',
            'phone_mobile' => '',
            'email' => 'fcs-demo-fleisch-hersteller@mailinator.com',
            'date_add' => '2014-05-27 22:20:18',
            'date_upd' => '2015-04-07 16:18:28',
        ],
        [
            'id_address' => 177,
            'id_customer' => 0,
            'id_manufacturer' => 15,
            'lastname' => 'Milch-Hersteller',
            'firstname' => 'Demo',
            'address1' => 'Demostrasse 4',
            'address2' => '',
            'postcode' => '4644',
            'city' => 'Scharnstein',
            'comment' => '',
            'phone' => '',
            'phone_mobile' => '',
            'email' => 'fcs-demo-milch-hersteller@mailinator.com',
            'date_add' => '2014-06-04 21:46:38',
            'date_upd' => '2015-10-16 10:06:52',
        ],
        [
            'id_address' => 180,
            'id_customer' => 0,
            'id_manufacturer' => 5,
            'lastname' => 'Gemüse-Hersteller',
            'firstname' => 'Demo',
            'address1' => 'Demostrasse 4',
            'address2' => '',
            'postcode' => '4644',
            'city' => 'Scharnstein',
            'comment' => '',
            'phone' => '',
            'phone_mobile' => '',
            'email' => 'fcs-demo-gemuese-hersteller@mailinator.com',
            'date_add' => '2014-05-14 21:20:05',
            'date_upd' => '2015-12-30 00:54:35',
        ],
        [
            'id_address' => 181,
            'id_customer' => 0,
            'id_manufacturer' => 16,
            'lastname' => 'Hersteller ohne Customer-Eintrag',
            'firstname' => 'Demo',
            'address1' => 'Demostrasse 4',
            'address2' => '',
            'postcode' => '4644',
            'city' => 'Scharnstein',
            'comment' => '',
            'phone' => '',
            'phone_mobile' => '',
            'email' => 'fcs-hersteller-ohne-customer-eintrag@mailinator.com',
            'date_add' => '2014-05-14 21:20:05',
            'date_upd' => '2015-12-30 00:54:35',
        ],
        [
            'id_address' => 182,
            'id_customer' => 92,
            'id_manufacturer' => 0,
            'lastname' => 'Superadmin',
            'firstname' => 'Demo',
            'address1' => 'Demostrasse 4',
            'address2' => '',
            'postcode' => '4644',
            'city' => 'Demostadt',
            'comment' => NULL,
            'phone' => '',
            'phone_mobile' => '0600/000000',
            'email' => 'fcs-demo-superadmin@mailinator.com',
            'date_add' => '2017-07-26 13:19:19',
            'date_upd' => '2017-07-26 13:19:19',
        ],
        [
            'id_address' => 183,
            'id_customer' => 93,
            'id_manufacturer' => 0,
            'lastname' => 'SB-Kunde',
            'firstname' => 'Demo',
            'address1' => 'Demostrasse 4',
            'address2' => '',
            'postcode' => '4644',
            'city' => 'Demostadt',
            'comment' => NULL,
            'phone' => '',
            'phone_mobile' => '0600/000000',
            'email' => 'fcs-demo-sb-kunde@mailinator.com',
            'date_add' => '2017-07-26 13:19:19',
            'date_upd' => '2017-07-26 13:19:19',
        ],
    ];

}
?>