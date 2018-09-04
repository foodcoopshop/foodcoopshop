<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="sync-products">

    <?php
        $this->element('addScript', [
            'script' =>
                Configure::read('app.jsNamespace') . ".SyncBase.init();".
                Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Meine Produkte');" .
                Configure::read('app.jsNamespace') . ".SyncProducts.showLocalProductList('".addslashes(json_encode($localResponse))."');".
                Configure::read('app.jsNamespace') . ".SyncProducts.init();".
                Configure::read('app.jsNamespace') . ".Admin.addLoaderToSyncProductDataButton($('.sync-button-wrapper a.btn-default'));"
        ]);
    ?>
    
    <div class="filter-container-not-fixed">
        <?php
           echo $this->element('syncLoginForm', ['syncDomains' => $syncDomains]);
           echo '<div class="sync-button-wrapper">';
               echo $this->Html->link('<i class="fa fa-refresh"></i> Produkte laden', 'javascript:void(0);', [
                   'class' => 'btn btn-success',
                   'escape' => false
                ]);
               echo $this->Html->link(
                   '<i class="fa fa-arrow-circle-left"></i> Produktdaten synchronisieren',
                   $this->Network->getSyncProductData(),
                   [
                       'class' => 'btn btn-default',
                       'escape' => false
                   ]
               );
               echo '</div>';
        ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Network->getNetworkPluginDocs()]); ?>
        </div>
    </div>
    
    <div class="sc"></div>

    <h2>Produkte zuordnen</h2>
    
    <?php

    echo '<p>';
    if (!empty($emptyProductsString)) {
        echo $emptyProductsString;
    }
    echo '<b><a target="_blank" href="'.$this->Network->getNetworkPluginDocs().'">Hier geht\'s zur Anleitung.</a></b> <b>Varianten</b> werden klein und kursiv angezeigt, <b>Produkte</b> groß und nicht kursiv.';
    echo '</p>';

        echo '<div class="product-list local" data-sync-domain="'.'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : ''). '://'.$_SERVER['SERVER_NAME'].'"></div>';
    foreach ($syncDomains as $syncDomain) {
        echo '<div class="product-list remote" data-sync-domain="'.$syncDomain->domain.'"></div>';
    }
    ?>

</div>
