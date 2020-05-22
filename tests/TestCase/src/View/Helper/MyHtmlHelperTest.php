<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\View\Helper\MyHtmlHelper;
use Cake\View\View;

class MyHtmlHelperTest extends AppCakeTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->MyHtmlHelper = new MyHtmlHelper(new View());
    }

    public function testRemoveTimestampFromFileValidTimestamp()
    {
        $filename = 'asdf.jpg?1539847477';
        $result = 'asdf.jpg';
        $this->assertEquals($result, $this->MyHtmlHelper->removeTimestampFromFile($filename));
    }

    public function testRemoveTimestampFromFileNoTimestamp()
    {
        $filename = 'asdf.jpg';
        $result = 'asdf.jpg';
        $this->assertEquals($result, $this->MyHtmlHelper->removeTimestampFromFile($filename));
    }

    public function testRemoveTimestampFromFileInvalidTimestamp()
    {
        $filename = 'asdf.jpg?adfs';
        $result = 'asdf.jpg';
        $this->assertEquals($result, $this->MyHtmlHelper->removeTimestampFromFile($filename));
    }

}
