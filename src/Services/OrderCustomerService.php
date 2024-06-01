<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Datasource\FactoryLocator;
use Model\Tabel\CustomersTable;
use App\Model\Entity\Customer;

class OrderCustomerService
{

    public function isOrderForDifferentCustomerMode()
    {
        return Router::getRequest()->getSession()->read('OrderIdentity');
    }

    public function isSelfServiceModeByUrl()
    {
        $result = Router::getRequest()->getPath() == '/' . __('route_self_service');
        if (!empty(Router::getRequest()->getQuery('redirect'))) {
            $result |= preg_match('`' . '/' . __('route_self_service') . '`', Router::getRequest()->getQuery('redirect'));
        }
        return $result;
    }

    public function isSelfServiceModeByReferer()
    {
        $result = false;
        $serverParams = Router::getRequest()->getServerParams();
        $requestUriAllowed = [
            '/' . __('route_cart') . '/ajaxAdd/',
            '/' . __('route_cart') . '/ajaxRemove/'
        ];
        if (isset($serverParams['HTTP_REFERER'])) {
            $result = preg_match(
                '`' . preg_quote(Configure::read('App.fullBaseUrl')) . '/' . __('route_self_service') . '`',
                $serverParams['HTTP_REFERER'],
            );
        }
        if (!in_array($serverParams['REQUEST_URI'], $requestUriAllowed)) {
            $result = false;
        }
        return $result;
    }

    public function doSelfServiceUserLogin($selfServiceUserBarCode)
    {
        /*$addressCustomersTable = FactoryLocator::get('Table')->get('AddressCustomers');
        $customers->select($addressCustomersTable);
        $customers = $customers->toArray();
        $i = 0;
        foreach($customers as $customer) {     
    ------------------------------------------*/

 $customerTable = FactoryLocator::get('Table')->get('Customers');

 $conditions = [];
 $active = '1';

 $customers = $customerTable->find('all',
 fields: [
     'system_bar_code' => $customerTable->getBarcodeFieldString(),
 ],
 conditions: [
     'Customers.id_default_group =' => Customer::GROUP_SELF_SERVICE_CUSTOMER,
     'Customers.active =' => $active,
 ],
 order: $customerTable->getCustomerOrderClause('ASC'),
 contain: [
     'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
     ]
 );
 $customers = $customerTable->addCustomersNameForOrderSelect($customers);
 $customers->select($customerTable);
 $customers->select($customerTable->AddressCustomers);
 return $customers->first();

 if (empty($customers)) {
     throw new RecordNotFoundException('customers not found or not active');
 }
 else{
 throw new RecordNotFoundException('customer gefunden');
 }

/*

        $customersTable = FactoryLocator::get('Table')->get('Customers');
        $customerTable->dropManufacturersInNextFind();

        $conditions = [];
        $active = '1';

        $customers = $customerTable->find('all',
        fields: [
            'system_bar_code' => $customerTable->getBarcodeFieldString(),
        ],
        conditions: [
            'Customers.id_default_group =' => Customer::GROUP_SELF_SERVICE_CUSTOMER,
            'Customers.active =' => $active,
        ],
        order: $customerTable->getCustomerOrderClause('ASC'),
        contain: [
            'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ]
        );
        $customers = $customerTable->addCustomersNameForOrderSelect($customers);
        $customers->select($customerTable);
        $customers->select($customerTable->AddressCustomers);
        return $customers->first();

        if (empty($customers)) {
            throw new RecordNotFoundException('customers not found or not active');
        }
*/
/*
        echo $this->Html->link(
            '<i class="fas fa-arrow-right ok"></i>',
            $url,
            [
                'class' => 'btn btn-outline-light',
                'title' => $title,
                'target' => $targetBlank ? '_blank' : '',
                'escape' => false
            ]
        );
       $linkToInvoice = Configure::read('app.htmlHelper')->link(
            __d('admin', 'Print_receipt'),
            $invoiceRoute,
            [
                'class' => 'btn btn-outline-light btn-flash-message',
                'target' => '_blank',
                'escape' => false,
            ],
        );

        $conditions = [];
        if ($active != 'all') {
            $conditions['Customers.active'] = $active;
        }
        $query = $customersTable->find('all',
        conditions: $conditions,
        contain: []);
        $query = $customersTable->addCustomersNameForOrderSelect($query);
        $query->select($customersTable)


        if (empty($customerIds)) {
            throw new \Exception('no customer id passed');
        }

        $customerTable = FactoryLocator::get('Table')->get('Customers');

        $customers = $customerTable->find('all',
            fields: [
                'system_bar_code' => $customerTable->getBarcodeFieldString(),
            ],
            conditions: [
                'Customers.id_customer IN' => $customerIds,
            ],
            order: $customerTable->getCustomerOrderClause('ASC'),
            contain: [
                'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
            ]
        );
        $customers = $customerTable->addCustomersNameForOrderSelect($customers);
        $customers->select($customerTable);
        $customers->select($customerTable->AddressCustomers);
        return $customers;



        $validOrderDetails = $this->getAssociation('ValidOrderDetails');
            $i = 0;
            foreach($customers as $customer) {
                $customers[$i]->   validOrderDetailCount = $validOrderDetails->find('all', conditions: [
                    'id_customer' => $customers[$i]->id_customer
                ])->count();
                $i++;




        $barCode = $credentials[TokenIdentifier::CREDENTIAL_TOKEN] ?? '';
        $ormResolver = [
            'className' => OrmResolver::class,
            'userModel' => 'Customers',
            'finder' => 'auth', // CustomersTable::findAuth
        ];
        $service->loadIdentifier('App.BarCode', [
            'resolver' => $ormResolver,
        ]);*/


        /*$table = $this->getTableLocator()->get($this->_config['resolver']['userModel']);
        $user =  $table->find($this->_config['resolver']['finder'])->where([
            (new QueryExpression())->eq($this->getIdentifierField($table), $barCode),
        ])->first();

        $addressCustomersTable = FactoryLocator::get('Table')->get('AddressCustomers');
        $customers->select($addressCustomersTable);
        $customers = $customers->toArray();
        $i = 0;
        foreach($customers as $customer) {     
    
        $validOrderDetails = $this->getAssociation('ValidOrderDetails');
            $i = 0;
            foreach($customers as $customer) {
                $customers[$i]->validOrderDetailCount = $validOrderDetails->find('all', conditions: [
                    'id_customer' => $customers[$i]->id_customer
                ])->count();
                $i++;

     $customerId = $this->identity->getId();
        $pdfWriter = new MyMemberCardPdfWriterService();
        $customers = $pdfWriter->getMemberCardCustomerData($customerId);
        $pdfWriter->setFilename(__d('admin', 'Member_card') . ' ' . $customers->toArray()[0]->name.'.pdf');
        $pdfWriter->setData([
            'customers' => $customers,
        ]);
        die($pdfWriter->writeInline()); */
    }
}