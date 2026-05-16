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

class StockProductsControllerTest extends AppCakeTestCase
{

    public function testIndex(): void
    {
        $unitsTable = $this->getTableLocator()->get('Units');
        $unitEntity = $unitsTable->get(8);
        $unitEntity->use_weight_as_amount = 1;
        $unitsTable->save($unitEntity);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getStockProducts());

        $this->assertResponseOk();
        $this->assertResponseContains('Lagerprodukte');
        $this->assertResponseContains('Lagerprodukt 2');
        $this->assertResponseContains('Lagerprodukt mit Varianten');
        $this->assertResponseContains('0,5 kg');
        $this->assertResponseContains('Preis');
        $this->assertResponseContains('Lagerwert');
        $this->assertResponseContains('14.985,00 €');
        $this->assertResponseContains('<td class="hide cell-id">351</td>');
        $this->assertResponseContains('product-quantity-edit-button');
        $this->assertResponseContains('<select name="manufacturerId" id="manufacturerid">');
        $this->assertResponseContains('<select name="active" id="active">');
        $this->assertResponseContains('<select name="stockFilter" id="stockfilter">');
        $this->assertResponseContains('Produkte: alle');
        $this->assertResponseContains('Produkte: Lagerstand &lt;= 0');
        $this->assertResponseContains('Produkte: auslaufend');
        $this->assertResponseContains('<option value="5">Demo Gemüse-Hersteller</option>');
        $this->assertResponseContains('<a href="/admin/stock-products?manufacturerId=5&amp;active=1&amp;stockFilter=all">Demo Gemüse-Hersteller</a>');
    }

    public function testIndexAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->get($this->Slug->getStockProducts());

        $this->assertResponseOk();
    }

    public function testIndexAsManufacturerWithEnabledStockManagementIsDenied(): void
    {
        $this->loginAsVegetableManufacturer();
        $this->get($this->Slug->getStockProducts() . '?manufacturerId=15');

        $this->assertRedirectToLoginPage();
    }

    public function testIndexAsManufacturerWithDisabledStockManagement(): void
    {
        $this->loginAsMeatManufacturer();
        $this->get($this->Slug->getStockProducts());

        $this->assertRedirectToLoginPage();
    }

    public function testManufacturerFilter(): void
    {
        $this->loginAsSuperadmin();
        $this->get($this->Slug->getStockProducts() . '?manufacturerId=5');

        $this->assertResponseOk();
        $this->assertResponseContains('<option value="5" selected="selected">Demo Gemüse-Hersteller</option>');
        $this->assertResponseContains('Lagerprodukt 2');
    }

    public function testActiveFilter(): void
    {
        $this->loginAsSuperadmin();
        $this->get($this->Slug->getStockProducts() . '?active=1');

        $this->assertResponseOk();
        $this->assertResponseContains('Produkte: aktiviert');
        $this->assertResponseContains('Lagerprodukt 2');
    }

    public function testEmptyStockFilter(): void
    {
        $stockAvailablesTable = $this->getTableLocator()->get('StockAvailables');
        $stockAvailablesTable->updateAll([
            'quantity' => -1,
        ], [
            'id_product' => 351,
            'id_product_attribute' => 0,
        ]);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getStockProducts() . '?stockFilter=empty');

        $this->assertResponseOk();
        $this->assertResponseContains('<option value="empty" selected="selected">Produkte: Lagerstand &lt;= 0</option>');
        $this->assertResponseContains('Lagerprodukt 2');
        $this->assertResponseContains('<td class="amount negative-stock" style="text-align:right;">');
        $this->assertResponseNotContains('Lagerprodukt mit Varianten');
    }

    public function testNegativeStockIsDarkRed(): void
    {
        $stockAvailablesTable = $this->getTableLocator()->get('StockAvailables');
        $stockAvailablesTable->updateAll([
            'quantity' => -1,
        ], [
            'id_product' => 351,
            'id_product_attribute' => 0,
        ]);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getStockProducts());

        $this->assertResponseOk();
        $this->assertResponseContains('Lagerprodukt 2');
        $this->assertResponseContains('<td class="amount negative-stock" style="text-align:right;">');
    }

    public function testRunningOutOfStockFilter(): void
    {
        $stockAvailablesTable = $this->getTableLocator()->get('StockAvailables');
        $stockAvailablesTable->updateAll([
            'quantity' => 0,
            'sold_out_limit' => 1000,
        ], [
            'id_product' => 351,
            'id_product_attribute' => 0,
        ]);
        $stockAvailablesTable->updateAll([
            'sold_out_limit' => 6,
        ], [
            'id_product' => 350,
            'id_product_attribute' => 13,
        ]);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getStockProducts() . '?stockFilter=running-out-of-stock');

        $this->assertResponseOk();
        $this->assertResponseContains('<option value="running-out-of-stock" selected="selected">Produkte: auslaufend</option>');
        $this->assertResponseNotContains('Lagerprodukt 2');
        $this->assertResponseContains('Lagerprodukt mit Varianten');
        $this->assertResponseContains('<td class="amount below-minimum-amount" style="text-align:right;">');
    }

    public function testInactiveProductsAreDeactivated(): void
    {
        $productsTable = $this->getTableLocator()->get('Products');
        $productsTable->updateAll([
            'active' => APP_OFF,
        ], [
            'id_product' => 351,
        ]);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getStockProducts() . '?active=0');

        $this->assertResponseOk();
        $this->assertResponseContains('<tr class="data deactivated">');
        $this->assertResponseNotContains('sub-row');
    }

    public function testWithPurchasePrices(): void
    {
        $this->changeConfiguration('FCS_PURCHASE_PRICE_ENABLED', 1);

        $this->loginAsSuperadmin();
        $this->get($this->Slug->getStockProducts());

        $this->assertResponseOk();
        $this->assertResponseContains('Einkaufspreis netto');
        $this->assertResponseContains('0,00 €');
    }

}