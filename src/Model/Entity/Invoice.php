<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Invoice extends Entity
{

    protected $_virtual = ['sum_price_excl', 'sum_tax', 'sum_price_incl'];

    protected $sum_price_excl;

    protected $sum_tax;

    protected $sum_price_incl;

    protected function _getSumPriceExcl()
    {
        $result = 0;
        foreach($this->invoice_taxes as $invoiceTax) {
            $result += $invoiceTax->total_price_tax_excl;
        }
        return $result;
    }

    protected function _getSumTax()
    {
        $result = 0;
        foreach($this->invoice_taxes as $invoiceTax) {
            $result += $invoiceTax->total_price_tax;
        }
        return $result;
    }

    protected function _getSumPriceIncl()
    {
        $result = 0;
        foreach($this->invoice_taxes as $invoiceTax) {
            $result += $invoiceTax->total_price_tax_incl;
        }
        return $result;
    }

}
