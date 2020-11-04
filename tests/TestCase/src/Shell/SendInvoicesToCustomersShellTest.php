<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Application;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Console\CommandRunner;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;

class SendInvoicesToCustomersShellTest extends AppCakeTestCase
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

    public function testContentOfInvoice()
    {

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');

        $pickupDay = '2018-02-02';

        $this->changeConfiguration('FCS_SEND_INVOICES_TO_CUSTOMERS', 1);
        $this->loginAsSuperadmin();

        $customerId = Configure::read('test.superadminId');
        $this->addPayment($customerId, 2, 'deposit', 0, '', $pickupDay);

        // add product with price pre unit
        $productIdA = 347; // forelle
        $productIdB = '348-11'; // rindfleisch + attribute
        $this->addProductToCart($productIdA, 1);
        $this->addProductToCart($productIdB, 3);
        $this->finishCart(1, 1, '', null, $pickupDay);

        $query = 'UPDATE ' . $this->OrderDetail->getTable().' SET pickup_day = :pickupDay WHERE id_order_detail IN(4,5);';
        $params = [
            'pickupDay' => $pickupDay,
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);

        $this->get('/admin/customers/getInvoice.pdf?customerId='.$customerId.'&dateFrom=01.02.2018&dateTo=28.02.2018&outputType=html');
//         pr($this->_response);
//         exit;
        $expectedResult = file_get_contents(TESTS . 'config' . DS . 'data' . DS . 'customerInvoice.html');
        $expectedResult = $this->getCorrectedLogoPathInHtmlForPdfs($expectedResult);
        $this->assertResponseContains($expectedResult);
    }

}
