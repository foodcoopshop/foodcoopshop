<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ShoppingListProduct;
use Cake\Core\Configure;
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
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\ShoppingListProduct>
 */
class ShoppingListProductsTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('ShoppingLists', [
            'foreignKey' => 'shopping_list_id',
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'id_product',
        ]);
        $this->belongsTo('ProductAttributes', [
            'foreignKey' => 'id_product_attribute',
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $maxAmount = (int) Configure::read('app.maxProductAmountForCart');
        $validator
            ->integer('amount', __('Please_enter_a_correct_amount.'))
            ->range('amount', [1, $maxAmount], __('Please_enter_a_number_between_{0}_and_{1}.', [1, $maxAmount]))
            ->notEmptyString('amount', __('Please_enter_a_correct_amount.'));
        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('shopping_list_id', 'ShoppingLists'));
        $rules->add($rules->existsIn('id_product', 'Products'));
        $rules->add($rules->isUnique(['shopping_list_id', 'id_product', 'id_product_attribute']));
        return $rules;
    }

    /**
     * adds the product to the list or replaces the amount if it already exists
     */
    public function addOrUpdate(int $shoppingListId, int $productId, int $attributeId, int $amount): ShoppingListProduct
    {
        $shoppingListProduct = $this->find()
            ->where([
                'shopping_list_id' => $shoppingListId,
                'id_product' => $productId,
                'id_product_attribute' => $attributeId,
            ])
            ->first();

        if ($shoppingListProduct === null) {
            $shoppingListProduct = $this->newEmptyEntity();
            $shoppingListProduct->shopping_list_id = $shoppingListId;
            $shoppingListProduct->id_product = $productId;
            $shoppingListProduct->id_product_attribute = $attributeId;
        }

        $this->patchEntity($shoppingListProduct, ['amount' => $amount]);
        $this->save($shoppingListProduct);

        return $shoppingListProduct;
    }

}
