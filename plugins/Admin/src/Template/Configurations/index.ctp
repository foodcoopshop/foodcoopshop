<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

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
                'FCS_MINIMAL_CREDIT_BALANCE'
            ])) {
                continue;
            }
            if (! Configure::read('app.memberFeeEnabled') && $configuration->name == 'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA') {
                continue;
            }
            if (! Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $configuration->name != 'FCS_TIMEBASED_CURRENCY_ENABLED' && substr($configuration->name, 0, 23) == 'FCS_TIMEBASED_CURRENCY_') {
                continue;
            }

            if ($configuration->name == 'FCS_SHOW_PRODUCT_PRICE_FOR_GUESTS' && !Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS')) {
                continue;
            }

            echo '<tr>';

                echo '<td class="first">';
                    echo $configuration->text;
                echo '</td>';

                echo '<td style="width:30px;">';

                    // timebased currency module is still in beta mode - only enable it in database and do not show edit icon
                    if ($configuration->name != 'FCS_TIMEBASED_CURRENCY_ENABLED') {
                        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), [
                            'title' => __d('admin', 'Edit'),
                            'class' => 'edit-configuration-button'
                        ], $this->Slug->getConfigurationEdit($configuration->id_configuration, $configuration->name));
                    }

                echo '</td>';

                echo '<td>';

                switch ($configuration->type) {
                    case 'number':
                    case 'text':
                    case 'textarea':
                    case 'textarea_big':
                        echo $configuration->value;
                        break;
                    case 'dropdown':
                        echo $this->Configuration->getConfigurationDropdownOption($configuration->name, $configuration->value);
                        break;
                    case 'boolean':
                        echo (boolean) $configuration->value ? __d('admin', 'yes') : __d('admin', 'no');
                        break;
                }

                echo '</td>';

            echo '</tr>';
        }
        ?>
        
        <?php if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) { ?>
            <tr>
                <td>
                    <b><?php echo __d('admin', 'Remote_foodcoops'); ?></b>
                    <br /><div class="small"><?php echo __d('admin', 'Foodcoops_with_which_manufacturers_can_synchronize_their_product_data.'); ?><br /><a target="_blank" href="<?php echo $this->Network->getNetworkPluginDocs(); ?>"><?php echo __d('admin', 'Info_page_for_network_module'); ?></a></div>
                </td>
                <?php if (!Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) { ?>
                <td colspan="2" class="sync-domain-list">
                <?php
                    echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> '.__d('admin', 'Add_remote_foodcoop').'', $this->Network->getSyncDomainAdd(), [
                        'class' => 'btn btn-default',
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
                        echo $this->Html->image($this->Html->getFamFamFamPath('accept.png'));
                    } else {
                        echo $this->Html->image($this->Html->getFamFamFamPath('delete.png'));
                    }
                    echo '</td>';
                    echo '<td>';
                    echo $this->Html->getJqueryUiIcon(
                        $this->Html->image($this->Html->getFamFamFamPath('page_edit.png')),
                        [
                        'title' => __d('admin', 'Edit')
                        ],
                        $this->Network->getSyncDomainEdit($syncDomain->id)
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

            echo '<tr>';

                echo '<td class="first">';
                    echo $configuration->text;
                echo '</td>';

                echo '<td>';
                    echo $configuration->value;
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
            <td><?php echo $lastMigration['migration_name'] . ' ' . $lastMigration['version']; ?></td>
        </tr>
        <?php } ?>

        <tr>
            <td>app.cakeServerName</td>
            <td><a target="_blank"href="<?php echo Configure::read('app.cakeServerName'); ?>"><?php echo Configure::read('app.cakeServerName'); ?></a></td>
        </tr>

        <tr>
            <td>app.emailOrderReminderEnabled</td>
            <td><?php echo Configure::read('app.emailOrderReminderEnabled') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.registrationNotificationEmails</td>
            <td><?php echo join(', ', Configure::read('app.registrationNotificationEmails')); ?></td>
        </tr>

        <tr>
            <td>app.adminEmail / app.adminPassword</td>
            <td><?php echo Configure::read('app.adminEmail'); ?> / <?php echo preg_replace("|.|", "*", Configure::read('app.adminPassword')); ?></td>
        </tr>

        <tr>
            <td>app.additionalOrderStatusChangeInfo</td>
            <td><?php echo Configure::read('app.additionalOrderStatusChangeInfo'); ?></td>
        </tr>

        <tr>
            <td>app.allowManualOrderListSending</td>
            <td><?php echo Configure::read('app.allowManualOrderListSending') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.sendOrderListsWeekday</td>
            <td><?php echo $this->MyTime->getWeekdayName(Configure::read('app.sendOrderListsWeekday')); ?></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Pick_up_day'); ?></td>
            <td><?php echo $this->MyTime->getWeekdayName(Configure::read('app.sendOrderListsWeekday') + Configure::read('app.deliveryDayDelta')); ?> (app.sendOrderListsWeekday + app.deliveryDayDelta)</td>
        </tr>

        <tr>
            <td>app.paymentMethods</td>
            <td><?php echo join(', ', Configure::read('app.paymentMethods')); ?></td>
        </tr>

        <tr>
            <td>app.visibleOrderStates</td>
            <td><?php echo json_encode(Configure::read('app.visibleOrderStates')); ?></td>
        </tr>

        <tr>
            <td>app.memberFeeEnabled</td>
            <td><?php echo Configure::read('app.memberFeeEnabled') ?  __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.isDepositPaymentCashless</td>
            <td><?php echo Configure::read('app.isDepositPaymentCashless') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <?php if (Configure::read('app.isDepositPaymentCashless')) { ?>
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

        <?php
        if ($this->elementExists('latestGitCommit')) {
            echo '<tr>';
            echo '<td>'.__d('admin', 'Software_update_version').'</td>';
            echo '<td>';
            echo nl2br($this->element('latestGitCommit'));
            echo __d('admin', 'Please_find_more_information_in_the_changelog_{0}.', ['<a href="https://www.foodcoopshop.com/changelog" target="_blank">Changelog</a>']);
            echo '</td>';
            echo '</tr>';
        }
        ?>

        <tr>
            <td>app.emailErrorLoggingEnabled</td>
            <td><?php echo Configure::read('app.emailErrorLoggingEnabled') ? __d('admin', 'yes') : __d('admin', 'no'); ?></td>
        </tr>

        <tr>
            <td>app.defaultTax</td>
            <td><?php echo $this->Number->formatAsPercent($defaultTax->rate); ?> - <?php echo $defaultTax->active ? __d('admin', 'activated') : __d('admin', 'deactivated'); ?></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Logo_for_website,_width:'); ?> 260px<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/logo.jpg</td>
            <td><img src="<?php echo Configure::read('app.cakeServerName'); ?>/files/images/logo.jpg" /></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Logo_for_order_lists_and_invoices,_width:'); ?> 260px<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/logo-pdf.jpg</td>
            <td><img src="/files/images/logo-pdf.jpg" /></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Default_image_for_product,_width:'); ?> 150x150<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/products/de-default-home_default.jpg</td>
            <td><img src="/files/images/products/de-default-home_default.jpg" /></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Default_image_for_manufacturer,_width:'); ?> 125x125<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/manufacturers/de-default-medium_default.jpg</td>
            <td><img src="/files/images/manufacturers/de-default-medium_default.jpg" /></td>
        </tr>

        <tr>
            <td><?php echo __d('admin', 'Default_image_for_blog_post,_width:'); ?> 150x113<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/blog_posts/no-home-default.jpg</td>
            <td><img src="/files/images/blog_posts/no-home-default.jpg" /></td>
        </tr>

    </table>

</div>
