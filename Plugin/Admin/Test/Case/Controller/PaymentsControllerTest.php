<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('CakeActionLog', 'Model');
App::uses('CakePayment', 'Model');
App::uses('Customer', 'Model');

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
        $this->CakeActionLog = new CakeActionLog();
        $this->CakePayment = new CakePayment();
    }

    public function testAddPaymentLoggedOut()
    {
        $this->addPayment(Configure::read('test.customerId'), 0, 'product');
        $this->assert403ForbiddenHeader();
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

    public function testAddProductPaymentForOneself()
    {
        $this->loginAsCustomer();
        $this->addPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.customerId'),
            10.5,
            'product'
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.customerId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung wurde erfolgreich eingetragen: €&nbsp;10,50'
        );
    }

    public function testAddProductPaymentAsSuperadminForAnotherUser()
    {
        $this->loginAsSuperadmin();
        $this->addPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.customerId'),
            20.5,
            'product'
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung für Demo Mitglied wurde erfolgreich eingetragen: €&nbsp;20,50'
        );
    }

    public function testAddDepositPaymentToCustomer()
    {
        $this->loginAsSuperadmin();
        $this->addPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.customerId'),
            10,
            'deposit'
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_deposit_customer_added',
            'payments',
            'Pfand-Rückgabe für Demo Mitglied wurde erfolgreich eingetragen: €&nbsp;10,00'
        );
    }

    public function testAddDepositToManufacturerEmptyGlasses()
    {
        $this->addDepositToManufacturer(
            'empty_glasses',
            'Pfand-Rücknahme (Leergebinde) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: €&nbsp;10,00'
        );
    }

    public function testAddDepositToManufacturerMoney()
    {
        $this->addDepositToManufacturer(
            'money',
            'Pfand-Rücknahme (Ausgleichszahlung) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: €&nbsp;10,00'
        );
    }

    public function testDeletePaymentLoggedOut()
    {
        $this->deletePayment(1);
        $this->assert403ForbiddenHeader();
    }

    public function testDeletePaymentWithApprovalOk()
    {
        $this->loginAsCustomer();
        $this->addPayment(Configure::read('test.customerId'), 10.5, 'product');
        $addResponse = $this->browser->getJsonDecodedContent();

        // change approval to APP_ON via sql query
        $query = 'UPDATE ' . $this->CakePayment->tablePrefix . $this->CakePayment->useTable.' SET approval = :approval WHERE id = :paymentId';
        $params = array(
            'approval' => APP_ON,
            'paymentId' => $addResponse->paymentId
        );
        $this->CakePayment->getDataSource()->fetchAll($query, $params);

        $this->deletePayment($addResponse->paymentId);
        $deleteResponse = $this->browser->getJsonDecodedContent();
        $this->assertEquals(0, $deleteResponse->status);
        $this->assertRegExpWithUnquotedString('payment id ('.$addResponse->paymentId.') not correct or already approved (approval: 1)', $deleteResponse->msg);
    }

    public function testDeletePaymentAsCustomer()
    {

        $creditBalanceBeforeAddAndDelete = $this->Customer->getCreditBalance(Configure::read('test.customerId'));

        $this->loginAsCustomer();
        $this->addPayment(Configure::read('test.customerId'), 10.5, 'product');
        $response = $this->browser->getJsonDecodedContent();
        $this->deletePayment($response->paymentId);

        $creditBalanceAfterAddAndDelete = $this->Customer->getCreditBalance(Configure::read('test.customerId'));
        $this->assertEquals($creditBalanceBeforeAddAndDelete, $creditBalanceAfterAddAndDelete);
    }

    private function addDepositToManufacturer($depositText, $cakeActionLogText)
    {
        $this->Customer = new Customer();

        $this->loginAsSuperadmin();
        $amountToAdd = 10;
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));

        $manufacturerDepositSum = $this->CakePayment->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $this->assertEmpty($manufacturerDepositSum[0][0]['sumDepositReturned']);

        $jsonDecodedContent = $this->addPayment(0, $amountToAdd, 'deposit', $manufacturerId, $depositText);
        $this->assertEquals(1, $jsonDecodedContent->status);
        $this->assertEquals($amountToAdd, $jsonDecodedContent->amount);
        $manufacturerDepositSum = $this->CakePayment->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $this->assertEquals($amountToAdd, $manufacturerDepositSum[0][0]['sumDepositReturned']);
        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_deposit_manufacturer_added',
            'payments',
            $cakeActionLogText
        );
    }

    private function addPaymentAndAssertIncreasedCreditBalance($customerId, $amountToAdd, $paymentType)
    {
        $creditBalanceBeforeAdd = $this->Customer->getCreditBalance($customerId);
        $jsonDecodedContent = $this->addPayment($customerId, $amountToAdd, $paymentType);
        $creditBalanceAfterAdd = $this->Customer->getCreditBalance($customerId);
        $this->assertEquals($amountToAdd, $creditBalanceAfterAdd - $creditBalanceBeforeAdd, 'add payment '.$paymentType.' did not increase credit balance');
        $this->assertEquals(1, $jsonDecodedContent->status);
        $this->assertEquals($amountToAdd, $jsonDecodedContent->amount);
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
     * @param int $paymentId
     * @return string
     */
    private function deletePayment($paymentId)
    {
        $this->browser->ajaxPost('/admin/payments/changeState', array(
            'data' => array(
                'paymentId' => $paymentId
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }

    /**
     * @param int $customerId
     * @param int $amount
     * @param string $type
     * @param int $manufacturerId optional
     * @param string $text optional
     * @return string
     */
    private function addPayment($customerId, $amount, $type, $manufacturerId = 0, $text = '')
    {
        $this->browser->ajaxPost('/admin/payments/add', array(
            'data' => array(
                'customerId' => $customerId,
                'amount' => $amount,
                'type' => $type,
                'manufacturerId' => $manufacturerId,
                'text' => $text
            )
        ));
        return $this->browser->getJsonDecodedContent();
    }
}
