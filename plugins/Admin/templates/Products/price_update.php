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

    <?php if (empty($previewResult)): ?>

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
                'onchange' => 'this.form.submit()',
                'label' => __('Upload_changed_template_with_products') . ': ',
                'style' => 'padding-left:5px;',
            ]);
            ?>
        </div>

        <?php echo $this->Form->end(); ?>

    <?php else: ?>

        <?php
        $hasErrors = !empty($previewResult['errors']);
        ?>

        <div class="price-update-preview">

            <h2><?php echo __('Price_update_preview'); ?></h2>

            <table class="list no-clone-last-row">
                <tbody>
                    <tr>
                        <td><?php echo __('Price_update_preview_updated'); ?></td>
                        <td><b><?php echo $previewResult['updated']; ?></b></td>
                        <td>
                            <?php if (!empty($previewResult['updated_products'])): ?>
                                <?php echo h(implode(', ', $previewResult['updated_products'])); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo __('Price_update_preview_inserted'); ?></td>
                        <td><b><?php echo $previewResult['inserted']; ?></b></td>
                        <td>
                            <?php if (!empty($previewResult['inserted_products'])): ?>
                                <?php echo h(implode(', ', $previewResult['inserted_products'])); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo __('Price_update_preview_deactivated'); ?></td>
                        <td><b><?php echo $previewResult['deactivated']; ?></b></td>
                        <td>
                            <?php if (!empty($previewResult['deactivated_products'])): ?>
                                <?php echo h(implode(', ', $previewResult['deactivated_products'])); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($hasErrors): ?>
                    <tr class="error">
                        <td><?php echo __('Price_update_preview_errors'); ?></td>
                        <td><b><?php echo count($previewResult['errors']); ?></b></td>
                        <td>
                            <?php foreach ($previewResult['errors'] as $error): ?>
                                <?php echo __('Price_update_preview_row_{0}', [$error['index'] + 2]); ?>:
                                <?php
                                $messages = [];
                                array_walk_recursive($error['errors'], function($msg) use (&$messages) { $messages[] = $msg; });
                                echo h(implode(', ', $messages));
                                ?>
                                <br />
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!$hasErrors): ?>
                <?php
                echo $this->Form->create(null, ['id' => 'price-update-confirm']);
                echo $this->Form->hidden('confirmed', ['value' => '1']);
                echo $this->Form->button(__('Price_update_apply'), ['class' => 'btn btn-success']);
                echo $this->Form->end();
                ?>
            <?php endif; ?>

            <?php
            echo $this->Form->create(null, ['id' => 'price-update-cancel', 'style' => 'display:inline']);
            echo $this->Form->hidden('cancel', ['value' => '1']);
            echo $this->Form->button(__('Price_update_cancel'), ['class' => 'btn btn-outline-light', 'style' => 'margin-left:10px']);
            echo $this->Form->end();
            ?>

        </div>

    <?php endif; ?>

    <?php
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
