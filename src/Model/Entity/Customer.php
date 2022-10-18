<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;

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
class Customer extends Entity
{

    protected $_virtual = ['name'];

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

}
