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

    public function initialize(array $config)
    {
        $this->setTable('product_attribute_combination');
        parent::initialize($config);
    }

    public $primaryKey = 'id_attribute';

    public $actsAs = [
        'Containable'
    ];

    public $belongsTo = [
        'Attributes' => [
            'foreignKey' => 'id_attribute'
        ],
        'ProductAttributes' => [
            'foreignKey' => 'id_product_attribute'
        ]
    ];

    public function getCombinationCounts($attributeId)
    {
        $this->recursive = 2;

        $combinations = $this->find('all', [
            'conditions' => [
                'Attributes.id_attribute' => $attributeId
            ],
            'contain' => [
                'Attributes',
                'ProductAttributes.Products.ProductLangs',
                'ProductAttributes.Products.ProductShops',
                'ProductAttributes.Products.Manufacturers'
            ]
        ]);

        $return = [
            'online' => [],
            'offline' => []
        ];
        foreach ($combinations as $combination) {
            $preparedProduct = $combination['ProductAttributes']['Products'];

            $preparedProduct['link'] = Configure::read('AppConfig.htmlHelper')->link($preparedProduct['ProductLangs']['name'] . ' - ' . $preparedProduct['Manufacturers']['name'], Configure::read('AppConfig.slugHelper')->getProductDetail($preparedProduct['id_product'], $preparedProduct['ProductLangs']['name']));

            if ($combination['ProductAttributes']['Products']['active'] == 1) {
                $return['online'][] = $preparedProduct;
            }

            if ($combination['ProductAttributes']['Products']['active'] == 0) {
                $return['offline'][] = $preparedProduct;
            }
        }

        $return['online'] = Set::sort($return['online'], '{n}.ProductLang.name', 'asc');
        $return['offline'] = Set::sort($return['offline'], '{n}.ProductLang.name', 'asc');

        return $return;
    }
}
