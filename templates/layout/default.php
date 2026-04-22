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

echo $this->element('layout/header');

?>

<div id="container">

    <div id="header">
        <?php
            echo $this->Flash->render();
            echo $this->Flash->render('auth');
        ?>
        <?php echo $this->element('userMenu'); ?>
        <div class="header-main-row">
            <?php echo $this->element('logo'); ?>
            <?php echo $this->element('mainMenu'); ?>
        </div>
    </div>

    <div id="content">
        <?php echo $this->element('slider', ['slides' => !empty($slides) ? $slides : []]); ?>
        <?php echo $this->element('sidebar'); ?>
        <div id="inner-content" class="<?php echo empty($categoriesForMenu) && empty($manufacturersForMenu) ? 'without-sidebar' : ''; ?>">
            <?php echo $this->fetch('content'); ?>
            <div class="sc"></div>
        </div>
    </div>

    <div id="footer">
        <div class="inner-footer">
            <?php
                echo $this->element('footer');
            ?>
        </div>
    </div>

    <div class="hide">
        <div id="modal-cart-wrapper">
            <?php
                echo $this->element('cart', [
                    'selfServiceModeEnabled' => false,
                    'showLoadLastOrderDetailsDropdown' => true,
                    'showFutureOrderDetails' => true,
                    'icon' => 'fa-shopping-cart',
                    'name' => __('Cart'),
                    'cartButtonIcon' => 'fa-cart-plus',
                    'cartEmptyMessage' => __('Your_cart_is_empty.'),
                ]);
            ?>
        </div>
        <div id="modal-info-box-wrapper">
            <?php
                echo $this->element('globalNoDeliveryDayBox');
                echo $this->element('infoBox');
            ?>
    </div>

</div>

<?php echo $this->element('scrollToTopButton'); ?>

<div class="sc"></div>

<?php
    echo $this->element('layout/footer', [
        'mobileInitFunction' => Configure::read('app.jsNamespace').".Mobile.initMenusFrontend();"
    ]);
?>
