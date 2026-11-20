<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ShoppingList;
use App\Model\Entity\ShoppingListProduct;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

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
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\ShoppingList>
 * @property \App\Model\Table\ShoppingListProductsTable $ShoppingListProducts
 */
class ShoppingListsTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setDisplayField('name');
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer',
        ]);
        $this->hasMany('ShoppingListProducts', [
            'foreignKey' => 'shopping_list_id',
            'dependent' => true,
        ]);
        $this->addBehavior('Timestamp');
    }

    /**
     * @param EventInterface<\App\Model\Table\ShoppingListsTable> $event
     * @param ArrayObject<string, mixed> $data
     * @param ArrayObject<string, mixed> $options
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        if (isset($data['name'])) {
            $data['name'] = $this->normalizeName((string) $data['name']);
        }
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name', __('Please_enter_a_name.'))
            ->maxLength('name', 255)
            ->nonNegativeInteger('position', __('Please_enter_a_number.'))
            ->allowEmptyString('position')
            ->boolean('is_default');
        return $validator;
    }

    /**
     * the first list of a customer becomes the default list
     *
     * @param EventInterface<\App\Model\Table\ShoppingListsTable> $event
     * @param ArrayObject<string, mixed> $options
     */
    public function beforeSave(EventInterface $event, ShoppingList $shoppingList, ArrayObject $options): void
    {
        if ($shoppingList->isNew() && !$shoppingList->is_default) {
            $hasOtherLists = $this->find('ownedBy', customerId: $shoppingList->id_customer)->count() > 0;
            if (!$hasOtherLists) {
                $shoppingList->is_default = true;
            }
        }
    }

    /**
     * only one default list per customer
     *
     * @param EventInterface<\App\Model\Table\ShoppingListsTable> $event
     * @param ArrayObject<string, mixed> $options
     */
    public function afterSave(EventInterface $event, ShoppingList $shoppingList, ArrayObject $options): void
    {
        if ($shoppingList->is_default && $shoppingList->isDirty('is_default')) {
            $this->updateAll(
                ['is_default' => false],
                [
                    'id_customer' => $shoppingList->id_customer,
                    'id !=' => $shoppingList->id,
                ],
            );
        }
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('id_customer', 'Customers'));
        // comparison is case insensitive due to the collation of the column
        $rules->add($rules->isUnique(
            ['id_customer', 'name'],
            __('A_shopping_list_with_this_name_already_exists.'),
        ), 'uniqueName', ['errorField' => 'name']);
        return $rules;
    }

    /**
     * strips tags, trims and collapses multiple whitespaces
     */
    protected function normalizeName(string $name): string
    {
        return (string) preg_replace('/\s+/u', ' ', $this->sanitizePlainStringValue($name));
    }

    /**
     * @param SelectQuery<ShoppingList> $query
     * @return SelectQuery<ShoppingList>
     */
    public function findOwnedBy(SelectQuery $query, int $customerId): SelectQuery
    {
        return $query->where([$this->aliasField('id_customer') => $customerId]);
    }

    /**
     * @param SelectQuery<ShoppingList> $query
     * @return SelectQuery<ShoppingList>
     */
    public function findSorted(SelectQuery $query): SelectQuery
    {
        return $query->orderBy([
            $this->aliasField('position') => 'ASC',
            $this->aliasField('name') => 'ASC',
        ]);
    }

    /**
     * sorted lists of the customer with the virtual field "product_count"
     *
     * @return list<ShoppingList>
     */
    public function getForMenu(int $customerId): array
    {
        $query = $this->find('ownedBy', customerId: $customerId)->find('sorted');
        $query->select($this)
            ->select(['product_count' => $query->func()->count('ShoppingListProducts.id')])
            ->leftJoinWith('ShoppingListProducts')
            ->groupBy(['ShoppingLists.id']);
        return $query->all()->toList();
    }

    /**
     * @return array<string, int> amount by composite product id (eg. "12" or "12-3")
     */
    public function getAmountsByCompositeProductId(ShoppingList $shoppingList): array
    {
        $shoppingListProducts = $this->ShoppingListProducts->find()
            ->where(['shopping_list_id' => $shoppingList->id])
            ->orderBy(['ShoppingListProducts.id' => 'ASC'])
            ->all();
        $result = [];
        foreach ($shoppingListProducts as $shoppingListProduct) {
            $result[$this->getCompositeProductId($shoppingListProduct)] = $shoppingListProduct->amount;
        }
        return $result;
    }

    /**
     * eg. "12" or "12-3"
     */
    private function getCompositeProductId(ShoppingListProduct $shoppingListProduct): string
    {
        return (string) $this->ShoppingListProducts->Products->getTarget()->getCompositeProductIdAndAttributeId(
            $shoppingListProduct->id_product,
            $shoppingListProduct->id_product_attribute,
        );
    }

    public function removeAllProducts(ShoppingList $shoppingList): int
    {
        return $this->ShoppingListProducts->deleteAll([
            'shopping_list_id' => $shoppingList->id,
        ]);
    }

    /**
     * @param list<string> $compositeProductIds eg. ["12", "12-3"]
     */
    public function removeProducts(ShoppingList $shoppingList, array $compositeProductIds): int
    {
        if (empty($compositeProductIds)) {
            return 0;
        }
        $productsTable = $this->ShoppingListProducts->Products->getTarget();
        $productConditions = [];
        foreach ($compositeProductIds as $compositeProductId) {
            $ids = $productsTable->getProductIdAndAttributeId($compositeProductId);
            $productConditions[] = [
                'id_product' => $ids['productId'],
                'id_product_attribute' => $ids['attributeId'],
            ];
        }
        return $this->ShoppingListProducts->deleteAll([
            'shopping_list_id' => $shoppingList->id,
            'OR' => $productConditions,
        ]);
    }

    /**
     * all products (as composite ids like "12" or "12-3") that are stored on any list of the given customer
     *
     * @return list<string>
     */
    public function getCompositeProductIdsForCustomer(int $customerId): array
    {
        $shoppingListProducts = $this->ShoppingListProducts->find()
            ->select(['ShoppingListProducts.id_product', 'ShoppingListProducts.id_product_attribute'])
            ->innerJoinWith('ShoppingLists', function (SelectQuery $q) use ($customerId) {
                return $q->where(['ShoppingLists.id_customer' => $customerId]);
            })
            ->distinct(['ShoppingListProducts.id_product', 'ShoppingListProducts.id_product_attribute'])
            ->all();

        $result = [];
        foreach ($shoppingListProducts as $shoppingListProduct) {
            $result[] = $this->getCompositeProductId($shoppingListProduct);
        }
        return $result;
    }

    /**
     * all (sorted) lists of the given customer, amount is null if the product is not on the list
     *
     * @return list<array{id: int, name: string, isDefault: bool, amount: int|null}>
     */
    public function getListsForProduct(int $customerId, int $productId, int $attributeId): array
    {
        $shoppingLists = $this->find('ownedBy', customerId: $customerId)
            ->contain([
                'ShoppingListProducts' => function (SelectQuery $q) use ($productId, $attributeId) {
                    return $q->where([
                        'ShoppingListProducts.id_product' => $productId,
                        'ShoppingListProducts.id_product_attribute' => $attributeId,
                    ]);
                },
            ])
            ->find('sorted')
            ->all();

        $result = [];
        foreach ($shoppingLists as $shoppingList) {
            $result[] = [
                'id' => $shoppingList->id,
                'name' => $shoppingList->name,
                'isDefault' => (bool) $shoppingList->is_default,
                'amount' => empty($shoppingList->shopping_list_products) ? null : $shoppingList->shopping_list_products[0]->amount,
            ];
        }
        return $result;
    }

    /**
     * the product is stored in all given lists (with the amount per list) and removed from all other lists of the customer
     * if $newListName is not empty, a new list is created and the product is added to it with $newListAmount
     *
     * @param array<int, int> $amountsByShoppingListId
     * @return list<string> validation errors
     */
    public function saveProductForCustomer(int $customerId, int $productId, int $attributeId, array $amountsByShoppingListId, string $newListName = '', int $newListAmount = 1): array
    {
        $errors = [];

        $this->getConnection()->transactional(function () use ($customerId, $productId, $attributeId, $amountsByShoppingListId, $newListName, $newListAmount, &$errors) {

            if ($newListName !== '') {
                $newShoppingList = $this->newEntity(['name' => $newListName]);
                $newShoppingList->id_customer = $customerId;
                if ($newShoppingList->hasErrors() || !$this->save($newShoppingList)) {
                    $errors = $this->getAllValidationErrors($newShoppingList);
                    return false;
                }
                $amountsByShoppingListId[$newShoppingList->id] = $newListAmount;
            }

            $removeFromShoppingListIds = [];
            foreach ($this->find('ownedBy', customerId: $customerId)->all() as $shoppingList) {
                if (!array_key_exists($shoppingList->id, $amountsByShoppingListId)) {
                    $removeFromShoppingListIds[] = $shoppingList->id;
                    continue;
                }
                $shoppingListProduct = $this->ShoppingListProducts->addOrUpdate($shoppingList->id, $productId, $attributeId, $amountsByShoppingListId[$shoppingList->id]);
                if ($shoppingListProduct->hasErrors()) {
                    $errors = $this->ShoppingListProducts->getAllValidationErrors($shoppingListProduct);
                    return false;
                }
            }

            if (!empty($removeFromShoppingListIds)) {
                $this->ShoppingListProducts->deleteAll([
                    'shopping_list_id IN' => $removeFromShoppingListIds,
                    'id_product' => $productId,
                    'id_product_attribute' => $attributeId,
                ]);
            }

            return true;
        });

        return $errors;
    }

    /**
     * if saving fails, the returned entity has errors (also the errors of the products)
     *
     * @param list<array{productId: int, attributeId: int, amount: int}> $items
     */
    public function createWithProducts(int $customerId, string $name, array $items): ShoppingList
    {
        $shoppingList = $this->newEntity(['name' => $name]);
        $shoppingList->id_customer = $customerId;
        if ($shoppingList->hasErrors()) {
            return $shoppingList;
        }

        $this->getConnection()->transactional(function () use ($shoppingList, $items) {
            if (!$this->save($shoppingList)) {
                return false;
            }
            foreach ($items as $item) {
                $shoppingListProduct = $this->ShoppingListProducts->addOrUpdate($shoppingList->id, $item['productId'], $item['attributeId'], $item['amount']);
                if ($shoppingListProduct->hasErrors()) {
                    $shoppingList->setErrors(['shopping_list_products' => $this->ShoppingListProducts->getAllValidationErrors($shoppingListProduct)]);
                    return false;
                }
            }
            return true;
        });

        return $shoppingList;
    }

    /**
     * "Meine Einkaufsliste" - or "Meine Einkaufsliste #2", "#3"... if the name is already used by the customer
     */
    public function getDefaultName(int $customerId): string
    {
        /** @var list<string> $names */
        $names = $this->find('ownedBy', customerId: $customerId)
            ->select(['name'])
            ->all()
            ->extract('name')
            ->toList();
        $existingNames = array_map('mb_strtolower', $names);

        $baseName = __('My_shopping_list');
        $name = $baseName;
        $i = 2;
        while (in_array(mb_strtolower($name), $existingNames, true)) {
            $name = $baseName . ' #' . $i;
            $i++;
        }
        return $name;
    }

}
