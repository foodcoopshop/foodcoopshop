<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Shell;

use App\Lib\DeliveryNote\GenerateDeliveryNote;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;

class SendDeliveryNotesShell extends AppShell
{

    public $cronjobRunDay;

    public function main()
    {
        parent::main();

        if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
            throw new ForbiddenException();
        }
        $this->startTimeLogging();

        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfLastMonth($this->cronjobRunDay);
        $dateTo = Configure::read('app.timeHelper')->getLastDayOfLastMonth($this->cronjobRunDay);

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');

        $manufacturers = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.send_delivery_notes' => APP_ON,
            ],
            'order' => [
                'Manufacturers.name' => 'ASC',
            ]
        ]);

        $actionLogDatas = [];
        $manufacturersWithData = [];
        foreach($manufacturers as $manufacturer) {

            $orderDetails = $this->OrderDetail->getOrderDetailsForDeliveryNotes($manufacturer->id_manufacturer, $dateFrom, $dateTo);
            if ($orderDetails->count() == 0) {
                continue;
            }

            $newData = '- <i class="fas fa-envelope not-ok" data-identifier="send-delivery-note-'.$manufacturer->id_manufacturer.'"></i> ';
            $newData .= html_entity_decode($manufacturer->name);
            $actionLogDatas[] = $newData;

            $generateDeliverNotes = new GenerateDeliveryNote();
            $spreadsheet = $generateDeliverNotes->getSpreadsheet($orderDetails);
            $manufacturer->deliverNotesFilename = $generateDeliverNotes->writeSpreadsheetAsFile($spreadsheet, $dateFrom, $dateTo, $manufacturer->name);

            $manufacturersWithData[] = $manufacturer;

        }

        $this->stopTimeLogging();

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $message = join('<br />', $actionLogDatas);
        if (count($actionLogDatas) > 0) {
            $message .= '<br />';
        }
        $message .=  __('{0,plural,=1{1_delivery_note_was} other{#_delivery_notes_were}}_generated_successfully.', [count($manufacturersWithData)]);
        $actionLog = $this->ActionLog->customSave('cronjob_send_delivery_notes', 0, 0, 'manufacturers', $message . '<br />' . $this->getRuntime());

        foreach($manufacturersWithData as $manufacturer) {

            $this->QueuedJobs = $this->loadModel('Queue.QueuedJobs');
            $this->QueuedJobs->createJob('SendDeliveryNote', [
                'deliveryNoteFile' => $manufacturer->deliverNotesFilename,
                'manufacturerId' => $manufacturer->id_manufacturer,
                'actionLogId' => $actionLog->id,
            ]);

        }

        return true;

    }

}
