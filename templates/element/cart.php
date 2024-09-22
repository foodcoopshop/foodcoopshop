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
use App\Model\Entity\Customer;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Cart.setCartButtonIcon('".$cartButtonIcon."');"
]);

if ($identity === null || $identity->isManufacturer()) {
    return;
}

if ($identity->getProducts() !== null) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Cart.initCartProducts('".addslashes(json_encode($identity->getProducts()))."');"
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
    if ($this->name == 'Carts' && in_array($this->request->getParam('action'), ['finish', 'detail'])) {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace').".Cart.initRemoveFromCartLinks();".
            Configure::read('app.jsNamespace').".Cart.initChangeAmountLinks();"
        ]);
    }
}
?>

<div id="cart" class="box cart">
    <h3>
        <i class="fas <?php echo $icon; ?>"></i>
        <?php echo $name; ?>
        <?php
        if (!Configure::read('app.selfServiceEasyModeEnabled') || !$identity->isSelfServiceCustomer()){
        ?>
            <a class="question" target="_blank" href="<?php echo $docsLink; ?>"><i class="far fa-question-circle"></i></a>
        <?php } ?>
    </h3>
    <div class="inner">

        <?php
        if ($orderCustomerService->isOrderForDifferentCustomerMode()) {
            $this->element('addScript', ['script' =>
                Configure::read('app.jsNamespace').".ModalOrderForDifferentCustomerCancel.init();"
            ]);
            echo '<p class="cart-extra-info order-for-different-customer-info">';
                echo __('This_order_will_be_placed_for_{0}.', ['<b>'.$identity->name.'</b>']);
                if (Configure::read('appDb.FCS_SHOW_NON_STOCK_PRODUCTS_IN_INSTANT_ORDERS')) {
                    echo ' ' . __('Only_stock_products_are_shown.');
                }
            echo '<b><a class="btn btn-outline-light" href="javascript:void(0);">'.__('Cancel').'</a></b>';
            echo '</p>';
        }

        if (isset($shoppingPrice) && in_array($shoppingPrice, [Customer::PURCHASE_PRICE, Customer::ZERO_PRICE])) {
            echo '<p class="cart-extra-info shopping-price-info">';
                echo $this->Html->getShoppingPricesForDropdown()[$shoppingPrice] . ' ' . __('activated');
            echo '</p>';
        }

        if ($showLoadLastOrderDetailsDropdown && !$orderCustomerService->isOrderForDifferentCustomerMode()) {
            $lastOrderDetails = $identity->getLastOrderDetailsForDropdown();
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

        if ($identity !== null && $this->Html->paymentIsCashless() && !$identity->isSelfServiceCustomer()) {
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

        <div class="sums-wrapper">
            <p class="product-sum-wrapper"><b><?php echo __('Value_of_goods'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
            <p class="deposit-sum-wrapper"><b>+ <?php echo __('Deposit_sum'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
            <p class="total-sum-wrapper">
                <b class="amount-sum-wrapper"><span class="sum"><span class="value">0</span>x</span></b>
                <b><?php echo __('Total'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span>
            </p>
            <p class="tax-sum-wrapper"><b><?php echo __('Value_added_tax'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
        </div>

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
            <i class="fas fa-shopping-cart fa-lg fa-fw"></i> <?php echo __('Show_cart_button'); ?>
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
                            '/admin/order-details?customerId='.$identity->getId().'&pickupDay[]=' . $futureOrderDetail->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'))
                        );
                    }
                    echo join(' / ', $links);
                echo '</p>';
            }
        ?>

    </div>
</div>