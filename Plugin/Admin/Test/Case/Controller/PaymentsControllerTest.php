<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('CakeActionLog', 'Model');

/**
 * PaymentsControllerTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PaymentsControllerTest extends AppCakeTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->CakeActionLog= new CakeActionLog();
    }

    public function testAddPaymentLoggedOut()
    {
        $this->addPayment(Configure::read('test.customerId'), 0, 'product');
        $this->assert403ForbiddenHeader();
        $this->assertEmpty($this->browser->getContent());
    }

    public function testAddPaymentParameterPrice()
    {
        $this->loginAsCustomer();

        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '-10', 'product');
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Ein negativer Betrag ist nicht erlaubt', $jsonDecodedContent->msg);

        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '10,--', 'product');
        $this->assertEquals(1, $jsonDecodedContent->status);
        $this->assertEquals(10, $jsonDecodedContent->amount);

        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '10.00', 'product');
        $this->assertEquals(10, $jsonDecodedContent->amount);

        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '65,03', 'product');
        $this->assertEquals(65.03, $jsonDecodedContent->amount);
    }

    public function testAddPaymentWithInvalidType()
    {
        $this->loginAsCustomer();

        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '10', 'invalid_type');
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('payment type not correct: invalid_type', $jsonDecodedContent->msg);
    }

    public function testAddPaymentAsCustomerForAnotherUser()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.superadminId'), 10, 'product');
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('user without superadmin privileges tried to insert payment for another user: ', $jsonDecodedContent->msg);
    }

    public function testAddPaymentForOneself()
    {
        $this->loginAsCustomer();
        $this->addPaymentAndAssert(
            Configure::read('test.customerId'),
            10.5
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.customerId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung wurde erfolgreich eingetragen: €&nbsp;10,50'
        );
    }

    public function testAddPaymentAsSuperadminForAnotherUser()
    {
        $this->loginAsSuperadmin();
        $this->addPaymentAndAssert(
            Configure::read('test.customerId'),
            20.5
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung für Demo Mitglied wurde erfolgreich eingetragen: €&nbsp;20,50'
        );
    }

    public function testAddDepositPaymentAsManufacturer()
    {
    }
    
    private function addPaymentAndAssert($customerId, $amountToAdd)
    {
        $creditBalanceBeforeAdd = $this->Customer->getCreditBalance($customerId);
        $this->addPayment($customerId, $amountToAdd, 'product');
        $creditBalanceAfterAdd = $this->Customer->getCreditBalance($customerId);
        $this->assertEquals($amountToAdd, $creditBalanceAfterAdd - $creditBalanceBeforeAdd, 'add payment product did not increase credit balance');
    }

    private function assertActionLogRecord($customerId, $expectedType, $expectedObjectType, $expectedText)
    {
        $lastActionLog = $this->CakeActionLog->find('all', array(
            'conditions' => array(
                'CakeActionLog.customer_id' => $customerId
            ),
            'order' => array('CakeActionLog.date' => 'DESC')
        ));
        $this->assertEquals($expectedType, $lastActionLog[0]['CakeActionLog']['type'], 'cake action log type not correct');
        $this->assertEquals($expectedObjectType, $lastActionLog[0]['CakeActionLog']['object_type'], 'cake action log object type not correct');
        $this->assertRegExpWithUnquotedString($expectedText, $lastActionLog[0]['CakeActionLog']['text'], 'cake action log text not correct');
    }

    /**
     * @param int $productId
     * @return json string
     */
    private function addPayment($customerId, $amount, $type)
    {
        $this->browser->ajaxPost('/admin/payments/add', array(
            'data' => array(
                'customerId' => $customerId,
                'amount' => $amount,
                'type' => $type
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }
}
