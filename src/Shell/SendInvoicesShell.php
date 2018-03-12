<?php
/**
 * SendInvoicesShell
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
namespace App\Shell;

use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class SendInvoicesShell extends AppShell
{
    /**
     * sends invoices to manufacturers who have orders from the last month
     */
    public function main()
    {
        parent::main();

        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->Order = TableRegistry::get('Orders');
        $this->Manufacturer = TableRegistry::get('Manufacturers');

        $this->startTimeLogging();

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfLastMonth();
        $dateTo = Configure::read('app.timeHelper')->getLastDayOfLastMonth();

        // $dateFrom = '01.02.2016';
        // $dateTo = '29.02.2016';

        // 1) get all manufacturers (not only active ones)
        $manufacturers = $this->Manufacturer->find('all', [
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ])->toArray();

        // 2) get all orders in the given date range
        $orders = $this->Order->find('all', [
            'conditions' => [
                'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') >= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateFrom) . '\'',
                'DATE_FORMAT(Orders.date_add, \'%Y-%m-%d\') <= \'' . Configure::read('app.timeHelper')->formatToDbFormatDate($dateTo) . '\'',
                'Orders.current_state IN (' . join(",", [
                    ORDER_STATE_OPEN,
                    ORDER_STATE_CASH,
                    ORDER_STATE_CASH_FREE
                ]) . ')'
            ],
            'contain' => [
                'OrderDetails.Products'
            ]
        ]);

        // 3) add up the order detail by manufacturer
        $manufacturerOrders = [];
        foreach ($orders as $order) {
            foreach ($order->order_details as $orderDetail) {
                @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_quantity_sum'] += $orderDetail->product_quantity;
                @$manufacturerOrders[$orderDetail->product->id_manufacturer]['order_detail_price_sum'] += $orderDetail->total_price_tax_incl;
            }
        }

        // 4) merge the order detail count with the manufacturers array
        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->current_order_count = $manufacturerOrders[$manufacturer->id_manufacturer];
            $manufacturer->order_detail_quantity_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_quantity_sum'];
            $manufacturer->order_detail_price_sum = $manufacturerOrders[$manufacturer->id_manufacturer]['order_detail_price_sum'];
            $i++;
        }

        // 5) check if manufacturers have open order details and send email
        $i = 0;
        $outString = $dateFrom . ' bis ' . $dateTo . '<br />';

        $this->initSimpleBrowser();
        $this->browser->doFoodCoopShopLogin();

        foreach ($manufacturers as $manufacturer) {
            $sendInvoice = $this->Manufacturer->getOptionSendInvoice($manufacturer->send_invoice);
            if (!empty($manufacturer->current_order_count) && $sendInvoice) {
                $productString = ($manufacturer->order_detail_quantity_sum == 1 ? 'Produkt' : 'Produkte');
                $outString .= ' - ' . $manufacturer->name . ': ' . $manufacturer->order_detail_quantity_sum . ' ' . $productString . ' / ' . Configure::read('app.htmlHelper')->formatAsEuro($manufacturer->order_detail_price_sum) . '<br />';
                $url = $this->browser->adminPrefix . '/manufacturers/sendInvoice/' . $manufacturer->id_manufacturer . '/' . $dateFrom . '/' . $dateTo;
                $this->browser->get($url);
                $i ++;
            }
        }

        $this->browser->doFoodCoopShopLogout();

        // START send email to accounting employee
        $accountingEmail = Configure::read('appDb.FCS_ACCOUNTING_EMAIL');
        if ($accountingEmail != '') {
            $email = new AppEmail();
            $email->setTemplate('Admin.accounting_information_invoices_sent')
                ->setTo($accountingEmail)
                ->setSubject('Rechnungen für ' . Configure::read('app.timeHelper')->getLastMonthNameAndYear() . ' wurden verschickt')
                ->setViewVars([
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
                ])
                ->send();
        }
        // END send email to accounting employee

        $outString .= 'Verschickte Rechnungen: ' . $i;

        $this->stopTimeLogging();

        $this->ActionLog->customSave('cronjob_send_invoices', $this->browser->getLoggedUserId(), 0, '', $outString . '<br />' . $this->getRuntime());

        $this->out($outString);

        $this->out($this->getRuntime());
    }
}
