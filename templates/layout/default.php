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
        <?php echo $this->element('logo'); ?>
        <?php if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) { ?>
            <?php echo $this->element('productSearch', [
                'action' => __('route_search'),
                'placeholder' =>  __('Search'),
                'resetSearchUrl' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $this->Slug->getAllProducts(),
                'includeCategoriesDropdown' => false
            ]); ?>
        <?php } ?>
        <?php echo $this->element('userMenu'); ?>
        <?php echo $this->element('mainMenu'); ?>
    </div>

    <div id="content">
        <?php
            echo $this->Flash->render();
            echo $this->Flash->render('auth');
        ?>
        <?php echo $this->element('slider', ['slides' => !empty($slides) ? $slides : []]); ?>
        <?php echo $this->element('sidebar'); ?>
        <div id="inner-content">
            <?php echo $this->fetch('content'); ?>
            <div class="sc"></div>
        </div>
    </div>

    <div id="right">
        <div class="inner-right">
            <?php echo $this->element('globalNoDeliveryDayBox'); ?>
            <?php echo $this->element('cart', [
                'selfServiceModeEnabled' => false,
                'showLoadLastOrderDetailsDropdown' => true,
                'showCartDetailButton' => true,
                'showFutureOrderDetails' => true,
                'icon' => 'fa-shopping-cart',
                'name' => __('Cart'),
                'docsLink' => $this->Html->getDocsUrl(__('docs_route_order_handling')),
                'cartButtonIcon' => 'fa-cart-plus',
                'cartEmptyMessage' => __('Your_cart_is_empty.'),
            ]); ?>
            <?php echo $this->element('infoBox'); ?>
        </div>
    </div>

    <div id="footer">
        <div class="inner-footer">
            <?php
                echo $this->element('footer');
            ?>
        </div>
    </div>

</div>

<?php echo $this->element('scrollToTopButton'); ?>

<div class="sc"></div>

<?php
    echo $this->element('layout/footer', [
        'mobileInitFunction' => Configure::read('app.jsNamespace').".Mobile.initMenusFrontend();"
    ]);
?>
