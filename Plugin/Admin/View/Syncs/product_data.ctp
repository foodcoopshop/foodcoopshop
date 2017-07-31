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
<div id="sync-product-data">

    <?php
        $this->element('addScript', array(
            'script' =>
                Configure::read('app.jsNamespace') . ".Admin.init();".
                Configure::read('app.jsNamespace') . ".SyncProductData.init('".addslashes(json_encode($syncProducts))."', '".addslashes(json_encode($localSyncProducts))."');".
                Configure::read('app.jsNamespace') . ".SyncProductData.showLocalProductList();"
        ));
    ?>
   
    <div class="filter-container">
        <?php
           echo $this->element('syncLoginForm', array('syncDomains' => $syncDomains));
           echo '<div id="sync-button-wrapper">';
               echo $this->Html->link('<i class="fa fa-refresh"></i> Jetzt synchronisieren', 'javascript:void(0);', array(
                   'class' => 'btn btn-success',
                   'escape' => false
                ));
               echo '</div>';
        ?>
    </div>

    <div id="help-container">
        <ul>
            <li>Auf dieser Seite kannst du deine Produkte mit anderen Foodcoops abgleichen.</li>
        </ul>
    </div>
    
    <div class="product-list"></div>

</div>
