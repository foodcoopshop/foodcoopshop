<?php

namespace App\Model\Table;

use App\Model\Traits\ProductAndAttributeEntityTrait;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class BarcodeProductsTable extends AppTable
{

    use ProductAndAttributeEntityTrait;

    public function initialize(array $config): void
    {
        $this->setTable('barcodes');
        parent::initialize($config);
        $this->setPrimaryKey('product_id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->allowEmptyString('barcode');
        $validator->lengthBetween('barcode', [13, 13], __('The_length_of_the_barcode_needs_to_be_exactly_{0}.', [13]));
        return $validator;
    }

}
