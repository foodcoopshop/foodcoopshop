<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();" .
    Configure::read('app.jsNamespace') . ".Admin.initForm();".
    Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
    $('input.datepicker').datepicker();
    "
]);    
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a>
        <?php if ($this->request->getRequestTarget() != $this->Slug->getManufacturerMyOptions()) { ?>
            <a href="javascript:void(0);" class="btn btn-outline-light cancel"><i
            class="fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
        <?php } ?>
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_manufacturers'))]); ?>
    </div>
</div>

<div class="sc"></div>

<?php

$url = $this->Slug->getManufacturerEditOptions($manufacturer->id_manufacturer);
if ($appAuth->isManufacturer()) {
    $url = $this->Slug->getManufacturerMyOptions();
}
echo $this->Form->create($manufacturer, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $url,
    'id' => 'manufacturersEditOptionsForm'
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

echo '<h2>'.__d('admin', 'Visibility_of_the_products').'</h2>';

    echo $this->Form->control('Manufacturers.active', [
        'label' => ''.__d('admin', 'Active').'? <span class="after small">'.__d('admin', 'Manufacturer_profile_and_products_are_visible_(cannot_be_changed_by_manufacturer).').'</span>',
        'disabled' => ($appAuth->isManufacturer() ? 'disabled' : ''),
        'type' => 'checkbox',
        'escape' => false
    ]);

    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace') . ".Admin.setSelectPickerMultipleDropdowns('#manufacturers-no-delivery-days');"
    ]);
    echo $this->Form->control('Manufacturers.no_delivery_days', [
        'type' => 'select',
        'multiple' => true,
        'data-val' => $manufacturer->no_delivery_days,
        'label' => __d('admin', 'Delivery_break').' <span class="after small"><a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_manufacturers')).'" target="_blank">'.__d('admin', 'How_do_I_use_the_function_delivery_break?').'</a></span>',
        'options' => $noDeliveryBreakOptions,
        'escape' => false
    ]);
    echo '<div class="sc"></div>';


    echo $this->Form->control('Manufacturers.is_private', [
        'label' => __d('admin', 'Only_for_members').'? <span class="after small">'.__d('admin', 'Manufacturer_profile_and_products_are_only_visible_for_signed_in_members.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo '<h2>'.__d('admin', 'Notifications').'</h2>';

    echo $this->Form->control('Manufacturers.send_order_list', [
        'label' => __d('admin', 'Order_lists_by_email').' <span class="after small">'.($appAuth->isManufacturer() ? __d('admin', 'I_want') : __d('admin', 'The_manufacturer_wants')) . ' ' . __d('admin', 'to_receive_the_orders_per_email.') . '</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_order_list_cc', [
        'label' => __d('admin', 'CC_recipient_for_order_lists').' <span class="after small">'.__d('admin', 'Separate_multiple_emails_with_comma').'</span>',
        'escape' => false
    ]);

    echo $this->Form->control('Manufacturers.send_invoice', [
        'label' => __d('admin', 'Invoices_by_email').' <span class="after small">'.($appAuth->isManufacturer() ? __d('admin', 'I_want') : __d('admin', 'The_manufacturer_wants')) . ' '.__d('admin', 'to_receive_his_invoice_every_month_by_email.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_ordered_product_deleted_notification', [
        'label' => __d('admin', 'Cancellations').' <span class="after small">'.($appAuth->isManufacturer() ? __d('admin', 'I_want') : __d('admin', 'The_manufacturer_wants')) . ' '.__d('admin', 'to_receive_an_email_on_every_cancellation.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_ordered_product_price_changed_notification', [
        'label' => __d('admin', 'Adaptions_of_price_and_weight_of_ordered_products').' <span class="after small">'.($appAuth->isManufacturer() ? __d('admin', 'I_want') : __d('admin', 'The_manufacturer_wants')) . ' '.__d('admin', 'to_receive_an_email_on_every_adaption_of_price_or_weight_of_a_ordered_product.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_ordered_product_amount_changed_notification', [
        'label' => __d('admin', 'Adaptions_of_the_ordered_amount').' <span class="after small">'.($appAuth->isManufacturer() ? __d('admin', 'I_want') : __d('admin', 'The_manufacturer_wants')) . ' ' . __d('admin', 'to_receive_an_email_on_every_adaption_of_the_amount_of_a_ordered_product.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo $this->Form->control('Manufacturers.send_instant_order_notification', [
        'label' => __d('admin', 'Instant_orders').' <span class="after small">'.($appAuth->isManufacturer() ? __d('admin', 'I_want') : __d('admin', 'The_manufacturer_wants')) . ' ' .__d('admin', 'to_receive_an_email_on_every_instant_order.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    echo '<div class="sc"></div>';

    echo '<h2>'.__d('admin', 'Other_settings').'</h2>';

    if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && !$appAuth->isManufacturer()) {
        echo $this->Form->control('Manufacturers.variable_member_fee', [
        'label' => __d('admin', 'Variable_member_fee_in').' % <span class="after small">'.__d('admin', 'The_invoice_for_the_manufacturer_will_be_reduced_by_the_given_percentage_no_decimals_allowed.').'</span>',
        'class' => 'short',
        'type' => 'text',
        'escape' => false
        ]);
    }

    echo $this->Form->control('Manufacturers.default_tax_id', [
        'type' => 'select',
        'label' => __d('admin', 'Preselected_tax_rate_for_new_products'),
        'options' => $taxesForDropdown
    ]);

    echo $this->Form->control('Manufacturers.stock_management_enabled', [
        'label' => __d('admin', 'Advanced_stock_management_active?').' <span class="after small"><a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_products')).'" target="_blank">'.__d('admin', 'Infos_to_the_advanced_stock_management').'</a></span>',
        'type' => 'checkbox',
        'escape' => false
    ]);

    
    if ($manufacturer->stock_management_enabled) {
        echo $this->Form->control('Manufacturers.send_product_sold_out_limit_reached_for_manufacturer', [
            'label' => __d('admin', 'Sold_out_limit_reached_notification_for_manufacturer?').' <span class="after small">'.($appAuth->isManufacturer() ? __d('admin', 'I_want') : __d('admin', 'The_manufacturer_wants')) . ' ' . __d('admin', 'to_receive_a_notification_when_the_stock_limit_for_a_product_is_reached.').'</a></span>',
            'type' => 'checkbox',
            'escape' => false
        ]);
        if (!$appAuth->isManufacturer()) {
            echo $this->Form->control('Manufacturers.send_product_sold_out_limit_reached_for_contact_person', [
                'label' => __d('admin', 'Sold_out_limit_reached_notification_for_contact_person?').' <span class="after small">'. __d('admin', 'The_contact_person_wants_to_receive_a_notification_when_the_stock_limit_for_a_product_is_reached.').'</a></span>',
                'type' => 'checkbox',
                'escape' => false
            ]);
        }
    }

    if (!$appAuth->isManufacturer()) {
        $this->element('addScript', [
            'script' =>
            Configure::read('app.jsNamespace') . ".Admin.initCustomerDropdown(" . ($manufacturer->id_customer != '' ? $manufacturer->id_customer : '0') . ", 0, 'select#manufacturers-id-customer');"
        ]);
        echo $this->Form->control('Manufacturers.id_customer', [
        'type' => 'select',
        'label' => __d('admin', 'Contact_person'),
        'empty' => __d('admin', 'Chose_member'),
        'options' => []
        ]);
    }
    echo '<div class="sc"></div>';

    if (isset($isAllowedEditManufacturerOptionsDropdown) && $isAllowedEditManufacturerOptionsDropdown) {
        $this->element('addScript', [
            'script' =>
                Configure::read('app.jsNamespace') . ".Admin.setSelectPickerMultipleDropdowns('#manufacturers-enabled-sync-domains');
            "
        ]);
        echo $this->Form->control('Manufacturers.enabled_sync_domains', [
            'type' => 'select',
            'multiple' => true,
            'data-val' => $manufacturer->enabled_sync_domains,
            'label' => __d('admin', 'Remote_foodcoops').' <span class="after small"><a href="'.$this->Network->getNetworkPluginDocs().'" target="_blank">'.__d('admin', 'Info_page_for_network_module').'</a></span>',
            'options' => $syncDomainsForDropdown,
            'escape' => false
        ]);
        echo '<div class="sc"></div>';
    }

    if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
        echo '<h2>'.__d('admin', 'Paying_with_time').'</h2>';
        echo $this->Form->control('Manufacturers.timebased_currency_enabled', [
            'label' => __d('admin', 'Paying_with_time_module_active?').' <span class="after small"><a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_paying_with_time_module')).'" target="_blank">'.__d('admin', 'How_do_I_use_the_paying_with_time_module?').'</a></span>',
            'type' => 'checkbox',
            'escape' => false
        ]);
        if ($manufacturer->timebased_currency_enabled) {
            echo $this->Form->control('Manufacturers.timebased_currency_max_percentage', [
                'label' => __d('admin', 'Max_part_of_{0}_in_percent', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]).' <span class="after small">'.__d('admin', 'valid_for_all_products_0_means_paying_with_time_module_is_deactivated_in_the_shop.').'</span>',
                'type' => 'text',
                'class' => 'short',
                'escape' => false
            ]);
            echo $this->Form->control('Manufacturers.timebased_currency_max_credit_balance', [
                'label' => __d('admin', 'Maximum_credit_balance_in_{0}', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]).' <span class="after small">'.__d('admin', 'up_to_which_it_can_be_paid_in_{0}.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]).
                ' ' . __d('admin', 'Zero_means_no_limit_and_global_limit_of_{0}_is_used.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_MAX_CREDIT_BALANCE_MANUFACTURER') . ' ' . Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]) .
                '</span>',
                'type' => 'text',
                'class' => 'short',
                'escape' => false
            ]);
        }
    }

    echo $this->Form->end();

?>
