<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Authentication\IdentityInterface;
use Cake\Datasource\FactoryLocator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Customer extends Entity implements IdentityInterface
{

    protected $_virtual = ['name'];

    public function getIdentifier()
    {
        return $this->id_customer;
    }

    public function getOriginalData()
    {
        return $this;
    }

    protected function _getName()
    {
        $virtualNameFields = $this->firstname . ' ' . $this->lastname;
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            $virtualNameFields = $this->lastname . ' ' . $this->firstname;
        }
        if ($this->is_company) {
            $virtualNameFields = $this->firstname;
        }
        return $virtualNameFields;
    }

    public function termsOfUseAccepted(): bool
    {
        $formattedAcceptedDate = $this->get('terms_of_use_accepted_date')->i18nFormat(Configure::read('DateFormat.Database'));
        return $formattedAcceptedDate >= Configure::read('app.termsOfUseLastUpdate');
    }

    public function isSuperadmin(): bool
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->get('id_default_group') == CUSTOMER_GROUP_SUPERADMIN) {
            return true;
        }
        return false;
    }
    
    private function setManufacturer()
    {
        if (!empty($this->user()) &&
            !is_null($this->getController()->getRequest()->getSession()->read('Auth')) &&
            array_key_exists('Manufacturer', $this->getController()->getRequest()->getSession()->read('Auth'))) {
            return;
        }

        if (!empty($this->user())) {
            $mm = FactoryLocator::get('Table')->get('Manufacturers');
            $manufacturer = $mm->find('all', [
                'conditions' => [
                    'AddressManufacturers.email' => $this->user('email'),
                    'AddressManufacturers.id_manufacturer > ' . APP_OFF
                ],
                'contain' => [
                    'AddressManufacturers',
                    'Customers.AddressCustomers',
                ]
            ])->first();
            if (!is_null($manufacturer)) {
                $manufacturer = $manufacturer->toArray();
            }
            $this->getController()->getRequest()->getSession()->write('Auth.Manufacturer', $manufacturer);
        }
    }

    public function isManufacturer(): bool
    {
        // TODO REFACTOR AUTH
        return false;
        $this->setManufacturer();
        return !empty($this->getController()->getRequest()->getSession()->read('Auth.Manufacturer'));
    }    

}
