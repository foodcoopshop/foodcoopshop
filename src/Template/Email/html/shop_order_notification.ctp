<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<?php echo $this->element('email/tableHead'); ?>
    <tbody>
    
        <tr>
            <td style="font-weight: bold; font-size: 18px; padding-bottom: 20px;">
                <?php echo __('Hello'); ?> <?php echo $manufacturer->address_manufacturer->firstname; ?>,
            </td>
        </tr>

        <tr>
            <td>
                <p><?php echo __('There_has_been_placed_a_shop_order_for_{0}_by_{1}_(order_number_{2}).', ['<b>'.$appAuth->getUsername().'</b>', '<b>'.$originalLoggedCustomer['name'].'</b>', $order->id_order]); ?></p>
                <p><?php echo __('You_receive_this_message_because_this_order_was_automatically_moved_into_the_previous_order_period_and_therefore_it_does_not_appear_on_your_order_lists.')?></p>
            </td>
        </tr>
        
    </tbody>
</table>

<?php echo $this->element('email/tableHead', ['cellpadding' => 6]); ?>
    <?php echo $this->element('email/orderedProductsTable', [
        'manufacturerId' => $manufacturer->id_manufacturer,
        'cartProducts' => $cart['CartProducts'],
        'depositSum' => $depositSum,
        'productSum' => $productSum,
        'productAndDepositSum' => $productAndDepositSum
    ]); ?>
</table>
