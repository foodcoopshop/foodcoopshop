<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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
                <?php echo __d('admin', 'this_invoice_contains_all_products_that_have_been_delivered_in_{0}.', [$invoicePeriodMonthAndYear]); ?>
            </p>

            <p><?php echo __d('admin', 'If_the_invoice_amount_should_differ_from_your_own_recordings_please_tell_us.'); ?></p>

            <p><?php echo __d('admin', 'The_invoice_amount_will_be_transfered_to_your_bank_accound_withing_the_next_days.'); ?></p>

            <p><b><?php echo __d('admin', 'Thank_you_very_much_for_delivering_your_products_to_us!'); ?></b></p>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
