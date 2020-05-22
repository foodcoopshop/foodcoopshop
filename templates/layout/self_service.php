<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

echo $this->element('layout/header');

if ($isMobile) {
    if (!empty($_POST)) {
        $js = Configure::read('app.jsNamespace').".Mobile.showSelfServiceCart();";
    } else {
        $js = Configure::read('app.jsNamespace').".Mobile.hideSelfServiceCart();";
    }
    $this->element('addScript', ['script' => $js]);
}

?>

<div id="content" class="self-service">
    <?php
        // avoid "access denied" message on login page if protected /self-service is requested
        if ($this->request->is('get') && $this->request->getParam('action') == 'login') {
            $this->request->getSession()->delete('Flash');
        }
        echo $this->Flash->render();
        echo $this->Flash->render('auth');
    ?>
    <?php echo $this->fetch('content'); ?>

    <div class="footer">
        <div class="left-wrapper">
            <?php
                $logoutButton = $this->Menu->getAuthMenuElement($appAuth);
                if ($appAuth->user()) { ?>
                    <a class="btn btn-success <?php echo join(' ', $logoutButton['options']['class']); ?>" href="<?php echo $logoutButton['slug']; ?>"><i class="fas fa-fw fa-sign-out-alt"></i><?php echo $logoutButton['name']; ?></a> <span class="user-name-wrapper"><?php echo $appAuth->getUserName(); ?> - </span><?php echo str_replace('X', '<span class="auto-logout-timer"></span>', __('Auto_logout_in_X_sec')); ?>
            <?php } ?>
        </div>
        <div class="right-wrapper">
            <a class="btn btn-success" href="<?php echo $this->Slug->getHome(); ?>">
                <i class="fas fa-home"></i> <?php echo __('Home'); ?>
            </a>
            <?php echo $this->element('selfService/addDeposit'); ?>
            <?php echo $this->element('logo'); ?>
        </div>
    </div>

</div>

<?php
    echo $this->element('layout/footer', [
        'mobileInitFunction' => Configure::read('app.jsNamespace').".Mobile.initMenusSelfService();"
    ]);
?>