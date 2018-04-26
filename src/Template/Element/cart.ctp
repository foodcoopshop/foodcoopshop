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

if (!$appAuth->user() || $appAuth->isManufacturer() || !Configure::read('appDb.FCS_CART_ENABLED')) {
    return;
}

if ($appAuth->Cart->getProducts() !== null) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Cart.initCartProducts('".addslashes(json_encode($appAuth->Cart->getProducts()))."');"
    ]);

    if (!empty($cartErrors)) {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace').".Cart.initCartErrors('".addslashes(json_encode($cartErrors))."');"
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
    
    <h3><i class="fa fa-shopping-cart"></i>Warenkorb</h3>
    
    <div class="inner">
    
    	<?php
    	if (!$this->request->getSession()->check('Auth.shopOrderCustomer')) {
    	    $lastOrderDetails = $appAuth->getLastOrderDetailsForDropdown();
    	    if (!empty($lastOrderDetails)) {
    	        $lastOrderDetails['remove-all-products-from-cart'] = 'Warenkorb leeren...';
    	        $this->element('addScript', ['script' =>
        	        Configure::read('app.jsNamespace') . ".Cart.initLoadLastOrderDetailsDropdown();"
        	    ]);
        	    echo $this->Form->control('load-last-order-details', [
            	    'label' => '',
            	    'type' => 'select',
        	        'empty' => 'Vergangene Bestellung laden...',
        	        'options' => $lastOrderDetails
            	]);
    	    }
    	}
        	
        if ($appAuth->user() && $this->Html->paymentIsCashless()) {
            if ($this->request->getSession()->check('Auth.shopOrderCustomer')) {
                $this->element('addScript', ['script' =>
                    Configure::read('app.jsNamespace').".Helper.initLogoutShopOrderCustomerButton();"
                ]);
                echo '<p class="shop-order-customer-info">Diese Bestellung wird für <b>'.$this->request->getSession()->read('Auth.shopOrderCustomer')->name.'</b> getätigt. <b><a class="btn btn-default" href="javascript:void(0);">Sofort-Bestellung abbrechen?</a></b></p>';
            }
            $class = ['payment'];
            if ($creditBalance < 0) { // set in FrontendController
                $class[] = 'negative';
            }
            echo '<div class="credit-balance-wrapper">';
              echo '<p><b><a href="'.$this->Slug->getMyCreditBalance().'">Dein Guthaben</a></b><b class="'.implode(' ', $class).'">'.$this->Html->formatAsEuro($creditBalance).'</b></p>';
            if ($shoppingLimitReached) {
                echo '<p><b class="negative">Du hast das Bestelllimit von ' . $this->Html->formatAsEuro(Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE')) . ' erreicht. Bitte lade vor dem Bestellen neues Guthaben auf.</b></p>';
                echo '<p><a class="btn btn-success" href="'.$this->Slug->getMyCreditBalance().'">';
                echo 'Guthaben aufladen';
                echo '</a></p>';
            }
                echo '</div>';
        }
        ?>
        
        <?php if (!isset($shoppingLimitReached) || !$shoppingLimitReached) {  // set in AppController ?>
            <p class="no-products">Dein Warenkorb ist leer.</p>
            <p class="products"></p>
            <p class="sum-wrapper"><b>Summe</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
            <p class="deposit-sum-wrapper"><b>Pfand</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
            <p class="tax-sum-wrapper"><b>Umsatzsteuer</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
            
            <?php if (!$this->request->getSession()->check('Auth.shopOrderCustomer') && $appAuth->isTimebasedCurrencyEnabledForCustomer()) { ?>
            	<p class="timebased-currency-sum-wrapper"><b>Davon in <?php echo Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME'); ?></b><span class="sum"><?php echo $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($appAuth->Cart->getTimebasedCurrencySecondsSum()); ?></span></p>
            <?php } ?>
            
            <p class="tmp-wrapper"></p>
            
            <div class="sc"></div>
            
            <p><a class="btn btn-success" href="<?php echo $this->Slug->getCartDetail(); ?>">
                <i class="fa fa-shopping-cart"></i> Warenkorb anzeigen
            </a></p>
            
        <?php } ?>
        
    </div>
</div>