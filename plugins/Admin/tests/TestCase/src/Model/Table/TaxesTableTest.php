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
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TaxesTableTest extends AppCakeTestCase
{

    public $Tax;

    public function setUp(): void
    {
        parent::setUp();
        $this->Tax = $this->getTableLocator()->get('Taxes');
    }

    public function testGetNetPriceAndTaxId()
    {
        $testCases = [
            [
                'expectedNetPrice' => 90.909091,
                'expectedTaxId' => 2,
                'grossPrice' => 100,
                'taxRate' => 10,
            ],
            [
                'expectedNetPrice' => 50,
                'expectedTaxId' => 0,
                'grossPrice' => 50,
                'taxRate' => 0,
            ],
        ];

        foreach($testCases as $testCase) {
            $result = $this->Tax->getNetPriceAndTaxId($testCase['grossPrice'], $testCase['taxRate']);
            $this->assertEquals($testCase['expectedNetPrice'], $result['netPrice']);
            $this->assertEquals($testCase['expectedTaxId'], $result['taxId']);
        }

    }


}