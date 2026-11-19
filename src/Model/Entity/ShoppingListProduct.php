<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 *
 * @property int $id
 * @property int $shopping_list_id
 * @property int $id_product
 * @property int $id_product_attribute
 * @property int $amount
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \App\Model\Entity\ShoppingList $shopping_list
 * @property \App\Model\Entity\Product|null $product
 * @property \App\Model\Entity\ProductAttribute|null $product_attribute
 */
class ShoppingListProduct extends AppEntity
{

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'id_product' => true,
        'id_product_attribute' => true,
        'amount' => true,
    ];

}
