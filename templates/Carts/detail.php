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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".ModalText.init('.cart .input.checkbox label a.open-with-modal');".
    Configure::read('app.jsNamespace').".Cart.initCartFinish();"
]);

if (!empty($identity->getProducts())) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Cart.scrollToCartFinishButton();"
    ]);
}

if (!$identity->termsOfUseAccepted()) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace') . ".Helper.disableButton($('#CartsDetailForm button.btn-success'));"
    ]);
}
?>

<h1><?php echo $title_for_layout; ?></h1>

<?php
$classes = ['cart'];
if (Configure::read('app.showManufacturerListAndDetailPage')) {
    $classes[] = 'showManufacturerListAndDetailPage';
}
?>
<div class="<?php echo join(' ', $classes); ?>">

    <p class="no-products"><?php echo __('Your_cart_is_empty'); ?>.</p>
    <p class="products"></p>

    <?php if (!empty($identity->getProducts())) { ?>

        <div class="sums-wrapper">
            <?php if ($identity->getDepositSum() > 0) { ?>
                <p class="product-sum-wrapper"><b><?php echo __('Value_of_goods');?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
                <p class="deposit-sum-wrapper"><b>+ <?php echo __('Deposit_sum'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
            <?php } ?>
            <p class="total-sum-wrapper">
                <b class="amount-sum-wrapper"><span class="sum"><span class="value">0</span>x</span></b>
                <b><?php echo __('Total'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
        </div>
        <div class="sc"></div>

        <p class="tax-sum-wrapper"><?php echo __('Including_vat'); ?>: <span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>

        <?php if ($identity->getProductsWithUnitCount() > 0) { ?>
            <p>
                <?php echo __('The_delivered_weight_will_eventually_be_adapted_which_means_the_price_can_change_slightly.'); ?>
            </p>
        <?php } ?>

        <?php
            echo $this->Form->create($cart, [
                'class' => 'fcs-form',
                'id' => 'CartsDetailForm',
                'novalidate' => 'novalidate',
                'url' => $this->Slug->getCartFinish()
            ]);
            echo $this->element('cart/variableMemberFeeInfoText');
        ?>

        <?php if (Configure::read('app.showPaymentInfoText')) { ?>
            <p style="margin-top:10px;">
                <?php echo __('To_finish_order_click_here.'); ?>
                <?php echo $this->element('cart/paymentInfoText'); ?>
            </p>
        <?php } ?>

        <?php
           echo $this->element('cart/selectPickupDay');
        ?>

        <?php
            if (Configure::read('app.showPickupPlaceInfo')) {
               echo $this->element('cart/pickupPlaceInfoText');
            }
        ?>

        <?php
            echo $this->element('cart/generalTermsAndConditionsCheckbox');
            echo $this->element('cart/cancellationTermsCheckbox');
        ?>
        <div class="sc"></div>

        <?php
            echo $this->element('cart/pickupDayCommentTextareas');
        ?>

        <?php echo $this->element('cart/orderButton'); ?>

        <?php echo $this->Form->end(); ?>

    <?php } ?>

    <div class="accept-updated-terms-of-use-form-bottom-wrapper">
        <?php echo $this->element('acceptUpdatedTermsOfUseForm'); ?>
    </div>

</div>