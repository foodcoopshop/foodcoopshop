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

    protected $endpoint = 'https://api.hellocash.business/api/v1';

    public function isAuthorized($user)
    {
        return Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') &&
            Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED') &&
            $this->AppAuth->isSuperadmin();
    }

    protected function getClient()
    {
        return Client::createFromUrl($this->endpoint);
    }

    protected function encodeData($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function getOptions()
    {
        return [
            'auth' => [
                'username' => Configure::read('app.helloCashAtCredentials')['username'],
                'password' => Configure::read('app.helloCashAtCredentials')['password'],
            ],
            'type' => 'json',
        ];
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
            $this->getOptions(),
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
        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoiceData = $this->Invoice->getDataForCustomerInvoice($customerId, $currentDay);
        $depositTaxRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'));

        $userId = $this->createOrUpdateUser($customerId);

        $preparedInvoiceData = [
            'cashier_id' => Configure::read('app.helloCashAtCredentials')['cashier_id'],
            'invoice_user_id' => $userId,
            'invoice_testMode' => Configure::read('app.helloCashAtCredentials')['test_mode'],
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

    protected function createOrUpdateUser($customerId)
    {

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId,
            ],
            'contain' => [
                'AddressCustomers',
            ],
        ])->first();

        $data = [
            'user_firstname' => $customer->firstname,
            'user_surname' => $customer->lastname,
            'user_email' => $customer->email,
            'user_postalCode' => $customer->address_customer->postcode,
            'user_city' => $customer->address_customer->city,
            'user_street' => $customer->address_customer->address1,
        ];

        // updating user needs user_id in post data
        if ($customer->user_id_registrierkasse > 0) {
            $data = array_merge($data, [
                'user_id' => $customer->user_id_registrierkasse,
            ]);
        }

        $response = $this->getClient()->post(
            '/users',
            $this->encodeData($data),
            $this->getOptions(),
        );

        $helloCashUser = json_decode($response->getStringBody());

        if ($customer->user_id_registrierkasse == 0) {
            $customer->user_id_registrierkasse = $helloCashUser->user_id;
            $this->Customer->save($customer);
        }

        return $helloCashUser->user_id;

    }

    protected function postInvoiceData($data)
    {
        $response = $this->getClient()->post(
            '/invoices',
            $this->encodeData($data),
            $this->getOptions(),
        );
        return $response->getStringBody();
    }

}
