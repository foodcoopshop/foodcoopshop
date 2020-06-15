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

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".ModalText.init('.cart .input.checkbox label a.open-with-modal');".
    Configure::read('app.jsNamespace').".Cart.initCartFinish();"
]);
if (!$appAuth->termsOfUseAccepted()) {
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
    <p class="sum-wrapper"><b><?php echo __('Product_sum_including_vat');?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
    <?php if ($appAuth->Cart->getDepositSum() > 0) { ?>
        <p class="deposit-sum-wrapper"><b>+ <?php echo __('Deposit_sum'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
    <?php } ?>

    <?php if (!$appAuth->isInstantOrderMode() && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
        <p class="timebased-currency-sum-wrapper"><b><?php echo __('From_which_in'); ?> <?php echo Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'); ?></b><span class="sum"><?php echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($appAuth->Cart->getTimebasedCurrencySecondsSum()); ?></span></p>
    <?php } ?>

    <?php if (!empty($appAuth->Cart->getProducts())) { ?>
        <p class="tax-sum-wrapper"><?php echo __('Including_vat'); ?>: <span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>

        <?php if ($appAuth->Cart->getProductsWithUnitCount() > 0) { ?>
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
            echo $this->element('cart/timebasedCurrencyDropdown');
            echo $this->element('cart/variableMemberFeeInfoText');
        ?>

        <?php if (Configure::read('app.showPaymentInfoText')) { ?>
            <p style="margin-top:10px;">
                <?php echo __('To_finish_order_click_here.'); ?>
                <?php echo $this->element('cart/paymentInfoText'); ?>
            </p>
        <?php } ?>

        <?php
            if (Configure::read('app.showPickupPlaceInfo')) {
               echo $this->element('cart/pickupPlaceInfoText');
            }
        ?>

        <?php
            echo $this->element('cart/generalTermsAndConditionsCheckbox');
            echo $this->element('cart/cancellationTermsCheckbox');
            echo $this->element('cart/promiseToPickUpProductsCheckbox');
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