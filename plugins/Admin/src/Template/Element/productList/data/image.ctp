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

$imageExists = !empty($product->image);
echo '<td width="29px;" class="' . ((! empty($product->product_attributes) || isset($product->product_attributes)) && !$imageExists ? 'not-available' : '') . '">';
if ((! empty($product->product_attributes) || isset($product->product_attributes))) {
    echo $this->Html->link(
        '<i class="fas fa-image ok"></i>',
        'javascript:void(0);',
        [
            'class' => 'btn btn-outline-light add-image-button',
            'title' => $imageExists ? h('<img class="no-max-width" height="120" src="' . $this->Html->getProductImageSrc($product->image->id_image, 'home') . '" />') : __d('admin', 'add_image'),
            'data-object-id' => $product->id_product,
            'escape' => false
        ]
    );
    echo $this->element('imageUploadForm', [
        'id' => $product->id_product,
        'action' => '/admin/tools/doTmpImageUpload/' . $product->id_product,
        'imageExists' => $imageExists,
        'existingImageSrc' => $imageExists ? $this->Html->getProductImageSrc($product->image->id_image, 'thickbox') : ''
    ]);
}
echo '</td>';

?>