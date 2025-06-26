<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use League\Csv\Writer;
use App\Services\Csv\Writer\OrderDetailCsvWriterService;

class OrderDetailCsvWriterServiceTest extends AppCakeTestCase
{

    public string $defaultHeader = 'Id';

    public function testWrite(): void
    {

        $this->setDummyRequest();

        $writerService = new OrderDetailCsvWriterService();
        $writerService->setRequestQueryParams([
            'pickupDay' => [
                '2018-02-02',
            ],
        ]);
        $writerService->render();
        $result = $writerService->toString();
        $lines  = explode("\n", $result);

        $this->assertEquals(5, count($lines));
        $this->assertEquals(Writer::BOM_UTF8 . $this->defaultHeader, $lines[0]);
        $this->assertEquals('1', $lines[1]);
        $this->assertEquals('2', $lines[2]);
        $this->assertEquals('3', $lines[3]);

    }

}
