<?php
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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Shell;

use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

class SendInvoicesShell extends AppShell
{
    
    public $cronjobRunDay;
    
    /**
     * sends invoices to manufacturers who have order details with pickup_day of last month
     */
    public function main()
    {
        parent::main();

        $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');

        $this->startTimeLogging();

        // $this->cronjobRunDay can is set in unit test
        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }
        
        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfLastMonth($this->cronjobRunDay);
        $dateTo = Configure::read('app.timeHelper')->getLastDayOfLastMonth($this->cronjobRunDay);
        
        // update all order details that are already billed but cronjob did not change the order state
        // to new order state ORDER_STATE_BILLED (introduced in FCS 2.2)
        // can be removed safely in FCS v3.0
        if ($this->cronjobRunDay == Configure::read('app.dateOfFirstSendInvoiceCronjobWithPickupDayUpdate')) {
            $this->OrderDetail->legacyUpdateOrderStateToNewBilledState($dateFrom, ORDER_STATE_CASH_FREE, ORDER_STATE_BILLED_CASHLESS);
            $this->OrderDetail->legacyUpdateOrderStateToNewBilledState($dateFrom, ORDER_STATE_CASH, ORDER_STATE_BILLED_CASH);
            $this->OrderDetail->legacyUpdateOrderStateToNewBilledState($dateFrom, ORDER_STATE_ORDER_PLACED, Configure::read('app.htmlHelper')->getOrderStateBilled());
            $this->OrderDetail->legacyUpdateOrderStateToNewBilledState(null, ORDER_STATE_CASH_FREE, ORDER_STATE_ORDER_PLACED);
            $this->OrderDetail->legacyUpdateOrderStateToNewBilledState(null, ORDER_STATE_CASH, ORDER_STATE_ORDER_PLACED);
        }
        
        // 1) get all manufacturers (not only active ones)
        $manufacturers = $this->Manufacturer->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'contain' => [
                'Invoices'
            ]
        ])->toArray();

        // 2) get all order details with pickup day in the given date range
        $orderDetails = $this->OrderDetail->find('all', [
            'conditions' => [
                'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'',
                'DATE_FORMAT(OrderDetails.pickup_day, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'',
                'OrderDetails.order_state NOT IN (' . join(",", [
                    ORDER_STATE_BILLED_CASH,
                    ORDER_STATE_BILLED_CASHLESS
                ]) . ')' // order_state condition necessary for switch from OrderDetails.created to OrderDetails.pickup_day
            ],
            'contain' => [
                'Products.Manufacturers',
                'Products'
            ]
        ]);
        
        if (!Configure::read('app.includeStockProductsInInvoices')) {
            $orderDetails->where(function ($exp, $query) {
                return $exp->or_([
                    'Products.is_stock_product' => false,
                    'Manufacturers.stock_management_enabled' => false
                ]);
            });
        }

        // 3) add up the order detail by manufacturer
        $manufacturerOrders = [];
        foreach ($orderDetails as $orderDetail) {
            @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_amount_sum'] += $orderDetail->product_amount;
            @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
        }

        // 4) merge the order detail count with the manufacturers array
        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->current_order_count = $manufacturerOrders[$manufacturer->id_manufacturer];
            $manufacturer->order_detail_amount_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_amount_sum'];
            $manufacturer->order_detail_price_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_price_sum'];
            $i++;
        }

        // 5) check if manufacturers have open order details and send email
        $i = 0;
        $outString = $dateFrom . ' ' . __('to_(time_context)') . ' ' . $dateTo . '<br />';

        $this->initSimpleBrowser();
        $this->browser->doFoodCoopShopLogin();

        $tableData = '';
        $sumPrice = 0;
        foreach ($manufacturers as $manufacturer) {
            $sendInvoice = $this->Manufacturer->getOptionSendInvoice($manufacturer->send_invoice);
            $invoiceNumber = $this->Manufacturer->Invoices->getNextInvoiceNumber($manufacturer->invoices);
            $invoiceLink = '/admin/lists/getInvoice?file=' . str_replace(
                Configure::read('app.folder_invoices'), '', Configure::read('app.htmlHelper')->getInvoiceLink(
                    $manufacturer->name, $manufacturer->id_manufacturer, Configure::read('app.timeHelper')->formatToDbFormatDate($this->cronjobRunDay), $invoiceNumber
                )
            );
            if (!empty($manufacturer->current_order_count)) {
                $price = $manufacturer->order_detail_price_sum;
                $sumPrice += $price;
                $variableMemberFeeAsString = '';
                if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
                    $variableMemberFee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
                    $price = $this->OrderDetail->getVariableMemberFeeReducedPrice($manufacturer->order_detail_price_sum, $variableMemberFee);
                    if ($variableMemberFee > 0) {
                        $variableMemberFeeAsString = ' (' . $variableMemberFee . '%)';
                    }
                }
                $productString = __('{0,plural,=1{1_product} other{#_products}}', [$manufacturer->order_detail_amount_sum]);
                $tableData .= '<tr>';
                $tableData .= '<td>' . $manufacturer->name . '</td>';
                $tableData .= '<td>' . $invoiceNumber . '</td>';
                $tableData .= '<td>' . ($sendInvoice ? __('yes') : __('no')) . '</td>';
                $tableData .= '<td>' . $productString . '</td>';
                $tableData .= '<td align="right"><b>' . Configure::read('app.numberHelper')->formatAsCurrency($price) . '</b>'.$variableMemberFeeAsString.'</td>';
                $tableData .= '<td>';
                    $tableData .= Configure::read('app.htmlHelper')->getJqueryUiIcon(Configure::read('app.htmlHelper')->image(Configure::read('app.htmlHelper')->getFamFamFamPath('arrow_right.png')), [
                        'target' => '_blank'
                    ], $invoiceLink);
                $tableData .= '</td>';
                $tableData .= '</tr>';
                $i ++;
            }
            if (!empty($manufacturer->current_order_count) && $sendInvoice) {
                $url = $this->browser->adminPrefix . '/manufacturers/sendInvoice?manufacturerId=' . $manufacturer->id_manufacturer . '&dateFrom=' . $dateFrom . '&dateTo=' . $dateTo;
                $this->browser->get($url);
            }
        }
        if ($tableData != '') {
            $outString .= '<table class="list no-clone-last-row">';
            $outString .= '<tr>';
            $outString .= '<th>' . __('Manufacturer') . '</th>';
            $outString .= '<th>' . __('Invoice_number_abbreviation') . '</th>';
            $outString .= '<th>' . __('Sent') . '?</th>';
            $outString .= '<th>' . __('Products') . '</th>';
            $outString .= '<th style="text-align:right;">' . __('Sum') . '</th>';
            $outString .= '<th></th>';
            $outString .= '</tr>';
            $outString .= $tableData;
            $outString .= '<tr><td colspan="4" align="right">'.__('Total_sum').'</td><td align="right"><b>'.Configure::read('app.numberHelper')->formatAsCurrency($sumPrice).'</b></td><td></td></tr>';
            $outString .= '</table>';
        }
        
        $this->browser->doFoodCoopShopLogout();

        // START send email to accounting employee
        $accountingEmail = Configure::read('appDb.FCS_ACCOUNTING_EMAIL');
        if ($accountingEmail != '') {
            $email = new AppEmail();
            $email->viewBuilder()->setTemplate('Admin.accounting_information_invoices_sent');
            $email->setTo($accountingEmail)
                ->setSubject(__('Invoices_for_{0}_have_been_sent', [Configure::read('app.timeHelper')->getLastMonthNameAndYear()]))
                ->setViewVars([
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'cronjobRunDay' => $this->cronjobRunDay
                ])
                ->send();
        }
        // END send email to accounting employee

        $outString .= __('Generated_invoices') . ': ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_send_invoices', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime(), new Time($this->cronjobRunDay));

        $this->out($outString);

        $this->out($this->getRuntime());
        
        return true;
        
    }
}
