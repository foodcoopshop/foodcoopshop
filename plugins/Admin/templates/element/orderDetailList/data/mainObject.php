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

if ($groupBy != '') {
    $objectId = $orderDetail[$groupBy . '_id'];
    
    // product-id can be a string like "123-kg" -> remove "-kg"
    $splittedObjectId = explode('-', (string) $objectId);
    if (count($splittedObjectId) > 1) {
        $objectId = $splittedObjectId[0];
    }
    $groupByObjectHref = '/admin/order-details/index/' .
        '?pickupDay[]=' . join(',', $pickupDay) .
        '&' . $groupBy.'Id=' . $objectId .
        (isset($orderDetail['customer_id']) ? '' : '&customerId=' . $customerId );
        $groupByObjectLink = $this->Html->link($orderDetail['name'], $groupByObjectHref, ['escape' => false]);
}

if ($groupBy == '' || $groupBy == 'product') {
    echo '<td>';
    if ($groupBy == '') {

        if (Configure::read('appDb.FCS_FEEDBACK_TO_PRODUCTS_ENABLED')) {
            $productFeedback = __d('admin', 'Add_product_feedback');
            $buttonClasses = [
                'btn',
                'btn-outline-light',
                'product-feedback-button',
            ];
            $icon = 'far fa-comment-dots';
            if (!empty($orderDetail->order_detail_feedback)) {
                $productFeedback = $orderDetail->order_detail_feedback->text;
                $icon = 'fas fa-comment-dots';
                echo '<i class="' . $icon . ' ok product-feedback-button" title="'.h($productFeedback).'"></i>';
            } else {
                if (!$identity->isManufacturer()) {
                    echo $this->Html->link(
                        '<i class="' . $icon . ' ok"></i>',
                        'javascript:void(0);',
                        [
                            'class' => join(' ', $buttonClasses),
                            'title' => h($productFeedback),
                            'originalTitle' => h($productFeedback),
                            'escape' => false
                        ]
                    );
                }
            }
        }

        if ($identity->isSuperadmin() && $editRecordAllowed) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                'javascript:void(0);',
                [
                    'class' => 'btn btn-outline-light order-detail-product-name-edit-button',
                    'title' => __d('admin', 'Click_to_change_name'),
                    'escape' => false
                ]
            );
        }

        echo $this->MyHtml->link(
            $orderDetail->product_name,
            '/admin/order-details/index/?pickupDay[]=' . join(',', $pickupDay) . '&productId=' . $orderDetail->product_id,
            [
                'class' => 'name-for-dialog',
                'escape' => false,
            ]
        );
    }

    if ($groupBy == 'product') {
        echo $groupByObjectLink;
    }

    echo '</td>';
}

echo '<td class="' . ($identity->isManufacturer() ? 'hide' : '') . '">';
if ($groupBy == '') {
    echo $this->MyHtml->link(
        $orderDetail->product->manufacturer->name,
        '/admin/order-details/index/?pickupDay[]=' . join(',', $pickupDay) . '&manufacturerId=' . $orderDetail->product->id_manufacturer .  '&customerId=' . $customerId . '&groupBy='.$groupBy,
        [
            'escape' => false
        ]
    );
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
                'title' => h($commentText),
                'originalTitle' => h($commentText),
                'escape' => false
            ]
        );
    }
    $name = $orderDetail['name'];
    if ($orderDetail['different_pickup_day_count'] <= 2) {
        $name = '<i class="fas fa-carrot" title="'.__d('admin', 'Newbie_has_{0}_orders.', [
            $orderDetail['different_pickup_day_count'],
        ]).'"></i> ' . $name;
    }
    echo $name;
}
if ($groupBy == 'product') {
    echo $this->MyHtml->link(
        $orderDetail['manufacturer_name'],
        '/admin/order-details/index/?pickupDay[]=' . join(',', $pickupDay) . '&' . 'manufacturerId=' . $orderDetail['manufacturer_id'] . '&customerId=' . $customerId . '&groupBy=product',
        [
            'escape' => false
        ]
    );
}
echo '</td>';

if ($groupBy == 'customer') {
    echo '<td'.(!$isMobile ? ' style="width: 159px;"' : '').'>';
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
