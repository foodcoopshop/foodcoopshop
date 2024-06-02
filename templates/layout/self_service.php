<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

echo $this->element('layout/header');
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
        <div class="self-service-wrapper">
            <h6><?php echo __('Self_service_login_without_account'); ?></h6>
            <h6><?php echo __('Self_service_login_action'); ?></h6>
            <?php
    /*public function isSelfServiceModeByUrl()
    {
        $result = Router::getRequest()->getPath() == '/' . __('route_self_service');
        if (!empty(Router::getRequest()->getQuery('redirect'))) {
            $result |= preg_match('`' . '/' . __('route_self_service') . '`', Router::getRequest()->getQuery('redirect'));
        }
        return $result;
    }*/
            
            ?>

<a class="btn btn-success btn-success-self-service-user-login submit" href="javascript:void(0);">
                    <i class="fas fa-sign-in-alt"></i> <?php echo __('Sign_in_self_service_user'); ?>
                </a>         
                <?php
                /*
                  <button type="submit" class="btn btn-success btn-success-self-service-user-login"><i class="fas fa-sign-in-alt"></i> <?php echo __('Sign_in_self_service_user'); ?></button> */
                ?>
            <h6></h6><h6></h6><h6></h6>
            <h6><?php echo __('Self_service_login_with_account'); ?></h6>
            <h6><?php echo __('Self_service_login_action'); ?></h6>
        </div>
    <?php echo $this->fetch('content'); ?>
    <?php if (!$orderCustomerService->isOrderForDifferentCustomerMode()) { ?>
        <div class="footer">
            <div class="left-wrapper">
                <?php
                    $logoutButton = $this->Menu->getAuthMenuElement($identity);
                    if ($identity !== null) { ?>
                        <a class="btn btn-success <?php echo join(' ', $logoutButton['options']['class']); ?>" href="<?php echo $logoutButton['slug']; ?>"><i class="fas fa-fw fa-sign-out-alt"></i><?php echo $logoutButton['name']; ?></a> <span class="user-name-wrapper"><?php echo $identity->name; ?>
                        <?php if (Configure::read('app.selfServiceModeAutoLogoutDesktopEnabled')) { ?>
                             - </span><?php echo str_replace('X', '<span class="auto-logout-timer"></span>', __('Auto_logout_in_X_sec')); ?>
                        <?php } ?>
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
    <?php } ?>

</div>

<?php
    echo $this->element('layout/footer', [
        'mobileInitFunction' => Configure::read('app.jsNamespace').".Mobile.initMenusSelfService();"
    ]);
?>