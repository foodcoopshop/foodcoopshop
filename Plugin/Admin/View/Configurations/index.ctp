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

$this->element('addScript', array(
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();"
));
?>
<div id="configurations">

     <?php
    $this->element('addScript', array(
        'script' => "$('table.list').show();
        "
    ));
    ?>
    
    <div class="filter-container">
		<h1><?php echo $title_for_layout; ?></h1>
	</div>

	<div id="help-container">
		<ul>
			<li>Auf dieser Seite siehst du die Konfiguration deiner
				FoodCoopShop-Installation.</li>
		</ul>
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
            
            if (! Configure::read('htmlHelper')->paymentIsCashless() && in_array($configuration['Configuration']['name'], array(
                'FCS_BANK_ACCOUNT_DATA',
                'FCS_MINIMAL_CREDIT_BALANCE'
            ))) {
                continue;
            }
            if (! Configure::read('app.memberFeeEnabled') && $configuration['Configuration']['name'] == 'FCS_MEMBER_FEE_BANK_ACCOUNT_DATA') {
                continue;
            }
            
            echo '<tr>';
            
            echo '<td class="first">';
            echo $configuration['Configuration']['text'];
            echo '</td>';
            
            echo '<td style="width:30px;">';
            
            echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('page_edit.png')), array(
                'title' => 'Einstellung bearbeiten',
                'class' => 'edit-configuration-button'
            ), $this->Slug->getConfigurationEdit($configuration['Configuration']['id_configuration'], $configuration['Configuration']['name']));
            echo '</td>';
            
            echo '<td>';
            
            switch ($configuration['Configuration']['type']) {
                case 'number':
                case 'text':
                case 'textarea':
                    echo $configuration['Configuration']['value'];
                    break;
                case 'dropdown':
                    echo $this->Html->getConfigurationDropdownOption($configuration['Configuration']['name'], $configuration['Configuration']['value']);
                    break;
                case 'boolean':
                    echo (boolean) $configuration['Configuration']['value'] ? 'ja' : 'nein';
                    break;
            }
            
            echo '</td>';
            
            echo '</tr>';
        }
        ?>
    </table>

	<br />


	<h2 class="info">Die folgenden Einstellungen können (noch) nicht
		selbst geändert werden.</h2>

	<table class="list no-hover">

		<tr>
			<th>Einstellung</th>
			<th>Wert</th>
		</tr>

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
			<td><?php echo Configure::read('app.adminEmail'); ?> / <?php echo preg_replace("|.|","*",Configure::read('app.adminPassword')); ?></td>
		</tr>

		<tr>
			<td>app/Config/email.php</td>
            <?php
            require_once (APP . 'Config' . DS . 'email.php');
            $email = new EmailConfig();
            ?>
            <td><b>Host:</b> <?php echo $email->default['host']; ?>, <b>Username:</b> <?php echo $email->default['username']; ?>, <b>Log:</b> <?php echo (isset($email->default['log']) && $email->default['log']) ? 'on' : 'off'; ?></td>
		</tr>

		<tr>
			<td>app.useManufacturerCompensationPercentage</td>
			<td><?php echo Configure::read('app.useManufacturerCompensationPercentage') ? 'ja' : 'nein'; ?></td>
		</tr>

		<tr>
			<td>app.defaultCompensationPercentage</td>
			<td><?php echo Configure::read('app.defaultCompensationPercentage'); ?></td>
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
			<td>Abholtag</td>
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
			<td><?php echo date('d.m.Y', strtotime(Configure::read('app.depositPaymentCashlessStartDate'))); ?></td>
		</tr>
		<?php } ?>
		
        <tr>
			<td>app.depositForManufacturersStartDate</td>
			<td><?php echo date('d.m.Y', strtotime(Configure::read('app.depositForManufacturersStartDate'))); ?></td>
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
			<td><?php echo $this->Html->formatAsPercent($defaultTax['Tax']['rate']); ?> - <?php echo $defaultTax['Tax']['active'] ? 'aktiviert' : 'deaktiviert'; ?></td>
		</tr>

		<tr>
			<td><?php echo Configure::read('app.cakeServerName'); ?>/favicon.ico</td>
			<td><img
				src="<?php echo Configure::read('app.cakeServerName'); ?>/favicon.ico" /></td>
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