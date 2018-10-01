<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Customer extends Entity
{

    protected $_virtual = ['name'];

    protected function _getName()
    {

        if (!isset($this->_properties['firstname']) || !isset($this->_properties['lastname'])) {
            return '';
        }

        $virtualNameFields = $this->_properties['firstname'] . ' ' . $this->_properties['lastname'];
        if (Configure::read('app.customerMainNamePart') == 'lastname') {
            $virtualNameFields = $this->_properties['lastname'] . ' ' . $this->_properties['firstname'];
        }
        return $virtualNameFields;
    }
    
}
