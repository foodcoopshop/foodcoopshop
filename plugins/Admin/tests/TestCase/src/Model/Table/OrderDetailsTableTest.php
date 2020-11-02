<?php

use App\Test\TestCase\AppCakeTestCase;

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

class OrderDetailsTableTest extends AppCakeTestCase
{

    public $OrderDetail;

    public function setUp(): void
    {
        parent::setUp();
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
    }

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
        $result = $this->OrderDetail->getDepositTax($gross, $amount);
        $this->assertEquals($result, $expected);
    }

    private function assertGetDepositNet($gross, $amount, $expected)
    {
        $result = $this->OrderDetail->getDepositNet($gross, $amount);
        $this->assertEquals($result, $expected);
    }

}
