<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Validation\Validator;
use App\Model\Traits\NumberRangeValidatorTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class StockAvailablesTable extends AppTable
{

    use NumberRangeValidatorTrait;
    use ProductCacheClearAfterSaveAndDeleteTrait;

    public function initialize(array $config): void
    {
        $this->setTable('stock_available');
        parent::initialize($config);
        $this->setPrimaryKey('id_product');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->numeric('quantity', __('The_quantity_needs_to_be_a_number.'));
        $validator = $this->getNumberRangeValidator($validator, 'quantity', -5000, 5000, __('Field:_Stock'));

        $validator->numeric('quantity_limit', __('The_quantity_limit_needs_to_be_a_number.'));
        $validator = $this->getNumberRangeValidator($validator, 'quantity_limit', -5000, 0, __('Field:_Order_possible_until'));

        $validator->numeric('sold_out_quantity', __('The_sold_out_quantity_needs_to_be_a_number.'));
        $validator = $this->getNumberRangeValidator($validator, 'sold_out_quantity', -5000, 5000, __('Field:_Notification_if_quantity_limit_reached'));
        $validator->allowEmptyString('sold_out_quantity');

        $validator->numeric('default_quantity_after_sending_order_lists', __('The_default_quantity_after_sending_order_lists_needs_to_be_a_number.'));
        $validator = $this->getNumberRangeValidator($validator, 'default_quantity_after_sending_order_lists', 1, 5000, __('Field:_Default_quantity_after_sending_order_lists'));
        $validator->allowEmptyString('default_quantity_after_sending_order_lists');

        return $validator;
    }

    public function saveStockAvailable($stockAvailable2saveData, $stockAvailable2saveConditions): void
    {
        $i = 0;
        foreach($stockAvailable2saveConditions as $condition) {
            $stockAvailableEntity = $this->find('all',
                conditions: $condition,
            )->first();
            $stockAvailableEntity->quantity = $stockAvailable2saveData[$i]['quantity'];
            $originalPrimaryKey = $this->getPrimaryKey();
            if ($condition['id_product_attribute'] > 0) {
                $this->setPrimaryKey('id_product_attribute');
            }
            $this->save($stockAvailableEntity);
            $this->setPrimaryKey($originalPrimaryKey);
            $this->updateQuantityForMainProduct($condition['id_product']);
            $i++;
        }
    }

    public function updateQuantityForMainProduct($productId): void
    {
        $productId = (int) $productId;
        if ($productId > 0) {
            $query = 'UPDATE '.$this->getTable().' AS sa1, (
                        SELECT SUM(quantity) as quantitySum
                        FROM '.$this->getTable().'
                        WHERE id_product = :productId
                            AND id_product_attribute > 0
                        GROUP BY id_product
                        ) sa2
                    SET sa1.quantity = sa2.quantitySum
                    WHERE sa1.id_product = ' . $productId . '
                        AND sa1.id_product_attribute = 0';
            $params = [
                'productId' => $productId
            ];
            $statement = $this->getConnection()->getDriver()->prepare($query);
            $statement->execute($params);
        }
    }
}
