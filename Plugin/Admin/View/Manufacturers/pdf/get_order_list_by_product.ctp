<?php
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

echo $this->element('pdf/order_list', array(
    'groupType' => 'product',
    'groupType_de' => 'Produkt',
    'results' => $results_product,
    'manufacturer' => isset($manufacturer) ? $manufacturer : array()
)); // not defined in sendOrderList
