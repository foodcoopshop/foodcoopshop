<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <?php echo $this->element('email/greeting', ['data' => $customer]); ?>

    <tr>
        <td>

            <p>
                <?php
                    echo __d('admin', 'The_ordered_product_{0}_was_successfully_assigned_from_{1}_to_{2}.', [
                        '<b>' . $oldOrderDetail->product_name . '</b>',
                        Configure::read('app.htmlHelper')->getNameRespectingIsDeleted($oldOrderDetail->customer),
                        '<b>' . $newCustomer->name . '</b>'
                    ]);
                    echo $amountString;
                ?>
            </p>

            <p>
                <?php echo __d('admin', 'Why_has_another_member_been_assigned?'); ?><br />
                <b><?php echo '"' . $editCustomerReason . '"'; ?></b>
            </p>

            <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                <p><?php echo __d('admin', 'PS:_Your_credit_has_been_adapted_automatically.'); ?></p>
            <?php } ?>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
