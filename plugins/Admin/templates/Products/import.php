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
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();"
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_product_import'))]); ?>
    </div>
</div>

<div class="product-import-wrapper">


    <?php

    echo $this->Form->create(null, [
        'type' => 'file',
        'id' => 'csv-upload',
    ]);

    ?>

    <div>1. 
    <?php
        echo $this->MyHtml->link(
            __d('admin', 'Read_guide_for_product_import'),
            $this->Html->getDocsUrl(__d('admin', 'docs_route_product_import')),
            [
                'escape' => false,
                'target' => '_blank',
            ],
        );
    ?>
    </div>

    <div>2.
    <?php
        echo $this->MyHtml->link(
            __d('admin', 'Download_empty_CSV_template'),
            '/admin/products/downloadImportTemplate',
            [
                'escape' => false,
            ],
        );
    ?>
    </div>

    <div>3.
    
    <?php
        echo $this->Form->control('upload', [
            'type' => 'file',
            'accept' => '.csv',
            'onchange' => 'form.submit()',
            'label' => __d('admin', 'Upload_changed_template_with_products') . ': ',
            'style' => 'padding-left:5px;',
        ]);
        ?>
    </div>

        <?php 
        echo $this->Form->end();

        echo $this->MyHtml->link(
            '<i class="fas fa-arrow-left"></i> ' . __d('admin', 'Back_to_product_page'),
            $this->Slug->getProductAdmin($identity->isManufacturer() ? '' : $manufacturer->id_manufacturer),
            [
                'class' => 'btn btn-outline-light',
                'escape' => false,
            ],
        );

    ?>

</div>
