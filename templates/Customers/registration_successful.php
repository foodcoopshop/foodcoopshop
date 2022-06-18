<?php
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

<h1><?php echo $title_for_layout; ?></h1>

<ul>

    <li><?php echo __('The_confirmation_of_your_registration_was_sent_to_you_by_email.'); ?></li>
    <li><b><?php echo __('Please_also_check_the_spam_folder_of_your_inbox_and_be_aware_that_receiving_the_mail_might_last_up_to_a_several_minutes!'); ?></b></li>

    <?php if (!Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) { ?>
    <li><b><?php echo __('Your_accout_was_created_but_not_activated_which_means_you_cannot_login_yet!'); ?></b><br />
        <?php echo __('You_will_get_an_email_as_soon_as_we_activated_you.'); ?>.</li>
    <?php } ?>
</ul>

<?php
if (!empty($blogPosts) && $blogPosts->count() > 0) {
    echo '<h2 class="further-news"><a href="'.$this->Slug->getBlogList().'">'.__('News').'</a></h2>';
    echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts
    ]);
}
?>