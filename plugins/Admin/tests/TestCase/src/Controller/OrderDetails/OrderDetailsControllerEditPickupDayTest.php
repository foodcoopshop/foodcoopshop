<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\OrderDetailsControllerTestCase;
use Cake\Core\Configure;
use Cake\TestSuite\TestEmailTransport;

class OrderDetailsControllerEditPickupDayTest extends OrderDetailsControllerTestCase
{

    public function testEditPickupDayAsSuperadminNoOrderDetailIds()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([], '2018-01-01', 'asdf', true);
        $this->assertRegExpWithUnquotedString('error - no order detail id passed', $response->msg);
        $this->assertJsonError();
    }

    public function testEditPickupDayAsSuperadminWrongOrderDetailIds()
    {
        $this->loginAsSuperadmin();
        $response = $this->editPickupDayOfOrderDetails([200,40], '2018-01-01', 'asdf', true);
        $this->assertRegExpWithUnquotedString('error - order details wrong', $response->msg);
        $this->assertJsonError();
    }

    public function testEditPickupDayAsSuperadminOk()
    {
        $this->loginAsSuperadmin();
        $reason = 'this is the reason';
        $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-09-07', $reason, true);
        $this->assertJsonOk();
        $this->runAndAssertQueue();
        $this->assertMailContainsHtmlAt(0, $reason);
        $this->assertMailContainsHtmlAt(0, 'Neuer Abholtag : <b>Freitag, 07.09.2018</b>');
        $this->assertMailContainsHtmlAt(0, 'Alter Abholtag: Freitag, 02.02.2018');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailSuperadmin'));
        $this->assertMailSubjectContainsAt(0, 'Der Abholtag deiner Bestellung wurde geändert auf: Freitag, 07.09.2018');
    }

    public function testEditPickupDayAsSuperadminOkIsSubscribeNewsletterLinkAddedToMail()
    {
        $this->changeConfiguration('FCS_NEWSLETTER_ENABLED', 1);
        $this->changeCustomer(Configure::read('test.superadminId'), 'newsletter_enabled', 0);
        $this->loginAsSuperadmin();
        $reason = 'this is the reason';
        $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-09-07', $reason, true);
        $this->assertJsonOk();
        $this->runAndAssertQueue();
        $this->assertMailContainsAt(0, 'Du kannst unseren Newsletter <a href="' . Configure::read('app.cakeServerName') . '/admin/customers/profile">im Admin-Bereich unter "Meine Daten"</a> abonnieren.');
    }

    public function testEditPickupDayAsSuperadminWithoutEmailsOk()
    {
        $this->loginAsSuperadmin();
        $reason = 'this is the reason';
        $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-09-07', $reason, false);
        $this->assertJsonOk();
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }

    public function testEditPickupDayAsSuperadminNoReasonEmailsOk()
    {
        $this->loginAsSuperadmin();
        $reason = '';
        $this->editPickupDayOfOrderDetails([$this->orderDetailIdA, $this->orderDetailIdB], '2018-09-07', $reason, true);
        $this->assertJsonOk();
        $this->runAndAssertQueue();
        $this->assertMailCount(1);
        $email = TestEmailTransport::getMessages()[0];
        $this->assertDoesNotMatchRegularExpressionWithUnquotedString('Warum wurde der Abholtag geändert?', $email->getBodyHtml());
    }

    private function editPickupDayOfOrderDetails($orderDetailIds, $pickupDay, $reason, $sendEmail)
    {
        $this->ajaxPost(
            '/admin/order-details/editPickupDay/',
            [
                'orderDetailIds' => $orderDetailIds,
                'pickupDay' => $pickupDay,
                'editPickupDayReason' => $reason,
                'sendEmail' => $sendEmail,
            ]
        );
        return $this->getJsonDecodedContent();
    }

}