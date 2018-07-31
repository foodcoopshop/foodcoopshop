<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForHref('.cart .input.checkbox label a');".
    Configure::read('app.jsNamespace').".Cart.initCartFinish();"
]);
if (!$appAuth->termsOfUseAccepted()) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace') . ".Helper.disableButton($('#CartsDetailForm button.btn-success'));"
    ]);
}
?>

<h1><?php echo $title_for_layout; ?></h1>

<div class="cart">

    <p class="no-products"><?php echo __('Your_cart_is_empty'); ?>.</p>
    <p class="products"></p>
    <p class="sum-wrapper"><b><?php echo __('Product_sum_including_vat');?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
    <?php if ($appAuth->Cart->getDepositSum() > 0) { ?>
        <p class="deposit-sum-wrapper"><b>+ <?php echo __('Deposit_sum'); ?></b><span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>
    <?php } ?>
    
    <?php if (!$this->request->getSession()->check('Auth.instantOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
    	<p class="timebased-currency-sum-wrapper"><b><?php echo __('From_which_in'); ?> <?php echo Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'); ?></b><span class="sum"><?php echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($appAuth->Cart->getTimebasedCurrencySecondsSum()); ?></span></p>
    <?php } ?>

    <?php if (!empty($appAuth->Cart->getProducts())) { ?>
        <p class="tax-sum-wrapper"><?php echo __('Including_vat'); ?>: <span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>

        <?php
            echo $this->Form->create($cart, [
                'class' => 'fcs-form',
                'id' => 'CartsDetailForm',
                'url' => $this->Slug->getCartFinish()
            ]);
            echo $this->element('cart/timebasedCurrencyDropdown');
            echo $this->element('cart/variableMemberFeeInfoText');
        ?>

        <p style="margin-top: 20px;">
        	<?php echo __('To_finish_order_click_here.'); ?> 
        	<?php echo $this->element('cart/paymentInfoText'); ?>
        </p>
         
        <?php echo $this->element('cart/pickupPlaceInfoText'); ?>
    
    	<?php
            echo $this->element('cart/generalTermsOfUseCheckbox');
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