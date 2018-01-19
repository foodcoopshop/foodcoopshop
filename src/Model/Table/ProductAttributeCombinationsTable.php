<?php

namespace App\Model\Table;
use Cake\Core\Configure;

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductAttributeCombinationsTable extends AppTable
{

    public $useTable = 'product_attribute_combination';

    public $primaryKey = 'id_attribute';

    public $actsAs = [
        'Containable'
    ];

    public $belongsTo = [
        'Attribute' => [
            'foreignKey' => 'id_attribute'
        ],
        'ProductAttribute' => [
            'foreignKey' => 'id_product_attribute'
        ]
    ];

    public function getCombinationCounts($attributeId)
    {
        $this->recursive = 2;

        $combinations = $this->find('all', [
            'conditions' => [
                'Attribute.id_attribute' => $attributeId
            ],
            'contain' => [
                'Attribute',
                'ProductAttribute.Product.ProductLang',
                'ProductAttribute.Product.ProductShop',
                'ProductAttribute.Product.Manufacturer'
            ]
        ]);

        $return = [
            'online' => [],
            'offline' => []
        ];
        foreach ($combinations as $combination) {
            $preparedProduct = $combination['ProductAttribute']['Product'];

            $preparedProduct['link'] = Configure::read('AppConfig.htmlHelper')->link($preparedProduct['ProductLang']['name'] . ' - ' . $preparedProduct['Manufacturer']['name'], Configure::read('AppConfig.slugHelper')->getProductDetail($preparedProduct['id_product'], $preparedProduct['ProductLang']['name']));

            if ($combination['ProductAttribute']['Product']['active'] == 1) {
                $return['online'][] = $preparedProduct;
            }

            if ($combination['ProductAttribute']['Product']['active'] == 0) {
                $return['offline'][] = $preparedProduct;
            }
        }

        $return['online'] = Set::sort($return['online'], '{n}.ProductLang.name', 'asc');
        $return['offline'] = Set::sort($return['offline'], '{n}.ProductLang.name', 'asc');

        return $return;
    }
}
