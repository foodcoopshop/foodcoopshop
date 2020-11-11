<?php

namespace Admin\Controller;

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class InvoicesController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') && $this->AppAuth->isSuperadmin();
    }

    public function index()
    {

        $dateFrom = Configure::read('app.timeHelper')->getFirstDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getLastDayOfThisYear();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
        }
        $this->set('customerId', $customerId);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->Invoice = $this->getTableLocator()->get('Invoices');
        $invoices = $this->Invoice->find('all', [
            'contain' => [
                'InvoiceTaxes',
                'Customers',
            ],
            'conditions' => [
                'Invoices.id_customer > 0',
            ],
            'order' => [
                'Invoices.id' => 'DESC'
            ]
        ])->toArray();
        $this->set('invoices', $invoices);

        $this->set('customersForDropdown', $this->Customer->getForDropdown());
        $this->set('title_for_layout', __d('admin', 'Invoices'));

    }

    public function getInvoice()
    {
        $filenameWithPath = Configure::read('app.folder_invoices') . DS . h($this->getRequest()->getQuery('file'));
        return $this->getFile($filenameWithPath);
    }

    /**
     * invoices and order lists are not stored in webroot
     */
    private function getFile($filenameWithPath)
    {

        $this->disableAutoRender();

        $filenameWithPath = str_replace(DS.DS, '/', $filenameWithPath);
        $filenameWithPath = str_replace(DS, '/', $filenameWithPath);
        $explodedString = explode('/', $filenameWithPath);

        $filenameWithoutPath = $explodedString[count($explodedString) - 1 ];

        $this->response = $this->response->withType('pdf');
        $this->response = $this->response->withFile(
            $filenameWithPath,
            );
        $this->response = $this->response->withHeader('Content-Disposition', 'inline; filename="' . $filenameWithoutPath . '"');

        return $this->response;
    }
}
