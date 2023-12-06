<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Services\HelloCash\HelloCashService;
use Cake\Core\Configure;

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
class HelloCashController extends AdminAppController
{

    protected $helloCashService;
    protected $Invoice;

    public function initialize(): void
    {
        parent::initialize();
        $this->helloCashService = new HelloCashService();
    }

    public function isAuthorized($user)
    {
            $isAllowed = Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') &&
                Configure::read('appDb.FCS_HELLO_CASH_API_ENABLED') &&
                ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isCustomer());

            if ($this->AppAuth->isCustomer()) {
                $invoiceId = $this->request->getParam('pass')[0];
                $this->Invoice = $this->getTableLocator()->get('Invoices');
                $invoice = $this->Invoice->find('all', [
                    'conditions' => [
                        'Invoices.id' => $invoiceId,
                        'Invoices.id_customer' => $this->AppAuth->getUserId(),
                    ],
                ])->first();
                $isAllowed = !empty($invoice);
            }

            return $isAllowed;
    }

    public function getReceipt($invoiceId, $cancellation)
    {
        $this->disableAutoRender();
        $response = $this->helloCashService->getReceipt($invoiceId, $cancellation);
        $this->response = $this->response->withStringBody($response);
        return $this->response;
    }

    public function getInvoice($invoiceId, $cancellation)
    {
        $this->disableAutoRender();
        $response = $this->helloCashService->getInvoice($invoiceId, $cancellation);

        $headerA = 'Content-Type';
        $this->response = $this->response->withHeader($headerA, $response->getHeader($headerA));

        $headerB = 'Content-Disposition';
        $this->response = $this->response->withHeader($headerB,
            str_replace('attachment', 'inline', $response->getHeader($headerB))
        );

        $this->response = $this->response->withStringBody($response->getStringBody());
        return $this->response;
    }

}
