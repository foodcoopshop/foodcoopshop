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
    
    <p style="margin-top: 40px;">Um die Bestellung abzuschließen, klicke bitte auf "Zahlungspflichtig bestellen".</p>
    
    <?php
        if ($this->Html->paymentIsCashless()) {
            echo '<p>Der Betrag wird automatisch von deinem Guthaben abgebucht.</p>';
        } else {
            echo '<p>Der Betrag bitte bei der Abholung in bar bezahlen.</p>';
        }
    ?>
    
	<p><a class="btn btn-success" href="<?php echo $this->Slug->getCartFinish(); ?>"><i class="fa fa-check fa-lg"></i> Zahlungspflichtig bestellen</a></p>	
        
</div>