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

use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1><?php echo __('Order_placed'); ?></h1>

<p><b><?php echo __('Thank_you_your_order_was_placed_succesfully.'); ?></b></p>

<ul>

    <li><?php echo __('The_order_confirmation_was_sent_by_email_to_{0}.', ['<b>'.$appAuth->getEmail().'</b>']); ?></li>
    <li><?php echo __('Please_pick_up_the_ordered_products_at:_{0}', ['<b>'.str_replace('<br />', ', ', $this->Html->getAddressFromAddressConfiguration()).'</b>']); ?></li>

    <?php if ($this->Html->paymentIsCashless()) { ?>
        <li><a class="btn btn-success" href="<?php echo $this->Slug->getMyCreditBalance(); ?>"><?php echo __('Increase_credit'); ?></a></li>
    <?php } ?>

</ul>

<?php
if (!empty($blogPosts) && $blogPosts->count() > 0) {
    echo '<h2><a href="'.$this->Slug->getBlogList().'">'.__('News').'</a></h2>';
    echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts
    ]);
}
?>
