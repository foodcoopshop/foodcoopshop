<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\OrderDetailsControllerTestCase;
use Cake\Core\Configure;

class OrderDetailsControllerEditPickupDayTest extends OrderDetailsControllerTestCase
{

    public function testEditPickupDayAsSuperadminEmptyReason()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-01-01', '');
        $this->assertRegExpWithUnquotedString('Bitte gib an, warum der Abholtag geändert wird.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditPickupDayAsSuperadminNoOrderDetailIds()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([], '2018-01-01', 'asdf');
        $this->assertRegExpWithUnquotedString('error - no order detail id passed', $response->msg);
        $this->assertJsonError();
    }

    public function testEditPickupDayAsSuperadminWrongOrderDetailIds()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([200,40], '2018-01-01', 'asdf');
        $this->assertRegExpWithUnquotedString('error - order details wrong', $response->msg);
        $this->assertJsonError();
    }

    public function testEditPickupDayAsSuperadminWrongWeekday()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-01-01', 'bla');
        $this->assertRegExpWithUnquotedString('Der Abholtag muss ein Freitag sein.', $response->msg);
        $this->assertJsonError();
    }

    public function testEditPickupDayAsSuperadminOk()
    {
        $this->loginAsSuperadmin();
        $reason = 'this is the reason';
        $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-09-07', $reason);
        $this->assertJsonOk();

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs(
            $emailLogs[0],
            'Der Abholtag deiner Bestellung wurde geändert auf: Freitag, 07.09.2018',
            [
                $reason,
                'Neuer Abholtag : <b>Freitag, 07.09.2018</b>',
                'Alter Abholtag: Freitag, 02.02.2018',
            ],
            [Configure::read('test.loginEmailSuperadmin')]
        );
    }

    private function editPickupDayOfOrderDetails($orderDetailIds, $pickupDay, $reason)
    {
        $this->httpClient->ajaxPost(
            '/admin/order-details/editPickupDay/',
            [
                'orderDetailIds' => $orderDetailIds,
                'pickupDay' => $pickupDay,
                'changePickupDayReason' => $reason
            ]
        );
        return $this->httpClient->getJsonDecodedContent();
    }

}