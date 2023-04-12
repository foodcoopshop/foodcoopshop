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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1><?php echo __('Order_placed'); ?></h1>

<p><b><?php echo __('Thank_you_your_order_was_placed_succesfully.'); ?></b></p>

<ul>

    <li><?php echo __('The_order_confirmation_was_sent_by_email_to_{0}.', ['<b>'.$appAuth->getEmail().'</b>']); ?></li>
    <?php if (Configure::read('app.showPickupPlaceInfo')) { ?>
        <li><?php echo __('Please_pick_up_the_ordered_products_at:_{0}', ['<b>'.str_replace('<br />', ', ', $this->Html->getAddressFromAddressConfiguration()).'</b>']); ?></li>
    <?php } ?>

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
