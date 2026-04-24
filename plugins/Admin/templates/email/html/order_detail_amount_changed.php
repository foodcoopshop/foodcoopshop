<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <?php echo $this->element('email/greeting', ['data' => $oldOrderDetail->customer]); ?>

    <tr>
        <td>

            <p>
                <?php echo __('The_amount_of_the_product_{0}_has_been_adapted.', ['<b>'.$oldOrderDetail->product_name.'</b>']); ?> <?php echo __('You_have_ordered_it_on_{0}_at_manufacturer_{1}.', [
                    $oldOrderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')),
                    '<b>'.$oldOrderDetail->product->manufacturer->name.'</b>'
                ]); ?>
            </p>

            <ul style="padding-left: 10px;">
                <li><?php echo __('Old_amount'); ?>: <b><?php echo $oldOrderDetail->product_amount; ?></b></li>
                <li><?php echo __('New_amount_admin'); ?>: <b><?php echo $newOrderDetail->product_amount; ?></b></li>
            </ul>

            <p>
                <?php echo __('Why_has_the_amount_been_adpated?'); ?><br />
                <b><?php echo '"' . $editAmountReason . '"'; ?></b>
            </p>

            <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                <p><?php echo __('PS:_Your_credit_has_been_adapted_automatically.'); ?></p>
            <?php } ?>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
