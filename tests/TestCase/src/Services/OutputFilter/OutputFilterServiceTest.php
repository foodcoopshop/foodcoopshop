<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Services\OutputFilter\OutputFilterService;
use App\Test\TestCase\AppCakeTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class OutputFilterServiceTest extends AppCakeTestCase
{

    #[DataProvider('protectEmailAdressesDataProvider')]
    public function testProtectEmailAdresses(string $input, int $count): void
    {
        $result = OutputFilterService::protectEmailAdresses($input);
        preg_match_all('/javascript protected email address/', $result, $matches);
        $this->assertEquals(count($matches[0]), $count);
    }

    public static function protectEmailAdressesDataProvider(): array
    {
        return [
            'two-equal-emails-separated-with-space' => [
                'test@test.com test@test.online',
                2,
            ],
            'two-different-emails-separated-with-slash' => [
                'test1@test.com/test2@test.com',
                2,
            ],
        ];
    }

}
