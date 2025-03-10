<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Laminas\Diactoros\UploadedFile;
use Cake\TestSuite\EmailTrait;
use Cake\I18n\DateTime;
use App\Model\Entity\Payment;
use App\Model\Entity\Configuration;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PaymentsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;
    use EmailTrait;

    public function testAddCustomerPaymentLoggedOut(): void
    {
        $this->addCustomerPayment(Configure::read('test.customerId'), 0, Payment::TYPE_PRODUCT);
        $this->assertRedirectToLoginPage();
    }

    public function testAddCustomerPaymentParameterAmountOk(): void
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addCustomerPayment(Configure::read('test.customerId'), '65,03', Payment::TYPE_PRODUCT);
        $this->assertEquals(65.03, $jsonDecodedContent->amount);
    }

    public function testAddCustomerPaymentParameterAmountWithWhitespaceOk(): void
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addCustomerPayment(Configure::read('test.customerId'), ' 24,88 ', Payment::TYPE_PRODUCT);
        $this->assertEquals(24.88, $jsonDecodedContent->amount);
    }

    public function testAddCustomerPaymentParameterAmountNegative(): void
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addCustomerPayment(Configure::read('test.customerId'), '-10', Payment::TYPE_PRODUCT);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddCustomerPaymentParameterAmountAlmostZero(): void
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addCustomerPayment(Configure::read('test.customerId'), '0,003', Payment::TYPE_PRODUCT);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddCustomerPaymentParameterAmountZero(): void
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addCustomerPayment(Configure::read('test.customerId'), '0', Payment::TYPE_PRODUCT);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddCustomerPaymentParameterAmountWrongNumber(): void
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addCustomerPayment(Configure::read('test.customerId'), '10,--', Payment::TYPE_PRODUCT);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Bitte gib eine korrekte Zahl ein', $jsonDecodedContent->msg);
    }

    public function testAddCustomerPaymentWithInvalidType(): void
    {
        $this->loginAsCustomer();
        try {
            $this->addCustomerPayment(Configure::read('test.customerId'), '10', 'invalid_type');
        } catch (\Exception $e) {
            $this->assertRegExpWithUnquotedString('payment type not correct: invalid_type', $e->getMessage());
        }
    }

    public function testAddCustomerPaymentAsCustomerForAnotherUser(): void
    {
        $this->loginAsCustomer();
        try {
            $this->addCustomerPayment(Configure::read('test.superadminId'), 10, Payment::TYPE_PRODUCT);
        } catch (\Exception $e) {
            $this->assertRegExpWithUnquotedString('user without superadmin privileges tried to insert payment for another user: ', $e->getMessage());
        }
    }

    public function testAddCustomerProductPaymentForOneself(): void
    {
        $this->loginAsCustomer();
        $this->addCustomerPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.customerId'),
            '10,5',
            Payment::TYPE_PRODUCT,
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.customerId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung wurde erfolgreich eingetragen: <b>10,50 €'
        );
    }

    public function testAddCustomerProductPaymentAsSuperadminRetailModeEnabled(): void
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $this->addCustomerPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.customerId'),
            '20,5',
            Payment::TYPE_PRODUCT,
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung für Demo Mitglied wurde erfolgreich eingetragen: <b>20,50 €'
        );

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payment = $paymentsTable->find('all',
            order: [
                'Payments.id' => 'DESC' ,
            ]
        )->first();
        $this->assertEquals(APP_ON, $payment->approval);
    }

    public function testAddCustomerProductPaymentAsSuperadminRetailModeDisabled(): void
    {
        $this->loginAsSuperadmin();
        $this->addCustomerPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.superadminId'),
            '20,5',
            Payment::TYPE_PRODUCT,
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_product_added',
            'payments',
            'Guthaben-Aufladung wurde erfolgreich eingetragen: <b>20,50 €'
        );

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payment = $paymentsTable->find('all',
            order: [
                'Payments.id' => 'DESC' ,
            ]
        )->first();
        $this->assertEquals(APP_OFF, $payment->approval);
    }

    public function testAddCustomerDepositPaymentDefinedDepositTresholdExceeded(): void
    {
        $this->loginAsCustomer();
        $this->addCustomerPayment(Configure::read('test.customerId'), '100', 'deposit');
        $addResponse = $this->getJsonDecodedContent();
        $this->assertEquals(0, $addResponse->status);
        $this->assertEquals(1, $addResponse->confirmSubmit);
    }

    public function testAddCustomerDepositPayment(): void
    {
        $this->loginAsSuperadmin();
        $this->addCustomerPaymentAndAssertIncreasedCreditBalance(
            Configure::read('test.customerId'),
            '10,7',
            Payment::TYPE_DEPOSIT,
        );
        $this->logout();

        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_deposit_customer_added',
            'payments',
            'Pfand-Rückgabe für Demo Mitglied wurde erfolgreich eingetragen: <b>10,70 €'
        );
    }

    public function testAddManufacturerDepositEmptyGlassesWithoutDate(): void
    {
        $this->addDepositToManufacturer(
            Payment::TEXT_EMPTY_GLASSES,
            'Pfand-Rücknahme (Leergebinde) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: <b>10,00 €'
        );
    }

    public function testAddManufacturerDepositEmptyGlassesWithDateToday(): void
    {
        $today = date(Configure::read('DateFormat.DateShortAlt'), strtotime(Configure::read('app.timeHelper')->getCurrentDateForDatabase()));
        $payment = $this->addDepositToManufacturer(
            Payment::TEXT_EMPTY_GLASSES,
            'Pfand-Rücknahme (Leergebinde) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: <b>10,00 €',
            $today,
        );
        $this->assertNotEquals('00:00:00', $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('TimeShortWithSeconds')));
        $this->assertEquals($today, $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')));
    }

    public function testAddManufacturerDepositEmptyGlassesWithDatePast(): void
    {
        $dateAdd = '12.12.2018';
        $payment = $this->addDepositToManufacturer(
            Payment::TEXT_EMPTY_GLASSES,
            'Pfand-Rücknahme (Leergebinde) für Demo Fleisch-Hersteller wurde erfolgreich für den <b>'.$dateAdd.'</b> eingetragen: <b>10,00 €',
            $dateAdd,
        );
        $this->assertEquals('00:00:00', $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('TimeShortWithSeconds')));
        $this->assertEquals($dateAdd, $payment->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')));
    }

    public function testAddManufacturerDepositEmptyGlassesWithDateFuture(): void
    {
        $this->loginAsSuperadmin();
        $customersTable = $this->getTableLocator()->get('Customers');
        $manufacturerId = $customersTable->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));
        $dateAdd = '01.01.2099';
        $jsonDecodedContent = $this->addManufacturerPayment($manufacturerId, 30, Payment::TYPE_DEPOSIT, $dateAdd, Payment::TEXT_EMPTY_GLASSES);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertEquals('Das Datum darf nicht in der Zukunft liegen.', $jsonDecodedContent->msg);
    }

    public function testAddManufacturerDepositMoney(): void
    {
        $this->addDepositToManufacturer(
            Payment::TEXT_MONEY,
            'Pfand-Rücknahme (Ausgleichszahlung) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: <b>10,00 €'
        );
    }

    public function testDeletePaymentLoggedOut(): void
    {
        $this->deletePayment(1);
        $this->assertRedirectToLoginPage();
    }

    public function testDeleteCustomerPaymentWithApprovalOk(): void
    {
        $this->loginAsCustomer();
        $this->addCustomerPayment(Configure::read('test.customerId'), '10.5', 'product');
        $addResponse = $this->getJsonDecodedContent();

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $paymentsTable->save(
            $paymentsTable->patchEntity(
                $paymentsTable->get($addResponse->paymentId),
                [
                    'approval' => APP_ON,
                ]
            )
        );

        $this->deletePayment($addResponse->paymentId);
        $deleteResponse = $this->getJsonDecodedContent();
        $this->assertEquals(0, $deleteResponse->status);
        $this->assertRegExpWithUnquotedString('payment id ('.$addResponse->paymentId.') not correct or already approved (approval: 1)', $deleteResponse->msg);
    }

    public function testDeletePaymentAsCustomer(): void
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $creditBalanceBeforeAddAndDelete = $customersTable->getCreditBalance(Configure::read('test.customerId'));

        $this->loginAsCustomer();
        $this->addCustomerPayment(Configure::read('test.customerId'), '10,5', 'product');
        $response = $this->getJsonDecodedContent();
        $this->deletePayment($response->paymentId);

        $creditBalanceAfterAddAndDelete = $customersTable->getCreditBalance(Configure::read('test.customerId'));
        $this->assertEquals($creditBalanceBeforeAddAndDelete, $creditBalanceAfterAddAndDelete);
    }

    public function testCsvUploadCustomerNotFoundError(): void
    {
        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', Configuration::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);
        $this->loginAsSuperadmin();
        $uploadFile = TESTS . 'config' . DS . 'data' . DS . 'bankCsvExports' . DS . 'raiffeisen.csv';
        $this->post(
            Configure::read('app.slugHelper')->getReport('product'),
            [
                'upload' => new UploadedFile(
                    $uploadFile,
                    filesize($uploadFile),
                    UPLOAD_ERR_OK,
                    'raiffeisen.csv',
                    'text/csv',
                ),
            ]

        );
        $this->assertResponseContains('Upload erfolgreich.');
        $this->assertResponseContains('name="Payments[0][id_customer]" class="select-member form-error"');
        $this->assertResponseContains('Bitte wähle ein Mitglied aus.');
    }

    public function testCsvUploadSaveNotOk(): void
    {
        $newPaymentAmount = 200;
        $newPaymentContent = 'transaction text';
        $newPaymentDate = '2019-03-03 02:51:25.165000';

        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', Configuration::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);
        $this->loginAsSuperadmin();
        $this->post(
            Configure::read('app.slugHelper')->getReport('product'),
            [
                'Payments' => [
                    [
                        'selected' => true,
                        'original_id_customer' => 0,
                        'id_customer' => 0,
                        'content' => $newPaymentContent,
                        'already_imported' => false,
                        'amount' => $newPaymentAmount,
                        'date' => $newPaymentDate,
                    ]
                ]
            ]
        );
        $this->assertResponseContains('Beim Speichern sind Fehler aufgetreten!');
        $this->assertResponseContains('name="Payments[0][id_customer]" class="select-member form-error"');
        $this->assertResponseContains('Bitte wähle ein Mitglied aus.');
    }

    public function testCsvUploadSaveOk(): void
    {
        $newPaymentCustomerId = Configure::read('test.adminId');
        $newPaymentAmount = 200;
        $newPaymentContent = 'transaction text';
        $newPaymentDate = '2019-03-03 02:51:25.165000';

        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', Configuration::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);
        $this->loginAsSuperadmin();
        $this->post(
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

        $this->assertFlashMessage('Ein Datensatz wurde erfolgreich importiert. Summe: <b>200,00 €</b>');
        $paymentsTable = $this->getTableLocator()->get('Payments');
        $payments = $paymentsTable->find('all')->toArray();
        $newPayment = $payments[2];
        $this->assertEquals(3, count($payments));
        $this->assertEquals($newPaymentCustomerId, $newPayment->id_customer);
        $this->assertEquals('product', $newPayment->type);
        $this->assertEquals($newPaymentContent, $newPayment->transaction_text);
        $newPaymentDate = new DateTime($newPaymentDate);
        $this->assertEquals($newPaymentDate->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')), $newPayment->date_transaction_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')));
        $this->assertEquals(APP_ON, $newPayment->status);
        $this->assertEquals(APP_ON, $newPayment->approval);
        $this->assertEquals(Configure::read('test.superadminId'), $newPayment->created_by);

        $this->runAndAssertQueue();
        $this->assertMailCount(1);
        $this->assertMailSubjectContainsAt(0, 'Deine Überweisung (200,00 €) wurde ins Guthaben-System übernommen.');

    }

    private function addDepositToManufacturer($depositText, $actionLogText, $dateAdd = null): Payment
    {
        $customersTable = $this->getTableLocator()->get('Customers');

        $this->loginAsSuperadmin();
        $amountToAdd = 10;
        $manufacturerId = $customersTable->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $manufacturerDepositSum = $paymentsTable->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $this->assertEmpty($manufacturerDepositSum[0]['sumDepositReturned']);

        $jsonDecodedContent = $this->addManufacturerPayment($manufacturerId, $amountToAdd, Payment::TYPE_DEPOSIT, $dateAdd, $depositText);
        $payment = $paymentsTable->find('all',
            conditions: [
                'Payments.id' =>  $jsonDecodedContent->paymentId,
            ]
        )->first();

        $this->assertEquals(1, $payment->status);
        $manufacturerDepositSum = $paymentsTable->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $this->assertEquals($amountToAdd, $manufacturerDepositSum[0]['sumDepositReturned']);
        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_deposit_manufacturer_added',
            'payments',
            $actionLogText,
        );
        return $payment;
    }

    private function addCustomerPaymentAndAssertIncreasedCreditBalance($customerId, $amountToAdd, $paymentType): void
    {
        $customersTable = $this->getTableLocator()->get('Customers');
        $creditBalanceBeforeAdd = $customersTable->getCreditBalance($customerId);
        $jsonDecodedContent = $this->addCustomerPayment($customerId, $amountToAdd, $paymentType);
        $creditBalanceAfterAdd = $customersTable->getCreditBalance($customerId);
        $amountToAddAsDecimal = Configure::read('app.numberHelper')->getStringAsFloat($amountToAdd);

        $result = number_format($creditBalanceAfterAdd - $creditBalanceBeforeAdd, 1);
        $this->assertEquals($amountToAddAsDecimal, $result, 'add payment '.$paymentType.' did not increase credit balance');
        $this->assertEquals(1, $jsonDecodedContent->status);
        $this->assertEquals($amountToAdd, Configure::read('app.numberHelper')->formatAsDecimal($jsonDecodedContent->amount, 1));
    }

    private function assertActionLogRecord($customerId, $expectedType, $expectedObjectType, $expectedText): void
    {
        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $lastActionLog = $actionLogsTable->find('all',
            conditions: [
                'ActionLogs.customer_id' => $customerId
            ],
            order: ['ActionLogs.date' => 'DESC']
        )->toArray();
        $this->assertEquals($expectedType, $lastActionLog[0]->type, 'cake action log type not correct');
        $this->assertEquals($expectedObjectType, $lastActionLog[0]->object_type, 'cake action log object type not correct');
        $this->assertRegExpWithUnquotedString($expectedText, $lastActionLog[0]->text, 'cake action log text not correct');
    }

    private function deletePayment($paymentId): ?object
    {
        $this->ajaxPost('/admin/payments/changeStatus', [
            'paymentId' => $paymentId,
        ]);
        return $this->getJsonDecodedContent();
    }

}
