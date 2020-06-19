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
use Cake\ORM\TableRegistry;

class OrderDetailsControllerAddFeedbackTest extends OrderDetailsControllerTestCase
{

    public $orderDetailFeedback = 'Product tasted <i>great</i>! <b>Thank you</b>!<img src="/test.jpg"></img>';
    public $orderDetailId = 1;

    public function testAddFeedbackWithWrongOrderDetailId()
    {
        $this->loginAsSuperadmin();
        $response = $this->addFeedbackToOrderDetail(0, '');
        $this->assertRegExpWithUnquotedString('orderDetail not found', $response->msg);
        $this->assertJsonError();
    }

    public function testAddFeedbackWithEmptyFeedback()
    {
        $this->loginAsSuperadmin();
        $response = $this->addFeedbackToOrderDetail($this->orderDetailId, '');
        $this->assertRegExpWithUnquotedString('Bitte gib dein Feedback ein.', $response->msg);
        $this->assertJsonError();
    }

    public function testAddFeedbackAsSuperadmin()
    {
        $this->loginAsSuperadmin();
        $this->addFeedbackToOrderDetail($this->orderDetailId, $this->orderDetailFeedback);

        $expectedToEmails = [Configure::read('test.loginEmailVegetableManufacturer')];
        $expectedCcEmails = [];

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[0], 'Demo Superadmin hat ein Feedback zum Produkt "Artischocke : Stück" verfasst.', [$this->orderDetailFeedback], $expectedToEmails, $expectedCcEmails);

        $this->assertJsonOk();
    }

    public function testAddFeedbackAsCustomerForbidden()
    {
        $this->loginAsCustomerWithHttpClient();
        $this->addFeedbackToOrderDetail($this->orderDetailId, $this->orderDetailFeedback);
        $this->assertAccessDeniedMessage();
    }

    public function testAddFeedbackAsManufacturerForbidden()
    {
        $this->loginAsVegetableManufacturer();
        $this->addFeedbackToOrderDetail($this->orderDetailId, $this->orderDetailFeedback);
        $this->assertAccessDeniedMessage();
    }

    public function testAddFeedbackAsCustomerOk()
    {
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->OrderDetail->save(
            $this->OrderDetail->patchEntity(
                $this->OrderDetail->get($this->orderDetailId),
                [
                    'id_customer' => Configure::read('test.customerId')
                ]
            )
        );

        $this->loginAsCustomerWithHttpClient();
        $this->addFeedbackToOrderDetail($this->orderDetailId, $this->orderDetailFeedback);
        $expectedToEmails = [Configure::read('test.loginEmailVegetableManufacturer')];
        $expectedCcEmails = [];

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs($emailLogs[0], 'Demo Mitglied hat ein Feedback zum Produkt "Artischocke : Stück" verfasst.', [$this->orderDetailFeedback], $expectedToEmails, $expectedCcEmails);
    }

    private function addFeedbackToOrderDetail($orderDetailId, $orderDetailFeedback)
    {
        $this->httpClient->ajaxPost(
            '/admin/order-details/addFeedback/',
            [
                'orderDetailId' => $orderDetailId,
                'orderDetailFeedback' => $orderDetailFeedback,
            ]
        );
        return $this->httpClient->getJsonDecodedContent();
    }


}