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
        Configure::read('app.jsNamespace') . ".Helper.disableButton($('#OrderDetailForm button.btn-success'));"
    ]);
}
?>

<h1>Dein Warenkorb</h1>

<div class="cart">

    <p class="no-products">Dein Warenkorb ist leer.</p>
    <p class="products"></p>
    <p class="sum-wrapper"><b>Warenwert gesamt (inkl. USt.)</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
    <?php if ($appAuth->Cart->getDepositSum() > 0) { ?>
        <p class="deposit-sum-wrapper"><b>+ Pfand gesamt</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
    <?php } ?>
    
    <?php if (!$this->request->getSession()->check('Auth.shopOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
    	<p class="timebased-currency-sum-wrapper"><b>Davon in <?php echo Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'); ?></b><span class="sum"><?php echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($appAuth->Cart->getTimebasedCurrencySecondsSum()); ?></span></p>
    <?php } ?>

    <?php if (!empty($appAuth->Cart->getProducts())) { ?>
        <p class="tax-sum-wrapper">Enthaltene Umsatzsteuer: <span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>

        <?php
            echo $this->Form->create($order, [
                'class' => 'fcs-form',
                'id' => 'CartsDetailForm',
                'url' => $this->Slug->getCartFinish()
            ]);

            if (!$this->request->getSession()->check('Auth.shopOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer() && $appAuth->Cart->getTimebasedCurrencySecondsSum() > 0) {
                echo $this->Form->control('timebased_currency_order.seconds_sum_tmp', [
                    'label' => 'Wie viel davon will ich in '.Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME').' bezahlen?',
                    'type' => 'select',
                    'options' => $this->TimebasedCurrency->getTimebasedCurrencyHoursDropdown($appAuth->Cart->getTimebasedCurrencySecondsSumRoundedUp(), Configure::read('appDb.FCS_TIMEBASED_CURRENCY_EXCHANGE_RATE'))
                ]);
            }
        ?>
        
        <?php if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && Configure::read('app.manufacturerComponensationInfoText') != '') { ?>
            <p style="margin-top: 20px;"><b><?php echo Configure::read('app.manufacturerComponensationInfoText'); ?></b></p>
        <?php } ?>

        <p style="margin-top: 20px;">Um die Bestellung abzuschließen, klicke bitte auf "Zahlungspflichtig bestellen". 
        
        <?php
            if ($this->Html->paymentIsCashless()) {
                echo 'Der Betrag wird dann automatisch von deinem Guthaben abgebucht.</p>';
            } else {
                echo 'Den Betrag bitte bei der Abholung in bar bezahlen.</p>';
            }
        ?>
         
        <p>
            Bitte hole deine Produkte am <b><?php echo $this->Time->getFormattedDeliveryDateByCurrentDay(); ?></b> bei uns (<?php echo str_replace('<br />', ', ', $this->Html->getAddressFromAddressConfiguration()); ?>) ab. Die genaue Uhrzeit steht in der Box rechts.
        </p>
    
    	<?php
            echo '<div id="general-terms-and-conditions" class="featherlight-overlay">';
                echo $this->element('legal/generalTermsAndConditions');
            echo '</div>';
            echo $this->Form->control('Orders.general_terms_and_conditions_accepted', [
                'label' => 'Ich akzeptiere die <a href="#general-terms-and-conditions">AGB</a>.',
                'type' => 'checkbox',
                'escape' => false
            ]);
            echo '<div id="cancellation-terms" class="featherlight-overlay">';
                echo $this->element('legal/cancellationTerms');
            echo '</div>';
            echo $this->Form->control('Orders.cancellation_terms_accepted', [
                'label' => 'Ich nehme das <a href="#cancellation-terms">Rücktrittsrecht</a> zur Kenntnis und akzeptiere dessen Ausschluss für leicht verderbliche Waren.',
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
            echo $this->Html->link('<i class="fa fa-plus-circle"></i> Nachricht an den Abholdienst schreiben?', 'javascript:void(0);', [
            'class' => 'toggle-link',
            'title' => 'Nachricht an den Abholdienst schreiben?',
            'escape' => false
            ]);
            echo '<div class="toggle-content order-comment">';
            echo $this->Form->control('Orders.comment', [
                'type' => 'textarea',
                'placeholder' => 'Deine Nachricht wird bei deiner Bestellung im Admin-Bereich angezeigt. Die Hersteller sehen diese Nachricht nicht.',
                'label' => ''
            ]);
            echo '</div>';
        }
        ?>
        
        <p>
            <button type="submit" class="btn btn-success btn-order"><i class="fa fa-check"></i> Zahlungspflichtig bestellen</button>
        </p>
                
        </form>
    
    <?php } ?>
    
    <div class="accept-updated-terms-of-use-form-bottom-wrapper">
        <?php echo $this->element('acceptUpdatedTermsOfUseForm'); ?>
    </div>
    
</div>