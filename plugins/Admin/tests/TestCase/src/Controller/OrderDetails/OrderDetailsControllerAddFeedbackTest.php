<?php
declare(strict_types=1);

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

class OrderDetailsControllerAddFeedbackTest extends OrderDetailsControllerTestCase
{

    public $orderDetailFeedback = 'Product tasted <i>great</i>! <b>Thank you</b>!<img src="/test.jpg"></img>';
    public $orderDetailId = 1;

    public function setUp(): void
    {
        parent::setUp();
        $this->changeConfiguration('FCS_FEEDBACK_TO_PRODUCTS_ENABLED', 1);
    }

    public function testAddFeedbackWithWrongOrderDetailId()
    {
        $this->loginAsSuperadmin();
        $this->addFeedbackToOrderDetail(0, '');
        $this->assertResponseCode(500);
        $this->assertResponseContains('orderDetail not found');
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
        $this->changeManufacturer(5, 'anonymize_customers', 1);
        $this->loginAsSuperadmin();
        $this->addFeedbackToOrderDetail($this->orderDetailId, $this->orderDetailFeedback);
        $this->assertJsonOk();
        $this->runAndAssertQueue();
        $this->assertMailCount(1);
        $mailIndex = 0;
        $this->assertMailSentToAt($mailIndex, Configure::read('test.loginEmailVegetableManufacturer'));
        $this->assertMailContainsHtmlAt($mailIndex, $this->orderDetailFeedback);
        $this->assertMailSubjectContainsAt($mailIndex, 'Demo Superadmin hat ein Feedback zum Produkt "Artischocke : Stück" verfasst.');
    }

    public function testAddFeedbackAsCustomerForbidden()
    {
        $this->loginAsCustomer();
        $this->addFeedbackToOrderDetail($this->orderDetailId, $this->orderDetailFeedback);
        $this->assertAccessDeniedFlashMessage();
    }

    public function testAddFeedbackAsManufacturerForbidden()
    {
        $this->loginAsVegetableManufacturer();
        $this->addFeedbackToOrderDetail($this->orderDetailId, $this->orderDetailFeedback);
        $this->assertAccessDeniedFlashMessage();
    }

    public function testAddFeedbackAsCustomerOk()
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $orderDetailsTable->save(
            $orderDetailsTable->patchEntity(
                $orderDetailsTable->get($this->orderDetailId),
                [
                    'id_customer' => Configure::read('test.customerId')
                ]
            )
        );

        $this->loginAsCustomer();
        $this->addFeedbackToOrderDetail($this->orderDetailId, $this->orderDetailFeedback);
        $this->assertJsonOk();

        $this->runAndAssertQueue();
        $this->assertMailCount(1);
        $mailIndex = 0;
        $this->assertMailSentToAt($mailIndex, Configure::read('test.loginEmailVegetableManufacturer'));
        $this->assertMailContainsHtmlAt($mailIndex, $this->orderDetailFeedback);
        $this->assertMailSubjectContainsAt($mailIndex, 'Demo Mitglied hat ein Feedback zum Produkt "Artischocke : Stück" verfasst.');
    }

    private function addFeedbackToOrderDetail($orderDetailId, $orderDetailFeedback)
    {
        $this->ajaxPost(
            '/admin/order-details/addFeedback/',
            [
                'orderDetailId' => $orderDetailId,
                'orderDetailFeedback' => $orderDetailFeedback,
            ]
        );
        return $this->getJsonDecodedContent();
    }


}