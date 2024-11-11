<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Http\Exception\UnauthorizedException;
use App\Services\DeliveryRhythmService;
use Cake\Controller\Exception\InvalidParameterException;
use Cake\Log\Log;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ListsController extends AdminAppController
{

    public function invoices()
    {
        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $path = realpath(Configure::read('app.folder_invoices'));
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        $orderDetailsTable = $this->getTableLocator()->get('OrderDetails');
        $firstOrderYear = $orderDetailsTable->getFirstOrderYear((string) $this->identity->getManufacturerId());
        $lastOrderYear = $orderDetailsTable->getLastOrderYear((string) $this->identity->getManufacturerId());

        $year = h($this->getRequest()->getQuery('year', $lastOrderYear));
        $this->set('year', $year);

        $years = null;
        if ($lastOrderYear !== false && $firstOrderYear !== false) {
            $years = Configure::read('app.timeHelper')->getAllYearsUntilThisYear($lastOrderYear, $firstOrderYear);
        }
        $this->set('years', $years);

        if (!in_array($year, $years)) {
            throw new InvalidParameterException('year not allowed');
        }

        $dateFrom = $year . '-01-01';
        $dateTo = $year . '-12-31';

        $files = [];

        foreach ($objects as $name => $object) {

            if (!preg_match('/\.pdf$/', $object->getFileName())) {
                continue;
            }

            if (!preg_match('/'.__d('admin', '_Invoice_filename_').'/', $object->getFileName(), $matches)) {
                continue;
            }

            $invoiceDate = substr($object->getFileName(), 0, 10);
            $explodedString = explode('_', $object->getFileName());
            $manufacturerId = (int) $explodedString[2];
            $invoiceNumber = (int) $explodedString[4];

            // date check
            if (!(strtotime($invoiceDate) >= strtotime($dateFrom) && strtotime($invoiceDate) <= strtotime($dateTo))) {
                continue;
            }

            if ($this->identity->isManufacturer() && $manufacturerId != $this->identity->getManufacturerId()) {
                continue;
            }

            if (!$manufacturerId) {
                $message = 'error: ManufacturerId not found in ' . $object->getFileName();
                Log::error($message);
                continue;
            }

            $manufacturer = $manufacturersTable->find('all', conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId,
            ])->first();

            if ($manufacturer === null) {
                $message = 'error: Manufacturer not found, manufacturerId: ' . $manufacturerId;
                Log::error($message);
                continue;
            }

            $invoiceLink = '/admin/lists/getInvoice?file=' . str_replace(Configure::read('app.folder_invoices') . DS, '', $name);
            $invoiceLink = str_replace(DS, '/', $invoiceLink);
            
            $files[] = [
                'invoice_date' => $invoiceDate,
                'invoice_number' => $invoiceNumber,
                'manufacturer_name' => $manufacturer->name,
                'invoice' => [
                    'label' => __d('admin', 'Download'), 'link' => $invoiceLink, 'icon' => 'fa-arrow-right',
                ],
                'manufacturer_id' => $manufacturerId,
            ];
            
        }

        $files = Hash::sort($files, '{n}.invoice_date', 'asc');
        $files = Hash::sort($files, '{n}.manufacturer_name', 'asc');
        $this->set('files', $files);

        $this->set('title_for_layout', __d('admin', 'Invoices'));
    }

    public function orderLists()
    {

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $path = realpath(Configure::read('app.folder_order_lists'));
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $dateFrom = Configure::read('app.timeHelper')->formatToDateShort(Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        } else {
            $dateFrom = (new DeliveryRhythmService())->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
        }

        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $files = [];

        foreach ($objects as $name => $object) {

            if (!preg_match('/\.pdf$/', $name)) {
                continue;
            }

            // before 09/2017 ProductLists were generated and stored with "Artikel" in filename
            // the following preg_match avoids a batch renaming
            if (!preg_match('/'.__d('admin', '_Order_list_filename_').'('.__d('admin', 'product').'|Artikel)/', $name, $matches)) {
                continue;
            }

            $splittedFileName = $this->splitOrderDetailStringIntoParts($object->getFileName(), $matches[1]);
            $deliveryDate = $splittedFileName['deliveryDate'];
            $manufacturerId = $splittedFileName['manufacturerId'];
            $generationDate = $splittedFileName['generationDate'];

            // date check
            if (! (strtotime($dateFrom) == strtotime($deliveryDate))) {
                continue;
            }

            if ($this->identity->isManufacturer() && $manufacturerId != $this->identity->getManufacturerId()) {
                continue;
            }

            $isAnonymized = $this->isAnonymized($name);

            if ($this->identity->isManufacturer()) {
                if ($this->identity->getManufacturerAnonymizeCustomers() && !$isAnonymized) {
                    continue;
                }
                if (!$this->identity->getManufacturerAnonymizeCustomers() && $isAnonymized) {
                    continue;
                }
            }

            if (!$manufacturerId) {
                $message = 'error: ManufacturerId not found in ' . $object->getFileName();
                $this->Flash->error($message);
                $this->log($message);
                $this->set('files', []);
                return;
            }

            $manufacturer = $manufacturersTable->find('all', conditions: [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ])->first();

            $productListLink = '/admin/lists/getOrderList?file=' . str_replace(Configure::read('app.folder_order_lists') . DS, '', $name);
            $productListLink = str_replace(DS, '/', $productListLink);
            $customerListLink = preg_replace(
                '/' . str_replace(' ', '_', __d('admin', 'Order_list')) . '_' . $matches[1] . '/',
                str_replace(' ', '_', __d('admin', 'Order_list')) . '_' . __d('admin', 'member'),
                $productListLink,
            1);
            
            $listLabel = __d('admin', 'Order_list_with_clear_names');
            $listIcon = 'fa-eye';
            if ($isAnonymized) {
                $listLabel = __d('admin', 'Anonymized_order_list');
                $listIcon = 'fa-eye-slash';
            }
            if ($this->identity->isManufacturer()) {
                $listLabel = __d('admin', 'Show_order_list');
                $listIcon = 'fa-arrow-right';
            }
            $files[] = [
                'delivery_date' => $deliveryDate,
                'manufacturer_name' => $manufacturer->name,
                'product_lists' => [
                    ['label' => $listLabel, 'link' => $productListLink, 'icon' => $listIcon],
                ],
                'customer_lists' => [
                    ['label' => $listLabel, 'link' => $customerListLink, 'icon' => $listIcon],
                ],
                'generation_date' => $generationDate,
                'manufacturer_id' => $manufacturerId,
                'is_anonymized' => $isAnonymized,
            ];
            
        }

        $files = Hash::sort($files, '{n}.product_lists.0.label', 'asc');
        $files = Hash::sort($files, '{n}.manufacturer_name', 'asc');

        $this->set('files', $files);

        $this->set('title_for_layout', __d('admin', 'Order_lists'));
    }

    private function splitOrderDetailStringIntoParts($fileName, $ending)
    {
        $result = [];
        $result['deliveryDate'] = substr($fileName, 0, 10);

        // remove date
        $manufacturerString = substr($fileName, 11);

        // remove part after $positionOrderListsString (foodcoop name and file ending)
        $positionOrderListsString = strpos($manufacturerString, __d('admin', '_Order_list_filename_') . $ending);
        $manufacturerString = substr($manufacturerString, 0, $positionOrderListsString);
        $splittedManufacturerString = explode('_', $manufacturerString);

        $result['manufacturerId'] = (int) end($splittedManufacturerString);
        $result['generationDate'] = substr($fileName, -23, 19);

        return $result;
    }

    private function isAnonymized($path)
    {
        return preg_match('/anonymized/', $path);
    }

    public function getOrderList()
    {
        $filenameWithPath = Configure::read('app.folder_order_lists') . DS . h($this->getRequest()->getQuery('file'));

        if ($this->identity->isManufacturer()) {
            preg_match('/'.__d('admin', '_Order_list_filename_').'('.__d('admin', 'product').'|'.__d('admin', 'member').'|Artikel)/', h($this->getRequest()->getQuery('file')), $matches);
            if (!empty($matches[1])) {
                $splittedFileName = $this->splitOrderDetailStringIntoParts(h($this->getRequest()->getQuery('file')), $matches[1]);
                $manufacturerId = $splittedFileName['manufacturerId'];
                if ($manufacturerId != $this->identity->getManufacturerId()) {
                    throw new UnauthorizedException('manufacturer is not allowed to open order list of other manufacturers');
                }
                if ($this->identity->getManufacturerAnonymizeCustomers() && !$this->isAnonymized($filenameWithPath)) {
                    throw new UnauthorizedException('manufacturer is not allowed to open order list with clear text data');
                }
                if (!$this->identity->getManufacturerAnonymizeCustomers() && $this->isAnonymized($filenameWithPath)) {
                    throw new UnauthorizedException('manufacturer is not allowed to open order list with anonymized data');
                }
            }
        }

        return $this->getFile($filenameWithPath);
    }

    public function getInvoice()
    {
        $filenameWithPath = Configure::read('app.folder_invoices') . DS . h($this->getRequest()->getQuery('file'));

        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $this->identity->isCustomer()) {
            $string = h($this->getRequest()->getQuery('file'));
            $positionInvoiceString = strpos($string, '_' . __d('admin', 'Invoice') . '_');
            $splittedFileName = explode('_', substr($string, 0, $positionInvoiceString));
            $customerId = end($splittedFileName);
            if ($customerId != $this->identity->getId()) {
                throw new UnauthorizedException();
            }
        }

        return $this->getFile($filenameWithPath);
    }

    /**
     * invoices and order lists are not stored in webroot
     */
    private function getFile($filenameWithPath)
    {

        $this->disableAutoRender();

        $filenameWithPath = str_replace(DS.DS, '/', $filenameWithPath);
        $filenameWithPath = str_replace(DS, '/', $filenameWithPath);
        $explodedString = explode('/', $filenameWithPath);

        $filenameWithoutPath = $explodedString[count($explodedString) - 1 ];

        $this->response = $this->response->withType('pdf');
        $this->response = $this->response->withFile(
            $filenameWithPath,
        );
        $this->response = $this->response->withHeader('Content-Disposition', 'inline; filename="' . $filenameWithoutPath . '"');

        return $this->response;
    }
}
