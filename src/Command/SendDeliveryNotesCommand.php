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

use App\Mailer\AppMailer;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use App\Services\DeliveryNoteService;
use App\Command\Traits\CronjobCommandTrait;

class SendDeliveryNotesCommand extends AppCommand
{

    use CronjobCommandTrait;

    public function execute(Arguments $args, ConsoleIo $io): int
    {

        if (!Configure::read('appDb.FCS_PURCHASE_PRICE_ENABLED')) {
            throw new ForbiddenException();
        }

        $this->startTimeLogging();

        $this->setCronjobRunDay($args);

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfLastMonth($this->cronjobRunDay);
        $dateTo = Configure::read('app.timeHelper')->getLastDayOfLastMonth($this->cronjobRunDay);

        $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');

        $manufacturers = $manufacturersTable->find('all',
        conditions: [
            'Manufacturers.send_delivery_notes' => APP_ON,
        ],
        contain: [
            'AddressManufacturers',
            'Customers.AddressCustomers',
        ],
        order: [
            'Manufacturers.name' => 'ASC',
        ]);

        $actionLogDatas = [];
        $manufacturersWithData = [];
        foreach($manufacturers as $manufacturer) {

            $orderDetails = $orderDetailsTable->getOrderDetailsForDeliveryNotes($manufacturer->id_manufacturer, $dateFrom, $dateTo);
            if ($orderDetails->count() == 0) {
                continue;
            }

            $newData = '- <i class="fas fa-envelope not-ok" data-identifier="send-delivery-note-'.$manufacturer->id_manufacturer.'"></i> ';
            $newData .= $manufacturer->decoded_name;
            $actionLogDatas[] = $newData;

            $deliverNoteService = new DeliveryNoteService();
            $spreadsheet = $deliverNoteService->getSpreadsheet($orderDetails);
            $manufacturer->deliverNotesFilename = $deliverNoteService->writeSpreadsheetAsFile($spreadsheet, $dateFrom, $dateTo, $manufacturer->name);

            $manufacturersWithData[] = $manufacturer;

        }

        $this->stopTimeLogging();

        $message = join('<br />', $actionLogDatas);
        if (count($actionLogDatas) > 0) {
            $message .= '<br />';
        }
        $message .=  __('{0,plural,=1{1_delivery_note_was} other{#_delivery_notes_were}}_generated_successfully.', [count($manufacturersWithData)]);
        $actionLog = $actionLogsTable->customSave('cronjob_send_delivery_notes', 0, 0, 'manufacturers', $message . '<br />' . $this->getRuntime());

        $invoicePeriodMonthAndYear = Configure::read('app.timeHelper')->getLastMonthNameAndYear();

        foreach($manufacturersWithData as $manufacturer) {

            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('Admin.send_delivery_note');
            $email->setTo($manufacturer->address_manufacturer->email)
            ->setAttachments([
                TMP . $manufacturer->deliverNotesFilename,
            ])
            ->setSubject(__('Delivery_note_for_{0}', [$invoicePeriodMonthAndYear]))
            ->setViewVars([
                'manufacturer' => $manufacturer,
                'invoicePeriodMonthAndYear' => $invoicePeriodMonthAndYear,
                'showManufacturerUnsubscribeLink' => true,
            ]);

            $email->afterRunParams = [
                'actionLogIdentifier' => 'send-delivery-note-' . $manufacturer->id_manufacturer,
                'actionLogId' => $actionLog->id,
            ];
            $email->addToQueue();

        }

        return static::CODE_SUCCESS;

    }

}
