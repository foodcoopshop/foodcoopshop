<?php

use App\Model\Table\ConfigurationsTable;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PaymentsControllerTest extends AppCakeTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->Payment = TableRegistry::getTableLocator()->get('Payments');
    }

    public function testAddPaymentLoggedOut()
    {
        $this->addPayment(Configure::read('test.customerId'), 0, 'product');
        $this->assert403ForbiddenHeader();
    }

    public function testAddPaymentParameterPriceOk()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '65,03', 'product');
        $this->assertEquals(65.03, $jsonDecodedContent->amount);
    }

    public function testAddPaymentParameterPriceWithWhitespaceOk()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), ' 24,88 ', 'product');
        $this->assertEquals(24.88, $jsonDecodedContent->amount);
    }

    public function testAddPaymentParameterPriceNegative()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '-10', 'product');
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddPaymentParameterPriceAlmostZero()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '0,003', 'product');
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddPaymentParameterPriceZero()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '0', 'product');
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddPaymentParameterPriceWrongNumber()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '10,--', 'product');
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Bitte gib eine korrekte Zahl ein', $jsonDecodedContent->msg);
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
            '10,5',
            'product'
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.customerId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung wurde erfolgreich eingetragen: <b>10,50 €'
        );
    }

    public function testAddProductPaymentAsSuperadminForAnotherUser()
    {
        $this->loginAsSuperadmin();
        $this->addPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.customerId'),
            '20,5',
            'product'
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung für Demo Mitglied wurde erfolgreich eingetragen: <b>20,50 €'
        );
    }

    public function testAddDepositPaymentToCustomer()
    {
        $this->loginAsSuperadmin();
        $this->addPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.customerId'),
            '10,7',
            'deposit'
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_deposit_customer_added',
            'payments',
            'Pfand-Rückgabe für Demo Mitglied wurde erfolgreich eingetragen: <b>10,70 €'
        );
    }

    public function testAddDepositToManufacturerEmptyGlasses()
    {
        $this->addDepositToManufacturer(
            'empty_glasses',
            'Pfand-Rücknahme (Leergebinde) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: <b>10,00 €'
        );
    }

    public function testAddDepositToManufacturerMoney()
    {
        $this->addDepositToManufacturer(
            'money',
            'Pfand-Rücknahme (Ausgleichszahlung) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: <b>10,00 €'
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
        $this->addPayment(Configure::read('test.customerId'), '10.5', 'product');
        $addResponse = $this->httpClient->getJsonDecodedContent();

        $this->Payment->save(
            $this->Payment->patchEntity(
                $this->Payment->get($addResponse->paymentId),
                [
                    'approval' => APP_ON
                ]
            )
        );

        $this->deletePayment($addResponse->paymentId);
        $deleteResponse = $this->httpClient->getJsonDecodedContent();
        $this->assertEquals(0, $deleteResponse->status);
        $this->assertRegExpWithUnquotedString('payment id ('.$addResponse->paymentId.') not correct or already approved (approval: 1)', $deleteResponse->msg);
    }

    public function testDeletePaymentAsCustomer()
    {
        $creditBalanceBeforeAddAndDelete = $this->Customer->getCreditBalance(Configure::read('test.customerId'));

        $this->loginAsCustomer();
        $this->addPayment(Configure::read('test.customerId'), '10,5', 'product');
        $response = $this->httpClient->getJsonDecodedContent();
        $this->deletePayment($response->paymentId);

        $creditBalanceAfterAddAndDelete = $this->Customer->getCreditBalance(Configure::read('test.customerId'));
        $this->assertEquals($creditBalanceBeforeAddAndDelete, $creditBalanceAfterAddAndDelete);
    }

    public function testCsvUploadCustomerNotFoundError()
    {
        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);
        $this->loginAsSuperadmin();
        $this->httpClient->post(
            Configure::read('app.slugHelper')->getReport('product'),
            [
                'upload' => fopen(TESTS . 'config' . DS . 'data' . DS . 'test-data-raiffeisen.csv', 'r')
            ]

        );
        $this->assertRegExpWithUnquotedString('name="Payments[0][id_customer]" class="select-member form-error"', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('Bitte wähle ein Mitglied aus.', $this->httpClient->getContent());
    }

    public function testCsvUploadSaveOk()
    {
        $newPaymentCustomerId = Configure::read('test.adminId');
        $newPaymentAmount = 200;
        $newPaymentContent = 'transaction text';
        $newPaymentDate = '2019-03-03 02:51:25.165000';

        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);
        $this->loginAsSuperadmin();
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->post(
            Configure::read('app.slugHelper')->getReport('product'),
            [
                'Payments' => [
                    [
                            'selected' => true,
                            'original_id_customer' => 0,
                            'id_customer' => $newPaymentCustomerId,
                            'content' => $newPaymentContent,
                            'already_imported' => false,
                            'amount' => $newPaymentAmount,
                            'date' => $newPaymentDate,
                    ]
                 ]
            ]
        );

        $this->assertRegExpWithUnquotedString('Ein Datensatz wurde erfolgreich importiert.', $this->httpClient->getContent());
        $payments = $this->Payment->find('all')->toArray();
        $newPayment = $payments[1];
        $this->assertEquals(2, count($payments));
        $this->assertEquals($newPaymentCustomerId, $newPayment->id_customer);
        $this->assertEquals('product', $newPayment->type);
        $this->assertEquals($newPaymentContent, $newPayment->transaction_text);
        $newPaymentDateFrozen = new FrozenTime($newPaymentDate);
        $this->assertEquals($newPaymentDateFrozen->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')), $newPayment->date_transaction_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')));
        $this->assertEquals(APP_ON, $newPayment->status);
        $this->assertEquals(APP_ON, $newPayment->approval);
        $this->assertEquals(Configure::read('test.superadminId'), $newPayment->created_by);
    }

    private function addDepositToManufacturer($depositText, $ActionLogText)
    {
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');

        $this->loginAsSuperadmin();
        $amountToAdd = 10;
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));

        $manufacturerDepositSum = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $this->assertEmpty($manufacturerDepositSum[0]['sumDepositReturned']);

        $jsonDecodedContent = $this->addPayment(0, $amountToAdd, 'deposit', $manufacturerId, $depositText);
        $this->assertEquals(1, $jsonDecodedContent->status);
        $this->assertEquals($amountToAdd, $jsonDecodedContent->amount);
        $manufacturerDepositSum = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $this->assertEquals($amountToAdd, $manufacturerDepositSum[0]['sumDepositReturned']);
        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_deposit_manufacturer_added',
            'payments',
            $ActionLogText
        );
    }

    private function addPaymentAndAssertIncreasedCreditBalance($customerId, $amountToAdd, $paymentType)
    {
        $creditBalanceBeforeAdd = $this->Customer->getCreditBalance($customerId);
        $jsonDecodedContent = $this->addPayment($customerId, $amountToAdd, $paymentType);
        $creditBalanceAfterAdd = $this->Customer->getCreditBalance($customerId);
        $amountToAddAsDecimal = Configure::read('app.numberHelper')->getStringAsFloat($amountToAdd);
        $this->assertEquals($amountToAddAsDecimal, $creditBalanceAfterAdd - $creditBalanceBeforeAdd, 'add payment '.$paymentType.' did not increase credit balance');
        $this->assertEquals(1, $jsonDecodedContent->status);
        $this->assertEquals($amountToAdd, Configure::read('app.numberHelper')->formatAsDecimal($jsonDecodedContent->amount, 1));
    }

    private function assertActionLogRecord($customerId, $expectedType, $expectedObjectType, $expectedText)
    {
        $lastActionLog = $this->ActionLog->find('all', [
            'conditions' => [
                'ActionLogs.customer_id' => $customerId
            ],
            'order' => ['ActionLogs.date' => 'DESC']
        ])->toArray();
        $this->assertEquals($expectedType, $lastActionLog[0]->type, 'cake action log type not correct');
        $this->assertEquals($expectedObjectType, $lastActionLog[0]->object_type, 'cake action log object type not correct');
        $this->assertRegExpWithUnquotedString($expectedText, $lastActionLog[0]->text, 'cake action log text not correct');
    }

    /**
     * @param int $paymentId
     * @return string
     */
    private function deletePayment($paymentId)
    {
        $this->httpClient->ajaxPost('/admin/payments/changeState', [
            'paymentId' => $paymentId
        ]);
        return $this->httpClient->getJsonDecodedContent();
    }

    /**
     * @param int $customerId
     * @param int $amount - strange behavior: posting a string '64,32' leads to '64.32' in controller
     * @param string $type
     * @param int $manufacturerId optional
     * @param string $text optional
     * @return string
     */
    private function addPayment($customerId, $amount, $type, $manufacturerId = 0, $text = '')
    {
        $this->httpClient->ajaxPost('/admin/payments/add', [
            'customerId' => $customerId,
            'amount' => $amount,
            'type' => $type,
            'manufacturerId' => $manufacturerId,
            'text' => $text
        ]);
        return $this->httpClient->getJsonDecodedContent();
    }
}
