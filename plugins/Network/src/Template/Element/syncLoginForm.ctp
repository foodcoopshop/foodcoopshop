<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>

<?php foreach ($syncDomains as $syncDomain) { ?>
    <form class="sync-login-form" autocomplete="off" data-sync-domain="<?php echo $syncDomain->domain; ?>">
        <b><?php echo $syncDomain->domain; ?></b>
        <?php
            echo $this->Html->link('<i class="fa"></i> Mehr anzeigen', 'javascript:void(0);', [
                'class' => 'toggle-link',
                'title' => 'Mehr anzeigen',
                'escape' => false
            ]);
        ?>
        <div class="toggle-content">
            <span style="display: none;"><?php echo $syncDomain->domain; ?></span>
            <input type="text" class="username" value="" placeholder="E-Mail" style="margin-bottom: 5px;" />
            <input type="password" class="password" value="" placeholder="Passwort" />
        </div>
    </form>
<?php } ?>
