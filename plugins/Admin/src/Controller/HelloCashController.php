<?php

namespace Admin\Controller;

use App\Lib\HelloCash\HelloCash;
use Cake\Core\Configure;

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

    protected $helloCash;

    public function initialize(): void
    {
        parent::initialize();
        $this->helloCash = new HelloCash();
    }

    public function isAuthorized($user)
    {
        return Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') &&
            Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED') &&
            $this->AppAuth->isSuperadmin();
    }

    public function getReceipt($invoiceId)
    {
        $this->disableAutoRender();
        $response = $this->helloCash->getReceipt($invoiceId);
        $this->response = $this->response->withStringBody($response);
        return $this->response;
    }

    public function getInvoice($invoiceId)
    {

        $response = $this->helloCash->getRestClient()->get(
            '/invoices/' . $invoiceId . '/pdf',
            [],
            $this->helloCash->getOptions(),
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
        $this->disableAutoRender();
        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoiceData = $this->Invoice->getDataForCustomerInvoice($customerId, $currentDay);
        $response = $this->helloCash->generateInvoice($invoiceData, $currentDay, false);
        $this->response = $this->response->withStringBody($response);
        $this->response = $this->response->withHeader('Content-Type', 'json');
        return $this->response;
    }

}
