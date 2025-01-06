<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Command;

use App\Services\HelloCash\HelloCashService;
use App\Services\Invoice\GenerateInvoiceToCustomerService;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use App\Command\Traits\CronjobCommandTrait;

class SendInvoicesToCustomersCommand extends AppCommand
{

    use CronjobCommandTrait;

    public function execute(Arguments $args, ConsoleIo $io): int
    {

        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            throw new ForbiddenException();
        }
        $this->startTimeLogging();

        $customersTable = $this->getTableLocator()->get('Customers');
        $invoicesTable = $this->getTableLocator()->get('Invoices');
        $invoiceToCustomerService = new GenerateInvoiceToCustomerService();

        $this->setCronjobRunDay($args);

        $customersTable->dropManufacturersInNextFind();
        $customers = $customersTable->find('all',
        conditions: [
            'Customers.active' => APP_ON,
            'Customers.shopping_price <> "ZP"',
        ],
        contain: [
            'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
        ]);

        if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
            $helloCashService = new HelloCashService();
        }

        $i = 0;
        foreach($customers as $customer) {

            $data = $invoicesTable->getDataForCustomerInvoice($customer->id_customer, Configure::read('app.timeHelper')->formatToDbFormatDate($this->cronjobRunDay));

            if (!$data->new_invoice_necessary) {
                continue;
            }

            if (Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED')) {
                $helloCashService->generateInvoice($data, $this->cronjobRunDay, false, false);
                sleep(4); // the Hello Cash API handels max 60 requests per minute, one generateInvoice call uses up to 5 requests
            } else {
                $invoiceToCustomerService->run($data, $this->cronjobRunDay, false);
            }
            $i++;

        }

        $this->stopTimeLogging();

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $message = __('{0,plural,=1{1_invoice_was} other{#_invoices_were}}_generated_successfully.', [$i]);
        $actionLogsTable->customSave('invoice_added', 0, 0, 'invoices', $message . '<br />' . $this->getRuntime());

        return static::CODE_SUCCESS;

    }

}
