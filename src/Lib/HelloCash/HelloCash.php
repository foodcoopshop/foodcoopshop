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

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Datasource\FactoryLocator;

class HelloCash
{

    protected $Customer;

    protected $Invoice;

    protected $OrderDetail;

    protected $Payment;

    protected $hostname = 'https://myhellocash.com';

    public $restEndpoint;

    public function __construct() {
        $this->restEndpoint = Configure::read('app.helloCashRestEndpoint');
    }

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

    protected function getInvoicePostData($data, $userId, $paidInCash, $isPreview)
    {

        $depositTaxRate = Configure::read('app.numberHelper')->parseFloatRespectingLocale(
            Configure::read('appDb.FCS_DEPOSIT_TAX_RATE'),
        );

        $postData = [
            'cashier_id' => Configure::read('app.helloCashAtCredentials')['cashier_id'],
            'invoice_user_id' => $userId,
            'invoice_testMode' => $isPreview,
            'invoice_paymentMethod' => $paidInCash ? 'Bar' : 'Kreditrechnung',
            'signature_mandatory' => 0,
            'invoice_reference' => 0,
        ];
        $items = [];

        foreach($data->active_order_details as $orderDetail) {
            $items[] = [
                'item_name' => StringComponent::removeEmojis($orderDetail->product_name),
                'item_quantity' => $orderDetail->product_amount,
                'item_price' => $orderDetail->total_price_tax_incl / $orderDetail->product_amount,
                'item_taxRate' => $orderDetail->tax_rate,
            ];
        };

        if (!empty($data->ordered_deposit)) {
            $items[] = [
                'item_name' => __('Delivered_deposit'),
                'item_quantity' => $data->ordered_deposit['deposit_amount'],
                'item_price' => $data->ordered_deposit['deposit_incl'] / $data->ordered_deposit['deposit_amount'],
                'item_taxRate' => $depositTaxRate,
            ];
        };

        if (!empty($data->returned_deposit)) {
            $items[] = [
                'item_name' => __('Payment_type_deposit_return'),
                'item_quantity' => 1,
                'item_price' => $data->returned_deposit['deposit_incl'],
                'item_taxRate' => $depositTaxRate,
            ];
        };

        $postData['items'] = $items;

        return $postData;

    }

    public function getOptions()
    {
        $auth = [];
        if (Configure::read('app.helloCashAtCredentials')['username'] != '' &&
            Configure::read('app.helloCashAtCredentials')['password'] != '') {
            $auth = [
                'username' => Configure::read('app.helloCashAtCredentials')['username'],
                'password' => Configure::read('app.helloCashAtCredentials')['password'],
            ];
        }
        return [
            'auth' => $auth,
            'type' => 'json',
        ];
    }

    public function getReceipt($invoiceId)
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

    public function generateInvoice($data, $currentDay, $paidInCash, $isPreview)
    {

        $userId = $this->createOrUpdateUser($data->id_customer);
        $postData = $this->getInvoicePostData($data, $userId, $paidInCash, $isPreview);

        $response = $this->postInvoiceData($postData);
        $responseObject = json_decode($response);

        if (!$isPreview) {
            $responseObject = $this->afterSuccessfulInvoiceGeneration($responseObject, $data, $currentDay, $paidInCash);
        }

        return $responseObject;

    }

    protected function afterSuccessfulInvoiceGeneration($responseObject, $data, $currentDay, $paidInCash)
    {

        $this->Payment = FactoryLocator::get('Table')->get('Payments');
        $this->Payment->linkReturnedDepositWithInvoice($data, $responseObject->invoice_id);

        $this->OrderDetail = FactoryLocator::get('Table')->get('OrderDetails');
        $this->OrderDetail->updateOrderDetails($data, $responseObject->invoice_id);

        $this->Invoice = FactoryLocator::get('Table')->get('Invoices');

        // override taxes with data from payload to avoid rounding differences
        $data->tax_rates = [];
        foreach($responseObject->taxes as $tax) {
            $data->tax_rates[$tax->tax_taxRate] = [
                'sum_price_excl' => $tax->tax_net,
                'sum_price_incl' => $tax->tax_gross,
                'sum_tax' => $tax->tax_tax,
            ];
        }

        $this->Invoice->saveInvoice($responseObject->invoice_id, $data, $responseObject->invoice_number, '', $currentDay, $paidInCash);

        return $responseObject;

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

            $response = $this->getRestClient()->get(
                '/users/' . $customer->user_id_registrierkasse,
                [],
                $this->getOptions(),
            );
            $helloCashUser = json_decode($response->getStringBody());

            // check if associated user_id_registrierkasse is still available within hello cash)
            if ($helloCashUser != 'User not found') {
                $data = array_merge($data, [
                    'user_id' => $customer->user_id_registrierkasse,
                ]);
            }

        }

        $response = $this->getRestClient()->post(
            '/users',
            $this->encodeData($data),
            $this->getOptions(),
        );

        $helloCashUser = json_decode($response->getStringBody());

        if (!array_key_exists('user_id', $data)) {
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
