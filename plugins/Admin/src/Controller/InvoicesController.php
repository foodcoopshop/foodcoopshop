<?php

namespace Admin\Controller;

use Cake\Core\Configure;

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
class InvoicesController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $this->AppAuth->isSuperadmin();
    }

    public function index()
    {

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        }
        $this->set('customerId', $customerId);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Invoice = $this->getTableLocator()->get('Invoices');

        $conditions = [
            'Invoices.id_customer > 0',
            'DATE_FORMAT(Invoices.created, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'',
            'DATE_FORMAT(Invoices.created, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'',
        ];

        if ($customerId != '') {
            $conditions['Invoices.id_customer'] = $customerId;
        }

        $invoices = $this->Invoice->find('all', [
            'contain' => [
                'InvoiceTaxes',
                'Customers',
            ],
            'conditions' => $conditions,
            'order' => [
                'Invoices.id' => 'DESC'
            ]
        ])->toArray();

        $this->set('invoices', $invoices);

        $invoiceSums = [
            'total_sum_price_excl' => 0,
            'total_sum_tax' => 0,
            'total_sum_price_incl' => 0,
        ];

        foreach($invoices as $invoice) {
            foreach($invoice->invoice_taxes as $invoiceTax) {
                $invoiceSums['total_sum_price_excl'] += $invoiceTax->total_price_tax_excl;
                $invoiceSums['total_sum_tax'] += $invoiceTax->total_price_tax;
                $invoiceSums['total_sum_price_incl'] += $invoiceTax->total_price_tax_incl;
            }
        }
        $this->set('invoiceSums', $invoiceSums);

        $this->set('customersForDropdown', $this->Customer->getForDropdown());
        $this->set('title_for_layout', __d('admin', 'Invoices'));


        // prepare tax sums
        $defaultArray = [
            'sum_price_excl' => 0,
            'sum_tax' => 0,
            'sum_price_incl' => 0,
        ];
        $taxRates = [
            'cashless' => [],
            'cash' => [],
            'total' => [],
        ];
        $taxRatesSums = [
            'cashless' => $defaultArray,
            'cash' => $defaultArray,
            'total' => $defaultArray,
        ];
         foreach($invoices as $invoice) {
            $taxRateType = $invoice->paid_in_cash ? 'cash' : 'cashless';
            foreach([$taxRateType, 'total'] as $trt) {
                foreach($invoice->invoice_taxes as $invoiceTax) {
                    $taxRate = Configure::read('app.numberHelper')->formatTaxRate($invoiceTax->tax_rate);
                    if (!isset($taxRates[$trt][$taxRate])) {
                        $taxRates[$trt][$taxRate] = $defaultArray;
                    }
                    $taxRates[$trt][$taxRate]['sum_price_excl'] += $invoiceTax->total_price_tax_excl;
                    $taxRates[$trt][$taxRate]['sum_tax'] += $invoiceTax->total_price_tax;
                    $taxRates[$trt][$taxRate]['sum_price_incl'] += $invoiceTax->total_price_tax_incl;

                    $taxRatesSums[$trt]['sum_price_excl'] += $invoiceTax->total_price_tax_excl;
                    $taxRatesSums[$trt]['sum_tax'] += $invoiceTax->total_price_tax;
                    $taxRatesSums[$trt]['sum_price_incl'] += $invoiceTax->total_price_tax_incl;

                }
            }
        }

        ksort($taxRates['cashless']);
        ksort($taxRates['cash']);
        ksort($taxRates['total']);

        $this->set('taxRates', $taxRates);
        $this->set('taxRatesSums', $taxRatesSums);

    }

}
