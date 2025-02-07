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

use App\Services\CatalogService;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class CatalogServiceTest extends AppCakeTestCase
{

    public function testGetBarcodeWeight(): void
    {
        $this->setDummyRequest();
        $catalogService = new CatalogService();

        $barcode = '2712345000235';
        $this->assertEquals(0.023, $catalogService->getBarcodeWeight($barcode));

        $barcode = '2112345001234';
        $this->assertEquals(0.123, $catalogService->getBarcodeWeight($barcode));

    }

    public function testGetProductsWithShowOnlyProductsForNextWeekDisabled(): void
    {
        $this->setDummyRequest();
        $catalogService = new CatalogService();
        $products = $catalogService->getProducts(Configure::read('app.categoryAllProducts'));
        $this->assertCount(14, $products);
    }

    public function testGetProductsWithShowOnlyProductsForNextWeekEnabled(): void
    {

        $this->loginAsSuperadmin();
        $this->changeProductDeliveryRhythm(60, '1-week', '2100-01-15');

        $this->setDummyRequest();
        $this->loginAsCustomer();
        $catalogService = new CatalogService();
        $products = $catalogService->getProducts(Configure::read('app.categoryAllProducts'));
        $this->assertCount(13, $products);
    }

}
