<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Lib\HelloCash\HelloCash;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class HelloCashTest extends AppCakeTestCase
{
    protected $HelloCash;

    public function setUp(): void
    {
        parent::setUp();

        Configure::write('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        Configure::write('appDb.FCS_HELLO_CASH_API_ENABLED', 1);
        Configure::write('app.helloCashRestEndpoint', 'https://private-anon-4523ba7d1d-hellocashapi.apiary-mock.com/api/v1/invoices');
        Configure::write('app.helloCashAtCredentials.username', '');
        Configure::write('app.helloCashAtCredentials.password', '');
        Configure::write('app.helloCashAtCredentials.cashier_id', 0);
        Configure::write('app.helloCashAtCredentials.test_mode', true);

        $this->HelloCash = new HelloCash();
    }

    public function tearDown(): void
    {
        $this->assertLogFilesForErrors();
    }

    public function testGetInvoices()
    {
        $response = $this->HelloCash->getRestClient()->get(
            '/invoices',
            [],
            [],
        );
        $responseObject = json_decode($response->getStringBody());
        $this->assertEquals(8639, $responseObject->invoice_id);
    }

}
