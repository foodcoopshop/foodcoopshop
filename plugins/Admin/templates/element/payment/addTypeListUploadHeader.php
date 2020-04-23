<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".Admin.init();" .
    Configure::read('app.jsNamespace') . ".Admin.initDeletePayment();"
]);
?>
<div class="filter-container">
<h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
    	<?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_credit_system_with_csv_upload'))]); ?>
    </div>
</div>

<p>
	<b><?php echo __d('admin', 'Bank_account_data'); ?>: </b><?php echo Configure::read('appDb.FCS_BANK_ACCOUNT_DATA'); ?><br />
</p>
<?php echo $this->element('payment/personalTransactionCode', ['personalTransactionCode' => $personalTransactionCode]); ?>
