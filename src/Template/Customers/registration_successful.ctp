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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1><?php echo $title_for_layout; ?></h1>

<ul>

    <li><?php echo __('The_confirmation_of_your_registration_was_sent_to_you_by_email.'); ?></li>

    <?php if (!Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) { ?>
    <li><b><?php echo __('Your_accout_was_created_but_not_activated_which_means_you_cannot_login_yet!'); ?></b><br />
        <?php echo __('You_will_get_an_email_as_soon_as_we_activated_you.'); ?>.</li>
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