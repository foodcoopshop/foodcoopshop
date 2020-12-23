<?php

use App\Application;
use App\Model\Table\ConfigurationsTable;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\I18n\FrozenTime;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class CheckCreditBalanceShellTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use EmailTrait;
    use LoginTrait;

    public $commandRunner;

    public function setUp(): void
    {
        parent::setUp();
        $this->commandRunner = new CommandRunner(new Application(ROOT . '/config'));
    }

    public function testNoEmailsSent()
    {
        $this->commandRunner->run(['cake', 'check_credit_balance']);
        $this->assertMailCount(0);
    }

    public function testEmailSentWithIsCashlessPaymentTypeManual() {

        $this->loginAsCustomer();
        $this->addProductToCart(346, 20);
        $this->finishCart();
        $this->logout();

        $this->resetCustomerCreditBalance();
        $this->commandRunner->run(['cake', 'check_credit_balance']);

        $this->assertMailCount(2);
        $this->assertMailSubjectContainsAt(1, 'Dein Guthaben ist aufgebraucht');
        $this->assertMailContainsHtmlAt(1, 'Vergiss bitte nicht, diesen Betrag <b>in unser Guthaben-System einzutragen</b>, da es ansonsten zwar auf unserem Bankkonto gutgeschrieben ist, aber nicht in deinem Guthaben-System aufscheint.');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));

    }

    public function testEmailSentWithIsCashlessPaymentTypeListUpload() {

        $this->loginAsCustomer();
        $this->addProductToCart(346, 20);
        $this->finishCart();
        $this->logout();

        $this->resetCustomerCreditBalance();
        $this->changeConfiguration('FCS_CASHLESS_PAYMENT_ADD_TYPE', ConfigurationsTable::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD);

        $this->Payment = $this->getTableLocator()->get('Payments');
        $this->Payment->save(
            $this->Payment->patchEntity(
                $this->Payment->get(1),
                [
                    'date_transaction_add' => new FrozenTime('2020-06-22 10:22:30'),
                ]
            )
        );

        $this->commandRunner->run(['cake', 'check_credit_balance']);
        $this->assertMailCount(2);
        $this->assertMailSubjectContainsAt(1, 'Dein Guthaben ist aufgebraucht');
        $this->assertMailContainsHtmlAt(1, 'Es wurden alle Überweisungen bis zum 03.07.2018 20:00 berücksichtigt.');
        $this->assertMailContainsHtmlAt(1, 'Bitte überweise bald wieder ein neues Guthaben auf unser Konto und vergiss nicht, deinen persönlichen Überweisungscode anzugeben: <b>7E5D5EBD</b>');
        $this->assertMailSentToAt(1, Configure::read('test.loginEmailCustomer'));

    }

}
