<?php
declare(strict_types=1);

use App\Test\Fixture\ProductsFixture;
use App\Test\Fixture\ShoppingListsFixture;
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

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
class ShoppingListsTableTest extends AppCakeTestCase
{

    /**
     * @var \App\Model\Table\ShoppingListsTable
     */
    protected $ShoppingLists;

    public function setUp(): void
    {
        parent::setUp();
        $this->ShoppingLists = $this->getTableLocator()->get('ShoppingLists');
    }

    private function getCustomerId(): int
    {
        return Configure::read('test.customerId');
    }

    public function testFindOwnedBy(): void
    {
        $shoppingLists = $this->ShoppingLists->find('ownedBy', customerId: $this->getCustomerId())->all();
        $this->assertCount(1, $shoppingLists);
        $this->assertEquals('Wocheneinkauf', $shoppingLists->first()->name);
    }

    public function testValidationEmptyName(): void
    {
        $shoppingList = $this->ShoppingLists->newEntity(['name' => '   ']);
        $this->assertTrue($shoppingList->hasErrors());
    }

    public function testNameIsSanitized(): void
    {
        $shoppingList = $this->ShoppingLists->newEntity(['name' => ' <b>Grillen</b> ']);
        $this->assertEquals('Grillen', $shoppingList->name);
    }

    public function testCustomerIdIsNotMassAssignable(): void
    {
        $shoppingList = $this->ShoppingLists->newEntity(['name' => 'Test', 'id_customer' => 92]);
        $this->assertFalse($shoppingList->has('id_customer'));
    }

    public function testGetCompositeProductIdsForCustomer(): void
    {
        $productIds = $this->ShoppingLists->getCompositeProductIdsForCustomer($this->getCustomerId());
        sort($productIds);
        $this->assertEquals([(string) ProductsFixture::ID_ARTICHOKE, ProductsFixture::ID_MILK_0_5L], $productIds);
    }

    public function testGetListsForProduct(): void
    {
        $result = $this->ShoppingLists->getListsForProduct($this->getCustomerId(), 60, 10);
        $this->assertEquals([
            ['id' => ShoppingListsFixture::ID_CUSTOMER_WEEKLY, 'name' => 'Wocheneinkauf', 'isDefault' => true, 'amount' => 1],
        ], $result);

        $result = $this->ShoppingLists->getListsForProduct($this->getCustomerId(), ProductsFixture::ID_GARLIC, 0);
        $this->assertNull($result[0]['amount']);
    }

    public function testSaveProductForCustomerUpdatesAmount(): void
    {
        $errors = $this->ShoppingLists->saveProductForCustomer($this->getCustomerId(), ProductsFixture::ID_ARTICHOKE, 0, [ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 5]);
        $this->assertEmpty($errors);
        $result = $this->ShoppingLists->getListsForProduct($this->getCustomerId(), ProductsFixture::ID_ARTICHOKE, 0);
        $this->assertEquals(5, $result[0]['amount']);
    }

    public function testSaveProductForCustomerRemovesProductFromUnselectedLists(): void
    {
        $errors = $this->ShoppingLists->saveProductForCustomer($this->getCustomerId(), ProductsFixture::ID_ARTICHOKE, 0, []);
        $this->assertEmpty($errors);
        $productIds = $this->ShoppingLists->getCompositeProductIdsForCustomer($this->getCustomerId());
        $this->assertEquals([ProductsFixture::ID_MILK_0_5L], $productIds);
    }

    public function testSaveProductForCustomerCreatesNewList(): void
    {
        $errors = $this->ShoppingLists->saveProductForCustomer($this->getCustomerId(), ProductsFixture::ID_GARLIC, 0, [], 'Grillen', 3);
        $this->assertEmpty($errors);

        $result = $this->ShoppingLists->getListsForProduct($this->getCustomerId(), ProductsFixture::ID_GARLIC, 0);
        $this->assertCount(2, $result);
        // sorted by position, then by name
        $this->assertEquals('Grillen', $result[0]['name']);
        $this->assertEquals(3, $result[0]['amount']);
        $this->assertNull($result[1]['amount']);
    }

    public function testSaveProductForCustomerInvalidAmount(): void
    {
        $errors = $this->ShoppingLists->saveProductForCustomer($this->getCustomerId(), ProductsFixture::ID_GARLIC, 0, [ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 0], 'Neue Liste');
        $this->assertNotEmpty($errors);
        // transaction rolled back: new list was not created
        $this->assertCount(1, $this->ShoppingLists->find('ownedBy', customerId: $this->getCustomerId())->all());
    }

    public function testSaveProductForCustomerNotExistingProduct(): void
    {
        $errors = $this->ShoppingLists->saveProductForCustomer($this->getCustomerId(), 9999, 0, [ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 1]);
        $this->assertNotEmpty($errors);
    }

    public function testSaveProductForCustomerIgnoresListsOfOtherCustomers(): void
    {
        $errors = $this->ShoppingLists->saveProductForCustomer($this->getCustomerId(), ProductsFixture::ID_ARTICHOKE, 0, [ShoppingListsFixture::ID_SUPERADMIN => 1]);
        $this->assertEmpty($errors);
        $superadminProductIds = $this->ShoppingLists->getCompositeProductIdsForCustomer(Configure::read('test.superadminId'));
        $this->assertEquals([(string) ProductsFixture::ID_GARLIC], $superadminProductIds);
    }

    public function testSaveProductForCustomerDifferentAmountsPerList(): void
    {
        $errors = $this->ShoppingLists->saveProductForCustomer($this->getCustomerId(), ProductsFixture::ID_ARTICHOKE, 0, [ShoppingListsFixture::ID_CUSTOMER_WEEKLY => 7], 'Grillen', 2);
        $this->assertEmpty($errors);
        $amounts = [];
        foreach ($this->ShoppingLists->getListsForProduct($this->getCustomerId(), ProductsFixture::ID_ARTICHOKE, 0) as $list) {
            $amounts[$list['name']] = $list['amount'];
        }
        $this->assertEquals(['Grillen' => 2, 'Wocheneinkauf' => 7], $amounts);
    }

    public function testGetDefaultName(): void
    {
        $this->assertEquals('Meine Einkaufsliste', $this->ShoppingLists->getDefaultName($this->getCustomerId()));

        foreach (['meine einkaufsliste', 'Meine Einkaufsliste #2'] as $name) {
            $shoppingList = $this->ShoppingLists->newEntity(['name' => $name]);
            $shoppingList->id_customer = $this->getCustomerId();
            $this->ShoppingLists->saveOrFail($shoppingList);
        }
        $this->assertEquals('Meine Einkaufsliste #3', $this->ShoppingLists->getDefaultName($this->getCustomerId()));

        // lists of other customers are not relevant
        $this->assertEquals('Meine Einkaufsliste', $this->ShoppingLists->getDefaultName(Configure::read('test.superadminId')));
    }

    public function testNameIsNormalized(): void
    {
        $shoppingList = $this->ShoppingLists->newEntity(['name' => "  Wochen \t  einkauf  "]);
        $this->assertEquals('Wochen einkauf', $shoppingList->name);
    }

    public function testNameMustBeUniquePerCustomer(): void
    {
        $shoppingList = $this->ShoppingLists->newEntity(['name' => ' wocheneinkauf ']);
        $shoppingList->id_customer = $this->getCustomerId();
        $this->assertFalse($this->ShoppingLists->save($shoppingList));
        $this->assertEquals(['uniqueName' => 'Eine Einkaufsliste mit diesem Namen gibt es schon.'], $shoppingList->getError('name'));

        // same name is allowed for another customer
        $shoppingList = $this->ShoppingLists->newEntity(['name' => 'Wocheneinkauf']);
        $shoppingList->id_customer = Configure::read('test.superadminId');
        $this->assertNotFalse($this->ShoppingLists->save($shoppingList));
    }

    public function testSaveProductForCustomerNewListWithExistingName(): void
    {
        $errors = $this->ShoppingLists->saveProductForCustomer($this->getCustomerId(), ProductsFixture::ID_GARLIC, 0, [], 'Wocheneinkauf');
        $this->assertEquals(['Eine Einkaufsliste mit diesem Namen gibt es schon.'], $errors);
    }

    public function testFirstListOfCustomerBecomesDefault(): void
    {
        $customerId = Configure::read('test.adminId');
        $first = $this->ShoppingLists->newEntity(['name' => 'Erste']);
        $first->id_customer = $customerId;
        $this->ShoppingLists->saveOrFail($first);
        $second = $this->ShoppingLists->newEntity(['name' => 'Zweite']);
        $second->id_customer = $customerId;
        $this->ShoppingLists->saveOrFail($second);

        $this->assertTrue($this->ShoppingLists->get($first->id)->is_default);
        $this->assertFalse($this->ShoppingLists->get($second->id)->is_default);
    }

    public function testOnlyOneDefaultListPerCustomer(): void
    {
        $shoppingList = $this->ShoppingLists->newEntity(['name' => 'Grillen', 'is_default' => true]);
        $shoppingList->id_customer = $this->getCustomerId();
        $this->ShoppingLists->saveOrFail($shoppingList);

        $this->assertTrue($this->ShoppingLists->get($shoppingList->id)->is_default);
        $this->assertFalse($this->ShoppingLists->get(ShoppingListsFixture::ID_CUSTOMER_WEEKLY)->is_default);
        // default list of other customer is not changed
        $this->assertTrue($this->ShoppingLists->get(ShoppingListsFixture::ID_SUPERADMIN)->is_default);
    }

    public function testSortedByPositionAndName(): void
    {
        foreach ([['B-Liste', 5], ['A-Liste', 5], ['Erste', 1]] as [$name, $position]) {
            $shoppingList = $this->ShoppingLists->newEntity(['name' => $name, 'position' => $position]);
            $shoppingList->id_customer = $this->getCustomerId();
            $this->ShoppingLists->saveOrFail($shoppingList);
        }
        $names = $this->ShoppingLists->find('ownedBy', customerId: $this->getCustomerId())->find('sorted')->all()->extract('name')->toList();
        $this->assertEquals(['Erste', 'A-Liste', 'B-Liste', 'Wocheneinkauf'], $names);
    }

    public function testValidationPosition(): void
    {
        $shoppingList = $this->ShoppingLists->newEntity(['name' => 'Test', 'position' => -1]);
        $this->assertTrue($shoppingList->hasErrors());
    }

}
