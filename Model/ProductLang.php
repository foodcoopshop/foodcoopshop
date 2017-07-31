<?php
/**
 * ProductLang
 *
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
class ProductLang extends AppModel
{

    public $useTable = 'product_lang';

    public $primaryKey = 'id_product';

    public $belongsTo = array(
        'Product' => array(
            'foreignKey' => 'id_product'
        )
    );

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
     *                  )
     *          )
     *  )
     * @return boolean $success
     */
    public function changeName($products)
    {

        $productsLang2save = array();

        foreach ($products as $product) {
            $productId = key($product);
            $name = $product[$productId];
            $newName = StringComponent::removeSpecialChars(strip_tags(trim($name['name'])));
            if (strlen($newName) < 2) {
                throw new InvalidParameterException('Der Name des Produktes <b>'.$newName.'</b> muss mindestens zwei Zeichen lang sein.');
            } else {
                $productsLang2save[] = array(
                    'id_product' => $productId,
                    'name' => StringComponent::removeSpecialChars(strip_tags(trim($name['name']))),
                    'description' => strip_tags(htmlspecialchars_decode(trim($name['description'])), '<p><b><br>'),
                    'description_short' => strip_tags(htmlspecialchars_decode(trim($name['description_short'])), '<p><b><br>'),
                    'unity' => StringComponent::removeSpecialChars(strip_tags(trim($name['unity'])))
                );
            }
        }

        $success = false;
        if (!empty($productsLang2save)) {
            $success = $this->saveAll($productsLang2save);
        }

        return $success;
    }
}
