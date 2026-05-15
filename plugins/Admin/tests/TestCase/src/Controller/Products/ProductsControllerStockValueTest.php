<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\TestCase\AppCakeTestCase;

class ProductsControllerStockValueTest extends AppCakeTestCase
{

    public function testStockValue(): void
    {
        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntity = $unitsTable->get(8);
        $unitEntity->use_weight_as_amount = 1;
        $unitsTable->save($unitEntity);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getReportStockValue());

        $this->assertResponseOk();
        $this->assertResponseContains('<li class="active"><a href="/admin/products/stock-value">Lagerwert</a></li>');
        $this->assertResponseContains('Lagerprodukt 2');
        $this->assertResponseContains('Lagerprodukt mit Varianten');
        $this->assertResponseContains('0,5 kg');
        $this->assertResponseContains('Preis');
        $this->assertResponseContains('Lagerwert');
        $this->assertResponseContains('14.985,00 €');
        $this->assertResponseContains('<select name="manufacturerId" id="manufacturerid">');
        $this->assertResponseContains('<select name="active" id="active">');
        $this->assertResponseContains('Produkte: alle');
        $this->assertResponseContains('<option value="5">Demo Gemüse-Hersteller</option>');
        $this->assertResponseContains('<option value="15">Demo Milch-Hersteller</option>');
    }

    public function testStockValueManufacturerFilter(): void
    {
        $this->loginAsSuperadmin();
        $this->get($this->Slug->getReportStockValue() . '?manufacturerId=5');

        $this->assertResponseOk();
        $this->assertResponseContains('<option value="5" selected="selected">Demo Gemüse-Hersteller</option>');
        $this->assertResponseContains('Lagerprodukt 2');
    }

    public function testStockValueActiveFilter(): void
    {
        $this->loginAsSuperadmin();
        $this->get($this->Slug->getReportStockValue() . '?active=1');

        $this->assertResponseOk();
        $this->assertResponseContains('Produkte: aktiviert');
        $this->assertResponseContains('Lagerprodukt 2');
    }

    public function testStockValueInactiveProductsAreDeactivated(): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->updateAll([
            'active' => APP_OFF,
        ], [
            'id_product' => 351,
        ]);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getReportStockValue() . '?active=0');

        $this->assertResponseOk();
        $this->assertResponseContains('<tr class="data deactivated">');
        $this->assertResponseNotContains('sub-row');
    }

    public function testStockValueWithPurchasePrices(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getReportStockValue());

        $this->assertResponseOk();
        $this->assertResponseContains('Einkaufspreis netto');
        $this->assertResponseContains('0,00 €');
    }

}