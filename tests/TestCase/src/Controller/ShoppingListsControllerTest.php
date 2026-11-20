<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Test\Fixture\ProductsFixture;
use App\Test\Fixture\ShoppingListsFixture;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;

class ShoppingListsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    /**
     * @return \App\Model\Table\ShoppingListsTable
     */
    private function getShoppingListsTable()
    {
        return $this->getTableLocator()->get('ShoppingLists');
    }

    private function getWeeklyListUrl(): string
    {
        return $this->Slug->getShoppingListDetail(ShoppingListsFixture::ID_CUSTOMER_WEEKLY, 'Wocheneinkauf');
    }

    private function changeProductStatus(int $productId, int $status): void
    {
        $this->getTableLocator()->get('Products')->updateAll(['active' => $status], ['id_product' => $productId]);
    }

    public function testDetailLoggedOut(): void
    {
        $this->get($this->getWeeklyListUrl());
        $this->assertRedirectToLoginPage();
    }

    public function testDetailAsManufacturer(): void
    {
        $this->loginAsVegetableManufacturer();
        $this->get($this->getWeeklyListUrl());
        $this->assertAccessDeniedFlashMessage();
    }

    public function testDetailAsCustomer(): void
    {
        $this->loginAsCustomer();
        $this->get($this->getWeeklyListUrl());
        $this->assertResponseOk();
        $this->assertResponseContains('Wocheneinkauf');
        $this->assertResponseContains('Artischocke');
        $this->assertResponseContains('Milch');
        // buttons "add to cart" and "replace cart" (checked without the labels, they change often)
        $this->assertResponseContains('action="' . $this->Slug->getAddShoppingListToCart(ShoppingListsFixture::ID_CUSTOMER_WEEKLY) . '"');
        $this->assertResponseContains('name="emptyCart" value="0"');
        $this->assertResponseContains('name="emptyCart" value="1"');
        $this->assertResponseContains('action="' . $this->Slug->getShoppingListRemoveAllProducts(ShoppingListsFixture::ID_CUSTOMER_WEEKLY) . '"');
        // amounts of the list are preset via javascript
        $expectedAmounts = json_encode([(string) ProductsFixture::ID_ARTICHOKE => 2, ProductsFixture::ID_MILK_0_5L => 1], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT);
        $this->assertResponseContains('ShoppingListPage.init(' . ShoppingListsFixture::ID_CUSTOMER_WEEKLY . ', ' . $expectedAmounts . ')');
        // sidebar
        $this->assertResponseContains('id="shopping-lists-menu"');
        $this->assertResponseNotContains('Superadmin-Liste');
    }

    public function testSidebarNotShownForGuests(): void
    {
        $this->changeConfiguration('FCS_SHOW_PRODUCTS_FOR_GUESTS', true);
        $this->get($this->Slug->getAllProducts());
        $this->assertResponseOk();
        $this->assertResponseNotContains('id="shopping-lists-menu"');
        $this->assertResponseNotContains('Wocheneinkauf');
    }

    public function testSidebarShowsOnlyListsOfLoggedInCustomer(): void
    {
        $this->loginAsSuperadmin();
        $this->get($this->Slug->getAllProducts());
        $this->assertResponseContains('id="shopping-lists-menu"');
        $this->assertResponseContains('Superadmin-Liste');
        $this->assertResponseNotContains('Wocheneinkauf');
    }

    public function testDetailWrongSlugRedirects(): void
    {
        $this->loginAsCustomer();
        $this->get('/einkaufsliste/' . ShoppingListsFixture::ID_CUSTOMER_WEEKLY . '-falscher-name');
        $this->assertRedirect($this->getWeeklyListUrl());
    }

    public function testDetailListOfOtherCustomer(): void
    {
        $this->loginAsCustomer();
        $this->get($this->Slug->getShoppingListDetail(ShoppingListsFixture::ID_SUPERADMIN, 'Superadmin-Liste'));
        $this->assertResponseCode(404);
    }

    public function testDetailShowsUnavailableProducts(): void
    {
        $this->changeProductStatus(ProductsFixture::ID_ARTICHOKE, APP_OFF);
        $this->loginAsCustomer();
        $this->get($this->getWeeklyListUrl());
        $this->assertResponseContains('Folgende Produkte dieser Einkaufsliste sind derzeit nicht bestellbar: <b>Artischocke</b>');
    }

    public function testDetailShowsDeletedProducts(): void
    {
        $this->changeProductStatus(ProductsFixture::ID_ARTICHOKE, APP_DEL);
        $this->loginAsCustomer();
        $this->get($this->getWeeklyListUrl());
        $this->assertResponseContains('Folgende Produkte dieser Einkaufsliste sind derzeit nicht bestellbar: <b>Artischocke</b>');
    }

    public function testDetailShowsDeletedAttributes(): void
    {
        $this->getTableLocator()->get('ProductAttributes')->deleteProductAttribute(60, 10);
        $this->loginAsCustomer();
        $this->get($this->getWeeklyListUrl());
        $this->assertResponseContains('Folgende Produkte dieser Einkaufsliste sind derzeit nicht bestellbar: <b>Milch : gelöschte Variante</b>');
    }

    public function testRemoveUnavailableAttribute(): void
    {
        $this->getTableLocator()->get('ProductAttributes')->deleteProductAttribute(60, 10);
        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListRemoveUnavailableProducts(ShoppingListsFixture::ID_CUSTOMER_WEEKLY));
        $this->assertFlashMessage('1 Produkt wurde von der Einkaufsliste entfernt.');
        $this->assertEquals([(string) ProductsFixture::ID_ARTICHOKE], $this->getShoppingListsTable()->getCompositeProductIdsForCustomer(Configure::read('test.customerId')));
    }

    public function testRemoveUnavailableProducts(): void
    {
        $this->changeProductStatus(ProductsFixture::ID_ARTICHOKE, APP_OFF);
        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListRemoveUnavailableProducts(ShoppingListsFixture::ID_CUSTOMER_WEEKLY));
        $this->assertRedirect($this->getWeeklyListUrl());
        $this->assertFlashMessage('1 Produkt wurde von der Einkaufsliste entfernt.');
        $this->assertEquals([ProductsFixture::ID_MILK_0_5L], $this->getShoppingListsTable()->getCompositeProductIdsForCustomer(Configure::read('test.customerId')));
    }

    public function testRemoveAllProducts(): void
    {
        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListRemoveAllProducts(ShoppingListsFixture::ID_CUSTOMER_WEEKLY));
        $this->assertRedirect($this->getWeeklyListUrl());
        $this->assertFlashMessage('2 Produkte wurden von der Einkaufsliste entfernt.');
        $this->assertEquals([], $this->getShoppingListsTable()->getCompositeProductIdsForCustomer(Configure::read('test.customerId')));
        // list itself is not deleted
        $this->assertEquals(1, $this->getShoppingListsTable()->find()->where(['id' => ShoppingListsFixture::ID_CUSTOMER_WEEKLY])->count());
    }

    public function testRemoveAllProductsOfOtherCustomer(): void
    {
        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListRemoveAllProducts(ShoppingListsFixture::ID_SUPERADMIN));
        $this->assertResponseCode(404);
    }

    public function testRemoveAllProductsWithGetNotAllowed(): void
    {
        $this->loginAsCustomer();
        $this->get($this->Slug->getShoppingListRemoveAllProducts(ShoppingListsFixture::ID_CUSTOMER_WEEKLY));
        $this->assertResponseCode(405);
    }

    public function testCartDetailShowsSaveAsShoppingListButton(): void
    {
        $this->loginAsCustomer();
        $this->addProductToCart(ProductsFixture::ID_ARTICHOKE, 1);
        $this->get($this->Slug->getCartDetail());
        $this->assertResponseContains('Als Einkaufsliste speichern');
        $this->assertResponseContains('Willst du deinen Warenkorb als Einkaufsliste „Meine Einkaufsliste“ speichern?');
    }

    public function testCartDetailHidesSaveAsShoppingListButtonIfCartIsEmpty(): void
    {
        $this->loginAsCustomer();
        $this->get($this->Slug->getCartDetail());
        $this->assertResponseNotContains('Als Einkaufsliste speichern');
    }

    public function testSaveCart(): void
    {
        $this->loginAsCustomer();
        $this->addProductToCart(ProductsFixture::ID_ARTICHOKE, 3);
        $this->addProductToCart(ProductsFixture::ID_MILK_0_5L, 2);
        $this->post($this->Slug->getSaveCartAsShoppingList());

        $shoppingList = $this->getShoppingListsTable()->find()
            ->where(['id_customer' => Configure::read('test.customerId'), 'name' => 'Meine Einkaufsliste'])
            ->firstOrFail();
        $this->assertRedirect($this->Slug->getShoppingListDetail($shoppingList->id, $shoppingList->name));
        $this->assertFlashMessage('Dein Warenkorb wurde als Einkaufsliste <b>Meine Einkaufsliste</b> gespeichert.');
        $this->assertEquals([
            (string) ProductsFixture::ID_ARTICHOKE => 3,
            ProductsFixture::ID_MILK_0_5L => 2,
        ], $this->getShoppingListsTable()->getAmountsByCompositeProductId($shoppingList));

        // second time: automatic name with number
        $this->post($this->Slug->getSaveCartAsShoppingList());
        $this->assertFlashMessage('Dein Warenkorb wurde als Einkaufsliste <b>Meine Einkaufsliste #2</b> gespeichert.');
    }

    public function testSaveCartWithEmptyCart(): void
    {
        $this->loginAsCustomer();
        $this->post($this->Slug->getSaveCartAsShoppingList());
        $this->assertRedirect($this->Slug->getCartDetail());
        $this->assertFlashMessage('Dein Warenkorb ist leer.');
        $this->assertEquals(1, $this->getShoppingListsTable()->find()->where(['id_customer' => Configure::read('test.customerId')])->count());
    }

    public function testSaveCartWithGetNotAllowed(): void
    {
        $this->loginAsCustomer();
        $this->get($this->Slug->getSaveCartAsShoppingList());
        $this->assertResponseCode(405);
    }

    public function testSaveCartAsManufacturer(): void
    {
        $this->loginAsVegetableManufacturer();
        $this->post($this->Slug->getSaveCartAsShoppingList());
        $this->assertAccessDeniedFlashMessage();
    }

    public function testEdit(): void
    {
        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListEdit(ShoppingListsFixture::ID_CUSTOMER_WEEKLY), [
            'name' => 'Neuer Name',
            'position' => 3,
            'is_default' => 0,
            'id_customer' => Configure::read('test.superadminId'),
        ]);
        $this->assertRedirect($this->Slug->getShoppingListDetail(ShoppingListsFixture::ID_CUSTOMER_WEEKLY, 'Neuer Name'));

        $shoppingList = $this->getShoppingListsTable()->get(ShoppingListsFixture::ID_CUSTOMER_WEEKLY);
        $this->assertEquals('Neuer Name', $shoppingList->name);
        $this->assertEquals(3, $shoppingList->position);
        $this->assertFalse($shoppingList->is_default);
        $this->assertEquals(Configure::read('test.customerId'), $shoppingList->id_customer);
    }

    public function testEditDuplicateName(): void
    {
        $shoppingList = $this->getShoppingListsTable()->newEntity(['name' => 'Grillen']);
        $shoppingList->id_customer = Configure::read('test.customerId');
        $this->getShoppingListsTable()->saveOrFail($shoppingList);

        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListEdit(ShoppingListsFixture::ID_CUSTOMER_WEEKLY), [
            'name' => 'grillen ',
        ]);
        $this->assertRedirect($this->getWeeklyListUrl());
        $this->assertFlashMessage('Beim Speichern sind Fehler aufgetreten!<ul><li>Eine Einkaufsliste mit diesem Namen gibt es schon.</li></ul>');
        $this->assertEquals('Wocheneinkauf', $this->getShoppingListsTable()->get(ShoppingListsFixture::ID_CUSTOMER_WEEKLY)->name);
    }

    public function testEditListOfOtherCustomer(): void
    {
        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListEdit(ShoppingListsFixture::ID_SUPERADMIN), [
            'name' => 'Gehackt',
        ]);
        $this->assertResponseCode(404);
        $this->assertEquals('Superadmin-Liste', $this->getShoppingListsTable()->get(ShoppingListsFixture::ID_SUPERADMIN)->name);
    }

    public function testEditWithGetNotAllowed(): void
    {
        $this->loginAsCustomer();
        $this->get($this->Slug->getShoppingListEdit(ShoppingListsFixture::ID_CUSTOMER_WEEKLY));
        $this->assertResponseCode(405);
    }

    public function testDelete(): void
    {
        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListDelete(ShoppingListsFixture::ID_CUSTOMER_WEEKLY));
        $this->assertRedirect($this->Slug->getAllProducts());
        $this->assertFlashMessage('Die Einkaufsliste <b>Wocheneinkauf</b> wurde gelöscht.');
        $this->assertCount(0, $this->getShoppingListsTable()->find('ownedBy', customerId: Configure::read('test.customerId'))->all());
        // dependent products are deleted too
        $this->assertCount(0, $this->getShoppingListsTable()->ShoppingListProducts->find()->where(['shopping_list_id' => ShoppingListsFixture::ID_CUSTOMER_WEEKLY])->all());
    }

    public function testDeleteWithGetNotAllowed(): void
    {
        $this->loginAsCustomer();
        $this->get($this->Slug->getShoppingListDelete(ShoppingListsFixture::ID_CUSTOMER_WEEKLY));
        $this->assertResponseCode(405);
    }

    public function testDeleteListOfOtherCustomer(): void
    {
        $this->loginAsCustomer();
        $this->post($this->Slug->getShoppingListDelete(ShoppingListsFixture::ID_SUPERADMIN));
        $this->assertResponseCode(404);
        $this->assertCount(2, $this->getShoppingListsTable()->find()->all());
    }

    public function testAjaxGetShoppingListsForProduct(): void
    {
        $this->loginAsCustomer();
        $this->ajaxGet('/einkaufslisten/ajaxGetShoppingListsForProduct?productId=' . ProductsFixture::ID_MILK_0_5L);
        $this->assertJsonOk();
        $response = $this->getJsonDecodedResponse();
        $this->assertCount(1, $response->shoppingLists);
        $this->assertEquals('Wocheneinkauf', $response->shoppingLists[0]->name);
        $this->assertTrue($response->shoppingLists[0]->isDefault);
        $this->assertEquals(1, $response->shoppingLists[0]->amount);
        $this->assertEquals('Meine Einkaufsliste', $response->defaultName);
    }

    public function testAjaxSaveProduct(): void
    {
        $this->loginAsCustomer();
        $this->ajaxPost('/einkaufslisten/ajaxSaveProduct', [
            'productId' => ProductsFixture::ID_GARLIC,
            'amounts' => [
                ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 2,
                ShoppingListsFixture::ID_SUPERADMIN => 2,
            ],
            'newShoppingListName' => 'Grillen',
            'newShoppingListAmount' => 2,
        ]);
        $this->assertJsonOk();
        $response = $this->getJsonDecodedResponse();
        $this->assertContains((string) ProductsFixture::ID_GARLIC, $response->productIds);
        $this->assertEquals('Das Produkt <b>Knoblauch</b> wurde auf den Einkaufslisten <b>Grillen</b>, <b>Wocheneinkauf</b> gespeichert.', $response->msg);
        // updated sidebar menu with new list and product counts
        $this->assertStringContainsString('id="shopping-lists-menu"', $response->sidebarMenu);
        $this->assertStringContainsString('Grillen <span class="additional-info"> (1)</span>', $response->sidebarMenu);
        $this->assertStringContainsString('Wocheneinkauf <span class="additional-info"> (3)</span>', $response->sidebarMenu);

        $lists = $this->getShoppingListsTable()->getListsForProduct(Configure::read('test.customerId'), ProductsFixture::ID_GARLIC, 0);
        $this->assertCount(2, $lists);
        $this->assertEquals(2, $lists[0]['amount']);
        $this->assertEquals(2, $lists[1]['amount']);

        // list of other customer stays untouched
        $this->assertEquals(1, $this->getShoppingListsTable()->ShoppingListProducts->get(3)->amount);
    }

    public function testAjaxSaveProductAttributeOnOneList(): void
    {
        $this->loginAsCustomer();
        $this->ajaxPost('/einkaufslisten/ajaxSaveProduct', [
            'productId' => ProductsFixture::ID_MILK_0_5L,
            'amounts' => [ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 3],
        ]);
        $this->assertJsonOk();
        $this->assertEquals('Das Produkt <b>Milch : 0,5l</b> wurde auf der Einkaufsliste <b>Wocheneinkauf</b> gespeichert.', $this->getJsonDecodedResponse()->msg);
    }

    public function testAjaxSaveProductRemoveFromAllLists(): void
    {
        $this->loginAsCustomer();
        $this->ajaxPost('/einkaufslisten/ajaxSaveProduct', [
            'productId' => ProductsFixture::ID_ARTICHOKE,
        ]);
        $this->assertJsonOk();
        $response = $this->getJsonDecodedResponse();
        $this->assertEquals('Das Produkt <b>Artischocke</b> wurde von der Einkaufsliste <b>Wocheneinkauf</b> entfernt.', $response->msg);
        $this->assertNotContains((string) ProductsFixture::ID_ARTICHOKE, $response->productIds);
        // used to update the shopping list page
        $this->assertEquals(ShoppingListsFixture::ID_CUSTOMER_WEEKLY, $response->shoppingLists[0]->id);
        $this->assertNull($response->shoppingLists[0]->amount);
    }

    public function testAjaxSaveProductAddedAndRemoved(): void
    {
        $this->loginAsCustomer();
        $this->ajaxPost('/einkaufslisten/ajaxSaveProduct', [
            'productId' => ProductsFixture::ID_ARTICHOKE,
            'newShoppingListName' => 'Grillen',
            'newShoppingListAmount' => 2,
        ]);
        $this->assertJsonOk();
        $this->assertEquals('Das Produkt <b>Artischocke</b> wurde auf der Einkaufsliste <b>Grillen</b> gespeichert und von der Einkaufsliste <b>Wocheneinkauf</b> entfernt.', $this->getJsonDecodedResponse()->msg);
    }

    public function testAjaxSaveProductNotChanged(): void
    {
        $this->loginAsCustomer();
        $this->ajaxPost('/einkaufslisten/ajaxSaveProduct', [
            'productId' => ProductsFixture::ID_ARTICHOKE,
            'amounts' => [ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 2],
        ]);
        $this->assertJsonOk();
        $this->assertEquals('Die Einkaufslisten des Produktes <b>Artischocke</b> wurden nicht geändert.', $this->getJsonDecodedResponse()->msg);
    }

    public function testAjaxSaveProductInvalidAmount(): void
    {
        $this->loginAsCustomer();
        $this->ajaxPost('/einkaufslisten/ajaxSaveProduct', [
            'productId' => ProductsFixture::ID_GARLIC,
            'amounts' => [ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 0],
        ]);
        $this->assertJsonError();
    }

    public function testAjaxSaveProductAsManufacturer(): void
    {
        $this->loginAsVegetableManufacturer();
        $this->ajaxPost('/einkaufslisten/ajaxSaveProduct', [
            'productId' => ProductsFixture::ID_GARLIC,
            'amounts' => [ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 1],
        ]);
        $this->assertAccessDeniedFlashMessage();
        $this->assertCount(0, $this->getShoppingListsTable()->find('ownedBy', customerId: Configure::read('test.vegetableManufacturerId'))->all());
    }

}
