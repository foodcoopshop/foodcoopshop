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
 * @since         FoodCoopShop 3.7.0
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
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_products'))]); ?>
    </div>
</div>

<div class="product-import-wrapper">

<?php if (!isset($productEntities)) { ?>

    <div class="template-download-link-wrapper"">
        <?php
        echo $this->MyHtml->link(
            '<i class="fas fa-download"></i> '. __d('admin', 'Download_empty_CSV_template'),
            '/templates/product-import-template.csv',
            [
                'class' => 'btn btn-success',
                'style' => 'padding: 15px 30px',
                'escape' => false,
            ],
        );
        ?>
    </div>

<?php } ?>

    <?php

        echo $this->Form->create(null, [
            'type' => 'file',
            'id' => 'csv-upload',
        ]);
        echo $this->Form->control('upload', [
            'type' => 'file',
            'accept' => '.csv',
            'onchange' => 'form.submit()',
            'label' => __d('admin', 'Upload_changed_CSV_file_with_products') . ': ',
            'style' => 'padding-left:5px',
        ]);
        echo $this->Form->end();

        /*
        echo '<button type="submit" class="btn btn-success">
                <i class="fas fa-check"></i> ' . __d('admin', 'Save') . '
            </button>';
        */

        echo $this->Form->end();

        echo $this->MyHtml->link(
            '<i class="fas fa-arrow-left"></i> ' . __d('admin', 'Back_to_product_page'),
            $this->Slug->getProductAdmin($appAuth->isManufacturer() ? '' : $manufacturer->id_manufacturer),
            [
                'class' => 'btn btn-outline-light',
                'escape' => false,
            ],
        );

    ?>

</div>
