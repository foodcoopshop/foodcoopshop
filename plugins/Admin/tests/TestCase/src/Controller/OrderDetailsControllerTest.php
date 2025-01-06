<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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

    public function testAccessIndexIsCustomerAllowedToViewOwnOrdersTrue(): void
    {
        Configure::write('app.isCustomerAllowedToViewOwnOrders', true);
        $this->loginAsCustomer();
        $this->get($this->Slug->getOrderDetailsList());
        $this->assertResponseOk();
    }

    public function testAccessIndexIsCustomerAllowedToViewOwnOrdersFalse(): void
    {
        Configure::write('app.isCustomerAllowedToViewOwnOrders', false);
        $this->loginAsCustomer();
        $this->get($this->Slug->getOrderDetailsList());
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEditProductsPickedUp(): void
    {
        $this->loginAsSuperadmin();
        $result = $this->editProductsPickedUp([Configure::read('test.superadminId')], '2018-02-02', APP_ON);
        $this->assertEquals(1, $result->result->products_picked_up);
    }

    private function editProductsPickedUp($customerIds, $pickupDay, $state): ?object
    {
        $this->ajaxPost(
            '/admin/order-details/editProductsPickedUp/',
            [
                'customerIds' => $customerIds,
                'pickupDay' => $pickupDay,
                'state' => $state,
            ]
        );
        return $this->getJsonDecodedContent();
    }

}