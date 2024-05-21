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

use App\Services\DeliveryRhythmService;
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

        // $_SERVER is not available in tests, therefore the logic in Application.php is not executed
        // in real world ther is no redirect to login page
        $this->assertRedirectToLoginPage();
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
                (new DeliveryRhythmService())->getDbFormattedPickupDayByDbFormattedDate(date('Y-m-d')),
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

    public function testUpdateProductsAsManufacturer()
    {
        $this->configRequest([
            'environment' => [
                'PHP_AUTH_USER' => Configure::read('test.loginEmailVegetableManufacturer'),
                'PHP_AUTH_PW' => Configure::read('test.loginPassword'),
            ]
        ]);

        $productId = 346;

        $data = [
            'data' => [
                'data' => [
                    '0' => [
                        'image' => 'no-image',
                        'remoteProductId' => $productId,
                        'name' => [
                            'name' => 'Kantwurst',
                            'unity' => '',
                            'description' => '',
                            'description_short' => 'Superscharfe Wurst',
                            'is_declaration_ok' => 0,
                        ],
                        'is_stock_product' => 0,
                        'quantity' => [
                            'stock_available_quantity' => 5,
                            'stock_available_quantity_limit' => 0,
                            'stock_available_sold_out_limit' => '',
                            'stock_available_always_available' => 0,
                            'stock_available_default_quantity_after_sending_order_lists' => '',
                        ],
                        'price' => [
                            'gross_price' => 0,
                        ],
                        'deposit' => 0,
                        'delivery_rhythm' => [
                            'delivery_rhythm_type' => 'week',
                            'delivery_rhythm_count' => 1,
                            'delivery_rhythm_first_delivery_day' => '',
                            'delivery_rhythm_order_possible_until' => '',
                        ],
                        'active' => 1,
                    ],
                ],
            ],
        ];

        $this->post('/api/updateProducts.json', $data);
        $this->assertResponseOk();
        $this->assertJsonOk();
        $this->assertResponseContains('Es wurden 1 Produkt und 0 Varianten (Bild, Name, Lagerprodukt, Anzahl, Preis, Pfand, Lieferrhythmus, Status) erfolgreich synchronisiert.');

        $productsTable = $this->getTableLocator()->get('Products');
        $product = $productsTable->find('all',
            conditions: [
                'Products.id_product' => $productId,
            ],
            contain: [
                'StockAvailables',
            ],
        )->first();
        $this->assertEquals('Kantwurst', $product->name);
        $this->assertEquals('Superscharfe Wurst', $product->description_short);
        $this->assertEquals(5, $product->stock_available->quantity);
    }

    public function testGetOrdersWrongPickupDayFormat()
    {
        $this->configRequest([
            'environment' => [
                'PHP_AUTH_USER' => Configure::read('test.loginEmailMeatManufacturer'),
                'PHP_AUTH_PW' => Configure::read('test.loginPassword'),
            ]
        ]);
        $this->get('/api/getOrders.json?pickupDay=test');
        $response = json_decode($this->_response->getBody()->__toString());
        $this->assertEquals('wrong pickupDay format', $response->error);
    }

    public function testGetOrdersOk()
    {

        $this->loginAsSuperadmin();
        $productIdA = 347; // forelle
        $productIdB = '348-11'; // rindfleisch, 0,5 kg
        $productIdC = '103'; // bratwürstel
        $this->addProductToCart($productIdA, 2);
        $this->addProductToCart($productIdB, 3);
        $this->addProductToCart($productIdC, 1);
        $this->finishCart(1, 1);

        $productsTable = $this->getTableLocator()->get('Products');
        $dummyProduct = $productsTable->newEntity([
            'delivery_rhythm_type' => 'week',
            'delivery_rhythm_count' => '1',
            'is_stock_product' => '0',
        ]);
        $nextDeliveryDay = (new DeliveryRhythmService())->getNextPickupDayForProduct($dummyProduct);

        $this->configRequest([
            'environment' => [
                'PHP_AUTH_USER' => Configure::read('test.loginEmailMeatManufacturer'),
                'PHP_AUTH_PW' => Configure::read('test.loginPassword'),
            ]
        ]);
        $this->get('/api/getOrders.json?pickupDay=' . $nextDeliveryDay);
        $response = json_decode($this->_response->getBody()->__toString());

        $this->assertEquals(4, $response->app->orders[0]->id);
        $this->assertEquals(103, $response->app->orders[0]->product_id);
        $this->assertEquals(0, $response->app->orders[0]->attribute_id);
        $this->assertEquals('Bratwürstel', $response->app->orders[0]->name);
        $this->assertEquals(1, $response->app->orders[0]->amount);
        $this->assertEquals(3, $response->app->orders[0]->order_state);
        $this->assertFalse(isset($response->app->orders[0]->unit));

        $this->assertEquals(5, $response->app->orders[1]->id);
        $this->assertEquals(348, $response->app->orders[1]->product_id);
        $this->assertEquals(11, $response->app->orders[1]->attribute_id);
        $this->assertEquals('Rindfleisch', $response->app->orders[1]->name);
        $this->assertEquals(3, $response->app->orders[1]->amount);
        $this->assertEquals(3, $response->app->orders[1]->order_state);
        $this->assertEquals('kg', $response->app->orders[1]->unit->name);
        $this->assertEquals(1.500, $response->app->orders[1]->unit->product_quantity_in_units);
        $this->assertEquals(false, $response->app->orders[1]->unit->mark_as_saved);

        $this->assertEquals(6, $response->app->orders[2]->id);
        $this->assertEquals(347, $response->app->orders[2]->product_id);
        $this->assertEquals(0, $response->app->orders[2]->attribute_id);
        $this->assertEquals('Forelle : Stück', $response->app->orders[2]->name);
        $this->assertEquals(2, $response->app->orders[2]->amount);
        $this->assertEquals(3, $response->app->orders[2]->order_state);
        $this->assertEquals('g', $response->app->orders[2]->unit->name);
        $this->assertEquals(700, $response->app->orders[2]->unit->product_quantity_in_units);
        $this->assertEquals(false, $response->app->orders[2]->unit->mark_as_saved);
        
    }

}