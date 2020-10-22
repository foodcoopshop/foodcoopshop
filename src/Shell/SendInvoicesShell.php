<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Shell;

use App\Mailer\AppMailer;
use Cake\Core\Configure;
use Cake\I18n\Time;

class SendInvoicesShell extends AppShell
{

    public $cronjobRunDay;

    public function main()
    {
        parent::main();

        $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');

        // $this->cronjobRunDay can is set in unit test
        if (!isset($this->args[0])) {
            $this->cronjobRunDay = Configure::read('app.timeHelper')->getCurrentDateTimeForDatabase();
        } else {
            $this->cronjobRunDay = $this->args[0];
        }

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfLastMonth($this->cronjobRunDay);
        $dateTo = Configure::read('app.timeHelper')->getLastDayOfLastMonth($this->cronjobRunDay);

        // 1) get all manufacturers (not only active ones)
        $manufacturers = $this->Manufacturer->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ],
            'contain' => [
                'Invoices',
                'AddressManufacturers',
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

        if (!Configure::read('appDb.FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES')) {
            $orderDetails->where(function ($exp, $query) {
                return $exp->or([
                    'Products.is_stock_product' => 0, // do not use "false" here!
                    'Manufacturers.stock_management_enabled' => 0 // do not use "false" here!
                ]);
            });
        }

        // 3) add up the order detail by manufacturer
        $manufacturerOrders = [];
        foreach ($orderDetails as $orderDetail) {
            if (!isset($manufacturerOrders[$orderDetail->product->id_manufacturer])) {
                $manufacturerOrders[$orderDetail->product->id_manufacturer] = [
                    'order_detail_amount_sum' => 0,
                    'order_detail_price_sum' => 0,
                ];
            }
            $manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_amount_sum'] += $orderDetail->product_amount;
            $manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
        }

        // 4) merge the order detail count with the manufacturers array
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->current_order_count = $manufacturerOrders[$manufacturer->id_manufacturer] ?? 0;
            $manufacturer->order_detail_amount_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_amount_sum'] ?? 0;
            $manufacturer->order_detail_price_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_price_sum'] ?? 0;
        }

        // 5) write action log
        $outString = $dateFrom . ' ' . __('to_(time_context)') . ' ' . $dateTo . '<br />';
        $actionLogDatas = $this->getActionLogData($manufacturers);
        if ($actionLogDatas == '') {
            $outString .= __('Generated_invoices') . ': 0';
        }
        $outString .= $actionLogDatas;
        $actionLog = $this->ActionLog->customSave('cronjob_send_invoices', 0, 0, '', $outString, new Time($this->cronjobRunDay));
        $this->out($outString);

        // 6) trigger queue invoice generation
        $this->QueuedJobs = $this->getTableLocator()->get('Queue.QueuedJobs');
        foreach ($manufacturers as $manufacturer) {
            if (!empty($manufacturer->current_order_count)) {
                $this->QueuedJobs->createJob('GenerateInvoice', [
                    'invoiceNumber' => $manufacturer->invoiceNumber,
                    'invoicePdfFile' => $manufacturer->invoicePdfFile,
                    'manufacturerId' => $manufacturer->id_manufacturer,
                    'manufactuerName' => $manufacturer->name,
                    'actionLogId' => $actionLog->id,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                ]);
            }
        }

        // 7) send email to accounting employee
        $accountingEmail = Configure::read('appDb.FCS_ACCOUNTING_EMAIL');
        if ($accountingEmail != '') {
            $email = new AppMailer();
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

        return true;

    }

    protected function getActionLogData($manufacturers)
    {

        $tableData = '';
        $sumPrice = 0;
        $i = 0;
        $outString = '';

        foreach ($manufacturers as $manufacturer) {

            $sendInvoice = $this->Manufacturer->getOptionSendInvoice($manufacturer->send_invoice);
            $manufacturer->invoiceNumber = $this->Manufacturer->Invoices->getNextInvoiceNumber($manufacturer->invoices);
            $manufacturer->invoicePdfFile = Configure::read('app.htmlHelper')->getInvoiceLink(
                $manufacturer->name, $manufacturer->id_manufacturer, Configure::read('app.timeHelper')->formatToDbFormatDate($this->cronjobRunDay), $manufacturer->invoiceNumber
            );
            $invoiceLink = '/admin/lists/getInvoice?file=' . str_replace(Configure::read('app.folder_invoices'), '', $manufacturer->invoicePdfFile);

            if (!empty($manufacturer->current_order_count)) {

                $identifier = $manufacturer->id_manufacturer;
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
                $tableData .= '<td>' . html_entity_decode($manufacturer->name) . '</td>';
                $tableData .= '<td>' . $manufacturer->invoiceNumber . '</td>';
                $tableData .= '<td>' . ($sendInvoice ? '<i class="fas fa-envelope not-ok" data-identifier="send-invoice-'.$identifier.'"></i>' : '') . '</td>';
                $tableData .= '<td>' . $productString . '</td>';
                $tableData .= '<td align="right"><b>' . Configure::read('app.numberHelper')->formatAsCurrency($price) . '</b>'.$variableMemberFeeAsString.'</td>';
                $tableData .= '<td>';
                $tableData .= Configure::read('app.htmlHelper')->link(
                    '<i class="fas fa-arrow-right not-ok" data-identifier="generate-invoice-'.$identifier.'"></i>',
                    $invoiceLink,
                    [
                        'class' => 'btn btn-outline-light',
                        'target' => '_blank',
                        'escape' => false
                    ]
                    );
                $tableData .= '</td>';
                $tableData .= '</tr>';

                $i ++;

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
            $outString .= '<tr><td colspan="4" align="right">'.__('Generated_invoices').'</td><td align="right"><b>'.$i.'</b></td><td></td></tr>';
            $outString .= '</table>';
        }


        return $outString;

    }


}
