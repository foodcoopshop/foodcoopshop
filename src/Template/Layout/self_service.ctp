<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo $this->element('layout/header');
?>

<div id="self-service">
    <?php echo $this->element('cart', [
        'showLinkToSelfService' => false,
        'showLoadLastOrderDetailsDropdown' => false
    ]); ?>
    
    <?php
        echo $this->Flash->render();
        echo $this->Flash->render('auth');
    ?>
    <?php echo $this->fetch('content'); ?>
    
    <div class="logout-wrapper">
        <?php
            $logoutButton = $this->Menu->getAuthMenuElement($appAuth);
            if ($appAuth->user()) { ?>
            	<a class="btn btn-success <?php echo join(' ', $logoutButton['options']['class']); ?>" href="<?php echo $logoutButton['slug']; ?>"><i class="fas fa-fw fa-sign-out-alt"></i><?php echo $logoutButton['name']; ?></a> <?php echo $appAuth->getUserName(); ?> - <?php echo str_replace('X', '<span class="auto-logout-timer"></span>', __('Auto_logout_in_X_seconds')); ?>
        <?php } ?>
	</div>
    
    <?php echo $this->element('logo'); ?>
    
</div>

<?php
    echo $this->element('layout/footer');
?>