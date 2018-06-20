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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1><?php echo __('Order_placed'); ?></h1>

<p><b><?php echo __('Thank_you_your_order_was_placed_succesfully.'); ?></b></p>

<ul>

    <li><?php echo __('The_order_confirmation_was_sent_by_email_to_{0}.', ['<b>'.$order->customer->email.'</b>']); ?></li>
    <li><?php echo __('Please_pick_up_the_ordered_products_on_{0}_in_our_pick_up_store.', [$this->Time->getFormattedDeliveryDateByCurrentDay()]); ?></li>

    <?php if ($this->Html->paymentIsCashless()) { ?>
        <li>
            <?php if ($order->total_deposit > 0) { ?>
            	 <?php echo __('The_product_value_{0}_with_an_additional_deposit_of_{1}_was_automatically_reduced_from_your_credit.', ['<b>'.$this->Number->formatAsCurrency($order->total_paid).'</b>', '<b>'.$this->Number->formatAsCurrency($order->total_deposit).'</b>']); ?>
            <?php } else { ?>
            	<?php echo __('The_product_value_{0}_was_automatically_reduced_from_your_credit.', ['<b>'.$this->Number->formatAsCurrency($order->total_paid).'</b>']); ?>
            <?php } ?>
        </li>
        <li><a class="btn btn-success" href="<?php echo $this->Slug->getMyCreditBalance(); ?>"><?php echo __('Increase_credit'); ?></a></li>
    <?php } else { ?>
        <li><?php echo __('Please_do_not_forget_to_bring_exact_amount_of_cash.'); ?></li>
    <?php } ?>

</ul>

<?php
if (!empty($blogPosts)) {
    echo '<h2><a href="'.$this->Slug->getBlogList().'">'.__('News').'</a></h2>';
    echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts
    ]);
}
?>
