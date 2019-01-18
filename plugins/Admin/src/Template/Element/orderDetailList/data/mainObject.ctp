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

if ($groupBy != '') {
    $groupByObjectHref = '/admin/order-details/index/' .
        '?pickupDay[]=' . join(',', $pickupDay) .
        '&' . $groupBy.'Id=' . $orderDetail[$groupBy . '_id'] .
        (isset($orderDetail['customer_id']) ? '' : '&customerId=' . $customerId );
        $groupByObjectLink = $this->Html->link($orderDetail['name'], $groupByObjectHref);
}

if ($groupBy == '' || $groupBy == 'product') {
    echo '<td>';
    if ($groupBy == '') {
        echo $this->MyHtml->link($orderDetail->product_name, '/admin/order-details/index/?pickupDay[]=' . join(',', $pickupDay) . '&productId=' . $orderDetail->product_id, [
            'class' => 'name-for-dialog'
        ]);
    }
    if ($groupBy == 'product') {
        echo $groupByObjectLink;
    }
    echo '</td>';
}

echo '<td class="' . ($appAuth->isManufacturer() ? 'hide' : '') . '">';
if ($groupBy == '') {
    echo $this->MyHtml->link($orderDetail->product->manufacturer->name, '/admin/order-details/index/?pickupDay[]=' . join(',', $pickupDay) . '&manufacturerId=' . $orderDetail->product->id_manufacturer .  '&customerId=' . $customerId . '&groupBy='.$groupBy);
}
if ($groupBy == 'manufacturer') {
    echo $groupByObjectLink;
}
if ($groupBy == 'customer') {
    if (Configure::read('appDb.FCS_ORDER_COMMENT_ENABLED') && count($pickupDay) == 1) {
        $commentText = !empty($orderDetail['comment']) ? $orderDetail['comment'] : __d('admin', 'Add_comment');
        echo $this->Html->link(
            '<i class="fas fa-exclamation-circle not-ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light pickup-day-comment-edit-button' . (empty($orderDetail['comment']) ? ' btn-disabled' : ''),
                'title' => $commentText,
                'originalTitle' => $commentText,
                'escape' => false
            ]
        );
    }
    $name = $orderDetail['name'];
    if ($orderDetail['order_detail_count'] <= 25) {
        $name = '<i class="fas fa-carrot" title="'.__d('admin', 'Newbie_only_{0}_products_ordered.', [
            $orderDetail['order_detail_count']
        ]).'"></i> ' . $name;
    }
    echo $name;
}
if ($groupBy == 'product') {
    echo $this->MyHtml->link($orderDetail['manufacturer_name'], '/admin/order-details/index/?pickupDay[]=' . join(',', $pickupDay) . '&' . 'manufacturerId=' . $orderDetail['manufacturer_id'] . '&customerId=' . $customerId . '&groupBy=product');
}
echo '</td>';

if ($groupBy == 'customer') {
    echo '<td'.(!$isMobile ? ' style="width: 157px;"' : '').'>';
    echo $this->Html->link(
        '<i class="fas fa-shopping-cart ok"></i>' . (!$isMobile ? ' ' . __d('admin', 'Ordered_products') : ''),
        $groupByObjectHref,
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'Show_all_ordered_products_from_{0}', [$orderDetail['name']]),
            'escape' => false
        ]
    );
    echo '</td>';
}

?>
