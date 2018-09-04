<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductAttributeCombinationsTable extends AppTable
{

    public function initialize(array $config)
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

    public function getCombinationCounts($attributeId)
    {
        $combinations = $this->find('all', [
            'conditions' => [
                'Attributes.id_attribute' => $attributeId
            ],
            'contain' => [
                'Attributes',
                'ProductAttributes.Products',
                'ProductAttributes.Products.Manufacturers'
            ]
        ]);

        $result = [
            'online' => [],
            'offline' => []
        ];

        foreach ($combinations as $combination) {
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
