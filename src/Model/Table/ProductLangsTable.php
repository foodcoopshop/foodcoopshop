<?php

namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use App\Lib\Error\Exception\InvalidParameterException;

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
class ProductLangsTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('product_lang');
        parent::initialize($config);
        $this->setPrimaryKey('id_product');
        $this->belongsTo('Products', [
            'foreignKey' => 'id_product'
        ]);
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => Array
     *                  (
     *                      [name] => Brokkoli-1
     *                      [description] => grünes Gemüse: Strunk mit Röschen auch angeschwollenen Knospen-1
     *                      [description_short] => kbA, vom Gemüsehof Wild-Obermayr-1
     *                      [unity] => ca. 0,4 kg-1
     *                      [is_declaration_ok] => 1
     *                  )
     *          )
     *  )
     * @return boolean $success
     */
    public function changeName($products)
    {

        $productLangs2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $name = $product[$productId];
            $ids = $this->Products->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new InvalidParameterException('change name is not allowed for product attributes');
            }
            $newName = StringComponent::removeSpecialChars(strip_tags(trim($name['name'])));
            if (strlen($newName) < 2) {
                throw new InvalidParameterException('Der Name des Produktes <b>'.$newName.'</b> muss mindestens zwei Zeichen lang sein.');
            } else {
                $tmpProductLang2Save = [
                    'id_product' => $ids['productId'],
                    'name' => StringComponent::removeSpecialChars(strip_tags(trim($name['name']))),
                    'description' => strip_tags(htmlspecialchars_decode(trim($name['description'])), '<p><b><br><img>'),
                    'description_short' => strip_tags(htmlspecialchars_decode(trim($name['description_short'])), '<p><b><br>'),
                    'unity' => StringComponent::removeSpecialChars(strip_tags(trim($name['unity'])))
                ];
                if (isset($name['is_declaration_ok'])) {
                    $tmpProductLang2Save['is_declaration_ok'] = $name['is_declaration_ok'];
                }
                $productLangs2save[] = $tmpProductLang2Save;
            }
        }

        $success = false;
        if (!empty($productLangs2save)) {
            $entities = $this->newEntities($productLangs2save);
            $result = $this->saveMany($entities);
            $success = !empty($result);
        }

        return $success;
    }
}
