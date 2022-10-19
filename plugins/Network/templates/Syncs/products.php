<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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
                Configure::read('app.jsNamespace') . ".Admin.addLoaderToSyncProductDataButton($('.sync-button-wrapper a.btn-outline-light'));"
        ]);
    ?>

    <div class="filter-container-not-fixed">
        <?php
           echo $this->element('syncLoginForm', ['syncDomains' => $syncDomains]);
           echo '<div class="sync-button-wrapper">';
               echo $this->Html->link('<i class="fas fa-refresh"></i> ' . __d('network', 'Load_products'), 'javascript:void(0);', [
                   'class' => 'btn btn-success',
                   'escape' => false
                ]);
               echo '</div>';
        ?>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Network->getNetworkPluginDocs()]); ?>
        </div>
    </div>

    <div class="sc"></div>

    <?php
    echo $this->element('tabs', [
        'url' => $this->request->getUri()->getPath()
    ]);

    echo '<p>';
    if (!empty($emptyProductsString)) {
        echo $emptyProductsString;
    }
    echo '</p>';

    echo '<div class="product-list local" data-sync-domain="' . 'http' . (!empty($this->getRequest()->getEnv('HTTPS')) && $this->getRequest()->getEnv('HTTPS') === 'on' ? 's' : ''). '://'.$this->getRequest()->getEnv('SERVER_NAME').'"></div>';
    foreach ($syncDomains as $syncDomain) {
        echo '<div class="product-list remote" data-sync-domain="'.$syncDomain->domain.'"></div>';
    }
    ?>

</div>
