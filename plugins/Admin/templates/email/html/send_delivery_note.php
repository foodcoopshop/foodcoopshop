<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>

    <tr>
        <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                <?php echo __d('admin', 'Hello'); ?> <?php echo $manufacturer->address_manufacturer->firstname; ?>,
            </td>
    </tr>

    <tr>
        <td>

            <p>
                <?php echo __d('admin', 'this_delivery_note_contains_all_products_that_have_been_delivered_in_{0}.', [$invoicePeriodMonthAndYear]); ?>
            </p>

            <p><b><?php echo __d('admin', 'Thank_you_very_much_for_delivering_your_products_to_us!'); ?></b></p>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
