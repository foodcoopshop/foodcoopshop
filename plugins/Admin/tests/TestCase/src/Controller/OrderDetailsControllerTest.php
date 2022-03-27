<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;

class OrderDetailsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testAccessIndexIsCustomerAllowedToViewOwnOrdersTrue()
    {
        Configure::write('app.isCustomerAllowedToViewOwnOrders', true);
        $this->loginAsCustomer();
        $this->get($this->Slug->getOrderDetailsList());
        $this->assertResponseOk();
    }

    public function testAccessIndexIsCustomerAllowedToViewOwnOrdersFalse()
    {
        Configure::write('app.isCustomerAllowedToViewOwnOrders', false);
        $this->loginAsCustomer();
        $this->get($this->Slug->getOrderDetailsList());
        $this->assertAccessDeniedFlashMessage();
    }

}