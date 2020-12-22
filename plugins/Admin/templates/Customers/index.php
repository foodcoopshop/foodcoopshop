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

?>
<div id="customers-list">
    <?php
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            $('input.datepicker').datepicker();".
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".ModalCustomerStatusEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalCustomerGroupEdit.init();" .
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.customer-details-read-button, .customer-comment-edit-button');" .
            Configure::read('app.jsNamespace') . ".ModalCustomerCommentEdit.init();"
    ]);
    ?>

    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php echo $this->Form->control('active', ['type' => 'select', 'label' => '', 'options' => $this->MyHtml->getActiveStates(), 'default' => isset($active) ? $active : '']); ?>
            <?php echo __d('admin', 'Last_pickup_day'); ?> <?php echo $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameFrom' => 'dateFrom', 'nameTo' => 'dateTo']); ?>
            <?php
                echo $this->Form->control('year', [
                    'type' => 'select',
                    'label' => '',
                    'empty' => __d('admin', 'Member_fee') . ' - ' . __d('admin', 'Show_all_years'),
                    'options' => $years,
                    'default' => $year != '' ? $year : ''
                ]);
            ?>
            <div class="right">
                <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_members'))]); ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

<?php

echo '<table class="list">';
echo '<tr class="sort">';
echo $this->element('rowMarker/rowMarkerAll', [
    'enabled' => true
]);
echo '<th>' . $this->Paginator->sort('Customers.id_customer', 'ID') . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.' . Configure::read('app.customerMainNamePart'), __d('admin', 'Name')) . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.id_default_group', __d('admin', 'Group')) . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.email', __d('admin', 'Email')) . '</th>';
echo '<th>' . $this->Paginator->sort('Customers.active', __d('admin', 'Status')) . '</th>';
echo '<th style="text-align:right">'.__d('admin', 'Ordered_products').'</th>';
if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
    echo '<th>'.__d('admin', 'Credit').'</th>';
}
if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
    echo '<th>' . $this->Paginator->sort('Customers.timebased_currency_enabled', Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')) . '</th>';
}
if (Configure::read('app.emailOrderReminderEnabled')) {
    echo '<th>' . $this->Paginator->sort('Customers.email_order_reminder',  __d('admin', 'Reminder')) . '</th>';
}
echo '<th>' . $this->Paginator->sort('Customers.date_add',  __d('admin', 'Register_date')) . '</th>';
echo '<th>'.__d('admin', 'Last_pickup_day').'</th>';
if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
    echo '<th>' . $this->Paginator->sort('Customers.member_fee', __d('admin', 'Member_fee')) . '</th>';
}
echo '<th>'.__d('admin', 'Comment_abbreviation').'</th>';
echo '</tr>';

$i = 0;
$sumOrderDetailsCount = 0;
$sumEmailReminders = 0;
$sumTimebasedCurrency = null;
foreach ($customers as $customer) {
    $i ++;

    echo '<tr class="data" data-customer-id="'.$customer->id_customer.'">';

    echo $this->element('rowMarker/rowMarker', [
        'show' => true
    ]);

    echo '<td style="text-align:right;">';
    echo $customer->id_customer;
    echo '</td>';

    echo '<td class="name">';

        $customerName = $this->Html->getNameRespectingIsDeleted($customer);

        if ($appAuth->isSuperadmin()) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                $this->Slug->getCustomerEdit($customer->id_customer),
                [
                    'class' => 'btn btn-outline-light edit-link',
                    'title' => __d('admin', 'Edit'),
                    'escape' => false
                ]
            );
        }
        if (!empty($customer->valid_order_details) && $customer->valid_order_details[0]->valid_order_detail_count <= 25) {
            $customerName = '<i class="fas fa-carrot" title="'.__d('admin', 'Newbie_only_{0}_products_ordered.', [
                $customer->valid_order_details[0]->valid_order_detail_count
            ]).'"></i> ' . $customerName;
        }

        echo '<span class="name">' . $this->Html->link($customerName, '/admin/order-details?&pickupDay[]='.Configure::read('app.timeHelper')->formatToDateShort('2014-01-01').'&pickupDay[]=' . Configure::read('app.timeHelper')->formatToDateShort('2022-12-31') . '&customerId=' . $customer->id_customer . '&sort=OrderDetails.pickup_day&direction=desc', [
            'title' => __d('admin', 'Show_all_orders_from_{0}', [$this->Html->getNameRespectingIsDeleted($customer)]),
            'escape' => false
        ]) . '</span>';

        echo '<div class="customer-details-wrapper">';
            $imageSrc = $this->Html->getCustomerImageSrc($customer->id_customer, 'small');
            $imageExists = ! preg_match('/de-default-small_default/', $imageSrc);
            $fontawesomeClass = 'far';
            if ($imageExists) {
                $fontawesomeClass = 'fas';
                $imageSrc = $this->Html->privateImage($imageSrc);
                $customerDetails = '<div style="height:270px;">';
                $customerDetails .= $this->Html->getCustomerAddress($customer);
                $customerDetails .= '<br /><img style="margin-top:10px;" class="no-max-width" height="200" src="'.$imageSrc.'" />';
                $customerDetails .= '</div>';
            } else {
                $customerDetails = $this->Html->getCustomerAddress($customer);
            }
            echo '<i class="'.$fontawesomeClass.' fa-address-card ok fa-lg customer-details-read-button" title="'.h($customerDetails).'"></i>';
        echo '</div>';

    echo '</td>';

    echo '<td>';

    if ($appAuth->getGroupId() >= $customer->id_default_group) {
        echo '<div class="table-cell-wrapper group">';
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light customer-group-edit-button',
                'title' => __d('admin', 'Change_group'),
                'escape' => false
            ]
        );
        echo '<span>' . $this->Html->getGroupName($customer->id_default_group) . '</span>';
        echo '</div>';
    } else {
        echo $this->Html->getGroupName($customer->id_default_group);
    }
    echo '<span class="group-for-dialog">' . $customer->id_default_group . '</span>';
    echo '</td>';

    echo '<td>';
    echo '<span class="email">' . $customer->email . '</span>';
    echo '</td>';

    echo '<td style="text-align:center;width:42px;">';

    if ($customer->active == 1) {
        echo $this->Html->link(
            '<i class="fas fa-check-circle ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light set-state-to-inactive change-active-state',
                'id' => 'change-active-state-' . $customer->id_customer,
                'title' => __d('admin', 'deactivate'),
                'escape' => false
            ]
        );
    }

    if ($customer->active == '') {
        echo $this->Html->link(
            '<i class="fas fa-minus-circle not-ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light set-state-to-active change-active-state',
                'id' => 'change-active-state-' . $customer->id_customer,
                'title' => __d('admin', 'activate'),
                'escape' => false
            ]
        );
    }

    echo '</td>';

    echo '<td style="text-align:right">';
        if (!empty($customer->valid_order_details)) {
            echo $this->Number->formatAsDecimal($customer->valid_order_details[0]->valid_order_detail_count, 0);
            $sumOrderDetailsCount += $customer->valid_order_details[0]->valid_order_detail_count;
        } else {
            echo $this->Number->formatAsDecimal(0, 0);
        }
    echo '</td>';

    if ($this->Html->paymentIsCashless()) {
        $negativeClass = $customer->credit_balance < 0 ? 'negative' : '';
        echo '<td style="text-align:center" class="' . $negativeClass . '">';

        if ($appAuth->isSuperadmin()) {
            $creditBalanceHtml = '<span class="'.$negativeClass.'">' . $this->Number->formatAsCurrency($customer->credit_balance);
            echo $this->Html->link(
                $creditBalanceHtml,
                $this->Slug->getCreditBalance($customer->id_customer),
                [
                    'class' => 'btn btn-outline-light',
                    'title' => __d('admin', 'Show_credit'),
                    'escape' => false
                ]
            );
        } else {
            if ($customer->credit_balance != 0) {
                echo $this->Number->formatAsCurrency($customer->credit_balance);
            }
        }

        echo '</td>';
    }

    if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
        echo '<td>';
        if ($customer->timebased_currency_enabled) {
            $sumTimebasedCurrency += $customer->timebased_currency_credit_balance;

            $timebasedCurrencyCreditBalanceClasses = [];
            if ($customer->timebased_currency_credit_balance < 0) {
                $timebasedCurrencyCreditBalanceClasses[] = 'negative';
            }
            $timebasedCurrencyCreditBalanceHtml = '<span class="'.implode(' ', $timebasedCurrencyCreditBalanceClasses).'">' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($customer->timebased_currency_credit_balance);

            if ($appAuth->isSuperadmin()) {
                echo $this->Html->link(
                    $timebasedCurrencyCreditBalanceHtml,
                    $this->Slug->getTimebasedCurrencyPaymentDetailsForSuperadmins(0, $customer->id_customer),
                    [
                        'class' => 'btn btn-outline-light',
                        'title' => __d('admin', 'Show_{0}', [$this->TimebasedCurrency->getName()]),
                        'escape' => false
                    ]
                );
            } else {
                echo $timebasedCurrencyCreditBalanceHtml;
            }
        }
        echo '</td>';
    }


    if (Configure::read('app.emailOrderReminderEnabled')) {
        echo '<td>';
        echo $customer->email_order_reminder;
        $sumEmailReminders += $customer->email_order_reminder;
        echo '</td>';
    }

    echo '<td>';
    echo $customer->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateShort'));
    echo '</td>';

    echo '<td>';
        if (!empty($customer->last_order_date)) {
            echo $customer->last_order_date->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateShort'));
        }
    echo '</td>';

    if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
        echo '<td style="text-align:right;">';
            echo $this->Number->formatAsCurrency($customer->member_fee);
        echo '</td>';
    }

    echo '<td style="padding-left: 11px;">';
        $commentText = $customer->address_customer->comment != '' ? $customer->address_customer->comment : __d('admin', 'Add_comment');
        echo $this->Html->link(
            '<i class="fas fa-comment-dots ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light customer-comment-edit-button' . ($customer->address_customer->comment == '' ? ' btn-disabled' : ''),
                'title' => h($commentText),
                'originalTitle' => h($commentText),
                'escape' => false
            ]
        );
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="6"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
echo '<td style="text-align:right"><b>' . $this->Number->formatAsDecimal($sumOrderDetailsCount, 0) . '</b></td>';
if ($this->Html->paymentIsCashless()) {
    echo '<td></td>';
}
if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
    echo '<td><b class="' . ($sumTimebasedCurrency < 0 ? 'negative' : '') . '">'.$this->TimebasedCurrency->formatSecondsToTimebasedCurrency($sumTimebasedCurrency) . '</b></td>';
}
if (Configure::read('app.emailOrderReminderEnabled')) {
    echo '<td><b>' . $sumEmailReminders . '</b></td>';
}
$colspan = 3;
if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
    $colspan = 4;
}
echo '<td colspan="'.$colspan.'"></td>';
echo '</tr>';

echo '</table>';

echo '<div class="sc"></div>';

echo '<div class="bottom-button-container">';
    echo $this->element('customerList/button/email');
    echo $this->element('customerList/button/generateMemberCardsOfSelectedCustomers');
echo '</div>';
echo '<div class="sc"></div>';

echo '<div class="hide">';
    echo $this->Form->control('selectGroupId', [
        'type' => 'select',
        'label' => '',
        'options' => $this->Html->getAuthDependentGroups($appAuth->getGroupId())
    ]);
echo '</div>';

?>
    <div class="sc"></div>
</div>
