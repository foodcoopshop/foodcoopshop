<?php
declare(strict_types=1);

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
 * used in the sidebar and returned by ShoppingListsController::ajaxSaveProduct() to update the sidebar
 *
 * @var list<\App\Model\Entity\ShoppingList> $shoppingListsForMenu
 */

if (empty($shoppingListsForMenu)) {
    return;
}

$menuItems = [];
foreach ($shoppingListsForMenu as $shoppingList) {
    $menuItems[] = [
        'slug' => $this->Slug->getShoppingListDetail($shoppingList->id, $shoppingList->name),
        'name' => h($shoppingList->name) . ' <span class="additional-info"> (' . $shoppingList->product_count . ')</span>',
        'options' => [],
    ];
}

echo $this->Menu->render($menuItems, ['id' => 'shopping-lists-menu', 'class' => 'vertical menu', 'header' => '<i class="fas fa-heart"></i> ' . __('My_shopping_lists')]);
