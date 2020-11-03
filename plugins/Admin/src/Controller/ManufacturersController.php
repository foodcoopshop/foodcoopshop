<?php

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Lib\PdfWriter\InvoiceToManufacturerPdfWriter;
use App\Lib\PdfWriter\OrderListByProductPdfWriter;
use App\Lib\PdfWriter\OrderListByCustomerPdfWriter;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Http\Exception\NotFoundException;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
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
            [
                'active' => APP_ON,
                'is_private' => APP_ON
            ],
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
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['description', 'short_description'])));

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

            $this->Customer = $this->getTableLocator()->get('Customers');
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

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
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

        $this->set([
            'status' => true,
            'msg' => 'OK',
        ]);
        $this->viewBuilder()->setOption('serialize', ['status', 'msg']);

    }

    private function getDefaultDate() {
        $defaultDate = '';
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $defaultDate = Configure::read('app.timeHelper')->formatToDateShort(Configure::read('app.timeHelper')->getCurrentDateForDatabase());
        } else {
            $defaultDate = Configure::read('app.timeHelper')->getFormattedNextDeliveryDay(Configure::read('app.timeHelper')->getCurrentDay());
        }
        return $defaultDate;
    }

    public function index()
    {

        $dateFrom = $this->getDefaultDate();
        if (! empty($this->getRequest()->getQuery('dateFrom'))) {
            $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = $this->getDefaultDate();
        if (! empty($this->getRequest()->getQuery('dateTo'))) {
            $dateTo = h($this->getRequest()->getQuery('dateTo'));
        }
        $this->set('dateTo', $dateTo);

        $active = 1; // default value
        if (in_array('active', array_keys($this->getRequest()->getQueryParams()))) {
            $active = h($this->getRequest()->getQuery('active'));
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
            'sortableFields' => [
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

        $this->Product = $this->getTableLocator()->get('Products');
        $this->Payment = $this->getTableLocator()->get('Payments');
        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');

        if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
            $this->TimebasedCurrencyOrderDetail = $this->getTableLocator()->get('TimebasedCurrencyOrderDetails');
        }

        foreach ($manufacturers as $manufacturer) {
            $manufacturer->product_count = $this->Manufacturer->getProductsByManufacturerId($this->AppAuth, $manufacturer->id_manufacturer, true);
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

    private function getOptionVariableMemberFee($manufacturerId)
    {
        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ]
        ])->first();
        return $this->Manufacturer->getOptionVariableMemberFee($manufacturer->variable_member_fee);
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

        $this->Tax = $this->getTableLocator()->get('Taxes');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());

        if (!Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            $noDeliveryBreakOptions = Configure::read('app.timeHelper')->getNextWeeklyDeliveryDays();
            $this->set('noDeliveryBreakOptions', $noDeliveryBreakOptions);
        }

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
            $this->Customer = $this->getTableLocator()->get('Customers');
        }

        $this->setFormReferer();

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) {
            $this->SyncDomain = $this->getTableLocator()->get('Network.SyncDomains');
            $this->viewBuilder()->setHelpers(['Network.Network']);
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
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

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

            if (!Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY') && $this->getRequest()->getData('Manufacturers.no_delivery_days')) {
                $this->setRequest($this->getRequest()->withData('Manufacturers.no_delivery_days', implode(',', $this->getRequest()->getData('Manufacturers.no_delivery_days'))));
            }

            // remove post data that could be set by hacking attempt
            if ($this->AppAuth->isManufacturer()) {
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

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->ActionLog->customSave('manufacturer_options_changed', $this->AppAuth->getUserId(), $manufacturer->id_manufacturer, 'manufacturers', $message);

            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('manufacturer', $manufacturer);
    }

    public function getInvoice()
    {
        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        $dateFrom = h($this->getRequest()->getQuery('dateFrom'));
        $dateTo = h($this->getRequest()->getQuery('dateTo'));

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
        ])->first();

        $newInvoiceNumber = 'xxx';

        $pdfWriter = new InvoiceToManufacturerPdfWriter();
        $pdfWriter->prepareAndSetData($manufacturerId, $dateFrom, $dateTo, $newInvoiceNumber, [], '', 'xxx');
        if (isset($pdfWriter->getData()['productResults']) && empty($pdfWriter->getData()['productResults'])) {
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }

        if (!empty($this->request->getQuery('outputType')) && $this->request->getQuery('outputType') == 'html') {
            return $this->response->withStringBody($pdfWriter->writeHtml());
        }

        $invoicePdfFile = Configure::read('app.htmlHelper')->getInvoiceLink($manufacturer->name, $manufacturerId, date('Y-m-d'), $newInvoiceNumber);
        $invoicePdfFile = explode(DS, $invoicePdfFile);
        $invoicePdfFile = end($invoicePdfFile);
        $invoicePdfFile = substr($invoicePdfFile, 11);
        $invoicePdfFile = $this->request->getQuery('dateFrom'). '-' . $this->request->getQuery('dateTo') . '-' . $invoicePdfFile;
        $pdfWriter->setFilename($invoicePdfFile);

        die($pdfWriter->writeInline());
    }

    private function getOrderListFilenameForWriteInline($manufacturerId, $manufacturerName, $pickupDay, $type): string
    {
        $currentDateForOrderLists = Configure::read('app.timeHelper')->getCurrentDateTimeForFilename();
        $productPdfFile = Configure::read('app.htmlHelper')->getOrderListLink($manufacturerName, $manufacturerId, $pickupDay, $type, $currentDateForOrderLists);
        $productPdfFile = explode(DS, $productPdfFile);
        $productPdfFile = end($productPdfFile);
        $productPdfFile = substr($productPdfFile, 11);
        $productPdfFile = $pickupDay . '-' . $productPdfFile;
        return $productPdfFile;
    }

    public function getOrderListByProduct()
    {

        $pdfWriter = new OrderListByProductPdfWriter();
        return $this->getOrderList('product', $pdfWriter);
    }

    public function getOrderListByCustomer()
    {
        $pdfWriter = new OrderListByCustomerPdfWriter();
        return $this->getOrderList('customer', $pdfWriter);
    }

    protected function getOrderList($type, $pdfWriter)
    {

        $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        $pickupDay = h($this->getRequest()->getQuery('pickupDay'));
        $pickupDayDbFormat = Configure::read('app.timeHelper')->formatToDbFormatDate($pickupDay);

        $manufacturer = $this->Manufacturer->find('all', [
            'conditions' => [
                'Manufacturers.id_manufacturer' => $manufacturerId
            ],
        ])->first();

        $this->OrderDetail = $this->getTableLocator()->get('OrderDetails');
        $orderDetails = $this->OrderDetail->getOrderDetailsForOrderListPreview($pickupDayDbFormat);
        $orderDetails->where(['Products.id_manufacturer' => $manufacturerId]);
        $orderDetailIds = $orderDetails->all()->extract('id_order_detail')->toArray();

        if (empty($orderDetailIds)) {
            // do not throw exception because no debug mails wanted
            die(__d('admin', 'No_orders_within_the_given_time_range.'));
        }

        if ($type == 'product') {
            $typeString = __d('admin', 'product');
        } else {
            $typeString = __d('admin', 'member');
        }

        $pdfFile = $this->getOrderListFilenameForWriteInline($manufacturerId, $manufacturer->name, $pickupDay, $typeString);
        $pdfWriter->setFilename($pdfFile);

        $pdfWriter->prepareAndSetData($manufacturerId, $pickupDayDbFormat, [], $orderDetailIds);
        if (!empty($this->request->getQuery('outputType')) && $this->request->getQuery('outputType') == 'html') {
            return $this->response->withStringBody($pdfWriter->writeHtml());
        }

        die($pdfWriter->writeInline());

    }

}