<?php

use Admin\Controller\AdminAppController;
use Cake\Core\Configure;
use Cake\Utility\Hash;

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
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
        $this->uses = array(
            'Manufacturer'
        );

        $path = realpath(Configure::read('AppConfig.folder.order_lists'));
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

        $dateFrom = date('d.m.Y', Configure::read('AppConfig.timeHelper')->getDeliveryDay(Configure::read('AppConfig.timeHelper')->getCurrentDay()));
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);

        $files = array();

        foreach ($objects as $name => $object) {
            if (preg_match('/\.pdf$/', $name)) {
                // before 09/2017 ProductLists were generated and stored with "Artikel" in filename
                // the following preg_match does not make a batch renaming necessary
                if (!preg_match('/_Bestellliste_(Produkt|Artikel)_/', $name, $matches)) {
                    continue;
                }

                $deliveryDate = substr($object->getFileName(), 0, 10);

                // date check
                if (! (strtotime($dateFrom) == strtotime($deliveryDate))) {
                    continue;
                }

                // remove date
                $manufacturerString = substr($object->getFileName(), 11);

                // remove part after bestellistenString (foodcoop name and file ending)
                $positionBestelllistenString = strpos($manufacturerString, '_Bestellliste_'.$matches[1]);
                $manufacturerString = substr($manufacturerString, 0, $positionBestelllistenString);
                $splittedManufacturerString = explode('_', $manufacturerString);
                $manufacturerId = (int) end($splittedManufacturerString);

                if (!$manufacturerId) {
                    $message = 'Fehler: ManufacturerId nicht gefunden in ' . $object->getFileName();
                    $this->Flash->error($message);
                    $this->log($message);
                    $this->set('files', array());
                    return;
                }

                $manufacturer = $this->Manufacturer->find('first', array(
                    'conditions' => array(
                        'Manufacturer.id_manufacturer' => $manufacturerId
                    )
                ));

                $productListLink = '/admin/lists/getFile/?file=' . str_replace(Configure::read('AppConfig.folder.order_lists'), '', $name);
                $customerListLink = str_replace($matches[1], 'Mitglied', $productListLink);

                $files[] = array(
                    'delivery_date' => $deliveryDate,
                    'manufacturer_name' => $manufacturer['Manufacturer']['name'],
                    'product_list_link' => $productListLink,
                    'customer_list_link' => $customerListLink
                );

                $files = Hash::sort($files, '{n}.manufacturer_name', 'asc');
            }
        }
        $this->set('files', $files);

        $this->set('title_for_layout', 'Bestelllisten');
    }

    /**
     * invoices and order lists are not stored in webroot
     */
    public function getFile()
    {
        $this->autoRender = false;

        $filenameWithPath = str_replace(ROOT, '', Configure::read('AppConfig.folder.order_lists')) . DS . $this->params->query['file'];
        $explodedString = explode('\\', $filenameWithPath);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $explodedString[count($explodedString) - 1] . '"');

        readfile(ROOT . DS . $filenameWithPath);
    }
}
