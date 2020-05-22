<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.8.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */


/**
 * still fails on travis
 * https://travis-ci.com/foodcoopshop/foodcoopshop/jobs/283746006
 * @TODO rename filename to ApiControllerTest.php
 */

namespace Network\Test\TestCase;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\View\View;
use Network\View\Helper\NetworkHelper;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ApiControllerTest extends TestCase
{

    use IntegrationTestTrait;
    use StringCompareTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->Network = new NetworkHelper(new View());
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
    }

    protected function resetTestDatabaseData()
    {
        $this->dbConnection = ConnectionManager::get('test');
        $this->testDumpDir = TESTS . 'config' . DS . 'sql' . DS;
        $this->dbConnection->query(file_get_contents($this->testDumpDir . 'test-db-data.sql'));
        $this->resetTestDatabaseData();
        $this->Configuration->loadConfigurations();
    }

    public function testGetProductsLoggedOut()
    {
        $this->get('/api/getProducts.json');
        $this->assertResponseCode(401);
    }

    public function testGetProductsAsSuperadmin()
    {
        $this->configRequest([
            'environment' => [
                'PHP_AUTH_USER' => Configure::read('test.loginEmailSuperadmin'),
                'PHP_AUTH_PW' => Configure::read('test.loginPassword'),
            ]
        ]);
        $this->get('/api/getProducts.json');
        $this->assertResponseCode(403);
    }

    public function testGetProductsAsManufacturer()
    {
        $this->configRequest([
            'environment' => [
                'PHP_AUTH_USER' => Configure::read('test.loginEmailVegetableManufacturer'),
                'PHP_AUTH_PW' => Configure::read('test.loginPassword'),
            ]
        ]);
        $this->get('/api/getProducts.json');
        $this->assertResponseOk();
        $this->_compareBasePath = ROOT . DS . 'plugins' . DS . 'Network' . DS . 'tests' . DS . 'comparisons' . DS;
        $preparedResponse = str_replace(
            Configure::read('app.timeHelper')->getDbFormattedPickupDayByDbFormattedDate(date('Y-m-d')),
            '2020-01-17',
        $this->_response);
        $this->assertSameAsFile('products-for-demo-vegetable-manufacturer.json', $preparedResponse);
    }

}
