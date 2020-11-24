<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Shell;

use App\Lib\Invoice\GenerateInvoiceToCustomer;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;

class SendInvoicesToCustomersShell extends AppShell
{

    public $cronjobRunDay;

    public function main()
    {
        parent::main();

        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            throw new ForbiddenException();
        }

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoiceToCustomer = new GenerateInvoiceToCustomer();

        // $this->cronjobRunDay can is set in unit test
        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }

        $this->Customer->dropManufacturersInNextFind();
        $customers = $this->Customer->find('all', [
            'conditions' => [
                'Customers.active' => APP_ON,
            ],
            'contain' => [
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ],
        ]);

        $i = 0;
        foreach($customers as $customer) {

            $data = $this->Invoice->getDataForCustomerInvoice($customer->id_customer, Configure::read('app.timeHelper')->formatToDbFormatDate($this->cronjobRunDay));

            if (!$data->new_invoice_necessary) {
                continue;
            }

            $invoiceToCustomer->run($data, $this->cronjobRunDay, false);
            $i++;

        }

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $message = __('{0,plural,=1{1_invoice_was} other{#_invoices_were}}_generated_successfully.', [$i]);
        $this->ActionLog->customSave('invoice_added', 0, 0, 'invoices', $message);

        return true;

    }

}
