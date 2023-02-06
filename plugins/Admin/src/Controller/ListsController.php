<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Http\Exception\UnauthorizedException;
use App\Lib\DeliveryRhythm\DeliveryRhythm;

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

    public function isAuthorized($user)
    {
        return match($this->getRequest()->getParam('action')) {
            'getInvoice' => (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $this->AppAuth->user()) ||
                ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isManufacturer()),
             default => $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isManufacturer(),
        };
    }

    public function orderLists()
    {

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $path = realpath(Configure::read('app.folder_order_lists'));
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $dateFrom = Configure::read('app.timeHelper')->formatToDateShort(Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        } else {
            $dateFrom = DeliveryRhythm::getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
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

            // date check
            if (! (strtotime($dateFrom) == strtotime($deliveryDate))) {
                continue;
            }

            if ($this->AppAuth->isManufacturer() && $manufacturerId != $this->AppAuth->getManufacturerId()) {
                continue;
            }

            $isAnonymized = preg_match('/anonymized/', $name);

            if ($this->AppAuth->isManufacturer()) {
                if ($this->AppAuth->getManufacturerAnonymizeCustomers() && !$isAnonymized) {
                    continue;
                }
                if (!$this->AppAuth->getManufacturerAnonymizeCustomers() && $isAnonymized) {
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

            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $manufacturerId
                ]
            ])->first();

            $productListLink = '/admin/lists/getOrderList?file=' . str_replace(Configure::read('app.folder_order_lists') . DS, '', $name);
            $productListLink = str_replace(DS, '/', $productListLink);
            $customerListLink = preg_replace(
                '/' . str_replace(' ', '_', __d('admin', 'Order_list')) . '_' . $matches[1] . '/',
                str_replace(' ', '_', __d('admin', 'Order_list')) . '_' . __d('admin', 'member'),
                $productListLink,
            1);
            
            $listLabel = $isAnonymized ? __d('admin', 'Anonymized_list') : __d('admin', 'List_with_names');
            if ($this->AppAuth->isManufacturer()) {
                $listLabel = __d('admin', 'Order_list');
            }
            $files[] = [
                'delivery_date' => $deliveryDate,
                'manufacturer_name' => $manufacturer->name,
                'product_list_link' => $productListLink,
                'list_label' => $listLabel,
                'customer_list_link' => $customerListLink,
            ];
            
            $files = Hash::sort($files, '{n}.manufacturer_name', 'asc');
            
        }
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

    public function getOrderList()
    {
        $filenameWithPath = Configure::read('app.folder_order_lists') . DS . h($this->getRequest()->getQuery('file'));

        if ($this->AppAuth->isManufacturer()) {
            preg_match('/'.__d('admin', '_Order_list_filename_').'('.__d('admin', 'product').'|'.__d('admin', 'member').'|Artikel)/', h($this->getRequest()->getQuery('file')), $matches);
            if (!empty($matches[1])) {
                $splittedFileName = $this->splitOrderDetailStringIntoParts(h($this->getRequest()->getQuery('file')), $matches[1]);
                $manufacturerId = $splittedFileName['manufacturerId'];
                if ($manufacturerId != $this->AppAuth->getManufacturerId()) {
                    throw new UnauthorizedException();
                }
            }
        }

        return $this->getFile($filenameWithPath);
    }

    public function getInvoice()
    {
        $filenameWithPath = Configure::read('app.folder_invoices') . DS . h($this->getRequest()->getQuery('file'));

        if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $this->AppAuth->isCustomer()) {
            $string = h($this->getRequest()->getQuery('file'));
            $positionInvoiceString = strpos($string, '_' . __d('admin', 'Invoice') . '_');
            $splittedFileName = explode('_', substr($string, 0, $positionInvoiceString));
            $customerId = end($splittedFileName);
            if ($customerId != $this->AppAuth->getUserId()) {
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
