<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class TimebasedCurrencyPaymentsControllerTest extends AppCakeTestCase
{

    public $EmailLog;

    public function setUp()
    {
        parent::setUp();
        $reducedMaxPercentage = 15;
        $this->prepareTimebasedCurrencyConfiguration($reducedMaxPercentage);
        $this->EmailLog = TableRegistry::getTableLocator()->get('EmailLogs');
    }

    public function testAddPaymentLoggedOut()
    {
        $this->addPayment(Configure::read('test.customerId'), 1800, 0);
        $this->assertRedirectToLoginPage();
    }

    public function testAddPaymentAsCustomer()
    {
        $this->loginAsCustomer();
        $this->createPayment(0.5);
        $this->createPayment(3.2);
        
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getMyTimebasedCurrencyBalanceForCustomers());
        $this->assertRegExpWithUnquotedString('0,50 h', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('3,20 h', $this->httpClient->getContent());
        $this->assertRegExpWithUnquotedString('<b>3,70 h</b>', $this->httpClient->getContent());
    }

    public function testEditPaymentAsWrongManufacturer()
    {

        $this->loginAsCustomer();
        $this->createPayment(0.5);

        $this->loginAsVegetableManufacturer();
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getTimebasedCurrencyPaymentEdit(1));
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testEditPaymentAsCorrectManufacturer()
    {

        $this->loginAsCustomer();
        $this->createPayment(0.5);

        $comment = 'this is the comment';
        $hours = 0.25;
        $this->loginAsMeatManufacturer();
        
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->post($this->Slug->getTimebasedCurrencyPaymentEdit(1), [
            'seconds' => $hours * 3600,
            'approval_comment' => $comment,
            'approval' => APP_DEL,
            'referer' => '/'
        ]);
        $this->assert200OkHeader();

        $emailLogs = $this->EmailLog->find('all')->toArray();
        $this->assertEmailLogs(
            $emailLogs[0],
            'Wichtige Informationen zu deiner Zeit-Eintragung vom',
            [
                'Hallo Demo Mitglied,',
                'Die eingetragene Zeit wurde von <b>0,50 h</b> auf <b>0,25 h</b> angepasst.',
                'Deine Zeit-Aufladung wurde als "da stimmt was nicht..." markiert.',
            ],
            [
                Configure::read('test.loginEmailCustomer')
            ]
        );

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
        $this->addPayment(Configure::read('test.customerId'), $hours * 3600, $manufacturerId);
    }

    /**
     * @param int $customerId
     * @param int $seconds
     * @param int $manufacturerId
     * @param string $text optional
     * @return string
     */
    private function addPayment($customerId, $seconds, $manufacturerId, $text = '')
    {
        $this->httpClient->ajaxPost($this->Slug->getTimebasedCurrencyPaymentAdd($customerId), [
            'seconds' => $seconds,
            'id_manufacturer' => $manufacturerId,
            'text' => $text
        ]);
        return $this->httpClient->getContent();
    }

}

?>