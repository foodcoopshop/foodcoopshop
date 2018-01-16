<?php
/**
 * Tax
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
class Tax extends AppModel
{

    public $useTable = 'tax';

    public $primaryKey = 'id_tax';
 // sic! for binding from taxroulesgroup
    public $validate = array(
        'rate' => array(
            'range' => array(
                'rule' => array(
                    'range',
                    0,
                    100
                ),
                'message' => 'Bitte gibt eine Zahl von 0,01 bis 99,99 an'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'Dieser Steuersatz wird bereits verwendet.'
            )
        )
    );

    public function getForDropdown()
    {
        $taxes = $this->find('all', array(
            'conditions' => array(
                'Tax.active' => APP_ON
            ),
            'order' => array(
                'Tax.rate' => 'ASC'
            )
        ));
        $preparedTaxes = array(
            0 => '0 %'
        );
        foreach ($taxes as $tax) {
            $preparedTaxes[$tax['Tax']['id_tax']] = Configure::read('htmlHelper')->formatAsPercent($tax['Tax']['rate']);
        }
        return $preparedTaxes;
    }
}
