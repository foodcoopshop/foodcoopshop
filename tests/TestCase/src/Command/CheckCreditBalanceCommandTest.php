<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\I18n\DateTime;
use App\Model\Entity\Configuration;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class CheckCreditBalanceCommandTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public function testNoEmailsSent()
    {
        $this->exec('check_credit_balance');
        $this->runAndAssertQueue();
        $this->assertMailCount(0);
    }

    public function testEmailSentWithIsCashlessPaymentTypeManual() {

        $this->loginAsCustomer();
        $this->addProductToCart(346, 20);
        $this->finishCart();
        $this->logout();

        $this->resetCustomerCreditBalance();
        $this->exec('check_credit_balance');
        $this->runAndAssertQueue();

        $this->assertMailCount(2);
        $this->assertMailSubjectContainsAt(1, 'Bitte lade dein Guthaben auf');
        $this->assertMailContainsHtmlAt(1, ' dein aktuelles Guthaben beträgt <b>-46,40 €</b>');
        $this->assertMailContainsHtmlAt(1, 'Vergiss bitte nicht, diesen Betrag <b>in unser Guthaben-System einzutragen</b>, da es ansonsten zwar auf unserem Bankkonto gutgeschrieben ist, aber nicht in deinem Guthaben-System aufscheint.');
        $this->assertMailContainsHtmlAt(1, 'IBAN: AT65 5645 4154 8748 8999');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));

    }

    public function testEmailSentWithIsCashlessPaymentTypeManualReminderDisabled() {
        $this->changeCustomer(Configure::read('test.customerId'), 'check_credit_reminder_enabled', 0);
        $this->loginAsCustomer();
        $this->addProductToCart(346, 20);
        $this->finishCart();
        $this->logout();
        $this->resetCustomerCreditBalance();
        $this->exec('check_credit_balance');
        $this->runAndAssertQueue();
        $this->assertMailCount(1);
    }

    public function testEmailSentWithIsCashlessPaymentTypeListUpload() {

        $this->loginAsCustomer();
        $this->addProductToCart(346, 20);
        $this->finishCart();
        $this->logout();

        $this->resetCustomerCreditBalance();
        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', Configuration::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);

        $paymentsTable = $this->getTableLocator()->get('Payments');
        $paymentsTable->save(
            $paymentsTable->patchEntity(
                $paymentsTable->get(1),
                [
                    'date_transaction_add' => new DateTime('2020-06-22 10:22:30'),
                ]
            )
        );

        $this->exec('check_credit_balance');
        $this->runAndAssertQueue();
        $this->assertMailCount(2);
        $this->assertMailSubjectContainsAt(1, 'Bitte lade dein Guthaben auf');
        $this->assertMailContainsHtmlAt(1, 'Es wurden alle Überweisungen bis zum 03.07.2018 20:00 berücksichtigt.');
        $this->assertMailContainsHtmlAt(1, 'Bitte überweise bald wieder ein neues Guthaben auf unser Konto und vergiss nicht, deinen persönlichen Überweisungscode anzugeben: <b>7E5D5EBD</b>');
        $this->assertMailContainsHtmlAt(1, 'IBAN: AT65 5645 4154 8748 8999');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));

    }

}
