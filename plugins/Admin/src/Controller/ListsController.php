<?php

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\ORM\TableRegistry;

/**
 * ListsController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ListsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
    }

    public function orderLists()
    {

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $path = realpath(Configure::read('app.folder_order_lists'));
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);

        $dateFrom = Configure::read('app.timeHelper')->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = $this->getRequest()->getQuery('dateFrom');
        }
        $this->set('dateFrom', $dateFrom);

        $files = [];

        foreach ($objects as $name => $object) {
            if (preg_match('/\.pdf$/', $name)) {

                // before 09/2017 ProductLists were generated and stored with "Artikel" in filename
                // the following preg_match does not make a batch renaming necessary
                if (!preg_match('/'.__d('admin', '_Order_list_filename_').'('.__d('admin', 'product').'|Artikel)/', $name, $matches)) {
                    continue;
                }

                $deliveryDate = substr($object->getFileName(), 0, 10);

                // date check
                if (! (strtotime($dateFrom) == strtotime($deliveryDate))) {
                    continue;
                }

                // remove date
                $manufacturerString = substr($object->getFileName(), 11);

                // remove part after $positionOrderListsString (foodcoop name and file ending)
                $positionOrderListsString = strpos($manufacturerString, __d('admin', '_Order_list_filename_') . $matches[1]);
                $manufacturerString = substr($manufacturerString, 0, $positionOrderListsString);
                $splittedManufacturerString = explode('_', $manufacturerString);
                $manufacturerId = (int) end($splittedManufacturerString);

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

                $productListLink = '/admin/lists/getOrderList?file=' . str_replace(Configure::read('app.folder_order_lists'), '', $name);
                $customerListLink = str_replace($matches[1], __d('admin', 'member'), $productListLink);

                $files[] = [
                    'delivery_date' => $deliveryDate,
                    'manufacturer_name' => $manufacturer->name,
                    'product_list_link' => $productListLink,
                    'customer_list_link' => $customerListLink
                ];

                $files = Hash::sort($files, '{n}.manufacturer_name', 'asc');
            }
        }
        $this->set('files', $files);

        $this->set('title_for_layout', __d('admin', 'Order_lists'));
    }
    
    public function getOrderList()
    {
        $filenameWithPath = str_replace(ROOT, '', Configure::read('app.folder_order_lists')) . DS . $this->getRequest()->getQuery('file');
        $this->getFile($filenameWithPath);
    }
    
    public function getInvoice()
    {
        $filenameWithPath = str_replace(ROOT, '', Configure::read('app.folder_invoices')) . DS . $this->getRequest()->getQuery('file');
        $this->getFile($filenameWithPath);
    }

    /**
     * invoices and order lists are not stored in webroot
     */
    private function getFile($filenameWithPath)
    {
        $explodedString = explode('\\', $filenameWithPath);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $explodedString[count($explodedString) - 1] . '"');
        readfile(ROOT . $filenameWithPath);
        exit; // $this->autoRender = false; is not enough!
    }
}
