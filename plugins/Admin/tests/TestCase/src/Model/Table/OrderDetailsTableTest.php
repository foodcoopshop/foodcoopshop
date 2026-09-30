<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class OrderDetailsTableTest extends AppCakeTestCase
{

    public function testDepositSumsAreFloats(): void
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $paymentsTable = $this->getTableLocator()->get('Payments');
        $delivered = $orderDetailsTable->getDepositSum(0, false);
        $returned = $paymentsTable->getMonthlyDepositSumByManufacturer(0, false);

        $this->assertNotEmpty($delivered);
        $this->assertNotEmpty($returned);
        $this->assertSame('float', get_debug_type($delivered[0]['sumDepositDelivered']));
        $this->assertSame('float', get_debug_type($returned[0]['sumDepositReturned']));
    }

    public function testGetDepositTaxA(): void
    {
        $this->assertGetDepositTax(1, 1, 0.17);
    }

    public function testGetDepositTaxB(): void
    {
        $this->assertGetDepositTax(3, 3, 0.51);
    }

    public function testGetDepositNetA(): void
    {
        $this->assertGetDepositNet(1, 1, 0.83);
    }


    public function testGetDepositNetB(): void
    {
        $this->assertGetDepositNet(3, 3, 2.49);
    }

    private function assertGetDepositTax(float $gross, float $amount, float $expected): void
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails'); 
        $result = $orderDetailsTable->getDepositTax($gross, $amount, 20);
        $result = number_format($result, 2);
        $expected = number_format($expected, 2);
        $this->assertEquals($result, $expected);
    }

    private function assertGetDepositNet(float $gross, float $amount, float $expected): void
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $result = $orderDetailsTable->getDepositNet($gross, $amount, 20);
        $result = number_format($result, 2);
        $expected = number_format($expected, 2);
        $this->assertEquals($result, $expected);
    }

}
