<?php
declare(strict_types=1);

use App\Model\Table\ConfigurationsTable;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Laminas\Diactoros\UploadedFile;
use Cake\TestSuite\EmailTrait;
use Cake\I18n\DateTime;
use App\Model\Entity\Payment;

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

    protected $ActionLog;

    use AppIntegrationTestTrait;
    use LoginTrait;
    use EmailTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->Payment = $this->getTableLocator()->get('Payments');
    }

    public function testAddPaymentLoggedOut()
    {
        $this->addPayment(Configure::read('test.customerId'), 0, Payment::TYPE_PRODUCT);
        $this->assertRedirectToLoginPage();
    }

    public function testAddPaymentParameterAmountOk()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '65,03', Payment::TYPE_PRODUCT);
        $this->assertEquals(65.03, $jsonDecodedContent->amount);
    }

    public function testAddPaymentParameterAmountWithWhitespaceOk()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), ' 24,88 ', Payment::TYPE_PRODUCT);
        $this->assertEquals(24.88, $jsonDecodedContent->amount);
    }

    public function testAddPaymentParameterAmountNegative()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '-10', Payment::TYPE_PRODUCT);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddPaymentParameterAmountAlmostZero()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '0,003', Payment::TYPE_PRODUCT);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddPaymentParameterAmountZero()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '0', Payment::TYPE_PRODUCT);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('Der Betrag muss größer als 0 sein', $jsonDecodedContent->msg);
    }

    public function testAddPaymentParameterAmountWrongNumber()
    {
        $this->loginAsCustomer();
        $jsonDecodedContent = $this->addPayment(Configure::read('test.customerId'), '10,--', Payment::TYPE_PRODUCT);
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
        $jsonDecodedContent = $this->addPayment(Configure::read('test.superadminId'), 10, Payment::TYPE_PRODUCT);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertRegExpWithUnquotedString('user without superadmin privileges tried to insert payment for another user: ', $jsonDecodedContent->msg);
    }

    public function testAddProductPaymentAsCustomerForOneself()
    {
        $this->loginAsCustomer();
        $this->addPaymentAndAssertIncreasedCreditBalance(
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

    public function testAddProductPaymentAsSuperadminRetailModeEnabled()
    {
        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);

        $this->loginAsSuperadmin();
        $this->addPaymentAndAssertIncreasedCreditBalance(
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

        $payment = $this->Payment->find('all',
            order: [
                'Payments.id' => 'DESC' ,
            ]
        )->first();
        $this->assertEquals(APP_ON, $payment->approval);
    }

    public function testAddProductPaymentAsSuperadminRetailModeDisabled()
    {
        $this->loginAsSuperadmin();
        $this->addPaymentAndAssertIncreasedCreditBalance(
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

        $payment = $this->Payment->find('all',
            order: [
                'Payments.id' => 'DESC' ,
            ]
        )->first();
        $this->assertEquals(APP_OFF, $payment->approval);
    }

    public function testAddDepositPaymentToCustomer()
    {
        $this->loginAsSuperadmin();
        $this->addPaymentAndAssertIncreasedCreditBalance(
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

    public function testAddDepositToManufacturerEmptyGlassesWithoutDate()
    {
        $this->addDepositToManufacturer(
            Payment::TEXT_EMPTY_GLASSES,
            'Pfand-Rücknahme (Leergebinde) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: <b>10,00 €'
        );
    }

    public function testAddDepositToManufacturerEmptyGlassesWithDateToday()
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

    public function testAddDepositToManufacturerEmptyGlassesWithDatePast()
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

    public function testAddDepositToManufacturerEmptyGlassesWithDateFuture()
    {
        $this->loginAsSuperadmin();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));
        $dateAdd = '01.01.2099';
        $jsonDecodedContent = $this->addPayment(0, 30, Payment::TYPE_DEPOSIT, $manufacturerId, Payment::TEXT_EMPTY_GLASSES, $dateAdd);
        $this->assertEquals(0, $jsonDecodedContent->status);
        $this->assertEquals('Das Datum darf nicht in der Zukunft liegen.', $jsonDecodedContent->msg);
    }

    public function testAddDepositToManufacturerMoney()
    {
        $this->addDepositToManufacturer(
            Payment::TEXT_MONEY,
            'Pfand-Rücknahme (Ausgleichszahlung) für Demo Fleisch-Hersteller wurde erfolgreich eingetragen: <b>10,00 €'
        );
    }

    public function testDeletePaymentLoggedOut()
    {
        $this->deletePayment(1);
        $this->assertRedirectToLoginPage();
    }

    public function testDeletePaymentWithApprovalOk()
    {
        $this->loginAsCustomer();
        $this->addPayment(Configure::read('test.customerId'), '10.5', 'product');
        $addResponse = $this->getJsonDecodedContent();

        $this->Payment->save(
            $this->Payment->patchEntity(
                $this->Payment->get($addResponse->paymentId),
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

    public function testDeletePaymentAsCustomer()
    {
        $creditBalanceBeforeAddAndDelete = $this->Customer->getCreditBalance(Configure::read('test.customerId'));

        $this->loginAsCustomer();
        $this->addPayment(Configure::read('test.customerId'), '10,5', 'product');
        $response = $this->getJsonDecodedContent();
        $this->deletePayment($response->paymentId);

        $creditBalanceAfterAddAndDelete = $this->Customer->getCreditBalance(Configure::read('test.customerId'));
        $this->assertEquals($creditBalanceBeforeAddAndDelete, $creditBalanceAfterAddAndDelete);
    }

    public function testCsvUploadCustomerNotFoundError()
    {
        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);
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

    public function testCsvUploadSaveNotOk()
    {
        $newPaymentAmount = 200;
        $newPaymentContent = 'transaction text';
        $newPaymentDate = '2019-03-03 02:51:25.165000';

        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);
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

    public function testCsvUploadSaveOk()
    {
        $newPaymentCustomerId = Configure::read('test.adminId');
        $newPaymentAmount = 200;
        $newPaymentContent = 'transaction text';
        $newPaymentDate = '2019-03-03 02:51:25.165000';

        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);
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
        $payments = $this->Payment->find('all')->toArray();
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

    private function addDepositToManufacturer($depositText, $actionLogText, $dateAdd = null)
    {
        $this->Customer = $this->getTableLocator()->get('Customers');

        $this->loginAsSuperadmin();
        $amountToAdd = 10;
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));

        $manufacturerDepositSum = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $this->assertEmpty($manufacturerDepositSum[0]['sumDepositReturned']);

        $jsonDecodedContent = $this->addPayment(0, $amountToAdd, Payment::TYPE_DEPOSIT, $manufacturerId, $depositText, $dateAdd);
        $payment = $this->Payment->find('all',
            conditions: [
                'Payments.id' =>  $jsonDecodedContent->paymentId,
            ]
        )->first();

        $this->assertEquals(1, $payment->status);
        $manufacturerDepositSum = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturerId, false);
        $this->assertEquals($amountToAdd, $manufacturerDepositSum[0]['sumDepositReturned']);
        $this->assertActionLogRecord(
            Configure::read('test.superadminId'),
            'payment_deposit_manufacturer_added',
            'payments',
            $actionLogText,
        );
        return $payment;
    }

    private function addPaymentAndAssertIncreasedCreditBalance($customerId, $amountToAdd, $paymentType)
    {
        $creditBalanceBeforeAdd = $this->Customer->getCreditBalance($customerId);
        $jsonDecodedContent = $this->addPayment($customerId, $amountToAdd, $paymentType);
        $creditBalanceAfterAdd = $this->Customer->getCreditBalance($customerId);
        $amountToAddAsDecimal = Configure::read('app.numberHelper')->getStringAsFloat($amountToAdd);

        $result = number_format($creditBalanceAfterAdd - $creditBalanceBeforeAdd, 1);
        $this->assertEquals($amountToAddAsDecimal, $result, 'add payment '.$paymentType.' did not increase credit balance');
        $this->assertEquals(1, $jsonDecodedContent->status);
        $this->assertEquals($amountToAdd, Configure::read('app.numberHelper')->formatAsDecimal($jsonDecodedContent->amount, 1));
    }

    private function assertActionLogRecord($customerId, $expectedType, $expectedObjectType, $expectedText)
    {
        $lastActionLog = $this->ActionLog->find('all',
            conditions: [
                'ActionLogs.customer_id' => $customerId
            ],
            order: ['ActionLogs.date' => 'DESC']
        )->toArray();
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
        $this->ajaxPost('/admin/payments/changeState', [
            'paymentId' => $paymentId
        ]);
        return $this->getJsonDecodedContent();
    }

}
