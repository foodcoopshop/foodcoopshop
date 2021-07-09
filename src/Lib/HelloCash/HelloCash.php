<?php
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
namespace App\Lib\HelloCash;

use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Datasource\FactoryLocator;

class HelloCash
{

    protected $hostname = 'https://myhellocash.com';

    protected $restEndpoint = 'https://api.hellocash.business/api/v1';

    public function getRestClient()
    {
        return Client::createFromUrl($this->restEndpoint);
    }

    protected function getClient()
    {
        return Client::createFromUrl($this->hostname);
    }

    protected function encodeData($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function getOptions()
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

        $httpClient = $this->getClient();
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
        $response = preg_replace('/src=("|\')\//', 'src=$1' . $this->hostname . '/', $response);
        $response = preg_replace('/print_frame\(\);/', '', $response);

        return $response;

    }

    public function generateInvoice($invoiceData, $currentDay, $paidInCash)
    {

        $depositTaxRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(
            Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'),
        );

        $userId = $this->createOrUpdateUser($invoiceData->id_customer);

        $preparedInvoiceData = [
            'cashier_id' => Configure::read('app.helloCashAtCredentials')['cashier_id'],
            'invoice_user_id' => $userId,
            'invoice_testMode' => Configure::read('app.helloCashAtCredentials')['test_mode'],
            'invoice_paymentMethod' => $paidInCash ? 'Bar' : 'Kreditrechnung',
            'signature_mandatory' => 0,
            'invoice_reference' => 0,
        ];
        $items = [];

        foreach($invoiceData->active_order_details as $orderDetail) {
            $items[] = [
                'item_name' => $orderDetail->product_name,
                'item_quantity' => $orderDetail->product_amount,
                'item_price' => $orderDetail->total_price_tax_incl / $orderDetail->product_amount,
                'item_taxRate' => $orderDetail->tax_rate,
            ];
        };

        if (!empty($invoiceData->ordered_deposit)) {
            $items[] = [
                'item_name' => __('Delivered_deposit'),
                'item_quantity' => $invoiceData->ordered_deposit['deposit_amount'],
                'item_price' => $invoiceData->ordered_deposit['deposit_incl'] / $invoiceData->ordered_deposit['deposit_amount'],
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

        return $response;
    }

    protected function createOrUpdateUser($customerId)
    {

        $this->Customer = FactoryLocator::get('Table')->get('Customers');
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

        $response = $this->getRestClient()->post(
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
        $response = $this->getRestClient()->post(
            '/invoices',
            $this->encodeData($data),
            $this->getOptions(),
        );
        return $response->getStringBody();
    }

}
