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

    <h2 class="info">Die folgenden Einstellungen können selbst geändert werden.</h2>

    <table class="list no-hover no-clone-last-row">

        <tr>
            <th>Einstellung</th>
            <th></th>
            <th>Wert</th>
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
                            'title' => 'Einstellung bearbeiten',
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
                        echo (boolean) $configuration->value ? 'ja' : 'nein';
                        break;
                }
    
                echo '</td>';

            echo '</tr>';
        }
        ?>
        
        <?php if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) { ?>
            <tr>
                <td>
                    <b>Remote-Foodcoops</b>
                    <br /><div class="small">Foodcoops, mit denen Hersteller ihre Produktdaten synchronisieren können.<br /><a target="_blank" href="<?php echo $this->Network->getNetworkPluginDocs(); ?>">Infos zum Netzwerk-Modul</a></div>
                </td>
                <?php if (!Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) { ?>
                <td colspan="2" class="sync-domain-list">
                <?php
                    echo $this->Html->link('<i class="fa fa-plus-square fa-lg"></i> Neue Remote-Foodcoop erstellen', $this->Network->getSyncDomainAdd(), [
                        'class' => 'btn btn-default',
                        'escape' => false
                    ]);
                if (!empty($syncDomains)) {
                    echo '<table class="list">';
                    echo '<tr class="sort">';
                    echo '<th>Domain</th>';
                    echo '<th>Aktiv</th>';
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
                        'title' => 'Remote-Foodcoop ' . $syncDomain->domain . ' ändern',
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
                <td colspan="2"><p>Solange der variable Mitgliedsbeitrag aktiviert ist, können für diese Foodcoop keine Remote-Foodcoops erstellt werden.</p></td>
                <?php } ?>
        </tr>
        <?php } ?>
    </table>

    <br />


    <h2 class="info">Die folgenden Einstellungen können (noch) nicht
        selbst geändert werden.</h2>

    <table class="list no-hover">

        <tr>
            <th>Einstellung</th>
            <th>Wert</th>
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
            <td>Version FoodCoopShop</td>
            <td><?php echo $versionFoodCoopShop; ?></td>
        </tr>

        <?php if (!empty($lastMigration)) { ?>
        <tr>
            <td>Zuletzt ausgeführte Migration</td>
            <td><?php echo $lastMigration['migration_name'] . ' ' . $lastMigration['version']; ?></td>
        </tr>
        <?php } ?>

        <?php if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) { ?>
        <tr>
            <td>Version Netzwerk-Modul</td>
            <td><?php echo $versionNetworkPlugin; ?></td>
        </tr>
        <?php } ?>

        <tr>
            <td>app.cakeServerName</td>
            <td><a target="_blank"
                href="<?php echo Configure::read('app.cakeServerName'); ?>"><?php echo Configure::read('app.cakeServerName'); ?></a></td>
        </tr>
        

        <tr>
            <td>app.emailOrderReminderEnabled</td>
            <td><?php echo Configure::read('app.emailOrderReminderEnabled') ? 'ja' : 'nein'; ?></td>
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
            <td><?php echo Configure::read('app.allowManualOrderListSending') ? 'ja' : 'nein'; ?></td>
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
            <td><?php echo Configure::read('app.memberFeeEnabled') ? 'ja' : 'nein'; ?></td>
        </tr>

        <tr>
            <td>app.isDepositPaymentCashless</td>
            <td><?php echo Configure::read('app.isDepositPaymentCashless') ? 'ja' : 'nein'; ?></td>
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
            echo '<td>Software-Update / Version</td>';
            echo '<td>';
            echo nl2br($this->element('latestGitCommit'));
            echo 'Mehr Informationen zu den Änderungen findest du im <a href="https://www.foodcoopshop.com/changelog" target="_blank">Changelog</a>.';
            echo '</td>';
            echo '</tr>';
        }
        ?>

        <tr>
            <td>app.emailErrorLoggingEnabled</td>
            <td><?php echo Configure::read('app.emailErrorLoggingEnabled') ? 'ja' : 'nein'; ?></td>
        </tr>

        <tr>
            <td>app.defaultTax</td>
            <td><?php echo $this->Number->formatAsPercent($defaultTax->rate); ?> - <?php echo $defaultTax->active ? 'aktiviert' : 'deaktiviert'; ?></td>
        </tr>

        <tr>
            <td>Logo für Webseite (Breite: 260px)<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/logo.jpg</td>
            <td><img
                src="<?php echo Configure::read('app.cakeServerName'); ?>/files/images/logo.jpg" /></td>
        </tr>

        <tr>
            <td>Logo für Bestelllisten und Rechnungen (Breite: 260px)<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/logo-pdf.jpg</td>
            <td><img src="/files/images/logo-pdf.jpg" /></td>
        </tr>

        <tr>
            <td>Default-Bild für Produkte (Liste, 150x150)<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/products/de-default-home_default.jpg</td>
            <td><img src="/files/images/products/de-default-home_default.jpg" /></td>
        </tr>

        <tr>
            <td>Default-Bild für Hersteller (Liste: 125x125)<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/manufacturers/de-default-medium_default.jpg</td>
            <td><img
                src="/files/images/manufacturers/de-default-medium_default.jpg" /></td>
        </tr>

        <tr>
            <td>Default-Bild für Aktuelles-Beitrag (Home, 150x113)<br /><?php echo Configure::read('app.cakeServerName'); ?>/files/images/blog_posts/no-home-default.jpg</td>
            <td><img src="/files/images/blog_posts/no-home-default.jpg" /></td>
        </tr>

    </table>

</div>
