<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\AssertPagesForErrorsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

class TimebasedCurrencyPaymentsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use AssertPagesForErrorsTrait;
    use LoginTrait;
    use EmailTrait;

    public function setUp(): void
    {
        parent::setUp();
        $reducedMaxPercentage = 15;
        $this->prepareTimebasedCurrencyConfiguration($reducedMaxPercentage);
    }

    public function testAddPaymentLoggedOut()
    {
        $this->addTimebasedCurrencyPayment(Configure::read('test.customerId'), 1800, 0);
        $this->assertResponseCode(403);
    }

    public function testAddPaymentAsCustomer()
    {
        $this->loginAsCustomer();
        $this->createPayment(0.5);
        $this->createPayment(3.2);

        $this->get($this->Slug->getMyTimebasedCurrencyBalanceForCustomers());
        $this->assertResponseContains('0,50 h');
        $this->assertResponseContains('3,20 h');
        $this->assertResponseContains('<b>3,70 h</b>');
    }

    public function testEditPaymentAsWrongManufacturer()
    {

        $this->loginAsCustomer();
        $this->createPayment(0.5);

        $this->loginAsVegetableManufacturer();
        $this->get($this->Slug->getTimebasedCurrencyPaymentEdit(1));
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEditPaymentAsCorrectManufacturer()
    {

        $this->loginAsCustomer();
        $this->createPayment(0.5);

        $comment = 'this is the comment';
        $hours = 0.25;
        $this->loginAsMeatManufacturer();

        $date = time();
        $this->post($this->Slug->getTimebasedCurrencyPaymentEdit(1), [
            'seconds' => $hours * 3600,
            'approval_comment' => $comment,
            'approval' => APP_DEL,
            'referer' => '/'
        ]);
        $this->assertFlashMessage('Die Zeiteintragung für den ' . date('d.m.Y', $date) . ' <b>(0,25 h)</b> von Demo Mitglied wurde geändert und eine E-Mail an Demo Mitglied verschickt.');

        $this->assertMailSubjectContainsAt(0, 'Wichtige Informationen zu deiner Zeit-Eintragung vom ' . date('d.m.Y H:i', $date));
        $this->assertMailContainsHtmlAt(0, 'Hallo Demo Mitglied,');
        $this->assertMailContainsHtmlAt(0, 'Die eingetragene Zeit wurde von <b>0,50 h</b> auf <b>0,25 h</b> angepasst.');
        $this->assertMailContainsHtmlAt(0, 'Deine Zeit-Aufladung wurde als "da stimmt was nicht..." markiert.');
        $this->assertMailSentToAt(0, Configure::read('test.loginEmailCustomer'));

    }

    public function testUrlsAsCustomer()
    {
        $this->loginAsCustomer();
        $testUrls = [
            $this->Slug->getTimebasedCurrencyPaymentAdd(Configure::read('test.customerId')),
            $this->Slug->getMyTimebasedCurrencyBalanceForCustomers(Configure::read('test.customerId'))
        ];
        $this->assertPagesForErrors($testUrls);
    }

    public function testUrlsAsManufacturer()
    {
        $this->loginAsVegetableManufacturer();
        $testUrls = [
            $this->Slug->getMyTimebasedCurrencyBalanceForManufacturers(),
            $this->Slug->getTimebasedCurrencyPaymentDetailsForManufacturers(Configure::read('test.customerId'))
        ];
        $this->assertPagesForErrors($testUrls);
    }

    private function createPayment($hours)
    {
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.meatManufacturerId'));
        $this->addTimebasedCurrencyPayment(Configure::read('test.customerId'), $hours * 3600, $manufacturerId);
    }

    /**
     * @param int $customerId
     * @param int $seconds
     * @param int $manufacturerId
     * @param string $text optional
     * @return string
     */
    private function addTimebasedCurrencyPayment($customerId, $seconds, $manufacturerId, $text = '')
    {
        $this->ajaxPost($this->Slug->getTimebasedCurrencyPaymentAdd($customerId), [
            'seconds' => $seconds,
            'id_manufacturer' => $manufacturerId,
            'text' => $text
        ]);
    }

}

?>
