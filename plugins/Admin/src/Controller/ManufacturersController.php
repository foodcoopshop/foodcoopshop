<?php

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
        $this->set('title_for_layout', __d('admin', 'Edit_profile'));
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
        $this->set('title_for_layout', __d('admin', 'Add_manufacturer'));
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

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('app.cakeServerName') . "/files/kcfinder/manufacturers/" . $manufacturerId,
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/manufacturers/" . $manufacturerId
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
        $this->set('title_for_layout', __d('admin', 'Edit_manufacturer'));
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
        if ($manufacturer->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('manufacturer', $manufacturer);
            $this->render('edit');
        } else {
            $manufacturer = $this->Manufacturer->save($manufacturer);

            if (!$isEditMode) {
                $customer = [];
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'manufacturer_added';
            } else {
                $customer = $this->Manufacturer->getCustomerRecord($unchangedManufacturerAddress->email);
                $messageSuffix = __d('admin', 'changed');
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

            if (!empty($this->getRequest()->getData('Manufacturers.tmp_general_terms_and_conditions'))) {
                $this->saveUploadedGeneralTermsAndConditions($manufacturer->id_manufacturer, $this->getRequest()->getData('Manufacturers.tmp_general_terms_and_conditions'));
            }
            
            if (!empty($this->getRequest()->getData('Manufacturers.delete_general_terms_and_conditions'))) {
                $this->deleteUploadedGeneralTermsAndConditions($manufacturer->id_manufacturer);
            }
            
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $message = __d('admin', 'The_manufacturer_{0}_has_been_{1}.', ['<b>' . $manufacturer->name . '</b>', $messageSuffix]);
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
    
    private function saveUploadedGeneralTermsAndConditions($manufacturerId, $filename)
    {
        
        $newFileName = Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrcTemplate($manufacturerId);
        
        $fileObject = new File(WWW_ROOT . $filename);
        
        // assure that folder structure exists
        $dir = new Folder();
        $path = dirname(WWW_ROOT . $newFileName);
        $dir->create($path);
        $dir->chmod($path, 0755);
        
        $fileObject->copy(WWW_ROOT . $newFileName);
    }
    
    private function deleteUploadedGeneralTermsAndConditions($manufacturerId)
    {
        $fileName = Configure::read('app.htmlHelper')->getManufacturerTermsOfUseSrcTemplate($manufacturerId);
        if (file_exists(WWW_ROOT . $fileName)) {
            unlink(WWW_ROOT . $fileName);
        }
    }

    public function setElFinderUploadPath($manufacturerId)
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

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('app.cakeServerName') . "/files/kcfinder/manufacturers/" . $manufacturerId,
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/manufacturers/" . $manufacturerId
        ];
        $this->set('data', [
            'status' => true,
            'msg' => 'OK'
        ]);
        $this->set('_serialize', 'data');
    }

    public function index()
    {
        $dateFrom = Configure::read('app.timeHelper')->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = $this->getRequest()->getQuery('dateFrom');
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('app.timeHelper')->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
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
                'Manufacturers.name', 'Manufacturers.stock_management_enabled', 'Manufacturers.no_delivery_days', 'Manufacturers.is_private', 'Customers.' . Configure::read('app.customerMainNamePart'), 'Manufacturers.timebased_currency_enabled'
            ],
            'order' => [
                'Manufacturers.name' => 'ASC'
            ]
        ])->toArray();
        
        // extract all email addresses for button
        $emailAddresses = [];
        $emailAddresses = $query->all()->extract('address_manufacturer.email')->toArray();
        $emailAddresses = array_unique($emailAddresses);
        $this->set('emailAddresses', $emailAddresses);

        $this->Product = TableRegistry::getTableLocator()->get('Products');
        $this->Payment = TableRegistry::getTableLocator()->get('Payments');
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');

        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
            $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        }

        foreach ($manufacturers as $manufacturer) {
            $manufacturer->product_count = $this->Manufacturer->getProductsByManufacturerId($manufacturer->id_manufacturer, true);
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
            $manufacturer->sum_open_order_detail = $this->OrderDetail->getOpenOrderDetailSum($manufacturer->id_manufacturer, $dateFrom);
        }
        $this->set('manufacturers', $manufacturers);

        $this->set('title_for_layout', __d('admin', 'Manufacturers'));
    }

    public function sendInvoice()
    {

        $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        $dateFrom = $this->getRequest()->getQuery('dateFrom');
        $dateTo = $this->getRequest()->getQuery('dateTo');

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'Invoices',
                'AddressManufacturers'
            ]
        ])->first();

        $validOrderStates = [
            ORDER_STATE_ORDER_PLACED,
            ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER
        ];
        
        // generate and save PDF - should be done here because count of results will be checked
        $product_results = $this->prepareInvoiceOrOrderList($manufacturerId, 'product', $dateFrom, $dateTo, $validOrderStates, 'F');

        // no orders in current period => do not send pdf but send information email
        if (count($product_results) == 0) {
            // orders exist => send pdf and email
        } else {
            // generate and save invoice number
            $newInvoiceNumber = $this->Manufacturer->Invoices->getNextInvoiceNumber($manufacturer->invoices);
            $this->set('newInvoiceNumber', $newInvoiceNumber);

            $this->RequestHandler->renderAs($this, 'pdf');
            $customer_results = $this->prepareInvoiceOrOrderList($manufacturerId, 'customer', $dateFrom, $dateTo, $validOrderStates, 'F');

            // generate invoice
            $this->render('get_invoice');
            $invoicePdfUrl = Configure::read('app.htmlHelper')->getInvoiceLink($manufacturer->name, $manufacturerId, date('Y-m-d'), $newInvoiceNumber);
            $invoicePdfFile = $invoicePdfUrl;

            $this->Flash->success(__d('admin', 'Invoice_for_manufacturer_{0}_successfully_sent_to_{1}.', ['<b>' . $manufacturer->name . '</b>', $manufacturer->address_manufacturer->email]));

            $invoice2save = [
                'id_manufacturer' => $manufacturerId,
                'send_date' => Time::now(),
                'invoice_number' => (int) $newInvoiceNumber,
                'user_id' => $this->AppAuth->getUserId()
            ];
            $this->Manufacturer->Invoices->save(
                $this->Manufacturer->Invoices->newEntity($invoice2save)
            );

            $invoicePeriodMonthAndYear = Configure::read('app.timeHelper')->getLastMonthNameAndYear();
            
            $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
            $this->OrderDetail->updateOrderState($dateFrom, $dateTo, $validOrderStates, Configure::read('app.htmlHelper')->getOrderStateBilled(), $manufacturerId);

            $sendEmail = $this->Manufacturer->getOptionSendInvoice($manufacturer->send_invoice);
            if ($sendEmail) {
                $email = new AppEmail();
                $email->viewBuilder()->setTemplate('Admin.send_invoice');
                $email->setTo($manufacturer->address_manufacturer->email)
                    ->setAttachments([
                        $invoicePdfFile
                    ])
                    ->setSubject(__d('admin', 'Invoice_number_abbreviataion_{0}_{1}', [$newInvoiceNumber, $invoicePeriodMonthAndYear]))
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

    public function sendOrderList()
    {

        $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        $pickupDay = $this->getRequest()->getQuery('pickupDay');
        $cronjobRunDay = $this->getRequest()->getQuery('cronjobRunDay');
        $pickupDayDbFormat = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'AddressManufacturers',
                'Customers.AddressCustomers'
            ]
        ])->first();

        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getOrderDetailsForSendingOrderLists($pickupDayDbFormat, $cronjobRunDay);
        $orderDetails->where(['Products.id_manufacturer' => $manufacturerId]);
        $orderDetailIds = $orderDetails->all()->extract('id_order_detail')->toArray();
        
        if (empty($orderDetailIds)) {
            // do not throw exception because no debug mails wanted
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }
        
        // override pickupDay necessary for order details with products that have an individual date as delivery rhythm
        $pickupDayDbFormat = $orderDetails->first()->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
        $pickupDay = $orderDetails->first()->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateLong2'));
        
        $validOrderStates = [ORDER_STATE_ORDER_PLACED];
        
        // generate and save PDF - should be done here because count of results will be checked
        $productResults = $this->prepareInvoiceOrOrderList($manufacturerId, 'product', $pickupDayDbFormat, null, [], 'F', $orderDetailIds);

        // no orders in current period => do not send pdf but send information email
        if (count($productResults) == 0) {
            // orders exist => send pdf and email
        } else {
            $this->RequestHandler->renderAs($this, 'pdf');

            // generate order list by procuct
            $this->render('get_order_list_by_product');
            $productPdfFile = Configure::read('app.htmlHelper')->getOrderListLink($manufacturer->name, $manufacturerId, $pickupDayDbFormat, __d('admin', 'product'));

            // generate order list by customer
            $customerResults = $this->prepareInvoiceOrOrderList($manufacturerId, 'customer', $pickupDayDbFormat, null, [], 'F', $orderDetailIds);
            $this->render('get_order_list_by_customer');
            $customerPdfFile = Configure::read('app.htmlHelper')->getOrderListLink($manufacturer->name, $manufacturerId, $pickupDayDbFormat, __d('admin', 'member'));

            $sendEmail = $this->Manufacturer->getOptionSendOrderList($manufacturer->send_order_list);
            $ccRecipients = $this->Manufacturer->getOptionSendOrderListCc($manufacturer->send_order_list_cc);

            $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
            $orderDetailIds = Hash::extract($customerResults, '{n}.OrderDetailId');
            $this->OrderDetail->updateOrderState(null, null, $validOrderStates, ORDER_STATE_ORDER_LIST_SENT_TO_MANUFACTURER, $manufacturerId, $orderDetailIds);
            
            $flashMessage = __d('admin', 'Order_lists_successfully_generated_for_manufacturer_{0}.', ['<b>'.$manufacturer->name.'</b>']);

            if ($sendEmail) {
                $flashMessage = __d('admin', 'Order_lists_successfully_generated_for_manufacturer_{0}_and_sent_to_{1}.', ['<b>'.$manufacturer->name.'</b>', $manufacturer->address_manufacturer->email]);
                $email = new AppEmail();
                $email->viewBuilder()->setTemplate('Admin.send_order_list');
                $email->setTo($manufacturer->address_manufacturer->email)
                ->setAttachments([
                    $productPdfFile,
                    $customerPdfFile
                ])
                ->setSubject(__d('admin', 'Order_lists_for_the_day') . ' ' . $pickupDay)
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
        $this->set('title_for_layout', __d('admin', 'Edit_settings'));
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
        $this->set('title_for_layout', $manufacturer->name . ': ' . __d('admin', 'Edit_settings'));

        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());
        
        $noDeliveryBreakOptions = Configure::read('app.timeHelper')->getNextDeliveryDays();
        $this->set('noDeliveryBreakOptions', $noDeliveryBreakOptions);

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
        if (is_null($manufacturer->send_instant_order_notification)) {
            $manufacturer->send_instant_order_notification = Configure::read('app.defaultSendInstantOrderNotification');
        }
        if (is_null($manufacturer->send_ordered_product_deleted_notification)) {
            $manufacturer->send_ordered_product_deleted_notification = Configure::read('app.defaultSendOrderedProductDeletedNotification');
        }
        if (is_null($manufacturer->send_ordered_product_price_changed_notification)) {
            $manufacturer->send_ordered_product_price_changed_notification = Configure::read('app.defaultSendOrderedProductPriceChangedNotification');
        }
        if (is_null($manufacturer->send_ordered_product_amount_changed_notification)) {
            $manufacturer->send_ordered_product_amount_changed_notification = Configure::read('app.defaultSendOrderedProductAmountChangedNotification');
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

        // if checkbox is disabled, false is returned even if checkbox is active
        // as i could not find out how to unset a specific request data index, override with value from database
        if ($this->AppAuth->isManufacturer()) {
            $this->setRequest($this->getRequest()->withData('Manufacturers.active', $manufacturer->active));
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

        if ($manufacturer->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
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
            if ($this->getRequest()->getData('Manufacturers.send_instant_order_notification') == Configure::read('app.defaultSendInstantOrderNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_instant_order_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_deleted_notification') == Configure::read('app.defaultSendOrderedProductDeletedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_deleted_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_price_changed_notification') == Configure::read('app.defaultSendOrderedProductPriceChangedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_price_changed_notification', null));
            }
            if ($this->getRequest()->getData('Manufacturers.send_ordered_product_amount_changed_notification') == Configure::read('app.defaultSendOrderedProductAmountChangedNotification')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.send_ordered_product_amount_changed_notification', null));
            }

            if (isset($isAllowedEditManufacturerOptionsDropdown) && $isAllowedEditManufacturerOptionsDropdown) {
                if ($this->getRequest()->getData('Manufacturers.enabled_sync_domains')) {
                    $this->setRequest($this->getRequest()->withData('Manufacturers.enabled_sync_domains', implode(',', $this->getRequest()->getData('Manufacturers.enabled_sync_domains'))));
                }
            }
            
            if ($this->getRequest()->getData('Manufacturers.no_delivery_days')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.no_delivery_days', implode(',', $this->getRequest()->getData('Manufacturers.no_delivery_days'))));
            }
            
            // remove post data that could be set by hacking attempt
            if ($this->AppAuth->isManufacturer()) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.bulk_orders_allowed', null));
                $this->setRequest($this->getRequest()->withData('Manufacturers.variable_member_fee', null));
                $this->setRequest($this->getRequest()->withData('Manufacturers.id_customer', null));
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

            $message = __d('admin', 'The_settings_of_manufacturer_{0}_have_been_changed.', ['<b>' . $manufacturer->name . '</b>']);
            if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getManufacturerMyOptions()) {
                $message = __d('admin', 'Your_settings_have_been_changed.');
                $this->renewAuthSession();
            }

            $this->Flash->success($message);

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('manufacturer_options_changed', $this->AppAuth->getUserId(), $manufacturer->id_manufacturer, 'manufacturers', $message);

            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('manufacturer', $manufacturer);
    }

    private function prepareInvoiceOrOrderList($manufacturerId, $groupType, $dateFrom, $dateTo, $orderState, $saveParam = 'I', $orderDetailIds = [])
    {
        $results = $this->Manufacturer->getDataForInvoiceOrOrderList($manufacturerId, $groupType, $dateFrom, $dateTo, $orderState, Configure::read('appDb.FCS_INCLUDE_STOCK_PRODUCTS_IN_INVOICES'), $orderDetailIds);
        if (empty($results)) {
            // do not throw exception because no debug mails wanted
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }

        $this->TimebasedCurrencyOrderDetail = TableRegistry::getTableLocator()->get('TimebasedCurrencyOrderDetails');
        $results = $this->TimebasedCurrencyOrderDetail->addTimebasedCurrencyDataToInvoiceData($results);

        $this->set('results_' . $groupType, $results);
        $this->set('manufacturerId', $manufacturerId);
        $this->set('dateFrom', date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(str_replace('/', '-', $dateFrom))));
        $this->set('dateTo', date(Configure::read('app.timeHelper')->getI18Format('DateShortAlt'), strtotime(str_replace('/', '-', $dateTo))));

        // only needed for order lists: format is english because it is used for filename => sorting!
        $this->set('deliveryDay', date('Y-m-d', strtotime('+' . Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') . ' day')));

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
            $sumAmount += $result['OrderDetailAmount'];
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

    public function getInvoice()
    {
        $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        $dateFrom = $this->getRequest()->getQuery('dateFrom');
        $dateTo = $this->getRequest()->getQuery('dateTo');

        $results = $this->prepareInvoiceOrOrderList($manufacturerId, 'customer', $dateFrom, $dateTo, []);
        if (empty($results)) {
            // do not throw exception because no debug mails wanted
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }
        $this->prepareInvoiceOrOrderList($manufacturerId, 'product', $dateFrom, $dateTo, [], 'I', []);
    }

    public function getOrderListByProduct()
    {
        $this->getOrderList('product');
    }

    public function getOrderListByCustomer()
    {
        $this->getOrderList('customer');
    }
    
    private function getOrderList($type)
    {
        $manufacturerId = $this->getRequest()->getQuery('manufacturerId');
        $pickupDay = $this->getRequest()->getQuery('pickupDay');
        $pickupDayDbFormat = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);
        
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getOrderDetailsForOrderListPreview($pickupDayDbFormat);
        $orderDetails->where(['Products.id_manufacturer' => $manufacturerId]);
        $orderDetailIds = $orderDetails->all()->extract('id_order_detail')->toArray();
        
        if (empty($orderDetailIds)) {
            // do not throw exception because no debug mails wanted
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }
        
        $this->prepareInvoiceOrOrderList($manufacturerId, $type, $pickupDay, null, [], 'I', $orderDetailIds);
        
    }

}