<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Model\Entity\ShoppingList;
use App\Services\CatalogService;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\View\JsonView;

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
 *
 * access is checked in ShoppingListsPolicy, ownership of the lists with the finder "ownedBy"
 */
class ShoppingListsController extends FrontendController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->addViewClasses([JsonView::class]);
    }

    public function detail(): ?Response
    {
        $shoppingListId = (int) $this->getRequest()->getParam('idAndSlug');
        $shoppingList = $this->getOwnedShoppingList($shoppingListId);

        $correctSlug = StringComponent::slugify($shoppingList->name);
        $givenSlug = StringComponent::removeIdFromSlug((string) $this->getRequest()->getParam('idAndSlug'));
        if ($correctSlug != $givenSlug) {
            return $this->redirect(Configure::read('app.slugHelper')->getShoppingListDetail($shoppingList->id, $shoppingList->name));
        }

        $amounts = $this->fetchTable('ShoppingLists')->getAmountsByCompositeProductId($shoppingList);
        $productsTable = $this->fetchTable('Products');
        $productIdsOnList = array_values(array_unique(array_map(
            fn($compositeProductId) => $productsTable->getProductIdAndAttributeId($compositeProductId)['productId'],
            array_keys($amounts),
        )));

        $products = [];
        if (!empty($productIdsOnList)) {
            // a shopping list is not paginated: load all pages of the catalog
            $catalogService = new CatalogService();
            $page = 1;
            do {
                $productsOfPage = $catalogService->getProducts(categoryId: 0, page: $page, productIds: $productIdsOnList);
                $products = array_merge($products, $productsOfPage);
                $page++;
            } while (count($productsOfPage) == CatalogService::MAX_PRODUCTS_PER_PAGE);
            $products = $catalogService->prepareProducts($products);
        }

        $displayedProductIds = array_map(fn($product) => $product->id_product, $products);
        $unavailableProductIds = array_values(array_diff($productIdsOnList, $displayedProductIds));

        $this->set(compact('shoppingList', 'products', 'amounts', 'unavailableProductIds'));
        $this->set('unavailableProductNames', $this->getProductNames($unavailableProductIds));
        $this->set('title_for_layout', $shoppingList->name);
        return null;
    }

    public function edit(int $shoppingListId): Response
    {
        $this->getRequest()->allowMethod(['post', 'put', 'patch']);
        $shoppingListsTable = $this->fetchTable('ShoppingLists');
        $shoppingList = $this->getOwnedShoppingList($shoppingListId);
        $shoppingList = $shoppingListsTable->patchEntity($shoppingList, $this->getRequest()->getData(), [
            'fields' => ['name', 'position', 'is_default'],
        ]);
        if ($shoppingListsTable->save($shoppingList)) {
            $this->Flash->success(__('The_shopping_list_{0}_has_been_changed.', ['<b>' . h($shoppingList->name) . '</b>']));
        } else {
            $this->Flash->error(__('Errors_while_saving!') . '<ul><li>' . join('</li><li>', $shoppingListsTable->getAllValidationErrors($shoppingList)) . '</li></ul>');
            $shoppingList = $this->getOwnedShoppingList($shoppingListId);
        }
        return $this->redirect(Configure::read('app.slugHelper')->getShoppingListDetail($shoppingList->id, $shoppingList->name));
    }

    public function delete(int $shoppingListId): Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $shoppingList = $this->getOwnedShoppingList($shoppingListId);
        $this->fetchTable('ShoppingLists')->delete($shoppingList);
        $this->Flash->success(__('The_shopping_list_{0}_has_been_deleted.', ['<b>' . h($shoppingList->name) . '</b>']));
        return $this->redirect(Configure::read('app.slugHelper')->getAllProducts());
    }

    /**
     * removes the products that are not shown on the list page (not active, deleted, delivery break...)
     */
    public function removeUnavailableProducts(int $shoppingListId): Response
    {
        $this->getRequest()->allowMethod(['post']);
        $shoppingList = $this->getOwnedShoppingList($shoppingListId);
        $productIds = array_map('intval', (array) $this->getRequest()->getData('productIds'));
        $removedCount = $this->fetchTable('ShoppingLists')->removeProducts($shoppingList, $productIds);
        $this->Flash->success(__('{0,plural,=1{1_product_has} other{#_products_have}}_been_removed_from_the_shopping_list.', [$removedCount]));
        return $this->redirect(Configure::read('app.slugHelper')->getShoppingListDetail($shoppingList->id, $shoppingList->name));
    }

    /**
     * used by the heart button
     */
    public function ajaxGetShoppingListsForProduct(): void
    {
        $this->getRequest()->allowMethod(['get']);
        $this->request = $this->request->withParam('_ext', 'json');

        $ids = $this->fetchTable('Products')->getProductIdAndAttributeId((string) $this->getRequest()->getQuery('productId'));
        $shoppingListsTable = $this->fetchTable('ShoppingLists');

        $this->set([
            'status' => 1,
            'msg' => 'ok',
            'shoppingLists' => $shoppingListsTable->getListsForProduct($this->identity->getId(), $ids['productId'], $ids['attributeId']),
            'defaultName' => $shoppingListsTable->getDefaultName($this->identity->getId()),
            'maxAmount' => (int) Configure::read('app.maxProductAmountForCart'),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'shoppingLists', 'defaultName', 'maxAmount']);
    }

    /**
     * used by the heart button
     */
    public function ajaxSaveProduct(): ?Response
    {
        $this->getRequest()->allowMethod(['post']);
        $this->request = $this->request->withParam('_ext', 'json');

        $ids = $this->fetchTable('Products')->getProductIdAndAttributeId((string) $this->getRequest()->getData('productId'));
        // only checked lists are sent: [shoppingListId => amount]
        $amountsByShoppingListId = [];
        foreach ((array) $this->getRequest()->getData('amounts') as $shoppingListId => $amount) {
            $amountsByShoppingListId[(int) $shoppingListId] = (int) $amount;
        }

        $shoppingListsTable = $this->fetchTable('ShoppingLists');
        $listsBefore = $shoppingListsTable->getListsForProduct($this->identity->getId(), $ids['productId'], $ids['attributeId']);
        $errors = $shoppingListsTable->saveProductForCustomer(
            $this->identity->getId(),
            $ids['productId'],
            $ids['attributeId'],
            $amountsByShoppingListId,
            trim((string) $this->getRequest()->getData('newShoppingListName')),
            (int) $this->getRequest()->getData('newShoppingListAmount'),
        );

        if (!empty($errors)) {
            return $this->sendAjaxError(new \Exception(join('<br />', $errors)));
        }

        // amounts of the product on all lists after saving (also used to update the shopping list page)
        $listsAfter = $shoppingListsTable->getListsForProduct($this->identity->getId(), $ids['productId'], $ids['attributeId']);

        $this->set([
            'status' => 1,
            'msg' => $this->getSaveProductSuccessMessage($ids['productId'], $ids['attributeId'], $listsBefore, $listsAfter),
            'shoppingLists' => $listsAfter,
            'productIds' => $shoppingListsTable->getCompositeProductIdsForCustomer($this->identity->getId()),
            // product counts (and maybe a new list) changed
            'sidebarMenu' => $this->createView()->element('shoppingList/sidebarMenu', [
                'shoppingListsForMenu' => $shoppingListsTable->getForMenu($this->identity->getId()),
            ]),
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg', 'shoppingLists', 'productIds', 'sidebarMenu']);
        return null;
    }

    /**
     * eg. "The product X has been saved on the shopping list A and removed from the shopping list B."
     *
     * @param list<array{id: int, name: string, isDefault: bool, amount: int|null}> $listsBefore
     * @param list<array{id: int, name: string, isDefault: bool, amount: int|null}> $listsAfter
     */
    private function getSaveProductSuccessMessage(int $productId, int $attributeId, array $listsBefore, array $listsAfter): string
    {
        $product = $this->fetchTable('Products')->find()
            ->contain(['ProductAttributes.ProductAttributeCombinations.Attributes'])
            ->where(['Products.id_product' => $productId])
            ->firstOrFail();
        $attributeName = '';
        foreach ($product->product_attributes as $productAttribute) {
            if ($productAttribute->id_product_attribute == $attributeId) {
                $attributeName = $productAttribute->product_attribute_combination->attribute->name ?? '';
                break;
            }
        }
        $productName = $this->fetchTable('Carts')->getProductNameWithUnity($product->name, $attributeName);

        $amountsBefore = [];
        foreach ($listsBefore as $shoppingList) {
            $amountsBefore[$shoppingList['id']] = $shoppingList['amount'];
        }

        $savedListNames = [];
        $removedListNames = [];
        foreach ($listsAfter as $shoppingList) {
            $amountBefore = $amountsBefore[$shoppingList['id']] ?? null;
            $listName = '<b>' . h($shoppingList['name']) . '</b>';
            if ($shoppingList['amount'] !== null && $shoppingList['amount'] !== $amountBefore) {
                $savedListNames[] = $listName;
            }
            if ($shoppingList['amount'] === null && $amountBefore !== null) {
                $removedListNames[] = $listName;
            }
        }

        $parts = [];
        if (!empty($savedListNames)) {
            $parts[] = count($savedListNames) == 1
                ? __('saved_on_the_shopping_list_{0}', [$savedListNames[0]])
                : __('saved_on_the_shopping_lists_{0}', [join(', ', $savedListNames)]);
        }
        if (!empty($removedListNames)) {
            $parts[] = count($removedListNames) == 1
                ? __('removed_from_the_shopping_list_{0}', [$removedListNames[0]])
                : __('removed_from_the_shopping_lists_{0}', [join(', ', $removedListNames)]);
        }

        $productName = '<b>' . $productName . '</b>';
        return match (count($parts)) {
            0 => __('The_shopping_lists_of_the_product_{0}_have_not_been_changed.', [$productName]),
            1 => __('The_product_{0}_has_been_{1}.', [$productName, $parts[0]]),
            default => __('The_product_{0}_has_been_{1}_and_{2}.', [$productName, $parts[0], $parts[1]]),
        };
    }

    /**
     * @param list<int> $productIds
     * @return list<string>
     */
    private function getProductNames(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }
        $names = $this->fetchTable('Products')->find()
            ->select(['Products.id_product', 'Products.name'])
            ->where(['Products.id_product IN' => $productIds])
            ->orderBy(['Products.name' => 'ASC'])
            ->all()
            ->extract('name')
            ->toList();
        // deleted products
        $deletedCount = count($productIds) - count($names);
        if ($deletedCount > 0) {
            $names[] = __('{0,plural,=1{1_deleted_product} other{#_deleted_products}}', [$deletedCount]);
        }
        return $names;
    }

    private function getOwnedShoppingList(int $shoppingListId): ShoppingList
    {
        return $this->fetchTable('ShoppingLists')
            ->find('ownedBy', customerId: $this->identity->getId())
            ->where(['ShoppingLists.id' => $shoppingListId])
            ->firstOrFail();
    }

}
