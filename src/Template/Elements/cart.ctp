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

if (!$appAuth->loggedIn() || $appAuth->isManufacturer() || !Configure::read('AppConfig.db_config_FCS_CART_ENABLED')) {
    return;
}

if ($appAuth->Cart->getProducts() !== null) {
    $this->element('addScript', ['script' =>
        Configure::read('AppConfig.jsNamespace').".Cart.initCartProducts('".addslashes(json_encode($appAuth->Cart->getProducts()))."');"
    ]);

    if (!empty($cartErrors)) {
        $this->element('addScript', ['script' =>
            Configure::read('AppConfig.jsNamespace').".Cart.initCartErrors('".addslashes(json_encode($cartErrors))."');"
        ]);
    }
    if ($this->name == 'Carts' && $this->action == 'detail') {
        $this->element('addScript', ['script' =>
            Configure::read('AppConfig.jsNamespace').".Cart.initRemoveFromCartLinks();".
            Configure::read('AppConfig.jsNamespace').".Cart.initChangeAmountLinks();"
        ]);
    }
}
?>

<div id="cart" class="box cart">
    
    <h3><i class="fa fa-shopping-cart"></i>Warenkorb</h3>
    
    <div class="inner">
        <?php
        if ($appAuth->loggedIn() && $this->Html->paymentIsCashless()) {
            if ($this->Session->read('Auth.shopOrderCustomer')) {
                $this->element('addScript', ['script' =>
                    Configure::read('AppConfig.jsNamespace').".Helper.initLogoutShopOrderCustomerButton();"
                ]);
                echo '<p class="shop-order-customer-info">Diese Bestellung wird für <b>'.$this->Session->read('Auth.shopOrderCustomer')['Customer']['name'].'</b> getätigt. <b><a class="btn btn-default" href="javascript:void(0);">Sofort-Bestellung abbrechen?</a></b></p>';
            }
            $class = ['payment'];
            if ($creditBalance < 0) { // set in AppController
                $class[] = 'negative';
            }
            echo '<div class="credit-balance-wrapper">';
              echo '<p><b><a href="'.$this->Slug->getMyCreditBalance().'">Dein Guthaben</a></b><b class="'.implode(' ', $class).'">'.$this->Html->formatAsEuro($creditBalance).'</b></p>';
            if ($shoppingLimitReached) {
                echo '<p><b class="negative">Du hast das Bestelllimit von ' . $this->Html->formatAsEuro(Configure::read('AppConfig.db_config_FCS_MINIMAL_CREDIT_BALANCE')) . ' erreicht. Bitte lade vor dem Bestellen neues Guthaben auf.</b></p>';
                echo '<p><a class="btn btn-success" href="'.$this->Slug->getMyCreditBalance().'">';
                echo 'Guthaben aufladen';
                echo '</a></p>';
            }
                echo '</div>';
        }
        ?>
        
        <?php if (!isset($shoppingLimitReached) || !$shoppingLimitReached) {  // set in appcontroller ?>
        
            <p class="no-products">Dein Warenkorb ist leer.</p>
            <p class="products"></p>
            <p class="sum-wrapper"><b>Summe</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
            <p class="deposit-sum-wrapper"><b>Pfand</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
            <p class="tax-sum-wrapper"><b>Umsatzsteuer</b><span class="sum"><?php echo $this->Html->formatAsEuro(0); ?></span></p>
            <p class="tmp-wrapper"></p>
            
            <div class="sc"></div>
            
            <p><a class="btn btn-success" href="<?php echo $this->Slug->getCartDetail(); ?>">
                <i class="fa fa-shopping-cart fa-lg"></i> Warenkorb anzeigen
            </a></p>
            
        <?php } ?>
        
    </div>
</div>