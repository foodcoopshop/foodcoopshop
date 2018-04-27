<?php

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Mailer\AppEmail;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * ManufacturersController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

class ManufacturersController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->getRequest()->getParam('action')) {
            case 'profile':
            case 'myOptions':
                return $this->AppAuth->isManufacturer();
                break;
            case 'index':
            case 'add':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
                break;
            case 'edit':
            case 'editOptions':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
                break;
            default:
                return $this->AppAuth->user();
                break;
        }
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Manufacturer = TableRegistry::getTableLocator()->get('Manufacturers');
    }

    public function profile()
    {
        $this->edit($this->AppAuth->getManufacturerId());
        $this->set('referer', $this->getRequest()->getUri()->getPath());
        $this->set('title_for_layout', 'Profil bearbeiten');
        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function add()
    {
        $manufacturer = $this->Manufacturer->newEntity(
            ['active' => APP_ON],
            ['validate' => false]
        );
        $this->set('title_for_layout', 'Hersteller erstellen');
        $this->_processForm($manufacturer, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($manufacturerId)
    {
        if ($manufacturerId === null) {
            throw new NotFoundException;
        }

        $_SESSION['KCFINDER'] = [
            'uploadURL' => Configure::read('app.cakeServerName') . "/files/kcfinder/manufacturers/" . $manufacturerId,
            'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/manufacturers/" . $manufacturerId
        ];

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'AddressManufacturers'
            ]
        ])->first();

        if (empty($manufacturer)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', 'Hersteller bearbeiten');
        $this->_processForm($manufacturer, true);
    }

    private function _processForm($manufacturer, $isEditMode)
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('manufacturer', $manufacturer);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData(), ['description', 'short_description'])));

        $this->setRequest($this->getRequest()->withData('Manufacturers.iban', str_replace(' ', '', $this->getRequest()->getData('Manufacturers.iban'))));
        $this->setRequest($this->getRequest()->withData('Manufacturers.bic', str_replace(' ', '', $this->getRequest()->getData('Manufacturers.bic'))));
        $this->setRequest($this->getRequest()->withData('Manufacturers.homepage', StringComponent::addHttpToUrl($this->getRequest()->getData('Manufacturers.homepage'))));

        if ($isEditMode) {
            // keep original data for getCustomerRecord - clone does not work on nested objects
            $unchangedManufacturerAddress = clone $manufacturer->address_manufacturer;
        }
        
        $manufacturer = $this->Manufacturer->patchEntity(
            $manufacturer,
            $this->getRequest()->getData(),
            [
                'associated' => [
                    'AddressManufacturers'
                ]
            ]
        );
        if (!empty($manufacturer->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            $this->set('manufacturer', $manufacturer);
            $this->render('edit');
        } else {
            $manufacturer = $this->Manufacturer->save($manufacturer);

            if (!$isEditMode) {
                $customer = [];
                $messageSuffix = 'erstellt';
                $actionLogType = 'manufacturer_added';
            } else {
                $customer = $this->Manufacturer->getCustomerRecord($unchangedManufacturerAddress->email);
                $messageSuffix = 'geändert';
                $actionLogType = 'manufacturer_changed';
            }

            $this->Customer = TableRegistry::getTableLocator()->get('Customers');
            $customerData = [
                'email' => $this->getRequest()->getData('Manufacturers.address_manufacturer.email'),
                'firstname' => $this->getRequest()->getData('Manufacturers.address_manufacturer.firstname'),
                'lastname' => $this->getRequest()->getData('Manufacturers.address_manufacturer.lastname'),
                'active' => APP_ON
            ];
            if (empty($customer)) {
                $customerEntity = $this->Customer->newEntity($customerData);
            } else {
                $customerEntity = $this->Customer->patchEntity($customer, $customerData);
            }
            $this->Customer->save($customerEntity);

            if (!empty($this->getRequest()->getData('Manufacturers.tmp_image'))) {
                $this->saveUploadedImage($manufacturer->id_manufacturer, $this->getRequest()->getData('Manufacturers.tmp_image'), Configure::read('app.htmlHelper')->getManufacturerThumbsPath(), Configure::read('app.manufacturerImageSizes'));
            }

            if (!empty($this->getRequest()->getData('Manufacturers.delete_image'))) {
                $this->deleteUploadedImage($manufacturer->id_manufacturer, Configure::read('app.htmlHelper')->getManufacturerThumbsPath(), Configure::read('app.manufacturerImageSizes'));
            }

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $message = 'Der Hersteller <b>' . $manufacturer->name . '</b> wurde ' . $messageSuffix . '.';
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $manufacturer->id_manufacturer, 'manufacturers', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $manufacturer->id_manufacturer);

            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getManufacturerProfile()) {
                $this->renewAuthSession();
            }

            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('manufacturer', $manufacturer);
    }

    public function setKcFinderUploadPath($manufacturerId)
    {
        $this->RequestHandler->renderAs($this, 'json');

        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        } else {
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $manufacturerId
                ]
            ])->first();
            $manufacturerId = $manufacturer->id_manufacturer;
        }

        $_SESSION['KCFINDER'] = [
            'uploadURL' => Configure::read('app.cakeServerName') . "/files/kcfinder/manufacturers/" . $manufacturerId,
            'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/manufacturers/" . $manufacturerId
        ];
        $this->set('data', [
            'status' => true,
            'msg' => 'OK'
        ]);
        $this->set('_serialize', 'data');
    }

    public function index()
    {
        $dateFrom = Configure::read('app.timeHelper')->getOrderPeriodFirstDay(Configure::read('app.timeHelper')->getCurrentDay());
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = $this->getRequest()->getQuery('dateFrom');
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getOrderPeriodLastDay(Configure::read('app.timeHelper')->getCurrentDay());
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = $this->getRequest()->getQuery('dateTo');
        }
        $this->set('dateTo', $dateTo);

        $active = 1; // default value
        if (in_array('active', array_keys($this->getRequest()->getQueryParams()))) {
            $active = $this->getRequest()->getQuery('active');
        }
        $this->set('active', $active);

        $conditions = [];
        if ($active != 'all') {
            $conditions = [
                'Manufacturers.active' => $active
            ];
        }

        $query = $this->Manufacturer->find('all', [
            'conditions' => $conditions,
            'fields' => [
                'is_holiday_active' => '!'.$this->Manufacturer->getManufacturerHolidayConditions()
            ],
            'contain' => [
                'AddressManufacturers',
                'Customers'
            ]
        ])
        ->select($this->Manufacturer)
        ->select($this->Manufacturer->Customers)
        ->select($this->Manufacturer->AddressManufacturers);

        $manufacturers = $this->paginate($query, [
            'sortWhitelist' => [
                'Manufacturers.name', 'Manufacturers.iban', 'Manufacturers.active', 'Manufacturers.holiday_from', 'Manufacturers.is_private', 'Customers.' . Configure::read('app.customerMainNamePart'), 'Manufacturers.timebased_currency_enabled'
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ])->toArray();

        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->Payment = TableRegistry::getTableLocator()->get('Payments');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');

        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        }
        
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->product_count = $this->Product->getCountByManufacturerId($manufacturer->id_manufacturer);
            $sumDepositDelivered = $this->OrderDetail->getDepositSum($manufacturer->id_manufacturer, false);
            $sumDepositReturned = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturer->id_manufacturer, false);
            $manufacturer->sum_deposit_delivered = $sumDepositDelivered[0]['sumDepositDelivered'];
            $manufacturer->deposit_credit_balance = $sumDepositDelivered[0]['sumDepositDelivered'] - $sumDepositReturned[0]['sumDepositReturned'];
            if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
                $manufacturer->timebased_currency_credit_balance = $this->TimebasedCurrencyOrderDetail->getCreditBalance($manufacturer->id_manufacturer);
            }
            if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
                $manufacturer->variable_member_fee = $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
            }
            $manufacturer->sum_open_order_detail = $this->OrderDetail->getOpenOrderDetailSum($manufacturer->id_manufacturer, $dateFrom, $dateTo);
        }
        $this->set('manufacturers', $manufacturers);

        $this->set('title_for_layout', 'Hersteller');
    }

    public function sendInvoice($manufacturerId, $from, $to)
    {
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'Invoices',
                'AddressManufacturers'
            ]
        ])->first();

        // generate and save PDF - should be done here because count of results will be checked
        $product_results = $this->prepareInvoiceOrOrderList($manufacturerId, 'product', $from, $to, [
            ORDER_STATE_OPEN,
            ORDER_STATE_CASH,
            ORDER_STATE_CASH_FREE
        ], 'F');

        // no orders in current period => do not send pdf but send information email
        if (count($product_results) == 0) {
            // orders exist => send pdf and email
        } else {
            // generate and save invoice number
            $invoiceNumber = 1; // default
            if (! empty($manufacturer->invoices)) {
                $invoiceNumber = $manufacturer->invoices[0]->invoice_number + 1;
            }
            $newInvoiceNumber = $this->Manufacturer->formatInvoiceNumber($invoiceNumber);
            $this->set('newInvoiceNumber', $newInvoiceNumber);

            $this->RequestHandler->renderAs($this, 'pdf');
            $customer_results = $this->prepareInvoiceOrOrderList($manufacturerId, 'customer', $from, $to, [
                ORDER_STATE_OPEN,
                ORDER_STATE_CASH,
                ORDER_STATE_CASH_FREE
            ], 'F');

            // generate invoice
            $this->render('get_invoice');
            $invoicePdfUrl = Configure::read('app.htmlHelper')->getInvoiceLink($manufacturer->name, $manufacturerId, date('Y-m-d'), $newInvoiceNumber);
            $invoicePdfFile = $invoicePdfUrl;

            $this->Flash->success('Rechnung für Hersteller "' . $manufacturer->name . '" erfolgreich versendet an ' . $manufacturer->address_manufacturer->email . '.</a>');

            $invoice2save = [
                'id_manufacturer' => $manufacturerId,
                'send_date' => Time::now(),
                'invoice_number' => $invoiceNumber,
                'user_id' => $this->AppAuth->getUserId()
            ];
            $this->Manufacturer->Invoices->save(
                $this->Manufacturer->Invoices->newEntity($invoice2save)
            );

            $invoicePeriodMonthAndYear = Configure::read('app.timeHelper')->getLastMonthNameAndYear();

            $sendEmail = $this->Manufacturer->getOptionSendInvoice($manufacturer->send_invoice);
            if ($sendEmail) {
                $email = new AppEmail();
                $email->setTemplate('Admin.send_invoice')
                    ->setTo($manufacturer->address_manufacturer->email)
                    ->setAttachments([
                    $invoicePdfFile
                    ])
                    ->setSubject('Rechnung Nr. ' . $newInvoiceNumber . ', ' . $invoicePeriodMonthAndYear)
                    ->setViewVars([
                    'manufacturer' => $manufacturer,
                    'invoicePeriodMonthAndYear' => $invoicePeriodMonthAndYear,
                    'appAuth' => $this->AppAuth,
                    'showManufacturerUnsubscribeLink' => true
                    ]);

                $email->send();
            }
        }

        $this->redirect($this->referer());
    }

    private function getOptionBulkOrdersAllowed($manufacturerId)
    {
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();
        return $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer->bulk_orders_allowed);
    }

    private function getOptionVariableMemberFee($manufacturerId)
    {
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();
        return $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
    }

    public function sendOrderList($manufacturerId, $from, $to)
    {
        Configure::read('app.timeHelper')->recalcDeliveryDayDelta();

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'AddressManufacturers',
                'Customers.AddressCustomers'
            ]
        ])->first();

        // generate and save PDF - should be done here because count of results will be checked
        $productResults = $this->prepareInvoiceOrOrderList($manufacturerId, 'product', $from, $to, [
            ORDER_STATE_OPEN
        ], 'F');

        // no orders in current period => do not send pdf but send information email
        if (count($productResults) == 0) {
            // orders exist => send pdf and email
        } else {
            $this->RequestHandler->renderAs($this, 'pdf');

            // generate order list by procuct
            $this->render('get_order_list_by_product');
            $productPdfFile = Configure::read('app.htmlHelper')->getOrderListLink($manufacturer->name, $manufacturerId, date('Y-m-d', strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day')), 'Produkt');

            // generate order list by customer
            $customerResults = $this->prepareInvoiceOrOrderList($manufacturerId, 'customer', $from, $to, [
                ORDER_STATE_OPEN
            ], 'F');
            $this->render('get_order_list_by_customer');
            $customerPdfFile = Configure::read('app.htmlHelper')->getOrderListLink($manufacturer->name, $manufacturerId, date('Y-m-d', strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day')), 'Mitglied');

            $sendEmail = $this->Manufacturer->getOptionSendOrderList($manufacturer->send_order_list);
            $ccRecipients = $this->Manufacturer->getOptionSendOrderListCc($manufacturer->send_order_list_cc);

            $flashMessage = 'Bestelllisten für Hersteller "' . $manufacturer->name . '" erfolgreich generiert';

            if ($sendEmail) {
                $flashMessage .= ' und an ' . $manufacturer->address_manufacturer->email . ' versendet';
                $email = new AppEmail();
                $email->setTemplate('Admin.send_order_list')
                ->setTo($manufacturer->address_manufacturer->email)
                ->setAttachments([
                    $productPdfFile,
                    $customerPdfFile
                ])
                ->setSubject('Bestellungen für den ' . date('d.m.Y', strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day')))
                ->setViewVars([
                'manufacturer' => $manufacturer,
                'appAuth' => $this->AppAuth,
                'showManufacturerUnsubscribeLink' => true
                ]);
                if (!empty($ccRecipients)) {
                    $email->setCc($ccRecipients);
                }
                $email->send();
            }
        }

        $flashMessage .= '.';
        $this->Flash->success($flashMessage);
        $this->redirect($this->referer());
    }

    public function myOptions()
    {
        $this->editOptions($this->AppAuth->getManufacturerId());
        $this->set('referer', $this->getRequest()->getUri()->getPath());
        $this->set('title_for_layout', 'Einstellungen bearbeiten');
        if (empty($this->getRequest()->getData())) {
            $this->render('editOptions');
        }
    }

    public function editOptions($manufacturerId)
    {
        if ($manufacturerId === null) {
            throw new NotFoundException;
        }

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();

        if (empty($manufacturer)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', $manufacturer->name . ': Einstellungen bearbeiten');

        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());

        // set default data if manufacturer options are null
        if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && is_null($manufacturer->variable_member_fee)) {
            $manufacturer->variable_member_fee = Configure::read('appDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE');
        }
        if (is_null($manufacturer->send_order_list)) {
            $manufacturer->send_order_list = Configure::read('app.defaultSendOrderList');
        }
        if (is_null($manufacturer->send_invoice)) {
            $manufacturer->send_invoice = Configure::read('app.defaultSendInvoice');
        }
        if (is_null($manufacturer->default_tax_id)) {
            $manufacturer->default_tax_id = Configure::read('app.defaultTaxId');
        }
        if (!$this->AppAuth->isManufacturer() && is_null($manufacturer->bulk_orders_allowed)) {
            $manufacturer->bulk_orders_allowed = Configure::read('app.defaultBulkOrdersAllowed');
        }
        if (is_null($manufacturer->send_shop_order_notification)) {
            $manufacturer->send_shop_order_notification = Configure::read('app.defaultSendShopOrderNotification');
        }
        if (is_null($manufacturer->send_ordered_product_deleted_notification)) {
            $manufacturer->send_ordered_product_deleted_notification = Configure::read('app.defaultSendOrderedProductDeletedNotification');
        }
        if (is_null($manufacturer->send_ordered_product_price_changed_notification)) {
            $manufacturer->send_ordered_product_price_changed_notification = Configure::read('app.defaultSendOrderedProductPriceChangedNotification');
        }
        if (is_null($manufacturer->send_ordered_product_quantity_changed_notification)) {
            $manufacturer->send_ordered_product_quantity_changed_notification = Configure::read('app.defaultSendOrderedProductQuantityChangedNotification');
        }
        
        $manufacturer->timebased_currency_max_credit_balance /= 3600;
        
        if (!$this->AppAuth->isManufacturer()) {
            $this->Customer = TableRegistry::getTableLocator()->get('Customers');
            $this->set('customersForDropdown', $this->Customer->getForDropdown());
        }

        $this->setFormReferer();

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) {
            $this->SyncDomain = TableRegistry::getTableLocator()->get('Network.SyncDomains');
            $this->helpers[] = 'Network.Network';
            $this->set('syncDomainsForDropdown', $this->SyncDomain->getForDropdown());
            $isAllowedEditManufacturerOptionsDropdown = $this->SyncDomain->isAllowedEditManufacturerOptionsDropdown($this->AppAuth);
            $this->set('isAllowedEditManufacturerOptionsDropdown', $isAllowedEditManufacturerOptionsDropdown);
        }

        if (empty($this->getRequest()->getData())) {
            $this->set('manufacturer', $manufacturer);
            return;
        }

        if (!empty($this->getRequest()->getData('Manufacturers.holiday_from'))) {
            $this->setRequest($this->getRequest()->withData('Manufacturers.holiday_from', new Time($this->getRequest()->getData('Manufacturers.holiday_from'))));
            
        }
        if (!empty($this->getRequest()->getData('Manufacturers.holiday_to'))) {
            $this->setRequest($this->getRequest()->withData('Manufacturers.holiday_to', new Time($this->getRequest()->getData('Manufacturers.holiday_to'))));
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData())));

        $manufacturer = $this->Manufacturer->patchEntity(
            $manufacturer,
            $this->getRequest()->getData(),
            [
                'validate' => 'editOptions'
            ]
        );
        if (!empty($this->getRequest()->getData('Manufacturers.timebased_currency_max_credit_balance'))) {
            $this->setRequest($this->getRequest()->withData('Manufacturers.timebased_currency_max_credit_balance', $this->getRequest()->getData('Manufacturers.timebased_currency_max_credit_balance') * 3600));
        }
        
        if (!empty($manufacturer->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            if (!empty($this->getRequest()->getData('Manufacturers.timebased_currency_max_credit_balance'))) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.timebased_currency_max_credit_balance', $this->getRequest()->getData('Manufacturers.timebased_currency_max_credit_balance') / 3600));
            }
            $this->set('manufacturer', $manufacturer);
            $this->render('edit_options');
        } else {
            // values that are the same as default values => null
            if (!$this->AppAuth->isManufacturer()) {
                // only admins and superadmins are allowed to change variable_member_fee
                if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && $this->getRequest()->getData('Manufacturers.variable_member_fee') == Configure::read('appDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE')) {
                    $this->setRequest($this->getRequest()->withData('Manufacturers.variable_member_fee', null));
                }
            }
            if ($this->getRequest()->getData('Manufacturers.default_tax_id') == Configure::read('app.defaultTaxId')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.default_tax_id', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_order_list') == Configure::read('app.defaultSendOrderList')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_order_list', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_invoice') == Configure::read('app.defaultSendInvoice')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_invoice', null));
            }
            if (!$this->AppAuth->isManufacturer() && $this->getRequest()->getData('Manufacturers.bulk_orders_allowed') == Configure::read('app.defaultBulkOrdersAllowed')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.bulk_orders_allowed', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_shop_order_notification') == Configure::read('app.defaultSendShopOrderNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_shop_order_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_deleted_notification') == Configure::read('app.defaultSendOrderedProductDeletedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_deleted_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_price_changed_notification') == Configure::read('app.defaultSendOrderedProductPriceChangedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_price_changed_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_quantity_changed_notification') == Configure::read('app.defaultSendOrderedProductQuantityChangedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_quantity_changed_notification', null));
            }

            if (isset($isAllowedEditManufacturerOptionsDropdown) && $isAllowedEditManufacturerOptionsDropdown) {
                if ($this->getRequest()->getData('Manufacturers.enabled_sync_domains')) {
                    $this->setRequest($this->getRequest()->withData('Manufacturers.enabled_sync_domains', implode(',', $this->getRequest()->getData('Manufacturers.enabled_sync_domains'))));
                }
            }

            // remove post data that could be set by hacking attempt
            if ($this->AppAuth->isManufacturer()) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.bulk_orders_allowed', null));
                $this->setRequest($this->getRequest()->withData('Manufacturers.variable_member_fee', null));
                $this->setRequest($this->getRequest()->withData('Manufacturers.id_customer', null));
            }

            // html could be manipulated and checkbox disabled attribute removed
            if ($this->AppAuth->isManufacturer()) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.active', null));
            }

            // sic! patch again!
            $manufacturer = $this->Manufacturer->patchEntity(
                $manufacturer,
                $this->getRequest()->getData()
            );
            $manufacturer = $this->Manufacturer->save($manufacturer);

            $this->getRequest()->getSession()->write('highlightedRowId', $manufacturer->id_manufacturer);

            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getManufacturerProfile()) {
                $this->renewAuthSession();
            }

            $message = 'Die Einstellungen des Herstellers <b>' . $manufacturer->name . '</b>';
            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getManufacturerMyOptions()) {
                $message = 'Deine Einstellungen';
                $this->renewAuthSession();
            }
            $message .= ' wurden erfolgreich gespeichert.';

            $this->Flash->success($message);

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('manufacturer_options_changed', $this->AppAuth->getUserId(), $manufacturer->id_manufacturer, 'manufacturers', $message);

            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('manufacturer', $manufacturer);
    }

    private function prepareInvoiceOrOrderList($manufacturerId, $groupType, $from, $to, $orderState, $saveParam = 'I')
    {
        $results = $this->Manufacturer->getDataForInvoiceOrOrderList($manufacturerId, $groupType, $from, $to, $orderState);
        if (empty($results)) {
            // do not throw exception because no debug mails wanted
            die('Keine Bestellungen im angegebenen Zeitraum vorhanden.');
        }
        
        $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        $results = $this->TimebasedCurrencyOrderDetail->addTimebasedCurrencyDataToInvoiceData($results);
        
        $this->set('results_' . $groupType, $results);
        $this->set('manufacturerId', $manufacturerId);
        $this->set('from', date('d.m.Y', strtotime(str_replace('/', '-', $from))));
        $this->set('to', date('d.m.Y', strtotime(str_replace('/', '-', $to))));

        // only needed for order lists: format is english because it is used for filename => sorting!
        $this->set('deliveryDay', date('Y-m-d', strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day')));

        // calculate sum of price
        $sumPriceIncl = 0;
        $sumPriceExcl = 0;
        $sumTax = 0;
        $sumAmount = 0;
        $sumTimebasedCurrencyPriceIncl = 0;
        foreach ($results as $result) {
            $sumPriceIncl += $result['OrderDetailPriceIncl'];
            $sumPriceExcl += $result['OrderDetailPriceExcl'];
            $sumTax += $result['OrderDetailTaxAmount'];
            $sumAmount += $result['OrderDetailQuantity'];
            if (isset($result['OrderDetailTimebasedCurrencyPriceInclAmount'])) {
                $sumTimebasedCurrencyPriceIncl += $result['OrderDetailTimebasedCurrencyPriceInclAmount'];
            }
        }
        $this->set('sumPriceExcl', $sumPriceExcl);
        $this->set('sumTax', $sumTax);
        $this->set('sumPriceIncl', $sumPriceIncl);
        $this->set('sumAmount', $sumAmount);
        $this->set('sumTimebasedCurrencyPriceIncl', $sumTimebasedCurrencyPriceIncl);
        
        $this->set('variableMemberFee', $this->getOptionVariableMemberFee($manufacturerId));
        $this->set('bulkOrdersAllowed', $this->getOptionBulkOrdersAllowed($manufacturerId));

        $this->set('saveParam', $saveParam);
        return $results;
    }

    public function getInvoice($manufacturerId, $from, $to)
    {
        $results = $this->prepareInvoiceOrOrderList($manufacturerId, 'customer', $from, $to, [
            ORDER_STATE_OPEN,
            ORDER_STATE_CASH,
            ORDER_STATE_CASH_FREE
        ]);
        if (empty($results)) {
            // do not throw exception because no debug mails wanted
            die('Keine Bestellungen im angegebenen Zeitraum vorhanden.');
        }
        $this->prepareInvoiceOrOrderList($manufacturerId, 'product', $from, $to, [
            ORDER_STATE_OPEN,
            ORDER_STATE_CASH,
            ORDER_STATE_CASH_FREE
        ]);
    }

    public function getOrderListByProduct($manufacturerId, $from, $to)
    {
        $orderStates = $this->getAllowedOrderStates($manufacturerId);
        $this->prepareInvoiceOrOrderList($manufacturerId, 'product', $from, $to, $orderStates);
    }

    public function getOrderListByCustomer($manufacturerId, $from, $to)
    {
        $orderStates = $this->getAllowedOrderStates($manufacturerId);
        $this->prepareInvoiceOrOrderList($manufacturerId, 'customer', $from, $to, $orderStates);
    }

    /**
     * if bulk orders are allowed for manufacturer, also show closed orders in order list
     * ONLY implemented for getOrderList, not for sendOrderList!
     *
     * @param int $manufacturerId
     * @return array
     */
    public function getAllowedOrderStates($manufacturerId)
    {
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();

        $this->set('manufacturer', $manufacturer);

        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer->bulk_orders_allowed);
        if ($bulkOrdersAllowed) {
            $orderStates = Configure::read('app.htmlHelper')->getOrderStateIds();
        } else {
            $orderStates = [
                ORDER_STATE_OPEN
            ];
        }

        return $orderStates;
    }
}