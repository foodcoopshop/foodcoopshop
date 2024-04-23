<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".ModalImage.init('.c1 a.open-with-modal');"
]);
?>

<h1><?php echo __('Manufacturers'); ?>
<?php
if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $identity !== null) {
    echo '<span>'.$manufacturers->count() . ' ' . __('found') . '</span>';
}
?>
</h1>

<?php

foreach ($manufacturers as $manufacturer) {
    echo '<div class="manufacturer-wrapper">';

        echo '<div class="c1">';
            $srcLargeImage = $this->Html->getManufacturerImageSrc($manufacturer->id_manufacturer, 'large');
            $largeImageExists = $this->Html->largeImageExists($srcLargeImage);
            if ($largeImageExists) {
                echo '<a class="open-with-modal" href="javascript:void(0);" data-modal-title="' . h($manufacturer->name) . '" data-modal-image="'.$srcLargeImage.'">';
            }
            echo '<img class="lazyload" data-src="' . $this->Html->getManufacturerImageSrc($manufacturer->id_manufacturer, 'medium'). '" />';
            if ($largeImageExists) {
                echo '</a>';
            }
        echo '</div>';

        echo '<div class="c2">';
            echo '<h4>'.$this->Html->link(
                $manufacturer->name,
                $this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name),
                ['escape' => false]
            ).'</h4>';
            echo $manufacturer->short_description;

            if (!$orderCustomerService->isOrderForDifferentCustomerMode() && !$orderCustomerService->isSelfServiceModeByUrl()) {
                $manufacturerNoDeliveryDaysString = $this->Html->getManufacturerNoDeliveryDaysString($manufacturer);
                if ($manufacturerNoDeliveryDaysString != '') {
                    echo '<h2 class="info">'.__('Delivery_break') . ': ' . $manufacturerNoDeliveryDaysString.'</h2>';
                }
            }

        echo '</div>';

        echo '<div class="c3">';
            $manufacturerDetailLinkName  = __('Show_manufacturer_profile');
            if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $identity !== null) {
                $manufacturerDetailLinkName = __('Show_products');
            }
            echo $this->Html->link(
                $manufacturerDetailLinkName . ($identity !== null || Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') ? ' (' . $manufacturer->product_count .')' : ''),
                $this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name),
                ['class' => 'btn btn-outline-light']
            );
            if ($identity !== null) {
                if (!empty($manufacturer->customer)) {
                    echo '<i>' . __('Contact_person') . ':<br />' . $manufacturer->customer->name . '</i>';
                }
            }
        echo '</div>';

    echo '</div>';

    echo '<div class="sc"></div>';
}

?>
