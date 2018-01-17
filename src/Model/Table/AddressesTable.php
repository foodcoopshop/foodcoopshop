<?php

namespace App\Model\Table\AddressTable;
use App\Model\Table\AppTable;

/**
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

abstract class AddressTable extends AppTable
{

    public $useTable = 'address';
    public $primaryKey = 'id_address';

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->virtualFields = [
            'name' => "TRIM(CONCAT(`{$this->alias}`.`firstname`,' ',`{$this->alias}`.`lastname`))"
        ];
    }
}
