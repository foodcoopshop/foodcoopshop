<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class Invoice extends AppEntity
{

    protected array $_virtual = ['sum_price_excl', 'sum_tax', 'sum_price_incl'];

    protected function _getSumPriceExcl()
    {
        $result = 0;
        if (empty($this->invoice_taxes)) {
            return $result;
        }
        foreach($this->invoice_taxes as $invoiceTax) {
            $result += $invoiceTax->total_price_tax_excl;
        }
        return $result;
    }

    protected function _getSumTax()
    {
        $result = 0;
        if (empty($this->invoice_taxes)) {
            return $result;
        }
        foreach($this->invoice_taxes as $invoiceTax) {
            $result += $invoiceTax->total_price_tax;
        }
        return $result;
    }

    protected function _getSumPriceIncl()
    {
        $result = 0;
        if (empty($this->invoice_taxes)) {
            return $result;
        }
        foreach($this->invoice_taxes as $invoiceTax) {
            $result += $invoiceTax->total_price_tax_incl;
        }
        return $result;
    }

}
