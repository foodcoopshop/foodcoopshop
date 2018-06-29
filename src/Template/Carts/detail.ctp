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
use Cake\I18n\I18n;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForHref('.cart .input.checkbox label a');".
    Configure::read('app.jsNamespace').".Cart.initCartFinish();"
]);
if (!$appAuth->termsOfUseAccepted()) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace') . ".Helper.disableButton($('#OrderDetailForm button.btn-success'));"
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
    
    <?php if (!$this->request->getSession()->check('Auth.shopOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
    	<p class="timebased-currency-sum-wrapper"><b><?php echo __('From_which_in'); ?> <?php echo Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'); ?></b><span class="sum"><?php echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($appAuth->Cart->getTimebasedCurrencySecondsSum()); ?></span></p>
    <?php } ?>

    <?php if (!empty($appAuth->Cart->getProducts())) { ?>
        <p class="tax-sum-wrapper"><?php echo __('Including_vat'); ?>: <span class="sum"><?php echo $this->Number->formatAsCurrency(0); ?></span></p>

        <?php
            echo $this->Form->create($order, [
                'class' => 'fcs-form',
                'id' => 'CartsDetailForm',
                'url' => $this->Slug->getCartFinish()
            ]);

            if (!$this->request->getSession()->check('Auth.shopOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer() && $appAuth->Cart->getTimebasedCurrencySecondsSum() > 0) {
                echo $this->Form->control('timebased_currency_order.seconds_sum_tmp', [
                    'label' => __('How_much_of_it_do_i_want_to_pay_in_{0}?', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]),
                    'type' => 'select',
                    'options' => $this->TimebasedCurrency->getTimebasedCurrencyHoursDropdown($appAuth->Cart->getTimebasedCurrencySecondsSumRoundedUp(), Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE'))
                ]);
            }
        ?>
        
        <?php if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && Configure::read('app.manufacturerComponensationInfoText') != '') { ?>
            <p style="margin-top: 20px;"><b><?php echo Configure::read('app.manufacturerComponensationInfoText'); ?></b></p>
        <?php } ?>

        <p style="margin-top: 20px;"><?php echo __('To_finish_order_click_here.'); ?> 
        
        <?php
            if ($this->Html->paymentIsCashless()) {
                echo __('The_amount_will_be_reduced_from_your_credit_balance.');
            } else {
                echo __('Please_pay_when_picking_up_products.');
            }
        ?>
        </p>
         
        <p>
            <?php
                echo __(
                    'Please_pick_up_your_products_on_{0}_at_{1}.', [
                        '<b>'.$this->Time->getFormattedDeliveryDateByCurrentDay().'</b>',
                        str_replace('<br />', ', ', $this->Html->getAddressFromAddressConfiguration())
                    ]
                );
            ?>
        </p>
    
    	<?php
            echo '<div id="general-terms-and-conditions" class="featherlight-overlay">';
                echo $this->element('legal/'.I18n::getLocale().'/generalTermsAndConditions');
            echo '</div>';
            $generalTermsOfUseLink = '<a href="#general-terms-and-conditions">'.__('general_terms_and_conditions').'</a>';
            echo $this->Form->control('Orders.general_terms_and_conditions_accepted', [
                'label' => __('I_accept_the_{0}', [$generalTermsOfUseLink]),
                'type' => 'checkbox',
                'escape' => false
            ]);

            echo '<div id="cancellation-terms" class="featherlight-overlay">';
                echo $this->element('legal/'.I18n::getLocale().'/rightOfWithdrawalTerms');
            echo '</div>';
            $cancellationTermsLink = '<a href="#cancellation-terms">'.__('right_of_withdrawal').'</a>';
            echo $this->Form->control('Orders.cancellation_terms_accepted', [
                'label' => __('I_accept_the_{0}_and_accept_that_it_is_not_valid_for_perishable_goods.', [$cancellationTermsLink]),
                'type' => 'checkbox',
                'escape' => false
            ]);
        ?>
        <div class="sc"></div>
        
        <?php
        if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED')) {
            $this->element('addScript', ['script' =>
                Configure::read('app.jsNamespace') . ".Helper.bindToggleLinks();"
            ]);
            if (((isset($cartErrors) && $cartErrors) || (isset($formErrors) && $formErrors)) && !empty($this->request->getData('Orders.comment')) && $this->request->getData('Orders.comment') != '') {
                $this->element('addScript', ['script' =>
                "$('.toggle-link').trigger('click');"
                ]);
            }
            echo $this->Html->link('<i class="fa"></i> ' . __('Write_message_to_pick_up_team?'), 'javascript:void(0);', [
            'class' => 'toggle-link',
            'title' => __('Write_message_to_pick_up_team?'),
            'escape' => false
            ]);
            echo '<div class="toggle-content order-comment">';
            echo $this->Form->control('Orders.comment', [
                'type' => 'textarea',
                'placeholder' => __('Placeholder_message_order_comment.'),
                'label' => ''
            ]);
            echo '</div>';
        }
        ?>
        
        <p>
            <button type="submit" class="btn btn-success btn-order"><i class="fa fa-check fa-lg"></i> <?php echo __('Order_button'); ?></button>
        </p>
                
        </form>
    
    <?php } ?>
    
    <div class="accept-updated-terms-of-use-form-bottom-wrapper">
        <?php echo $this->element('acceptUpdatedTermsOfUseForm'); ?>
    </div>
    
</div>