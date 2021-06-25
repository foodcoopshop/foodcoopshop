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
        $response = json_decode($response->getStringBody());
        pr($response);
        exit;
    }

    public function getPrintableBon($invoiceId)
    {
        //59252020
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
        echo $response;
        exit;

    }

    public function getA4InvoiceAsPdf($invoiceId)
    {
        //59252020
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

    public function generateTestInvoice()
    {

        $invoiceData = [
            'cashier_id' => 143635,
            'invoice_user_id' => 2390341,
            'invoice_testMode' => true,
            'invoice_paymentMethod' => 'Bar',
            'invoice_text' => 'Zusatztext der Rechnung',
            'signature_mandatory' => 0,
            'invoice_reference' => 0,
            'items' => [
                [
                    'item_name' => 'Birne',
                    'item_quantity' => 5,
                    'item_price' => 120,
                    'item_taxRate' => 20,
                ]
            ],
        ];
        $response = $this->getClient()->post(
            '/invoices',
            json_encode($invoiceData),
            [
                'auth' => $this->getAuth(),
                'type' => 'json',
            ],
        );
        $response = json_decode($response->getStringBody());
        pr($response);
        exit;

    }

}
