<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".ModalImage.init('.first-column a.open-with-modal');"
]);
?>

<h1><?php echo __('Manufacturers'); ?>
<?php
if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
    echo '<span>'.$manufacturers->count() . ' ' . __('found') . '</span>';
}
?>
</h1>

<?php

foreach ($manufacturers as $manufacturer) {
    echo '<div class="manufacturer-wrapper">';

        echo '<div class="first-column">';
            $srcLargeImage = $this->Html->getManufacturerImageSrc($manufacturer->id_manufacturer, 'large');
            $largeImageExists = preg_match('/de-default/', $srcLargeImage);
    if (!$largeImageExists) {
        echo '<a class="open-with-modal" href="javascript:void(0);" data-modal-title="' . h($manufacturer->name) . '" data-modal-image="'.$srcLargeImage.'">';
    }
            echo '<img class="lazyload" data-src="' . $this->Html->getManufacturerImageSrc($manufacturer->id_manufacturer, 'medium'). '" />';
    if (!$largeImageExists) {
        echo '</a>';
    }
        echo '</div>';

        echo '<div class="second-column">';
            echo '<h4>'.$this->Html->link(
                $manufacturer->name,
                $this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name),
                ['escape' => false]
            ).'</h4>';
            echo $manufacturer->short_description;

            if (!$appAuth->isInstantOrderMode() && !$appAuth->isSelfServiceModeByUrl()) {
                $manufacturerNoDeliveryDaysString = $this->Html->getManufacturerNoDeliveryDaysString($manufacturer);
                if ($manufacturerNoDeliveryDaysString != '') {
                    echo '<h2 class="info">'.__('Delivery_break') . ': ' . $manufacturerNoDeliveryDaysString.'</h2>';
                }
            }

        echo '</div>';

        echo '<div class="third-column">';
            echo $this->Html->link(
                __('Show_all_products') . ($appAuth->user() || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') ? ' (' . $manufacturer->product_count .')' : ''),
                $this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name),
                ['class' => 'btn btn-outline-light']
            );
        echo '</div>';

    echo '</div>';

    echo '<div class="sc"></div>';
}

?>
