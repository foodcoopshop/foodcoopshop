<?php

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterSaveTrait;
use Cake\Validation\Validator;

/**
 * fake model for using associations with foreign keys that are not the id of the model
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class UnitProductsTable extends AppTable
{

    use ProductCacheClearAfterSaveTrait;

    public function initialize(array $config): void
    {
        $this->setTable('units');
        parent::initialize($config);
        $this->setPrimaryKey('id_product');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->greaterThanOrEqual('purchase_price_incl_per_unit', 0, __('The_price_needs_to_be_greater_or_equal_than_0.'));
        return $validator;
    }

}
