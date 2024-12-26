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

    public function testGetDepositTaxA()
    {
        $this->assertGetDepositTax(1, 1, 0.17);
    }

    public function testGetDepositTaxB()
    {
        $this->assertGetDepositTax(3, 3, 0.51);
    }

    public function testGetDepositNetA()
    {
        $this->assertGetDepositNet(1, 1, 0.83);
    }


    public function testGetDepositNetB()
    {
        $this->assertGetDepositNet(3, 3, 2.49);
    }

    private function assertGetDepositTax($gross, $amount, $expected)
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails'); 
        $result = $orderDetailsTable->getDepositTax($gross, $amount, 20);
        $result = number_format($result, 2);
        $expected = number_format($expected, 2);
        $this->assertEquals($result, $expected);
    }

    private function assertGetDepositNet($gross, $amount, $expected)
    {
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $result = $orderDetailsTable->getDepositNet($gross, $amount, 20);
        $result = number_format($result, 2);
        $expected = number_format($expected, 2);
        $this->assertEquals($result, $expected);
    }

}
