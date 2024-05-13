<?php
declare(strict_types=1);

namespace Admin\Traits\Manufacturers;

use App\Services\DeliveryNoteService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

trait GetDeliveryNoteTrait
{

    public function getDeliveryNote()
    {

        $this->disableAutoRender();

        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        $dateTo = h($this->getRequest()->getQuery('dateTo'));

        $manufacturer = $this->Manufacturer->find('all', conditions: [
            'Manufacturers.id_manufacturer' => $manufacturerId
        ])->first();

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getOrderDetailsForDeliveryNotes($manufacturerId, $dateFrom, $dateTo);

        $deliverNoteService = new DeliveryNoteService();
        $spreadsheet = $deliverNoteService->getSpreadsheet($orderDetails);

        $filename = $deliverNoteService->writeSpreadsheetAsFile($spreadsheet, $dateFrom, $dateTo, $manufacturer->name);

        $this->response = $this->response->withHeader('Content-Disposition', 'inline;filename="'.$filename.'"');
        $this->response = $this->response->withFile(TMP . $filename);

        $deliverNoteService->deleteTmpFile($filename);

        return $this->response;

    }

}