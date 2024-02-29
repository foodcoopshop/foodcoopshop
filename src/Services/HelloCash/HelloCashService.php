<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\HelloCash;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Datasource\FactoryLocator;
use App\Services\Invoice\SendInvoiceToCustomerService;

class HelloCashService
{

    protected $Customer;

    protected $Invoice;

    protected $OrderDetail;

    protected $Payment;

    protected $QueuedJobs;

    protected $hostname = 'https://bookgoodlook.at';

    public $restEndpoint;

    public $locale = 'de_AT';

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

    public function cancelInvoice($customerId, $originalInvoiceId, $currentDay)
    {
        $postData = [
            'cancellation_cashier_id' => Configure::read('app.helloCashAtCredentials')['cashier_id'],
        ];

        $this->Customer = FactoryLocator::get('Table')->get('Customers');
        $customer = $this->Customer->find('all',
            conditions: [
                'Customers.id_customer' => $customerId,
            ],
            contain: [
                'AddressCustomers',
            ]
        )->first();

        $response = $this->getRestClient()->post(
            '/invoices/' . $originalInvoiceId . '/cancellation',
            $this->encodeData($postData),
            $this->getOptions(),
        );
        $responseObject = $this->decodeApiResponseAndCheckForErrors($response);
        $paidInCash = $responseObject->invoice_payment == 'Bar' ? 1 : 0;

        $taxRates = $this->prepareTaxesFromResponse($responseObject, true);

        $this->Invoice = FactoryLocator::get('Table')->get('Invoices');
        $newInvoice = $this->Invoice->saveInvoice(
            $responseObject->cancellation_details->cancellation_number,
            $customerId,
            $taxRates,
            $responseObject->cancellation_details->cancellation_number,
            '',
            $currentDay,
            $paidInCash,
            $customer->invoices_per_email_enabled,
        );

        $newInvoice->original_invoice_id = $originalInvoiceId;
        if ($customer->invoices_per_email_enabled) {
            $this->sendInvoiceToCustomer($customer, $newInvoice, true, $paidInCash);
        }

        return $responseObject;
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
            'invoice_paymentMethod' => $paidInCash ? Configure::read('app.helloCashAtCredentials')['payment_type_cash'] : Configure::read('app.helloCashAtCredentials')['payment_type_cashless'],
            'signature_mandatory' => 0,
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

        if (!empty($data->ordered_deposit) && $data->ordered_deposit['deposit_amount'] > 0) {
            $items[] = [
                'item_name' => __('Delivered_deposit'),
                'item_quantity' => $data->ordered_deposit['deposit_amount'],
                'item_price' => $data->ordered_deposit['deposit_incl'] / $data->ordered_deposit['deposit_amount'],
                'item_taxRate' => $depositTaxRate,
            ];
        };

        if (!empty($data->returned_deposit) && $data->returned_deposit['deposit_amount'] > 0) {
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
        $authHeader = [];
        if (Configure::read('app.helloCashAtCredentials')['token'] != '') {
            $authHeader = [
                'Authorization' => 'Bearer ' . Configure::read('app.helloCashAtCredentials')['token'],
            ];
        }
        $options = [
            'headers' => $authHeader,
        ];
        return $options;
    }

    public function getReceiptOld($invoiceId, $cancellation)
    {
        $response = $this->getReceiptOrInvoice('print', $invoiceId, $cancellation);
        $response = $response->getStringBody();
        $response = preg_replace('/src=("|\')\//', 'src=$1' . $this->hostname . '/', $response);
        $response = preg_replace('/print_frame\(\);/', '', $response);
        return $response;
    }

    public function getInvoice($invoiceId, $cancellation)
    {
        $response = $this->getRestClient()->get(
            'invoices/' . $invoiceId . '/pdf?locale=' . $this->locale . '&cancellation=' . ($cancellation ? 'true' : 'false'),
            [],
            $this->getOptions(),
        );
        $responseObject = $this->decodeApiResponseAndCheckForErrors($response);
        return base64_decode($responseObject->pdf_base64_encoded);
    }

    protected function getReceiptOrInvoice($type, $invoiceId, $cancellation)
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
            '/intern/cash-register/invoice/' . $type . '?iid=' . $invoiceId . ($cancellation ? '&cancellation=true' : ''),
            [],
            [
                'cookies' => [
                    'locale' => 'de_AT',
                ],
            ]
        );

        $response = $this->checkRequestForErrors($response);

        return $response;

    }

    public function generateInvoice($data, $currentDay, $paidInCash, $isPreview)
    {

        $userId = $this->createOrUpdateUser($data->id_customer);
        $postData = $this->getInvoicePostData($data, $userId, $paidInCash, $isPreview);

        $response = $this->getRestClient()->post(
            '/invoices',
            $this->encodeData($postData),
            $this->getOptions(),
        );
        $responseObject = $this->decodeApiResponseAndCheckForErrors($response);

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

        $taxRates = $this->prepareTaxesFromResponse($responseObject, false);

        $this->Invoice = FactoryLocator::get('Table')->get('Invoices');
        $newInvoice = $this->Invoice->saveInvoice(
            $responseObject->invoice_id,
            $data->id_customer,
            $taxRates,
            $responseObject->invoice_number,
            '',
            $currentDay,
            $paidInCash,
            $data->invoices_per_email_enabled,
        );

        if ($data->invoices_per_email_enabled) {
            $this->sendInvoiceToCustomer($data, $newInvoice, false, $paidInCash);
        }

        return $responseObject;

    }

    protected function sendInvoiceToCustomer($customer, $invoice, $isCancellationInvoice, $paidInCash)
    {
        $this->Customer = FactoryLocator::get('Table')->get('Customers');
        $sendInvoiceToCustomerService = new SendInvoiceToCustomerService();
        $sendInvoiceToCustomerService->isCancellationInvoice = $isCancellationInvoice;
        $sendInvoiceToCustomerService->customerName = $customer->name;
        $sendInvoiceToCustomerService->customerEmail = $customer->email;
        $sendInvoiceToCustomerService->invoicePdfFile = '';
        $sendInvoiceToCustomerService->invoiceNumber = $invoice->invoice_number;
        $sendInvoiceToCustomerService->invoiceSumPriceIncl = $invoice->sumPriceIncl;
        $sendInvoiceToCustomerService->invoiceDate = $invoice->created->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
        $sendInvoiceToCustomerService->invoiceId = $invoice->id;
        $sendInvoiceToCustomerService->originalInvoiceId = $invoice->original_invoice_id ?? null;
        $sendInvoiceToCustomerService->creditBalance = $this->Customer->getCreditBalance($customer->id_customer);
        $sendInvoiceToCustomerService->paidInCash = $paidInCash;
        $sendInvoiceToCustomerService->run();
    }

    protected function prepareTaxesFromResponse($responseObject, $cancellation)
    {

        $cancellationFactor = 1;

        if ($cancellation) {
            $cancellationFactor = -1;
        }

        $taxRates = [];
        foreach($responseObject->taxes as $tax) {
            $taxRates[$tax->tax_taxRate] = [
                'sum_price_excl' => $tax->tax_net * $cancellationFactor,
                'sum_price_incl' => $tax->tax_gross * $cancellationFactor,
                'sum_tax' => $tax->tax_tax * $cancellationFactor,
            ];
        }
        return $taxRates;
    }

    protected function createOrUpdateUser($customerId)
    {

        $this->Customer = FactoryLocator::get('Table')->get('Customers');
        $customer = $this->Customer->find('all',
            conditions: [
                'Customers.id_customer' => $customerId,
            ],
            contain: [
                'AddressCustomers',
            ],
        )->first();

        $data = [
            'user_firstname' => $customer->firstname,
            'user_surname' => $customer->lastname,
            'user_email' => $customer->email,
            'user_postalCode' => $customer->address_customer->postcode,
            'user_city' => $customer->address_customer->city,
            'user_street' => $customer->address_customer->address1,
        ];

        if ($customer->is_company) {
            $data['user_company'] = $customer->firstname;
            $data['user_firstname'] = '';
            $data['user_lastname'] = '';
            if ($customer->lastname != '') {
                $data['lastname'] = $customer->lastname;
            }
        }

        // updating user needs user_id in post data
        if ($customer->user_id_registrierkasse > 0) {

            $response = $this->getRestClient()->get(
                '/users/' . $customer->user_id_registrierkasse,
                [],
                $this->getOptions(),
            );
            $helloCashUser = $this->decodeApiResponseAndCheckForErrors($response);

            // check if associated user_id_registrierkasse is still available within hello cash)
            if (!empty($helloCashUser->user_id)) {
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

        $helloCashUser = $this->decodeApiResponseAndCheckForErrors($response);

        if (!array_key_exists('user_id', $data)) {
            $customer->user_id_registrierkasse = $helloCashUser->user_id;
            $this->Customer->save($customer);
        }

        return $helloCashUser->user_id;

    }

    protected function checkRequestForErrors($response)
    {
        if (preg_match('/Seite nicht gefunden/', $response->getStringBody())) {
            throw new HelloCashApiException($response->getStringBody());
        }
        return $response;
    }

    public function decodeApiResponseAndCheckForErrors($response)
    {

        $decodedResponse = json_decode($response->getStringBody());

        if (!empty($decodedResponse->error)) {
            if ($decodedResponse->error == 'An error occurred: Invalid authentication') {
                throw new HelloCashApiException($decodedResponse->error);
            }
        }

        if ($decodedResponse === 'An Error occurred') {
            throw new HelloCashApiException($decodedResponse);
        }

        return $decodedResponse;

    }

}
