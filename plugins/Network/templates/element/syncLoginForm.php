<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>

<?php foreach ($syncDomains as $syncDomain) { ?>
    <form class="sync-login-form" autocomplete="off" data-sync-domain="<?php echo $syncDomain->domain; ?>">
        <b><?php echo $syncDomain->domain; ?></b>
        <?php
            echo $this->Html->link('<i class="fa"></i> ' . __d('network', 'Show_more'), 'javascript:void(0);', [
                'class' => 'toggle-link',
                'title' => __d('network', 'Show_more'),
                'escape' => false
            ]);
        ?>
        <div class="toggle-content">
            <span style="display: none;"><?php echo $syncDomain->domain; ?></span>
            <input type="text" class="username" value="" placeholder="<?php echo __d('network', 'Email'); ?>" style="margin-bottom: 5px;" />
            <input type="password" class="password" value="" placeholder="<?php echo __d('network', 'Password'); ?>" />
        </div>
    </form>
<?php } ?>
