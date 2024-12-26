<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TaxesTable extends AppTable
{

    public function initialize(array $config): void
    {
        $this->setTable('tax');
        parent::initialize($config);
        $this->setPrimaryKey('id_tax');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('rate', __('Please_enter_a_tax_rate.'));
        $validator->range('rate', [0.01, 99.99], __('Please_enter_a_number_between_{0}_and_{1}.', [0.01,99.99]));
        $validator->add('rate', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => __('This_tax_rate_is_already_being_used.')
        ]);
        return $validator;
    }

    public function getValidTaxIds() {
        $taxes = $this->getForDropdown();
        $taxes = array_keys($taxes);
        sort($taxes);
        return $taxes;
    }

    public function getValidTaxRatesWithoutPercentSign() {
        $taxes = $this->getForDropdown();
        $taxes = array_values($taxes);
        $taxes = array_map(function($tax) {
            return str_replace('%', '', $tax);
        }, $taxes);
        sort($taxes);
        return $taxes;
    }

    public function getNetPriceAndTaxId($grossPrice, $taxRate)
    {

        $taxId = false;
        $calculatedTaxRate = 0;

        if ($taxRate == 0) {
            $taxId = 0;
        } else {
            $tax = $this->find('all', conditions: [
                'Taxes.active' => APP_ON,
                'Taxes.rate' => $taxRate,
            ])->first();
            if (!empty($tax)) {
                $taxId = $tax->id_tax;
                $calculatedTaxRate = $tax->rate;
            }
        }

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        return [
            'netPrice' => $productsTable->getNetPrice($grossPrice, $calculatedTaxRate),
            'taxId' => $taxId,
        ];

    }

    public function getForDropdown($useRateAsKey = false)
    {
        $taxes = $this->find('all',
        conditions: [
            'Taxes.active' => APP_ON
        ],
        order: [
            'Taxes.rate' => 'ASC'
        ]);

        $preparedTaxes = [];
        if (Configure::read('app.isZeroTaxEnabled')) {
            $preparedTaxes = [
                0 => '0%'
            ];
        }
        foreach ($taxes as $tax) {
            $value = Configure::read('app.numberHelper')->formatTaxRate($tax->rate) . '%';
            if ($useRateAsKey) {
                $preparedTaxes[$tax->rate] = $value;
            } else {
                $preparedTaxes[$tax->id_tax] = $value;
            }
        }
        return $preparedTaxes;
    }
}
