<?php
namespace Admin\Controller;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
* FoodCoopShop - The open source software for your foodcoop
*
* Licensed under The MIT License
* For full copyright and license information, please see the LICENSE.txt
* Redistributions of files must retain the above copyright notice.
*
* @since         FoodCoopShop 2.5.0
* @license       http://www.opensource.org/licenses/mit-license.php MIT License
* @author        Mario Rothauer <office@foodcoopshop.com>
* @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
* @link          https://www.foodcoopshop.com
*/

class StatisticsController extends AdminAppController
{

    public $manufacturerId;

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'index':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
                break;
            case 'myIndex':
                return $this->AppAuth->isManufacturer();
                break;
            default:
                return $this->AppAuth->isManufacturer();
                break;
        }
    }

    /**
     * $this->manufacturerId needs to be set in calling method
     * @return int
     */
    private function getManufacturerId()
    {
        $manufacturerId = '';
        if (!empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        } if ($this->manufacturerId > 0) {
            $manufacturerId = $this->manufacturerId;
        }
        return $manufacturerId;
    }

    public function myIndex()
    {
        $this->manufacturerId = $this->AppAuth->getManufacturerId();
        $this->index();
        $this->render('index');
    }

    public function index()
    {
        $manufacturerId = $this->getManufacturerId();

        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());
        $this->set('manufacturerId', $manufacturerId);

        if ($manufacturerId == '') {
            $this->set('title_for_layout', __d('admin', 'Statistics'));
            return;
        }

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();
        $this->set('manufacturer', $manufacturer);
        
        $this->set('title_for_layout', __d('admin', 'Statistics') . ' ' . $manufacturer->name);
        
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $monthlySumProducts = $this->OrderDetail->getMonthlySumProductByManufacturer($manufacturerId);
        
        $xAxisData = [];
        $yAxisData = [];
        foreach($monthlySumProducts as $data) {
            $xAxisData[] = $data['MonthAndYear'];
            $yAxisData[] = $data['SumTotalPaid'];
        }
        
        $this->set('xAxisData', $xAxisData);
        $this->set('yAxisData', $yAxisData);
        
    }
}
