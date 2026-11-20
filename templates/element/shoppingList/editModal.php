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
 * @var \App\Model\Entity\ShoppingList $shoppingList
 */
?>
<div id="modal-shopping-list-edit" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            echo $this->Form->create($shoppingList, [
                'url' => $this->Slug->getShoppingListEdit($shoppingList->id),
                'novalidate' => 'novalidate',
            ]);
            ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-heart"></i> <?php echo __('Edit_shopping_list'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo h(__('Close')); ?>">
                    <i class="fas fa-2x fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="edit-field">
                    <label for="shopping-list-edit-name"><?php echo __('Name'); ?></label>
                    <?php
                    echo $this->Form->text('name', [
                        'id' => 'shopping-list-edit-name',
                        'class' => 'form-control',
                        'maxlength' => 255,
                    ]);
                    ?>
                </div>
                <div class="edit-field">
                    <label for="shopping-list-edit-position"><?php echo __('Rank'); ?></label>
                    <?php
                    echo $this->Form->number('position', [
                        'id' => 'shopping-list-edit-position',
                        'class' => 'form-control shopping-list-edit-position',
                        'min' => 0,
                    ]);
                    ?>
                </div>
                <div class="shopping-list-rows">
                    <div class="shopping-list-row">
                        <?php
                        echo $this->Form->checkbox('is_default', [
                            'id' => 'shopping-list-edit-is-default',
                        ]);
                        ?>
                        <label for="shopping-list-edit-is-default">
                            <span class="name"><?php echo __('Default_shopping_list'); ?></span>
                            <span class="status"><?php echo __('Default_shopping_list_description'); ?></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0);" class="btn-delete-shopping-list me-auto" data-confirm-message="<?php echo h(__('Do_you_really_want_to_delete_the_shopping_list_{0}?', [$shoppingList->name])); ?>">
                    <i class="fas fa-trash-alt"></i> <?php echo __('Delete_shopping_list'); ?>
                </a>
                <button type="submit" class="btn btn-success"><i class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></button>
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal"><?php echo __('Cancel'); ?></button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>
<?php
// separate form as forms cannot be nested
echo $this->Form->create(null, [
    'url' => $this->Slug->getShoppingListDelete($shoppingList->id),
    'id' => 'shopping-list-delete-form',
    'class' => 'hide',
]);
echo $this->Form->end();
