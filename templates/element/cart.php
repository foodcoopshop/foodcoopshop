<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!$appAuth->user() || $appAuth->isManufacturer()) {
    return;
}

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Cart.setCartButtonIcon('".$cartButtonIcon."');"
]);

if ($appAuth->Cart->getProducts() !== null) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Cart.initCartProducts('".addslashes(json_encode($appAuth->Cart->getProducts()))."');"
    ]);

    if (!empty($cartErrors)) {
        $javascriptClass = 'Cart';
        if ($selfServiceModeEnabled) {
            $javascriptClass = 'SelfService';
        }
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace').".".$javascriptClass.".initCartErrors('".addslashes(json_encode($cartErrors))."');"
        ]);
    }
    if ($this->name == 'Carts' && $this->request->getParam('action') == 'detail') {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();".
            Configure::read('app.jsNamespace').".Cart.initChangeAmountLinks();"
        ]);
    }
}
?>

<div id="cart" class="box cart">

    <h3>
        <i class="fa <?php echo $icon; ?>"></i>
        <?php echo $name; ?>
        <a class="question" target="_blank" href="<?php echo $docsLink; ?>"><i class="far fa-question-circle"></i></a>
    </h3>

    <div class="inner">

        <?php
        if ($showLoadLastOrderDetailsDropdown && !$appAuth->isInstantOrderMode()) {
            $lastOrderDetails = $appAuth->getLastOrderDetailsForDropdown();
            if (!empty($lastOrderDetails)) {
                $lastOrderDetails['remove-all-products-from-cart'] = __('Empty_cart').'...';
                $this->element('addScript', ['script' =>
                    Configure::read('app.jsNamespace') . ".ModalLoadLastOrderDetails.init();"
                ]);
                echo $this->Form->control('load-last-order-details', [
                    'label' => '',
                    'type' => 'select',
                    'empty' => __('Load_past_orders').'...',
                    'options' => $lastOrderDetails
                ]);
            }
        }

        if ($appAuth->isInstantOrderMode()) {
            $this->element('addScript', ['script' =>
                Configure::read('app.jsNamespace').".ModalInstantOrderCancel.init();"
            ]);
            echo '<p class="instant-order-customer-info">';
                echo __('This_order_will_be_placed_for_{0}.', ['<b>'.$this->request->getSession()->read('Auth.instantOrderCustomer')->name.'</b>']);
                if (Configure::read('appDb.FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS')) {
                    echo ' ' . __('Only_stock_products_are_shown.');
                }
            echo '<b><a class="btn btn-outline-light" href="javascript:void(0);">'.__('Cancel_instant_order?').'</a></b>';
            echo '</p>';
        }
        if ($appAuth->user() && $this->Html->paymentIsCashless()) {
            $class = ['payment'];
            if ($creditBalance < 0) {
                $class[] = 'negative';
            }
            echo '<div class="credit-balance-wrapper">';
              echo '<p><b><a href="'.$this->Slug->getMyCreditBalance().'">'.__('Your_credit_balance').'</a></b><b class="'.implode(' ', $class).'">'.$this->Number->formatAsCurrency($creditBalance).'</b></p>';
            echo '</div>';
        }
        ?>

        <p class="no-products"><?php echo $cartEmptyMessage; ?></p>
        <p class="products"></p>
        <p class="sum-wrapper"><b><?php echo __('Sum'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
        <p class="deposit-sum-wrapper"><b><?php echo __('Deposit'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
        <p class="tax-sum-wrapper"><b><?php echo __('Value_added_tax'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>

        <?php if (!$appAuth->isInstantOrderMode() && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
            <p class="timebased-currency-sum-wrapper"><b><?php echo __('From_which_in'); ?> <?php echo Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'); ?></b><span class="sum"><?php echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($appAuth->Cart->getTimebasedCurrencySecondsSum()); ?></span></p>
        <?php } ?>

        <p class="tmp-wrapper"></p>

        <div class="sc"></div>

        <?php
            if ($showCartDetailButton) {
                $this->element('addScript', ['script' => "
                    $('.btn-cart-detail').on('click', function () {
                        foodcoopshop.Helper.disableButton($(this));
                        foodcoopshop.Helper.addSpinnerToButton($(this), 'fa-shopping-cart');
                    });"
                ]);
        ?>

        <p><a class="btn btn-success btn-cart-detail" href="<?php echo $this->Slug->getCartDetail(); ?>">
            <i class="fas fa-shopping-cart fa-lg"></i> <?php echo __('Show_cart_button'); ?>
        </a></p>
        <?php } ?>

        <?php
            if ($showFutureOrderDetails && !empty($futureOrderDetails)) {
                echo '<p class="future-orders">';
                    echo '<b>'.__('Already_ordered_products').'</b><br />';
                    $links = [];
                    foreach($futureOrderDetails as $futureOrderDetail) {
                        $links[] = $this->Html->link(
                            $futureOrderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2')) . ' (' . $futureOrderDetail->orderDetailsCount . ')' ,
                            '/admin/order-details?customerId='.$appAuth->getUserId().'&pickupDay[]=' . $futureOrderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'))
                        );
                    }
                    echo join(' / ', $links);
                echo '</p>';
            }
        ?>

    </div>
</div>