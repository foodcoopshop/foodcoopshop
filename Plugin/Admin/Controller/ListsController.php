<?php

App::uses('Folder', 'Utility');

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

    public function order_lists()
    {
        $this->uses = array(
            'Manufacturer'
        );
        
        $path = realpath(Configure::read('app.folder.order_lists'));
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        
        $dateFrom = date('d.m.Y', Configure::read('timeHelper')->getDeliveryDay());
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);
        
        $files = array();
        
        foreach ($objects as $name => $object) {
            
            if (preg_match('/\.pdf$/', $name)) {
                
                if (preg_match('/_Artikel_/', $name))
                    continue;
                
                $deliveryDate = substr($object->getFileName(), 0, 10);
                
                // date check
                if (! (strtotime($dateFrom) == strtotime($deliveryDate))) {
                    continue;
                }
                
                // manufacturer name can be more than word... kinda complicated to get to the id
                $manufacturerString = str_replace(Inflector::slug(Configure::read('app.name')), '', substr($object->getFileName(), 11));
                $manufacturerString = str_replace('Bestellliste_Mitglied_', '', $manufacturerString);
                $manufacturerString = str_replace('.pdf', '', $manufacturerString);
                $manufacturerString = substr($manufacturerString, 0, - 1); // letztes _ weg
                $splittedManufacturerString = explode('_', $manufacturerString);
                
                $manufacturerId = end($splittedManufacturerString);
                $manufacturer = $this->Manufacturer->find('first', array(
                    'conditions' => array(
                        'Manufacturer.id_manufacturer' => $manufacturerId
                    )
                ));
                
                $customerListLink = '/admin/lists/get_file/?file=' . str_replace(Configure::read('app.folder.order_lists'), '', $name);
                $productListLink = str_replace('Mitglied', 'Artikel', $customerListLink);
                
                $files[] = array(
                    'delivery_date' => $deliveryDate,
                    'manufacturer_name' => $manufacturer['Manufacturer']['name'],
                    'product_list_link' => $productListLink,
                    'customer_list_link' => $customerListLink
                );
                
                $files = Set::sort($files, '{n}.manufacturer_name', 'desc');
            }
        }
        
        $files = Set::sort($files, '{n}.delivery_date', 'DESC');
        $this->set('files', $files);
        
        $this->set('title_for_layout', 'Bestelllisten');
    }

    /**
     * invoices and order lists are not stored in webroot
     */
    public function get_file()
    {
        $this->autoRender = false;
        
        $filenameWithPath = str_replace(ROOT, '', Configure::read('app.folder.order_lists')) . DS . $this->params->query['file'];
        $explodedString = explode('\\', $filenameWithPath);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $explodedString[count($explodedString) - 1] . '"');
        
        readfile(ROOT . DS . $filenameWithPath);
    }
}

?>