<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Traits\ProductCacheClearAfterSaveAndDeleteTrait;
use Cake\Validation\Validator;

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
class AttributesTable extends AppTable
{

    use ProductCacheClearAfterSaveAndDeleteTrait;

    public function initialize(array $config): void
    {
        $this->setTable('attribute');
        parent::initialize($config);
        $this->setPrimaryKey('id_attribute');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', __('Please_enter_a_name.'));
        $validator->add('name', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => __('An_attribute_with_this_name_already_exists.')
        ]);
        return $validator;
    }

    public function getForDropdown(): array
    {
        $attributes = $this->find('all', order: [
            $this->aliasField('name') => 'ASC',
        ]);

        $attributesForDropdown = [];
        foreach ($attributes as $attribute) {
            $attributesForDropdown[$attribute->id_attribute] = $attribute->name;
        }

        return $attributesForDropdown;
    }
}
