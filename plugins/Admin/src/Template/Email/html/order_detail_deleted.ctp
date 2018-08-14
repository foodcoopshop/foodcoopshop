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
use Cake\Core\Configure;

?>
<?php echo $this->element('email/tableHead'); ?>
<tbody>
    
    <?php echo $this->element('email/greeting', ['data' => $orderDetail->customer]); ?>
            
    <tr>
        <td>

            <p>
                <b><?php echo __d('admin', 'sorry,_{0}_can_not_be_delivered.', [$orderDetail->product_name]); ?></b>
            </p>

            <ul style="padding-left: 10px;">
                <li><?php echo __d('admin', 'Price'); ?>: <b><?php echo $this->MyNumber->formatAsCurrency($orderDetail->total_price_tax_incl); ?></b></li>
                <li><?php echo __d('admin', 'Amount'); ?>: <b><?php echo $orderDetail->product_amount; ?></b></li>
                <li><?php echo __d('admin', 'Manufacturer'); ?>: <b><?php echo $orderDetail->product->manufacturer->name; ?></b></li>
                <li><?php echo __d('admin', 'Order_date'); ?>: <b><?php echo $orderDetail->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')); ?></b></li>
            </ul>

            <p>
                <?php echo __d('admin', 'Why_has_the_product_been_cancelled?'); ?><br />
                <b><?php echo '"' . $cancellationReason . '"'; ?></b>
            </p>

            <p><?php echo __d('admin', 'Sorry,_but_sometimes_our_manufacturers_cannot_deliver_the_ordered_products._You_receive_this_email_so_you_can_buy_the_products_elsewhere.'); ?></p>
            <p><?php echo __d('admin', 'Thanks_for_respecting_that!'); ?></p>
                
                <?php if ($this->MyHtml->paymentIsCashless()) { ?>
                    <p><?php echo __d('admin', 'PS:_Your_credit_has_been_adapted_automatically.'); ?></p>
                <?php } ?>

        </td>

    </tr>

</tbody>
<?php echo $this->element('email/tableFoot'); ?>
