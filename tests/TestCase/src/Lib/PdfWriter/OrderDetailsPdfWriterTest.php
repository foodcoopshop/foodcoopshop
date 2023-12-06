<?php
declare(strict_types=1);

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

use App\Controller\Component\AppAuthComponent;
use App\Test\TestCase\AppCakeTestCase;
use App\Services\PdfWriter\OrderDetailsPdfWriterService;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;

class OrderDetailsPdfWriterTest extends AppCakeTestCase
{

    public function testPdfContent()
    {
        $this->changeConfiguration('FCS_SAVE_STORAGE_LOCATION_FOR_PRODUCTS', 1);
        $pickupDay = ['2018-02-02'];
        $order = 'storageLocation';
        $appAuth = new AppAuthComponent(new ComponentRegistry(new Controller()));

        $pdfWriter = new OrderDetailsPdfWriterService();
        $pdfWriter->prepareAndSetData($appAuth, $pickupDay, $order);
        $html = $pdfWriter->writeHtml();

        $this->assertRegExpWithUnquotedString('<b>Lagerort: Keine Kühlung', $html);
        $this->assertRegExpWithUnquotedString('7,98', $html);
        $this->assertRegExpWithUnquotedString('Abholtag: 02.02.2018 / ID: 92', $html);
    }

}
