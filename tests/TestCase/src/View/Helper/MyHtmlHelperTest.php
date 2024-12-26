<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\View\Helper\MyHtmlHelper;
use Cake\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

class MyHtmlHelperTest extends AppCakeTestCase
{

    protected MyHtmlHelper $MyHtmlHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->MyHtmlHelper = new MyHtmlHelper(new View());
    }

    public function testAnonymizeCustomerNameNormal()
    {
        $name = 'Demo Admin';
        $id = 1;
        $result = 'D.A. - ID 1';
        $this->assertEquals($result, $this->MyHtmlHelper->anonymizeCustomerName($name, $id));
    }

    public function testAnonymizeCustomerNameAdvanced()
    {
        $name = 'Demo-Marie Test Admin';
        $id = 1;
        $result = 'D.T.A. - ID 1';
        $this->assertEquals($result, $this->MyHtmlHelper->anonymizeCustomerName($name, $id));
    }
    
    #[DataProvider('removeTimestampFromFileDataProvider')]
    public function testRemoveTimestampFromFile(string $filename, string $result): void
    {
        $this->assertEquals($result, $this->MyHtmlHelper->removeTimestampFromFile($filename));
    }

    public static function removeTimestampFromFileDataProvider()
    {
        return [
            'correct-timestamp' => [
                'asdf.jpg?1539847477',
                'asdf.jpg',
            ],
            'no-timestamp' => [
                'asdf.jpg',
                'asdf.jpg',
            ],
            'invalid-timestamp' => [
                'asdf.jpg?adfs',
                'asdf.jpg',
            ],
        ];
    }

}
