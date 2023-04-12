<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Traits\ProductAndAttributeEntityTrait;
use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class BarcodeProductsTable extends AppTable
{

    use ProductAndAttributeEntityTrait;
    use ProductCacheClearAfterSaveAndDeleteTrait;

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
