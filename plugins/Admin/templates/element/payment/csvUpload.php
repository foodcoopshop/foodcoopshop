<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\I18n\DateTime;

if (empty($csvRecords)) {
    echo $this->Form->create(null, [
        'type' => 'file',
        'id' => 'csv-upload',
    ]);
    echo $this->Form->control('upload', [
        'type' => 'file',
        'accept' => '.csv',
        'onchange' => 'form.submit()',
        'label' => __d('admin', 'Upload_CSV_file_with_bank_transactions') . ': ',
    ]);
    echo $this->Form->end();
}

if (empty($csvPayments)) {
    return;
}

echo $this->Form->create($csvPayments, [
    'id' => 'csv-records'
]);

$this->element('addScript', [
    'script' =>
    Configure::read('app.jsNamespace') . ".Helper.initTooltip('.transaction-text');" .
    Configure::read('app.jsNamespace') . ".Admin.initSaveCsvUploadPayments();" .
    Configure::read('app.jsNamespace') . ".Admin.initRemoveValidationErrorAfterSelectChange('form#csv-records .select-member');" .
    Configure::read('app.jsNamespace') . ".Admin.bindSelectCsvRecord('.select-csv-record');" .
    Configure::read('app.jsNamespace') . ".Admin.initCsvUploadPaymentsCustomerDropdowns();"
]);
echo '<table class="list no-clone-last-row">';

echo '<th style="text-align:center;">'.__d('admin', 'Save').'?</th>';
echo '<th>' . __d('admin', 'Member'). '</th>';
echo '<th>' . __d('admin', 'Transaction_text'). '</th>';
echo '<th style="text-align:right;">' . $this->Html->getPaymentText($paymentType) . '</th>';
echo '<th style="text-align:right;">' . __d('admin', 'Transaction_added_on'). '</th>';

$i = 0;
foreach($csvPayments as $csvPayment) {

    echo '<tr class="' . (!$csvPayment->selected ? ' not-selected' : '') . '">';

    echo '<td style="text-align:center;">';
    echo $this->Form->control('Payments.'.$i.'.selected', [
        'type' => 'checkbox',
        'label' => '',
        'class' => 'select-csv-record',
        'checked' => $csvPayment->selected,
    ]);
    echo '</td>';

    echo '<td>';
    echo $this->Form->hidden('Payments.'.$i.'.original_id_customer');
    if ($csvPayment->original_id_customer > 0) {
        $customerModel = FactoryLocator::get('Table')->get('Customers');
        $customer = $customerModel->find('all',
            conditions: [
                'id_customer' => $csvPayment->original_id_customer
            ]
        )->first();
        echo $customer->name;
    } else {
        echo $this->Form->control('Payments.'.$i.'.id_customer', [
            'type' => 'select',
            'label' => '',
            'empty' => __d('admin', 'Please_select_a_member.'),
            'class' => 'select-member',
            'options' => $customersForDropdown,
            'value' => $csvPayment->id_customer,
        ]);
    }
    echo  '</td>';

    echo '<td style="text-align:center;">';
    if ($csvPayment->already_imported) {
        echo '<span style="color:red;float:left;"">'.__d('admin', 'This_transaction_was_already_imported.') . '</span>';
    }
    echo '<i class="fa fa-info-circle transaction-text ok fa-lg" title="'.$csvPayment->content.'"></i>';
    echo $this->Form->hidden('Payments.'.$i.'.content', ['value' => $csvPayment->content]);
    echo $this->Form->hidden('Payments.'.$i.'.already_imported', ['value' => $csvPayment->already_imported]);
    echo '</td>';

    echo '<td style="text-align:right;">';
    echo $this->Form->hidden('Payments.'.$i.'.amount');
    echo $this->Number->formatAsCurrency($csvPayment->amount);
    echo '</td>';

    echo '<td style="text-align:right;">';
    echo $this->Form->hidden('Payments.'.$i.'.date');
    $date = new DateTime($csvPayment->date);
    echo $date->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort'));
    echo '</td>';

    echo '</tr>';

    $i++;

}

echo '</table>';

echo '<button type="submit" class="btn btn-success">
        <i class="fas fa-check"></i> ' . __d('admin', 'Save') . '
    </button>';

echo $this->Form->end();
