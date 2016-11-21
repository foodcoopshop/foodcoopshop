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
$this->element('addScript', array('script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForHref('.cart .input.checkbox label a');".
    Configure::read('app.jsNamespace').".Cart.initCartFinish();"
));
?>

<h1>Dein Warenkorb</h1>

<div class="cart">

	<p class="no-products">Dein Warenkorb ist leer.</p>
    <p class="products"></p>
    <p class="sum-wrapper"><b>Warenwert gesamt (inkl. USt.)</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
    <?php if ($appAuth->Cart->getDepositSum() > 0) { ?>
    	<p class="deposit-sum-wrapper"><b>+ Pfand gesamt</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
    <?php } ?>
    
    <?php
        if (empty($appAuth->Cart->getProducts())) {
            $this->element('addScript', array('script' =>
                "foodcoopshop.Helper.disableButton($('.cart .btn-success'));"
            ));
        }
    ?>
    
    <p class="tax-sum-wrapper">Enthaltene Umsatzsteuer: <span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
    
    <p>Um die Bestellung abzuschließen, klicke bitte auf "Zahlungspflichtig bestellen". 
    
<?php

    if ($this->Html->paymentIsCashless()) {
        echo 'Der Betrag wird dann automatisch von deinem Guthaben abgebucht.</p>';
    } else {
        echo 'Den Betrag bitte bei der Abholung in bar bezahlen.</p>';
    }
 
    echo $this->Form->create('Order', array(
        'class' => 'fcs-form',
        'url' => $this->Slug->getCartFinish()
    ));
    
        echo '<div id="general-terms-and-conditions" class="featherlight-overlay">';
            echo $this->element('legal/generalTermsAndConditions');
        echo '</div>';
        echo $this->Form->input('Order.general_terms_and_conditions_accepted', array(
            'label' => 'Ich akzeptiere die <a href="#general-terms-and-conditions">AGB</a>',
            'type' => 'checkbox'
        ));
        echo '<div id="cancellation-terms" class="featherlight-overlay">';
            echo $this->element('legal/cancellationTerms');
        echo '</div>';
        echo $this->Form->input('Order.cancellation_terms_accepted', array(
            'label' => 'Ich akzeptiere den <a href="#cancellation-terms">Ausschluss des Rücktrittsrechts</a>',
            'type' => 'checkbox'
        ));
    ?>
    <div class="sc"></div>
    
	<p>
		<button type="submit" class="btn btn-success"><i class="fa fa-check fa-lg"></i> Zahlungspflichtig bestellen</button>
	</p>
    		
    </form>
    
</div>