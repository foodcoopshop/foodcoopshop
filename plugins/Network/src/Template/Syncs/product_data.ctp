<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<div id="sync-product-data">

    <?php
        $this->element('addScript', [
            'script' =>
                Configure::read('app.jsNamespace') . ".SyncBase.init();".
                Configure::read('app.jsNamespace') . ".Admin.selectMainMenuAdmin('Meine Produkte');" .
                Configure::read('app.jsNamespace') . ".Admin.addLoaderToSyncProductDataButton($('a.sync-product-button.btn-default'));"
        ]);

        if (!empty($localSyncProducts)) {
            $this->element('addScript', [
                'script' =>
                    Configure::read('app.jsNamespace') . ".SyncProductData.init('".addslashes(json_encode($syncProducts))."', '".addslashes(json_encode($localSyncProducts))."');".
                    Configure::read('app.jsNamespace') . ".SyncProductData.showLocalProductList();"
            ]);
        }

    ?>
   
    <div class="filter-container-not-fixed">
        <?php
        if (!empty($localSyncProducts)) {
            echo $this->element('syncLoginForm', ['syncDomains' => $syncDomains]);
        }
        echo '<div class="sync-button-wrapper">';
        if (!empty($localSyncProducts)) {
            echo $this->Html->link('<i class="fa fa-check-circle"></i> Vorschau laden', 'javascript:void(0);', [
                'class' => 'btn btn-success show-preview-button',
                'escape' => false
            ]);
        }
        if (!empty($localSyncProducts)) {
            echo $this->Html->link('<i class="fa fa-refresh"></i> Produkte synchronisieren', 'javascript:void(0);', [
            'class' => 'btn btn-danger sync-products-button',
            'escape' => false
            ]);
        }
           echo '</div>';
        ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Network->getNetworkPluginDocs()]); ?>
        </div>

    </div>
    <div class="sc"></div>

    <h2>Produkte synchronisieren</h2>
    
    <?php
    echo $this->element('tabs', [
        'url' => $this->request->getUri()->getPath()
    ]);
    
    echo '<p>';
        if (!empty($emptyProductsString)) {
            echo $emptyProductsString;
        }
        echo 'Rot hinterlegte Felder (Vorschau) zeigen Unterschiede zwischen Master-Foodcoop und Remote-Foodcoop an.';
        echo '</p>';
    ?>
    
    <h2 class="info" id="everything-allright"><b>Super!</b> Du kannst dich entspannt zurücklehen. Deine Produktdaten sind bei allen Foodcoops synchron.</h2>
    
    <div class="product-list"></div>
    
</div>
