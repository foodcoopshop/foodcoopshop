<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Utility\Hash;

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
class ProductAttributeCombinationsTable extends AppTable
{

    public function initialize(array $config): void
    {
        $this->setTable('product_attribute_combination');
        parent::initialize($config);
        $this->setPrimaryKey('id_attribute');
        $this->belongsTo('Attributes', [
            'foreignKey' => 'id_attribute'
        ]);
        $this->belongsTo('ProductAttributes', [
            'foreignKey' => 'id_product_attribute'
        ]);
    }

    public function getCombinationCounts($attributeId): array
    {
        $combinations = $this->find('all',
        conditions: [
            'Attributes.id_attribute' => $attributeId
        ],
        contain: [
            'Attributes',
            'ProductAttributes.Products',
            'ProductAttributes.Products.Manufacturers'
        ]);

        $result = [
            'online' => [],
            'offline' => []
        ];

        foreach ($combinations as $combination) {

            // product might have been hard deleted from database
            if (empty($combination->product_attribute->product)) {
                continue;
            }

            $preparedProduct = $combination->product_attribute->product;

            $tmpProduct = [];
            $tmpProduct['link'] = Configure::read('app.htmlHelper')->link($preparedProduct->name . ' - ' . $preparedProduct->manufacturer->name, Configure::read('app.slugHelper')->getProductDetail($preparedProduct->id_product, $preparedProduct->name));
            $tmpProduct['name']= $preparedProduct->name;
            $tmpProduct['manufacturer_name'] = $preparedProduct->manufacturer->name;

            if ($preparedProduct->active == 1) {
                $result['online'][] = $tmpProduct;
            }

            if ($preparedProduct->active == 0) {
                $result['offline'][] = $tmpProduct;
            }
        }

        $result['online'] = Hash::sort($result['online'], '{n}.name', 'asc');
        $result['offline'] = Hash::sort($result['offline'], '{n}.name', 'asc');

        return $result;
    }
}
