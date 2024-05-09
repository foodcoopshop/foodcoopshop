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
use Cake\Datasource\FactoryLocator;

?>
<div id="manufacturers-list">
    <?php
    $this->element('addScript', [
        'script' =>
            Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            $('input.datepicker').datepicker();".
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".Admin.initEmailToAllButton();" .
            Configure::read('app.jsNamespace') . ".ModalImage.init('a.open-with-modal');" .
            Configure::read('app.jsNamespace') . ".Helper.setFullBaseUrl('" . Configure::read('App.fullBaseUrl') . "');".
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.manufacturer-details-read-button, .manufacturer-email-button, .test-order-list, .no-delivery-days-button, .feedback-button');"
    ]);
    $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#manufacturer-'
    ]);
    ?>

    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php echo __d('admin', 'Pickup_days') . ': ' .  $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameTo' => 'dateTo', 'nameFrom' => 'dateFrom']); ?>
            <?php echo $this->Form->control('active', ['type' => 'select', 'label' => '', 'options' => $this->MyHtml->getActiveStates(), 'default' => isset($active) ? $active : '']); ?>
            <div class="right">
                <?php
                if (Configure::read('app.showManufacturerListAndDetailPage') || count($manufacturers) == 0) {
                    echo '<div id="add-manufacturer-button-wrapper" class="add-button-wrapper">';
                    echo $this->Html->link('<i class="fas fa-plus-circle ok"></i> ' . __d('admin', 'Add_manufacturer'), $this->Slug->getManufacturerAdd(), [
                        'class' => 'btn btn-outline-light',
                        'escape' => false
                    ]);
                    echo '</div>';
                }
                echo $this->element('manufacturerList/moreDropdown', [
                    'helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_manufacturers')),
                ]);
                ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

<?php

$this->Paginator->setPaginated($manufacturers);
echo '<table class="list">';
echo '<tr class="sort">';
    echo $this->element('rowMarker/rowMarkerAll', [
        'enabled' => true,
    ]);
    echo '<th class="hide">' . $this->Paginator->sort('Manufacturers.id_manufacturer', 'ID') . '</th>';
    echo '<th>Logo</th>';
    echo '<th>' . $this->Paginator->sort('Manufacturers.name', __d('admin', 'Name')) . '</th>';
    echo '<th style="width:83px;">'.__d('admin', 'Products').'</th>';
    if (Configure::read('app.isDepositEnabled')) {
        echo '<th>'.__d('admin', 'Deposit').'</th>';
    }
    echo '<th>' . __d('admin', 'Email') . '</th>';
    echo '<th>' . $this->Paginator->sort('Manufacturers.stock_management_enabled', __d('admin', 'Stock_products')) . '</th>';
    echo '<th>' . $this->Paginator->sort('Manufacturers.no_delivery_days', __d('admin', 'Delivery_break')) . '</th>';
    echo '<th style="width:40px;">' . $this->Paginator->sort('Manufacturers.is_private', __d('admin', 'Only_for_members')) . '</th>';
    echo '<th title="'.__d('admin', 'Sum_of_open_orders_in_given_time_range').'">'.__d('admin', 'Open_orders_abbreviation').'</th>';
    echo '<th>'.__d('admin', 'Settings_abbreviation').'</th>';
    if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
        echo '<th>%</th>';
    }
    echo '<th></th>';
    if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED') || !Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo '<th></th>';
    }
    if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $identity->isSuperadmin()) {
        echo '<th>'.__d('admin', 'Feedback').'</th>';
    }
    if (Configure::read('app.showManufacturerListAndDetailPage')) {
        echo '<th></th>';
    }
echo '</tr>';
$i = 0;
$sumProductCount = 0;
$sumFeedback = 0;
$sumFeedbackNotApproved = 0;
foreach ($manufacturers as $manufacturer) {

    $i ++;
    echo '<tr id="manufacturer-' . $manufacturer->id_manufacturer . '" data-manufacturer-id="' . $manufacturer->id_manufacturer . '" class="data">';

    echo $this->element('rowMarker/rowMarker', [
        'show' => true,
    ]);

    echo '<td class="hide">';
        echo $manufacturer->id_manufacturer;
    echo '</td>';
    echo '<td align="center" class="image">';
        $srcLargeImage = $this->Html->getManufacturerImageSrc($manufacturer->id_manufacturer, 'large');
        $largeImageExists = preg_match('/de-default-large_default/', $srcLargeImage);
        if (! $largeImageExists) {
            echo '<a class="open-with-modal" href="javascript:void(0);" data-modal-title="' . h($manufacturer->name) . '" data-modal-image="' . $srcLargeImage . '">';
        }
        echo '<img width="50" src="' . $this->Html->getManufacturerImageSrc($manufacturer->id_manufacturer, 'medium') . '" />';
        if (! $largeImageExists) {
            echo '</a>';
        }
    echo '</td>';

    echo '<td class="name">';

        $details = $manufacturer->address_manufacturer->firstname . ' ' . $manufacturer->address_manufacturer->lastname;
        if ($manufacturer->address_manufacturer->phone_mobile != '') {
            $details .= '<br /><a href="tel:'.$manufacturer->address_manufacturer->phone_mobile.'">' . $manufacturer->address_manufacturer->phone_mobile . '</a>';
        }
        if ($manufacturer->address_manufacturer->phone != '') {
            $details .= '<br /><a href="tel:'.$manufacturer->address_manufacturer->phone.'">' . $manufacturer->address_manufacturer->phone . '</a>';
        }
        echo '<div class="manufacturer-details-wrapper">';
            echo '<i class="fas fa-phone-square ok fa-lg manufacturer-details-read-button" title="'.h($details).'"></i>';
        echo '</div>';

        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            $this->Slug->getManufacturerEdit($manufacturer->id_manufacturer),
            [
                'class' => 'btn btn-outline-light edit-link',
                'title' => __d('admin', 'Edit'),
                'escape' => false
            ]
        );

        echo '<span class="name">';
            echo '<b>' . $manufacturer->name . '</b>';
            if ($manufacturer->address_manufacturer->city != '') {
                echo '<br />' . $manufacturer->address_manufacturer->city;
            }
            if (!empty($manufacturer->customer)) {
                echo '<br /><i class="fas fa-fw fa-user" title="' . __d('admin', 'Contact_person') . '"></i>' . $manufacturer->customer->firstname . ' ' . $manufacturer->customer->lastname;
            }
        echo '</span>';

    echo '</td>';

    echo '<td style="width:145px;">';
    $sumProductCount += $manufacturer->product_count;
    $productString = __d('admin', '{0,plural,=1{1_product} other{#_products}}', [$manufacturer->product_count]);

    echo $this->Html->link(
        '<i class="fas fa-tag ok"></i> ' . str_replace(' ', '&nbsp;', $productString),
        $this->Slug->getProductAdmin($manufacturer->id_manufacturer),
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'Show_all_products_from_{0}', [$manufacturer->name]),
            'escape' => false
        ]
    );

    echo '</td>';

    if (Configure::read('app.isDepositEnabled')) {
        echo '<td>';
        if ($manufacturer->sum_deposit_delivered > 0) {
            $depositCreditBalanceClasses = [];
            if ($manufacturer->deposit_credit_balance < 0) {
                $depositCreditBalanceClasses[] = 'negative';
            }
            $depositCreditBalanceHtml = '<span class="'.implode(' ', $depositCreditBalanceClasses).'">' . $this->Number->formatAsCurrency($manufacturer->deposit_credit_balance);
            echo $this->Html->link(
                $depositCreditBalanceHtml,
                $this->Slug->getDepositList($manufacturer->id_manufacturer),
                [
                    'class' => 'btn btn-outline-light',
                    'title' => __d('admin', 'Show_deposit_account'),
                    'escape' => false
                ]
            );
        }
        echo '</td>';
    }

    echo '<td style="text-align:center;">';
        $classes = ['far fa-envelope ok fa-lg manufacturer-email-button'];
        echo '<i class="'.join(' ', $classes).'" title="'.h($manufacturer->address_manufacturer->email).'" data-email="'.h($manufacturer->address_manufacturer->email).'"></i>';
    echo '</td>';

    echo '<td style="text-align:center;width:42px;">';
        if ($manufacturer->stock_management_enabled == 1) {
            echo '<i class="fas fa-check-circle ok"></i>';
        }
    echo '</td>';

    echo '<td style="text-align:center;">';
        $noDeliveryDaysString = $this->Html->getManufacturerNoDeliveryDaysString($manufacturer);
        if ($noDeliveryDaysString != '') {
            echo '<i class="fas fa-ban not-ok no-delivery-days-button" title="' . __d('admin', 'Delivery_break') . ': ' . h($noDeliveryDaysString) . '"><i>';
        }
    echo '</td>';

    echo '<td align="center">';
    if ($manufacturer->is_private == 1) {
        echo '<i class="fas fa-check-circle ok"></i>';
    }
    echo '</td>';

    echo '<td class="right">';
    if ($manufacturer->sum_open_order_detail > 0) {
        echo $this->Number->formatAsCurrency($manufacturer->sum_open_order_detail);
    }
    echo '</td>';

    echo '<td>';

    echo $this->Html->link(
        '<i class="fas fa-cog ok"></i>',
        $this->Slug->getManufacturerEditOptions($manufacturer->id_manufacturer),
        [
            'class' => 'btn btn-outline-light',
            'title' => __d('admin', 'Edit_manufacturer_settings'),
            'escape' => false
        ]
    );

    if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
        echo '<td>';
            echo $manufacturer->variable_member_fee.'%';
        echo '</td>';
    }

    echo '<td style="width:140px;">';
        $orderListProductBaseLink = '/admin/manufacturers/getOrderListByProduct.pdf?manufacturerId=' . $manufacturer->id_manufacturer . '&pickupDay=' . $dateFrom;
        $testOrderListLinks = '<div class="generate-order-lists-tooltip">';
        $testOrderListLinks .= '<p><b>' . h($manufacturer->name) . '</b><br />';
        $testOrderListLinks .= __d('admin', 'Anonymize_customers?') . ' <b>' . ($manufacturer->anonymize_customers ? __d('admin', 'yes') . ' <i class="fas fa-eye-slash ok"></i>' : __d('admin', 'no') . ' <i class="fas fa-eye ok"></i>') . '</b></p>';
        $testOrderListLinks .= $this->Html->link(
            '<i class="fas fa-eye ok"></i> ' . __d('admin', 'Order_list_with_clear_names') . ' - ' . __d('admin', 'grouped_by_product'),
            $orderListProductBaseLink . '&isAnonymized=0',
            [
                'class' => 'btn btn-outline-light',
                'style' => 'margin-bottom:5px;',
                'target' => '_blank',
                'escape' => false,
        ]);
        $testOrderListLinks .= '<br />';
        $testOrderListLinks .= $this->Html->link(
            '<i class="fas fa-eye-slash ok"></i> ' . __d('admin', 'Anonymized_list') . ' - ' . __d('admin', 'grouped_by_product'),
            $orderListProductBaseLink . '&isAnonymized=1',
            [
                'class' => 'btn btn-outline-light',
                'style' => 'margin-bottom:15px;',
                'target' => '_blank',
                'escape' => false,
        ]);
        $testOrderListLinks .= '<br />';
        $orderListCustomerBaseLink = '/admin/manufacturers/getOrderListByCustomer.pdf?manufacturerId=' . $manufacturer->id_manufacturer . '&pickupDay=' . $dateFrom;
        $testOrderListLinks .= $this->Html->link(
            '<i class="fas fa-eye ok"></i> ' . __d('admin', 'Order_list_with_clear_names') . ' - ' . __d('admin', 'grouped_by_customer'),
            $orderListCustomerBaseLink . '&isAnonymized=0',
            [
                'class' => 'btn btn-outline-light',
                'style' => 'margin-bottom:5px;',
                'target' => '_blank',
                'escape' => false,
        ]);
        $testOrderListLinks .= '<br />';
        $testOrderListLinks .= $this->Html->link(
            '<i class="fas fa-eye-slash ok"></i> ' . __d('admin', 'Anonymized_order_list') . ' - ' . __d('admin', 'grouped_by_customer'),
            $orderListCustomerBaseLink . '&isAnonymized=1',
            [
                'class' => 'btn btn-outline-light',
                'target' => '_blank',
                'escape' => false,
        ]);
        $testOrderListLinks .= '</div>';
        echo '<span class="test-order-list" title="' . h($testOrderListLinks) . '">' . __d('admin', 'Test_order_list').'</span>';
    echo '</td>';


    if (Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
        echo '<td>';
            echo $this->Html->link(
                __d('admin', 'Delivery_note'),
                '/admin/manufacturers/getDeliveryNote.xlsx?manufacturerId=' . $manufacturer->id_manufacturer . '&dateFrom=' . $dateFrom . '&dateTo=' . $dateTo,
                [
                    'target' => '_blank',
                ],
            );
        echo '</td>';
    } else {
        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            echo '<td>';
                echo $this->Html->link(
                    __d('admin', 'Test_invoice'),
                    '/admin/manufacturers/getInvoice.pdf?manufacturerId=' . $manufacturer->id_manufacturer . '&dateFrom=' . $dateFrom . '&dateTo=' . $dateTo,
                    [
                        'target' => '_blank',
                    ],
                );
            echo '</td>';
        }
    }

    if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $identity->isSuperadmin()) {
        echo '<td align="center">';
        if (!empty($manufacturer->feedback)) {
            $feedbackTable = FactoryLocator::get('Table')->get('Feedbacks');
            $approved = $feedbackTable->isApproved($manufacturer->feedback);
            $tooltipContent = __d('admin', 'created') . ': ' . $manufacturer->feedback->created->i18nFormat($this->Time->getI18Format('DateNTimeShort2')) . '<br />';
            $tooltipContent .= __d('admin', 'changed') . ': ' . $manufacturer->feedback->modified->i18nFormat($this->Time->getI18Format('DateNTimeShort2'));
            echo $this->Html->link(
                '<i class="fas fa-heart '.(!$approved ? 'not-ok' : 'ok').'"></i>',
                $this->Slug->getFeedbackForm($manufacturer->feedback->customer_id),
                [
                    'class' => 'btn btn-outline-light feedback-button',
                    'escape' => false,
                    'title' => $tooltipContent,
                ]
                );
            $sumFeedback++;
            if (!$approved) {
                $sumFeedbackNotApproved++;
            }
        } else {
            $tooltipContent = __d('admin', 'Create_feedback_for_{0}.', [
                $manufacturer->name,
            ]);
            echo $this->Html->link(
                '<i class="far fa-heart ok"></i>',
                $this->Slug->getFeedbackForm($manufacturer->customer_record_id),
                [
                    'class' => 'btn btn-outline-light feedback-button',
                    'escape' => false,
                    'title' => $tooltipContent,
                ],
            );
        }
        echo '</td>';
    }

    if (Configure::read('app.showManufacturerListAndDetailPage')) {
        echo '<td style="width: 29px;">';
        if ($manufacturer->active) {
            $manufacturerLink = $this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name);
            echo $this->Html->link(
                '<i class="fas fa-arrow-right ok"></i>',
                $manufacturerLink,
                [
                    'class' => 'btn btn-outline-light',
                    'title' => __d('admin', 'Manufacturer_profile'),
                    'target' => '_blank',
                    'escape' => false
                ]
            );
        }
        echo '</td>';
    }
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="3"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
echo '<td><b>' . $sumProductCount . '</b></td>';
$colspan = 8;
echo '<td></td>';

if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
    $colspan ++;
}
echo '<td colspan="' . $colspan . '"></td>';

if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $sumFeedback > 0) {
    echo '<td align="center"><b>' . $sumFeedback . ($sumFeedbackNotApproved > 0 ? ' (' . $sumFeedbackNotApproved . ')' : ''). '</b></td>';
}
echo '<td></td>';

echo '</tr>';
echo '</table>';
echo '<div class="sc"></div>';

?>
</div>
