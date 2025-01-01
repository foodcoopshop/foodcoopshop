<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Services\PdfWriter\MyMemberCardPdfWriterService;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class MyMemberCardPdfWriterServiceTest extends AppCakeTestCase
{

    public function testPdfContent(): void
    {
        $customerIds = [Configure::read('test.customerId')];
        $pdfWriter = new MyMemberCardPdfWriterService();
        $pdfWriter->setData([
            'customers' => $pdfWriter->getMemberCardCustomerData($customerIds),
        ]);
        $html = $pdfWriter->writeHtml();
        $this->assertRegExpWithUnquotedString('Mitgliedskarte: <b>FoodCoop Test</b>', $html);
        $this->assertRegExpWithUnquotedString('Mitglieds-Nr.: <b>87</b>', $html);
        $this->assertRegExpWithUnquotedString('Reg.-Datum: 02.12.2014', $html);
        $this->assertRegExpWithUnquotedString('<img src="@iVBORw0KGgoAAAANSUhEUgAAAMAAAABmAQMAAACnVwy3AAAABlBMVEX///8AAABVwtN+AAAAAXRSTlMAQObYZgAAAAlwSFlzAAAOxAAADsQBlSsOGwAAADRJREFUSIljaKp8Ms/R5lOFyidHm8kHQWge5xOgIMOoxKjEqMSoxKjEqMSoxKjEqMTwkQAAyiX7oxzbA4UAAAAASUVORK5CYII=">', $html);
    }

}
