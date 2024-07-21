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

use App\Services\DeliveryRhythmService;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();"
]);
?>
<div id="configurations">

        <?php
        $this->element('addScript', [
        'script' => "$('table.list').show();
        "
        ]);
    ?>

    <div class="filter-container">
        <h1><?php echo $title_for_layout; ?></h1>
        <div class="right">
            <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_settings'))]); ?>
        </div>
    </div>

    <?php
        echo $this->element('navTabs/configurationNavTabs', [
            'key' => 'configurations',
        ]);
    ?>

    <h2 class="info"><?php echo __d('admin', 'The_following_settings_can_be_changed_in_the_admin_area.'); ?></h2>

    <table class="list no-hover no-clone-last-row">

        <tr>
            <th><?php echo __d('admin', 'Setting'); ?></th>
            <th></th>
            <th><?php echo __d('admin', 'Value'); ?></th>
        </tr>

        <?php
        foreach ($configurations as $configuration) {
            if ($configuration->type == 'readonly') {
                continue;
            }

            if (! Configure::read('app.htmlHelper')->paymentIsCashless() && in_array($configuration->name, [
                'FCS_BANK_ACCOUNT_DATA',
                'FCS_MINIMAL_CREDIT_BALANCE',
                'FCS_CASHLESS_PAYMENT_ADD_TYPE',
                'FCS_CHECK_CREDIT_BALANCE_LIMIT',
            ])) {
                continue;
            }

            if (in_array($configuration->name, ['FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS']) && !Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                continue;
            }

            if (! Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && $configuration->name == 'FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED') {
                continue;
            }

            echo '<tr>';

                echo '<td class="first">';
                    echo $configuration->text;
                    $subtext = $configuration->subtext;
                    if ($subtext != '') {
                        echo '<br /><div class="small">' . $subtext . '</div>';
                    }
                echo '</td>';

                echo '<td style="width:30px;">';
                    echo $this->Html->link(
                        '<i class="fas fa-pencil-alt ok"></i>',
                        $this->Slug->getConfigurationEdit($configuration->id_configuration, $configuration->name),
                        [
                            'class' => 'btn btn-outline-light',
                            'title' => __d('admin', 'Edit'),
                            'escape' => false
                        ]
                    );
                echo '</td>';

                echo '<td>';

                echo match($configuration->type) {
                    'number', 'text', 'textarea', 'textarea_big' => $configuration->value,
                    'dropdown' => $this->Configuration->getConfigurationDropdownOption($configuration->name, $configuration->value, $identity),
                    'multiple_dropdown' => $this->Configuration->getConfigurationMultipleDropdownOptions($configuration->name, $configuration->value),
                    'boolean' => (bool) $configuration->value ? __d('admin', 'yes') : __d('admin', 'no'),
                };

                echo '</td>';

            echo '</tr>';
        }
        ?>

        <?php if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) { ?>
            <tr>
                <td class="first">
                    <b><?php echo __d('admin', 'Remote_foodcoops'); ?></b>
                    <br /><div class="small"><?php echo __d('admin', 'Foodcoops_with_which_manufacturers_can_synchronize_their_product_data.'); ?><br /><a target="_blank" href="<?php echo $this->Network->getNetworkPluginDocs(); ?>"><?php echo __d('admin', 'Info_page_for_network_module'); ?></a></div>
                </td>
                <?php if (!Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) { ?>
                <td colspan="2" class="sync-domain-list">
                <?php
                    echo $this->Html->link('<i class="fas fa-plus-circle ok"></i> '.__d('admin', 'Add_remote_foodcoop').'', $this->Network->getSyncDomainAdd(), [
                        'class' => 'btn btn-outline-light',
                        'escape' => false
                    ]);
                if (!empty($syncDomains)) {
                    echo '<table class="list">';
                    echo '<tr class="sort">';
                    echo '<th>'.__d('admin', 'Domain').'</th>';
                    echo '<th>'.__d('admin', 'Active').'</th>';
                    echo '<th></th>';
                    echo '</th>';
                }

                foreach ($syncDomains as $syncDomain) {
                    echo '<tr>';
                    echo '<td>'.$syncDomain->domain.'</td>';
                    echo '<td align="center">';
                    if ($syncDomain->active == 1) {
                        echo '<i class="fas fa-check-circle ok"></i>';
                    } else {
                        echo '<i class="fas fa-minus-circle not-ok"></i>';
                    }
                    echo '</td>';
                    echo '<td>';
                    echo $this->Html->link(
                        '<i class="fas fa-pencil-alt ok"></i>',
                        $this->Network->getSyncDomainEdit($syncDomain->id),
                        [
                            'class' => 'btn btn-outline-light',
                            'title' => __d('admin', 'Edit'),
                            'escape' => false
                        ]
                    );
                    echo '</td>';
                    echo '<tr>';
                }
                if (!empty($syncDomains)) {
                    echo '</table>';
                }
                    ?>
                </td>
                <?php } else { ?>
                <td colspan="2"><p><?php echo __d('admin', 'As_long_as_the_variable_member_fee_is_active_no_remote_foodcoops_can_be_added_for_this_foodcoop.'); ?></p></td>
                <?php } ?>
        </tr>
        <?php } ?>
    </table>

    <br />


    <h2 class="info"><?php echo __d('admin', 'The_following_settings_can_not_be_changed_in_the_admin_area.'); ?></h2>

    <table class="list no-hover">

        <tr>
            <th><?php echo __d('admin', 'Setting'); ?></th>
            <th><?php echo __d('admin', 'Value'); ?></th>
        </tr>

        <?php
        foreach ($configurations as $configuration) {

            if ($configuration->type != 'readonly') {
                continue;
            }

            if (in_array($configuration->name, ['FCS_DEPOSIT_TAX_RATE', 'FCS_INVOICE_HEADER_TEXT']) && !Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
                continue;
            }

            echo '<tr>';

                echo '<td class="first">';
                    echo $configuration->text;
                echo '</td>';

                echo '<td>';
                    echo match($configuration->name) {
                        'FCS_WEEKLY_PICKUP_DAY' => $this->MyTime->getWeekdayName($configuration->value),
                        'FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA' => $configuration->value . ' (' . $this->MyTime->getWeekdayName((new DeliveryRhythmService)->getSendOrderListsWeekday()) . ')',
                        default => $configuration->value,
                    };
                echo '</td>';

            echo '</tr>';
        }
        ?>

        <tr>
            <td><?php echo __d('admin', 'Version_FoodCoopShop'); ?></td>
            <td><?php echo $versionFoodCoopShop; ?></td>
        </tr>

        <?php if (!empty($lastMigration)) { ?>
        <tr>
            <td><?php echo __d('admin', 'Last_executed_migration'); ?></td>
            <td><?php echo $lastMigration[0][0] . ' ' . $lastMigration[0][1]; ?></td>
        </tr>
        <?php } ?>

        <tr>
            <td>PHP Version</td>
            <td><?php echo PHP_VERSION; ?></td>
        </tr>

        <tr>
            <td>App.defaultTimezone</td>
            <td><?php echo Configure::read('App.defaultTimezone'); ?></td>
        </tr>

        <tr>
            <td>App.fullBaseUrl</td>
            <td><a target="_blank"href="<?php echo Configure::read('App.fullBaseUrl'); ?>"><?php echo Configure::read('App.fullBaseUrl'); ?></a></td>
        </tr>

        <tr>
            <td>app.emailOrderReminderEnabled</td>
            <td><?php echo Configure::read('app.emailOrderReminderEnabled') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <?php
        $ct = FactoryLocator::get('Table')->get('Configurations');
        if (Configure::read('appDb.FCS_CASHLESS_PAYMENT_ADD_TYPE') == $ct::CASHLESS_PAYMENT_ADD_TYPE_LIST_UPLOAD) { ?>
        <tr>
            <td>app.bankNameForCreditSystem</td>
            <td><?php echo Configure::read('app.bankNameForCreditSystem'); ?></td>
        </tr>
        <?php } ?>

        <tr>
            <td>app.paymentMethods</td>
            <td><?php echo join(', ', Configure::read('app.paymentMethods')); ?></td>
        </tr>

        <tr>
            <td>app.orderStates</td>
            <td><?php
                foreach(Configure::read('app.orderStates') as $key => $value) {
                    echo $value . ' (' . $key . ')<br />';
                }
            ?></td>
        </tr>

        <tr>
            <td>app.isDepositEnabled</td>
            <td><?php echo Configure::read('app.isDepositEnabled') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <?php if ($this->Html->paymentIsCashless()) { ?>
            <tr>
                <td>app.depositPaymentCashlessStartDate</td>
                <td><?php echo date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(Configure::read('app.depositPaymentCashlessStartDate'))); ?></td>
            </tr>
        <?php } ?>

        <tr>
            <td>app.depositForManufacturersStartDate</td>
            <td><?php echo date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(Configure::read('app.depositForManufacturersStartDate'))); ?></td>
        </tr>

        <tr>
            <td>app.customerMainNamePart</td>
            <td><?php echo Configure::read('app.customerMainNamePart'); ?></td>
        </tr>

        <tr>
            <td>app.isCustomerAllowedToModifyOwnOrders</td>
            <td><?php echo Configure::read('app.isCustomerAllowedToModifyOwnOrders') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.isCustomerAllowedToViewOwnOrders</td>
            <td><?php echo Configure::read('app.isCustomerAllowedToViewOwnOrders') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.isZeroTaxEnabled</td>
            <td><?php echo Configure::read('app.isZeroTaxEnabled') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.additionalTextForInvoice</td>
            <td><?php echo Configure::read('app.additionalTextForInvoice'); ?></td>
        </tr>

        <tr>
            <td>app.showStatisticsForAdmins</td>
            <td><?php echo Configure::read('app.showStatisticsForAdmins') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.isBlogFeatureEnabled</td>
            <td><?php echo Configure::read('app.isBlogFeatureEnabled') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.applyOrdersNotYetBilledCheckOnDeletingCustomers</td>
            <td><?php echo Configure::read('app.applyOrdersNotYetBilledCheckOnDeletingCustomers') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.applyPaymentsOkCheckOnDeletingCustomers</td>
            <td><?php echo Configure::read('app.applyPaymentsOkCheckOnDeletingCustomers') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.sendEmailWhenOrderDetailQuantityChanged</td>
            <td><?php echo Configure::read('app.sendEmailWhenOrderDetailQuantityChanged') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.selfServiceModeAutoLogoutDesktopEnabled</td>
            <td><?php echo Configure::read('app.selfServiceModeAutoLogoutDesktopEnabled') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.selfServiceModeShowOnlyStockProducts</td>
            <td><?php echo Configure::read('app.selfServiceModeShowOnlyStockProducts') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.selfServiceModeAutoGenerateInvoice</td>
            <td><?php echo Configure::read('app.selfServiceModeAutoGenerateInvoice') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.selfServiceLoginCustomers</td>
            <td><?php
                $output = array_map(function($selfServiceLoginCustomer) {
                    return $selfServiceLoginCustomer['label'] . ' (' . $selfServiceLoginCustomer['customerId'] . ')';
                }, Configure::read('app.selfServiceLoginCustomers'));
                echo $output ? join(' / ', $output) : '';
            ?></td>
        </tr>

        <tr>
            <td>app.showTaxInOrderConfirmationEmail</td>
            <td><?php echo Configure::read('app.showTaxInOrderConfirmationEmail') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.applyOpenOrderCheckForOrderReminder</td>
            <td><?php echo Configure::read('app.applyOpenOrderCheckForOrderReminder') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled</td>
            <td><?php echo Configure::read('app.changeOpenOrderDetailPriceOnProductPriceChangeDefaultEnabled') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.emailErrorLoggingEnabled</td>
            <td><?php echo Configure::read('app.emailErrorLoggingEnabled') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.paypalMeUsername</td>
            <td><?php echo Configure::read('app.paypalMeUsername'); ?></td>
        </tr>

        <tr>
            <td>app.defaultTax</td>
            <td><?php echo $this->Number->formatAsPercent($defaultTax->rate); ?> - <?php echo $defaultTax->active ? __d('admin', 'activated') : __d('admin', 'deactivated'); ?></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Logo_for_website,_width:'); ?> 260px<br /><?php echo Configure::read('App.fullBaseUrl'); ?>/files/images/<?php echo Configure::read('app.logoFileName'); ?></td>
            <td><img src="/files/images/<?php echo Configure::read('app.logoFileName'); ?>" /></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Logo_for_order_lists_and_invoices,_width:'); ?> 260px<br /><?php echo Configure::read('App.fullBaseUrl'); ?>/files/images/logo-pdf.jpg</td>
            <td><img src="/files/images/logo-pdf.jpg" /></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Default_image_for_product,_width:'); ?> 150x150<br /><?php echo Configure::read('App.fullBaseUrl'); ?>/files/images/products/de-default-home_default.jpg</td>
            <td><img src="/files/images/products/de-default-home_default.jpg" /></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Default_image_for_manufacturer,_width:'); ?> 150x150<br /><?php echo Configure::read('App.fullBaseUrl'); ?>/files/images/manufacturers/de-default-medium_default.jpg</td>
            <td><img src="/files/images/manufacturers/de-default-medium_default.jpg" /></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Default_image_for_blog_post,_width:'); ?> 150x113<br /><?php echo Configure::read('App.fullBaseUrl'); ?>/files/images/blog_posts/no-home-default.jpg</td>
            <td><img src="/files/images/blog_posts/no-home-default.jpg" /></td>
        </tr>

    </table>

</div>
