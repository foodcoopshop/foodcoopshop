<?php

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Client;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class HelloCashController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $this->AppAuth->isSuperadmin();
    }

    protected function getClient()
    {
        return Client::createFromUrl(Configure::read('app.helloCashAtEndpoint'));
    }

    protected function getAuth()
    {
        return [
            'username' => Configure::read('app.helloCashAtCredentials')['username'],
            'password' => Configure::read('app.helloCashAtCredentials')['password'],
        ];
    }

    public function getInvoices()
    {
        $response = $this->getClient()->get(
            '/invoices?mode=test',
            [],
            [
                'auth' => $this->getAuth(),
                'type' => 'json',
            ],
        );
        $this->disableAutoRender();
        $this->response = $this->response->withStringBody($response->getStringBody());
        $this->response = $this->response->withHeader('Content-Type', 'json');
        return $this->response;
    }

    public function getPrintableBon($invoiceId)
    {
        $host = 'https://myhellocash.com';
        $httpClient = Client::createFromUrl($host);

        $response = $httpClient->post(
            '/api/salon/login',
            [
                'email' => Configure::read('app.helloCashAtCredentials')['username'],
                'password' => Configure::read('app.helloCashAtCredentials')['password'],
            ],
            [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
            ],
        );

        $response = $httpClient->get(
            '/intern/cash-register/invoice/print?iid=' . $invoiceId,
             [],
             [
                 'cookies' => [
                     'locale' => 'de_AT',
                 ],
             ]
        );

        $response = $response->getStringBody();
        $response = preg_replace('/src=("|\')\//', 'src=$1' . $host . '/', $response);
        $response = preg_replace('/print_frame\(\);/', '', $response);

        $this->disableAutoRender();
        $this->response = $this->response->withStringBody($response);
        return $this->response;

    }

    public function getA4InvoiceAsPdf($invoiceId)
    {

        $response = $this->getClient()->get(
            '/invoices/' . $invoiceId . '/pdf',
            [],
            [
                'auth' => $this->getAuth(),
                'type' => 'json',
            ],
        );
        $response = json_decode($response->getStringBody());

        $this->response = $this->response->withType('pdf');
        $this->response = $this->response->withStringBody(
            base64_decode($response->pdf_base64_encoded),
        );
        $filenameWithoutPath = 'invoice.pdf';
        $this->response = $this->response->withHeader('Content-Disposition', 'inline; filename="' . $filenameWithoutPath . '"');

        return $this->response;
    }

    public function generateInvoice($customerId, $currentDay)
    {
        //3/2021-06-25
        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoiceData = $this->Invoice->getDataForCustomerInvoice($customerId, $currentDay);
        $depositTaxRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));

        $preparedInvoiceData = [
            'cashier_id' => 143635,
            'invoice_user_id' => 2390341,
            'invoice_testMode' => true,
            'invoice_paymentMethod' => 'Bar',
            'signature_mandatory' => 0,
            'invoice_reference' => 0,
        ];
        $items = [];

        foreach($invoiceData->active_order_details as $orderDetail) {
            $items[] = [
                'item_name' => $orderDetail->product_name,
                'item_quantity' => $orderDetail->product_amount,
                'item_price' => $orderDetail->total_price_tax_incl,
                'item_taxRate' => $orderDetail->tax_rate,
            ];
        };

        if (!empty($invoiceData->ordered_deposit)) {
            $items[] = [
                'item_name' => __('Delivered_deposit'),
                'item_quantity' => 1,
                'item_price' => $invoiceData->ordered_deposit['deposit_incl'],
                'item_taxRate' => $depositTaxRate,
            ];
        };

        if (!empty($invoiceData->returned_deposit)) {
            $items[] = [
                'item_name' => __('Payment_type_deposit_return'),
                'item_quantity' => 1,
                'item_price' => $invoiceData->returned_deposit['deposit_incl'],
                'item_taxRate' => $depositTaxRate,
            ];
        };

        $preparedInvoiceData['items'] = $items;

        $response = $this->postInvoiceData($preparedInvoiceData);

        $this->disableAutoRender();
        $this->response = $this->response->withStringBody($response);
        $this->response = $this->response->withHeader('Content-Type', 'json');
        return $this->response;

    }

    public function generateTestInvoice()
    {
        $preparedInvoiceData = [
            'cashier_id' => 143635,
            'invoice_user_id' => 2390341,
            'invoice_testMode' => true,
            'invoice_paymentMethod' => 'Bar',
            'signature_mandatory' => 0,
            'invoice_reference' => 0,
            'items' => [
                [
                    'item_name' => 'Forelle 280g',
                    'item_quantity' => 1,
                    'item_price' => 19.9,
                    'item_taxRate' => 13,
                ],
                [
                    'item_name' => 'Pfand Retour',
                    'item_quantity' => 2,
                    'item_price' => -1,
                    'item_taxRate' => 20,
                ],
            ],
        ];

        $response = $this->postInvoiceData($preparedInvoiceData);
        $this->disableAutoRender();
        $this->response = $this->response->withStringBody($response);
        $this->response = $this->response->withHeader('Content-Type', 'json');
        return $this->response;
    }

    private function postInvoiceData($invoiceData)
    {
        $response = $this->getClient()->post(
            '/invoices',
            json_encode($invoiceData, JSON_UNESCAPED_UNICODE),
            [
                'auth' => $this->getAuth(),
                'type' => 'json',
            ],
        );
        return $response->getStringBody();
    }

}
