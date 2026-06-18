<?php
declare(strict_types=1);

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Volker Peters
 * @link          https://www.foodcoopshop.com
 */
$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();"
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
</div>

<div class="product-import-wrapper">

    <?php

    echo $this->Form->create(null, [
        'type' => 'file',
        'id' => 'csv-upload',
    ]);

    ?>

    <div>
        <?php
        echo $this->Form->control('surcharge', [
            'type' => 'number',
            'step' => '0.01',
            'min' => '0',
            'value' => '10',
            'label' => __('Surcharge_in_percent_from_purchase_price_net') . ': ',
        ]);
        ?>
    </div>

    <div>
        <?php
        echo $this->Form->control('upload', [
            'type' => 'file',
            'accept' => '.csv',
            'label' => __('Upload_changed_template_with_products') . ': ',
            'style' => 'padding-left:5px;',
        ]);
        ?>
    </div>

    <div>
        <?php echo $this->Form->button(__('Price_update_start'), ['class' => 'btn btn-success']); ?>
    </div>

    <?php
    echo $this->Form->end();

    echo $this->MyHtml->link(
        '<i class="fas fa-arrow-left"></i> ' . __('Back_to_product_page'),
        $this->Slug->getProductAdmin($identity->isManufacturer() ? '' : $manufacturer->id_manufacturer),
        [
            'class' => 'btn btn-outline-light',
            'escape' => false,
        ],
    );

    ?>

</div>
