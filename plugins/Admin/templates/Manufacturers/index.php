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
<div id="manufacturers-list">
    <?php
    $this->element('addScript', [
        'script' =>
            Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            $('input.datepicker').datepicker();".
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".Admin.initEmailToAllButton();" .
            Configure::read('app.jsNamespace') . ".ModalImage.init('a.open-with-modal');" .
            Configure::read('app.jsNamespace') . ".Helper.setCakeServerName('" .
            Configure::read('app.cakeServerName') . "');".
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.manufacturer-details-read-button');"
    ]);
    $this->element('highlightRowAfterEdit', [
        'rowIdPrefix' => '#manufacturer-'
    ]);
    ?>

    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php echo __d('admin', 'Pickup_days') . ': ' .  $this->element('dateFields', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'nameTo' => 'dateTo', 'nameFrom' => 'dateFrom']); ?>
            <?php echo $this->Form->control('active', ['type' => 'select', 'label' => '', 'options' => $this->MyHtml->getActiveStatesOnOff(), 'default' => isset($active) ? $active : '']); ?>
            <div class="right">
                <?php
                if (Configure::read('app.showManufacturerListAndDetailPage') || count($manufacturers) == 0) {
                    echo '<div id="add-manufacturer-button-wrapper" class="add-button-wrapper">';
                    echo $this->Html->link('<i class="fas fa-plus-circle"></i> ' . __d('admin', 'Add_manufacturer'), $this->Slug->getManufacturerAdd(), [
                        'class' => 'btn btn-outline-light',
                        'escape' => false
                    ]);
                    echo '</div>';
                }
                echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_manufacturers'))]);
                ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

<?php

echo '<table class="list">';
echo '<tr class="sort">';
    echo '<th class="hide">' . $this->Paginator->sort('Manufacturers.id_manufacturer', 'ID') . '</th>';
    echo '<th>Logo</th>';
    echo '<th>' . $this->Paginator->sort('Manufacturers.name', __d('admin', 'Name')) . '</th>';
    echo '<th style="width:83px;">'.__d('admin', 'Products').'</th>';
    if (Configure::read('app.isDepositEnabled')) {
        echo '<th>'.__d('admin', 'Deposit').'</th>';
    }
    if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
        echo '<th>' . $this->Paginator->sort('Manufacturers.timebased_currency_enabled', Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')) . '</th>';
    }
    echo '<th>' . $this->Paginator->sort('Manufacturers.stock_management_enabled', __d('admin', 'Stock_products')) . '</th>';
    echo '<th>' . $this->Paginator->sort('Manufacturers.no_delivery_days', __d('admin', 'Delivery_break')) . '</th>';
    echo '<th>' . $this->Paginator->sort('Manufacturers.is_private', __d('admin', 'Only_for_members')) . '</th>';
    echo '<th title="'.__d('admin', 'Sum_of_open_orders_in_given_time_range').'">'.__d('admin', 'Open_orders_abbreviation').'</th>';
    echo '<th>'.__d('admin', 'Settings_abbreviation').'</th>';
    if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
        echo '<th>%</th>';
    }
    echo '<th></th>';
    echo '<th></th>';
    echo '<th></th>';
echo '</tr>';
$i = 0;
$sumProductCount = 0;
$sumTimebasedCurrency = null;
foreach ($manufacturers as $manufacturer) {
    $i ++;
    echo '<tr id="manufacturer-' . $manufacturer->id_manufacturer . '" class="data">';
    echo '<td class="hide">';
        echo $manufacturer->id_manufacturer;
    echo '</td>';
    echo '<td align="center" style="background-color: #fff;">';
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
        echo '<b>' . $manufacturer->name . '</b><br />';
        echo $manufacturer->address_manufacturer->city;
        echo '<br /><span class="email">' . $manufacturer->address_manufacturer->email . '</span>';

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
                __d('admin', 'Deposit') . ':&nbsp;' . $depositCreditBalanceHtml,
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

    if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
        echo '<td>';
            if ($manufacturer->timebased_currency_enabled) {
                $sumTimebasedCurrency += $manufacturer->timebased_currency_credit_balance;

                $timebasedCurrencyCreditBalanceClasses = [];
                if ($manufacturer->timebased_currency_credit_balance < 0) {
                    $timebasedCurrencyCreditBalanceClasses[] = 'negative';
                }
                $timebasedCurrencyCreditBalanceHtml = '<span class="'.implode(' ', $timebasedCurrencyCreditBalanceClasses).'">' . $this->TimebasedCurrency->formatSecondsToTimebasedCurrency($manufacturer->timebased_currency_credit_balance);

                if ($appAuth->isSuperadmin()) {
                    echo $this->Html->link(
                        $timebasedCurrencyCreditBalanceHtml,
                        $this->Slug->getTimebasedCurrencyBalanceForManufacturers($manufacturer->id_manufacturer),
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

    echo '<td style="text-align:center;width:42px;">';
        if ($manufacturer->stock_management_enabled == 1) {
            echo '<i class="fas fa-check-circle ok"></i>';
        }
    echo '</td>';

    echo '<td>';
        echo $this->Html->getManufacturerNoDeliveryDaysString($manufacturer);
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
    echo __d('admin', 'Test_order_list').'<br />';
    echo $this->Html->link(__d('admin', 'Product'), '/admin/manufacturers/getOrderListByProduct.pdf?manufacturerId=' . $manufacturer->id_manufacturer . '&pickupDay=' . $dateFrom, [
            'target' => '_blank'
        ]);
    echo ' / ';
    echo $this->Html->link(__d('admin', 'Member'), '/admin/manufacturers/getOrderListByCustomer.pdf?manufacturerId=' . $manufacturer->id_manufacturer . '&pickupDay=' . $dateFrom, [
        'target' => '_blank'
    ]);
    echo '</td>';

    echo '<td>';
    echo $this->Html->link(__d('admin', 'Test_invoice'), '/admin/manufacturers/getInvoice.pdf?manufacturerId=' . $manufacturer->id_manufacturer . '&dateFrom=' . $dateFrom . '&dateTo=' . $dateTo, [
        'target' => '_blank'
    ]);
    echo '</td>';

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
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="2"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
echo '<td><b>' . $sumProductCount . '</b></td>';
$colspan = 8;
echo '<td></td>';

if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
    $colspan ++;
}
if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
    echo '<td><b class="' . ($sumTimebasedCurrency < 0 ? 'negative' : '') . '">'.$this->TimebasedCurrency->formatSecondsToTimebasedCurrency($sumTimebasedCurrency) . '</b></td>';
}
echo '<td colspan="' . $colspan . '"></td>';
echo '</tr>';
echo '</table>';
echo '<div class="sc"></div>';
echo '<div class="bottom-button-container">';
echo '<button data-clipboard-text="'.join(',', $emailAddresses).'" class="btn-clipboard btn btn-outline-light"><i class="far fa-envelope"></i> '.__d('admin', 'Copy_all_email_addresses').'</button>';
echo '</div>';
echo '<div class="sc"></div>';

?>
</div>
