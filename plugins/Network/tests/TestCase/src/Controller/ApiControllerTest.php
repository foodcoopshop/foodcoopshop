<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.8.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace Network\Test\TestCase;

use App\Lib\DeliveryRhythm\DeliveryRhythm;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\TestSuite\StringCompareTrait;

class ApiControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use StringCompareTrait;

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
            [
                DeliveryRhythm::getDbFormattedPickupDayByDbFormattedDate(date('Y-m-d')),
                json_encode(Configure::read('App.fullBaseUrl')),
            ],
            [
                '2020-01-17',
                '"{{serverName}}"',
            ],
            $this->_response->getBody()->__toString(),
        );

        $this->assertSameAsFile('products-for-demo-vegetable-manufacturer.json', $preparedResponse);
    }

}
