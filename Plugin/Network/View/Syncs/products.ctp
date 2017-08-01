<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<div id="sync-products">

    <?php
        $this->element('addScript', array(
            'script' =>
                Configure::read('app.jsNamespace') . ".Admin.init();".
                Configure::read('app.jsNamespace') . ".SyncProducts.showLocalProductList('".addslashes(json_encode($localResponse))."');".
                Configure::read('app.jsNamespace') . ".SyncProducts.init();"
        ));
    ?>
   
    <div class="filter-container">
        <?php
           echo $this->element('syncLoginForm', array('syncDomains' => $syncDomains));
           $this->element('addScript', array(
               'script' => Configure::read('app.jsNamespace') . ".Admin.addLoaderToSyncProductDataButton($('.sync-button-wrapper a.btn-default'));"
           ));
           echo '<div class="sync-button-wrapper">';
               echo $this->Html->link('<i class="fa fa-refresh"></i> Produkte laden', 'javascript:void(0);', array(
                   'class' => 'btn btn-success',
                   'escape' => false
                ));
               echo $this->Html->link(
                   '<i class="fa fa-refresh"></i> Produkte synchronisieren',
                   $this->Slug->getSyncProductData(),
                   array(
                       'class' => 'btn btn-default',
                       'escape' => false
                   )
               );
               echo '</div>';
        ?>
    </div>

    <div id="help-container">
        <ul>
            <li>Auf dieser Seite kannst du deine Produkte mit anderen Foodcoops abgleichen.</li>
        </ul>
    </div>
    
    <?php
        echo '<div class="product-list local" data-sync-domain="'.'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : ''). '://'.$_SERVER['SERVER_NAME'].'"></div>';
    foreach ($syncDomains as $syncDomain) {
        echo '<div class="product-list remote" data-sync-domain="'.$syncDomain['SyncDomain']['domain'].'"></div>';
    }
    ?>

</div>
