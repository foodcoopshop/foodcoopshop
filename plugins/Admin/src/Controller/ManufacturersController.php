<?php

use Admin\Controller\AdminAppController;
use App\Controller\Component\StringComponent;
use Cake\Controller\Exception\MissingActionException;
use Cake\Core\Configure;
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
        switch ($this->action) {
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

    public function profile()
    {
        $this->edit($this->AppAuth->getManufacturerId());
        $this->set('referer', $this->here);
        $this->set('title_for_layout', 'Profil bearbeiten');
        $this->render('edit');
    }

    public function add()
    {
        $this->edit();
        $this->set('title_for_layout', 'Hersteller erstellen');
        $this->render('edit');
    }

    public function setKcFinderUploadPath($manufacturerId)
    {
        $this->RequestHandler->renderAs($this, 'json');

        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        } else {
            $this->recursive = -1;
            $manufacturer = $this->Manufacturer->find('first', array(
                'conditions' => array(
                    'Manufacturers.id_manufacturer' => $manufacturerId
                )
            ));
            $manufacturerId = $manufacturer['Manufacturers']['id_manufacturer'];
        }

        $_SESSION['KCFINDER'] = array(
            'uploadURL' => Configure::read('AppConfig.cakeServerName') . "/files/kcfinder/manufacturers/" . $manufacturerId,
            'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/manufacturers/" . $manufacturerId
        );
        $this->set('data', array(
            'status' => true,
            'msg' => 'OK'
        ));
        $this->set('_serialize', 'data');
    }

    public function edit($manufacturerId = null)
    {
        $this->setFormReferer();

        if ($manufacturerId > 0) {
            $unsavedManufacturer = $this->Manufacturer->find('first', array(
                'conditions' => array(
                    'Manufacturers.id_manufacturer' => $manufacturerId
                )
            ));

            $_SESSION['KCFINDER'] = array(
                'uploadURL' => Configure::read('AppConfig.cakeServerName') . "/files/kcfinder/manufacturers/" . $manufacturerId,
                'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/manufacturers/" . $manufacturerId
            );
        } else {
            $unsavedManufacturer = array();
        }

        $this->set('unsavedManufacturer', $unsavedManufacturer);
        $this->set('manufacturerId', $manufacturerId);
        $this->set('title_for_layout', 'Hersteller bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedManufacturer;
        } else {
            // validate data - do not use $this->Manufacturer->saveAll()
            $this->Manufacturer->id = $manufacturerId;

            // for making regex work, remove whitespace
            $this->request->data['Manufacturers']['iban'] = str_replace(' ', '', $this->request->data['Manufacturers']['iban']);
            $this->request->data['Manufacturers']['bic'] = str_replace(' ', '', $this->request->data['Manufacturers']['bic']);
            $this->request->data['Manufacturers']['homepage'] = StringComponent::addHttpToUrl($this->request->data['Manufacturers']['homepage']);

            $this->Manufacturer->set($this->request->data['Manufacturers']);

            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['Manufacturers'] as $key => &$data) {
                if (! in_array($key, array(
                    'description',
                    'short_description'
                ))) {
                    $data = strip_tags(trim($data));
                }
            }

            foreach ($this->request->data['Addresses'] as &$data) {
                $data = strip_tags(trim($data));
            }

            $errors = array();
            if (! $this->Manufacturer->validates()) {
                $errors = array_merge($errors, $this->Manufacturer->validationErrors);
            }
            $this->Manufacturer->Address->set($this->request->data['Addresses']);

            if (! $this->Manufacturer->Address->validates()) {
                $errors = array_merge($errors, $this->Manufacturer->Address->validationErrors);
            }

            if (empty($errors)) {
                $this->ActionLog = TableRegistry::get('ActionLogs');

                if (is_null($manufacturerId)) {
                    // default value for new manufacturer
                    $this->request->data['Manufacturers']['active'] = APP_ON;
                }
                $this->Manufacturer->save($this->request->data['Manufacturers'], array(
                    'validate' => false
                ));

                if (is_null($manufacturerId)) {
                    $customer = array();
                    $this->request->data['Addresses']['id_manufacturer'] = $this->Manufacturer->id;
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'manufacturer_added';
                } else {
                    $customer = $this->Manufacturer->getCustomerRecord($unsavedManufacturer);
                    $this->Manufacturer->Address->id = $unsavedManufacturer['Addresses']['id_address'];
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'manufacturer_changed';
                }

                // update or create customer record (for login)
                // customer might also be missing for existing manufacturers
                $this->Customer = TableRegistry::get('Customers');
                if (! empty($customer)) {
                    $this->Customer->id = $customer['Customers']['id_customer'];
                } else {
                    $this->Customer->id = null;
                }
                $customerData = array(
                    'id_customer' => $this->Customer->id,
                    'email' => $this->data['Addresses']['email'],
                    'firstname' => $this->data['Addresses']['firstname'],
                    'lastname' => $this->data['Addresses']['lastname'],
                    'active' => APP_ON,
                    'id_lang' => Configure::read('AppConfig.langId')
                );
                $this->Customer->save($customerData, false);

                $this->Manufacturer->Address->save($this->request->data, array(
                    'validate' => false
                ));

                if ($this->request->data['Manufacturers']['tmp_image'] != '') {
                    $this->saveUploadedImage($this->Manufacturer->id, $this->request->data['Manufacturers']['tmp_image'], Configure::read('AppConfig.htmlHelper')->getManufacturerThumbsPath(), Configure::read('AppConfig.manufacturerImageSizes'));
                }

                if ($this->request->data['Manufacturers']['delete_image']) {
                    $this->deleteUploadedImage($this->Manufacturer->id, Configure::read('AppConfig.htmlHelper')->getManufacturerThumbsPath(), Configure::read('AppConfig.manufacturerImageSizes'));
                }

                $message = 'Der Hersteller "' . $this->request->data['Manufacturers']['name'] . '" wurde ' . $messageSuffix;
                $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Manufacturer->id, 'manufacturers', $message);
                $this->Flash->success('Der Hersteller wurde erfolgreich gespeichert.');

                if ($this->here == Configure::read('AppConfig.slugHelper')->getManufacturerProfile()) {
                    $this->renewAuthSession();
                }

                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind ' . count($errors) . ' Fehler aufgetreten!');
            }
        }
    }

    public function changeStatus($manufacturerId, $status)
    {
        if (! in_array($status, array(
            APP_OFF,
            APP_ON
        ))) {
            throw new MissingActionException('Status muss 0 oder 1 sein!');
        }

        $this->Manufacturer->id = $manufacturerId;
        $this->Manufacturer->save(array(
            'active' => $status
        ));

        $statusText = 'deaktiviert';
        $actionLogType = 'manufacturer_set_inactive';
        if ($status) {
            $statusText = 'aktiviert';
            $actionLogType = 'manufacturer_set_active';
        }

        $this->Manufacturer->recursive = - 1;
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));

        $message = 'Der Hersteller "' . $manufacturer['Manufacturers']['name'] . '" wurde erfolgreich ' . $statusText;
        $message .= '.';

        $this->Flash->success($message);

        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $manufacturerId, 'manufacturer', $message);

        $this->redirect($this->referer());
    }

    public function index()
    {
        $dateFrom = Configure::read('AppConfig.timeHelper')->getOrderPeriodFirstDay(Configure::read('AppConfig.timeHelper')->getCurrentDay());
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('AppConfig.timeHelper')->getOrderPeriodLastDay(Configure::read('AppConfig.timeHelper')->getCurrentDay());
        if (! empty($this->params['named']['dateTo'])) {
            $dateTo = $this->params['named']['dateTo'];
        }
        $this->set('dateTo', $dateTo);

        $active = 1; // default value
        if (isset($this->params['named']['active'])) { // klappt bei orderState auch mit !empty( - hier nicht... strange
            $active = $this->params['named']['active'];
        }
        $this->set('active', $active);

        $conditions = array();
        if ($active != 'all') {
            $conditions = array(
                'Manufacturers.active' => $active
            );
        }

        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'Manufacturers.name' => 'ASC'
            ),
            'fields' => array('Manufacturers.*', 'Customers.*', 'Addresses.*', '!'.$this->Manufacturer->getManufacturerHolidayConditions().' as IsHolidayActive')
        ), $this->Paginator->settings);
        $manufacturers = $this->Paginator->paginate('Manufacturers');

        $this->Product = TableRegistry::get('Products');
        $this->Payment = TableRegistry::get('Payments');
        $this->OrderDetail = TableRegistry::get('OrderDetails');

        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            $manufacturers[$i]['product_count'] = $this->Product->getCountByManufacturerId($manufacturer['Manufacturers']['id_manufacturer']);
            $sumDepositDelivered = $this->OrderDetail->getDepositSum($manufacturer['Manufacturers']['id_manufacturer'], false);
            $sumDepositReturned = $this->Payment->getMonthlyDepositSumByManufacturer($manufacturer['Manufacturers']['id_manufacturer'], false);
            $manufacturers[$i]['sum_deposit_delivered'] = $sumDepositDelivered[0][0]['sumDepositDelivered'];
            $manufacturers[$i]['deposit_credit_balance'] = $sumDepositDelivered[0][0]['sumDepositDelivered'] - $sumDepositReturned[0][0]['sumDepositReturned'];
            if (Configure::read('AppConfigDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
                $manufacturers[$i]['Manufacturers']['variable_member_fee'] = $this->Manufacturer->getOptionVariableMemberFee($manufacturer['Manufacturers']['variable_member_fee']);
            }
            $manufacturers[$i]['sum_open_order_detail'] = $this->OrderDetail->getOpenOrderDetailSum($manufacturer['Manufacturers']['id_manufacturer'], $dateFrom, $dateTo);
            $i++;
        }
        $this->set('manufacturers', $manufacturers);

        $this->set('title_for_layout', 'Hersteller');
    }

    public function sendInvoice($manufacturerId, $from, $to)
    {
        $this->Manufacturer->recursive = 2; // for email
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));

        // generate and save PDF - should be done here because count of results will be checked
        $product_results = $this->prepareInvoiceAndOrderList($manufacturerId, 'product', $from, $to, array(
            ORDER_STATE_CASH,
            ORDER_STATE_CASH_FREE
        ), 'F');

        $email = new AppEmail();

        // no orders in current period => do not send pdf but send information email
        if (count($product_results) == 0) {
            // orders exist => send pdf and email
        } else {
            // generate and save invoice number
            $invoiceNumber = 1; // default
            if (! empty($manufacturer['Invoices'])) {
                $invoiceNumber = $manufacturer['Invoices'][0]['invoice_number'] + 1;
            }
            $newInvoiceNumber = $this->Manufacturer->formatInvoiceNumber($invoiceNumber);
            $this->set('newInvoiceNumber', $newInvoiceNumber);

            $this->RequestHandler->renderAs($this, 'pdf');
            $customer_results = $this->prepareInvoiceAndOrderList($manufacturerId, 'customer', $from, $to, array(
                ORDER_STATE_OPEN,
                ORDER_STATE_CASH,
                ORDER_STATE_CASH_FREE
            ), 'F');

            // generate invoice
            $this->render('get_invoice');
            $invoicePdfUrl = Configure::read('AppConfig.htmlHelper')->getInvoiceLink($manufacturer['Manufacturers']['name'], $manufacturerId, date('Y-m-d'), $newInvoiceNumber);
            $invoicePdfFile = $invoicePdfUrl;

            $this->Flash->success('Rechnung für Hersteller "' . $manufacturer['Manufacturers']['name'] . '" erfolgreich versendet an ' . $manufacturer['Addresses']['email'] . '.</a>');

            $loggedUser = $this->AppAuth->user();
            $invoice2Save = array(
                'id_manufacturer' => $manufacturerId,
                'send_date' => date('Y-m-d H:i:s'),
                'invoice_number' => $invoiceNumber,
                'user_id' => $loggedUser['id_customer']
            );
            $this->Manufacturer->Invoices->id = null;
            $this->Manufacturer->Invoices->save($invoice2Save);

            $invoicePeriodMonthAndYear = Configure::read('AppConfig.timeHelper')->getLastMonthNameAndYear();

            $sendEmail = $this->Manufacturer->getOptionSendInvoice($manufacturer['Manufacturers']['send_invoice']);
            if ($sendEmail) {
                $email->template('Admin.send_invoice')
                    ->to($manufacturer['Addresses']['email'])
                    ->attachments(array(
                    $invoicePdfFile
                    ))
                    ->emailFormat('html')
                    ->subject('Rechnung Nr. ' . $newInvoiceNumber . ', ' . $invoicePeriodMonthAndYear)
                    ->viewVars(array(
                    'manufacturer' => $manufacturer,
                    'invoicePeriodMonthAndYear' => $invoicePeriodMonthAndYear,
                    'appAuth' => $this->AppAuth,
                    'showManufacturerUnsubscribeLink' => true
                    ));

                $email->send();
            }
        }

        $this->redirect($this->referer());
    }

    private function getOptionBulkOrdersAllowed($manufacturerId)
    {
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));
        return $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer['Manufacturers']['bulk_orders_allowed']);
    }

    private function getOptionVariableMemberFee($manufacturerId)
    {
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));
        return $this->Manufacturer->getOptionVariableMemberFee($manufacturer['Manufacturers']['variable_member_fee']);
    }

    public function sendOrderList($manufacturerId, $from, $to)
    {
        Configure::read('AppConfig.timeHelper')->recalcDeliveryDayDelta();

        $this->Manufacturer->recursive = 2; // for email
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));

        // generate and save PDF - should be done here because count of results will be checked
        $productResults = $this->prepareInvoiceAndOrderList($manufacturerId, 'product', $from, $to, array(
            ORDER_STATE_OPEN
        ), 'F');

        $email = new AppEmail();

        // no orders in current period => do not send pdf but send information email
        if (count($productResults) == 0) {
            // orders exist => send pdf and email
        } else {
            $this->RequestHandler->renderAs($this, 'pdf');

            // generate order list by procuct
            $this->render('get_order_list_by_product');
            $productPdfUrl = Configure::read('AppConfig.htmlHelper')->getOrderListLink($manufacturer['Manufacturers']['name'], $manufacturerId, date('Y-m-d', strtotime('+' . Configure::read('AppConfig.deliveryDayDelta') . ' day')), 'Produkt');
            $productPdfFile = $productPdfUrl;

            // generate order list by customer
            $customerResults = $this->prepareInvoiceAndOrderList($manufacturerId, 'customer', $from, $to, array(
                ORDER_STATE_OPEN
            ), 'F');
            $this->render('get_order_list_by_customer');
            $customerPdfUrl = Configure::read('AppConfig.htmlHelper')->getOrderListLink($manufacturer['Manufacturers']['name'], $manufacturerId, date('Y-m-d', strtotime('+' . Configure::read('AppConfig.deliveryDayDelta') . ' day')), 'Mitglied');
            $customerPdfFile = $customerPdfUrl;

            $sendEmail = $this->Manufacturer->getOptionSendOrderList($manufacturer['Manufacturers']['send_order_list']);
            $ccRecipients = $this->Manufacturer->getOptionSendOrderListCc($manufacturer['Manufacturers']['send_order_list_cc']);

            $flashMessage = 'Bestelllisten für Hersteller "' . $manufacturer['Manufacturers']['name'] . '" erfolgreich generiert';

            if ($sendEmail) {
                $flashMessage .= ' und an ' . $manufacturer['Addresses']['email'] . ' versendet';
                $email->template('Admin.send_order_list')
                    ->to($manufacturer['Addresses']['email'])
                    ->emailFormat('html')
                    ->cc($ccRecipients)
                    -> // works also with empty array!
                        attachments(array(
                    $productPdfFile,
                    $customerPdfFile
                        ))
                    ->subject('Bestellungen für den ' . date('d.m.Y', strtotime('+' . Configure::read('AppConfig.deliveryDayDelta') . ' day')))
                    ->viewVars(array(
                    'manufacturer' => $manufacturer,
                    'appAuth' => $this->AppAuth,
                    'showManufacturerUnsubscribeLink' => true
                    ));

                $email->send();
            }
        }

        $flashMessage .= '.';
        $this->Flash->success($flashMessage);
        $this->redirect($this->referer());
        exit(); // important, on dev it happend that the url was called twice (browser-call)
    }

    public function myOptions()
    {
        $this->editOptions($this->AppAuth->getManufacturerId());
        $this->set('referer', $this->here);
        $this->set('title_for_layout', 'Einstellungen bearbeiten');
        $this->render('editOptions');
    }

    public function editOptions($manufacturerId = null)
    {

        $this->setFormReferer();

        $unsavedManufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));

        if (empty($unsavedManufacturer)) {
            throw new MissingActionException('manufacturer does not exist');
        }

        if (Configure::read('AppConfigDb.FCS_NETWORK_PLUGIN_ENABLED')) {
            $this->Network.SyncDomain = TableRegistry::get('Network.SyncDomains');
            $this->helpers[] = 'Network.Network';
            $this->set('syncDomainsForDropdown', $this->SyncDomain->getForDropdown());
            $isAllowedEditManufacturerOptionsDropdown = $this->SyncDomain->isAllowedEditManufacturerOptionsDropdown($this->AppAuth);
            $this->set('isAllowedEditManufacturerOptionsDropdown', $isAllowedEditManufacturerOptionsDropdown);
        }

        // set default data if manufacturer options are null
        if (Configure::read('AppConfigDb.FCS_USE_VARIABLE_MEMBER_FEE') && $unsavedManufacturer['Manufacturers']['variable_member_fee'] == '') {
            $unsavedManufacturer['Manufacturers']['variable_member_fee'] = Configure::read('AppConfigDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE');
        }
        if ($unsavedManufacturer['Manufacturers']['send_order_list'] == '') {
            $unsavedManufacturer['Manufacturers']['send_order_list'] = Configure::read('AppConfig.defaultSendOrderList');
        }
        if ($unsavedManufacturer['Manufacturers']['send_invoice'] == '') {
            $unsavedManufacturer['Manufacturers']['send_invoice'] = Configure::read('AppConfig.defaultSendInvoice');
        }
        if ($unsavedManufacturer['Manufacturers']['default_tax_id'] == '') {
            $unsavedManufacturer['Manufacturers']['default_tax_id'] = Configure::read('AppConfig.defaultTaxId');
        }
        if (!$this->AppAuth->isManufacturer() && $unsavedManufacturer['Manufacturers']['bulk_orders_allowed'] == '') {
            $unsavedManufacturer['Manufacturers']['bulk_orders_allowed'] = Configure::read('AppConfig.defaultBulkOrdersAllowed');
        }
        if ($unsavedManufacturer['Manufacturers']['send_shop_order_notification'] == '') {
            $unsavedManufacturer['Manufacturers']['send_shop_order_notification'] = Configure::read('AppConfig.defaultSendShopOrderNotification');
        }
        if ($unsavedManufacturer['Manufacturers']['send_ordered_product_deleted_notification'] == '') {
            $unsavedManufacturer['Manufacturers']['send_ordered_product_deleted_notification'] = Configure::read('AppConfig.defaultSendOrderedProductDeletedNotification');
        }
        if ($unsavedManufacturer['Manufacturers']['send_ordered_product_price_changed_notification'] == '') {
            $unsavedManufacturer['Manufacturers']['send_ordered_product_price_changed_notification'] = Configure::read('AppConfig.defaultSendOrderedProductPriceChangedNotification');
        }
        if ($unsavedManufacturer['Manufacturers']['send_ordered_product_quantity_changed_notification'] == '') {
            $unsavedManufacturer['Manufacturers']['send_ordered_product_quantity_changed_notification'] = Configure::read('AppConfig.defaultSendOrderedProductQuantityChangedNotification');
        }

        $unsavedManufacturer['Manufacturers']['holiday_from'] = Configure::read('AppConfig.timeHelper')->prepareDbDateForDatepicker($unsavedManufacturer['Manufacturers']['holiday_from']);
        $unsavedManufacturer['Manufacturers']['holiday_to'] = Configure::read('AppConfig.timeHelper')->prepareDbDateForDatepicker($unsavedManufacturer['Manufacturers']['holiday_to']);

        $this->set('unsavedManufacturer', $unsavedManufacturer);
        $this->set('manufacturerId', $manufacturerId);
        $this->set('title_for_layout', $unsavedManufacturer['Manufacturers']['name'].': Einstellungen bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedManufacturer;
        } else {
            // html could be manipulated and checkbox disabled attribute removed
            if ($this->AppAuth->isManufacturer()) {
                unset($this->request->data['Manufacturers']['active']);
            }

            $this->request->data['Manufacturers']['holiday_from'] = Configure::read('AppConfig.timeHelper')->formatForSavingAsDate($this->request->data['Manufacturers']['holiday_from']);
            $this->request->data['Manufacturers']['holiday_to'] = Configure::read('AppConfig.timeHelper')->formatForSavingAsDate($this->request->data['Manufacturers']['holiday_to']);

            // values that are the same as default values => null
            if (!$this->AppAuth->isManufacturer()) {
                // only admins and superadmins are allowed to change variable_member_fee
                if (Configure::read('AppConfigDb.FCS_USE_VARIABLE_MEMBER_FEE') && $this->request->data['Manufacturers']['variable_member_fee'] == Configure::read('AppConfigDb.FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE')) {
                    $this->request->data['Manufacturers']['variable_member_fee'] = null;
                }
            }
            if ($this->request->data['Manufacturers']['default_tax_id'] == Configure::read('AppConfig.defaultTaxId')) {
                $this->request->data['Manufacturers']['default_tax_id'] = null;
            }
            if ($this->request->data['Manufacturers']['send_order_list'] == Configure::read('AppConfig.defaultSendOrderList')) {
                $this->request->data['Manufacturers']['send_order_list'] = null;
            }
            if ($this->request->data['Manufacturers']['send_invoice'] == Configure::read('AppConfig.defaultSendInvoice')) {
                $this->request->data['Manufacturers']['send_invoice'] = null;
            }
            if (!$this->AppAuth->isManufacturer() && $this->request->data['Manufacturers']['bulk_orders_allowed'] == Configure::read('AppConfig.defaultBulkOrdersAllowed')) {
                $this->request->data['Manufacturers']['bulk_orders_allowed'] = null;
            }
            if ($this->request->data['Manufacturers']['send_shop_order_notification'] == Configure::read('AppConfig.defaultSendShopOrderNotification')) {
                $this->request->data['Manufacturers']['send_shop_order_notification'] = null;
            }
            if ($this->request->data['Manufacturers']['send_ordered_product_deleted_notification'] == Configure::read('AppConfig.defaultSendOrderedProductDeletedNotification')) {
                $this->request->data['Manufacturers']['send_ordered_product_deleted_notification'] = null;
            }
            if ($this->request->data['Manufacturers']['send_ordered_product_price_changed_notification'] == Configure::read('AppConfig.defaultSendOrderedProductPriceChangedNotification')) {
                $this->request->data['Manufacturers']['send_ordered_product_price_changed_notification'] = null;
            }
            if ($this->request->data['Manufacturers']['send_ordered_product_quantity_changed_notification'] == Configure::read('AppConfig.defaultSendOrderedProductQuantityChangedNotification')) {
                $this->request->data['Manufacturers']['send_ordered_product_quantity_changed_notification'] = null;
            }

            if (isset($isAllowedEditManufacturerOptionsDropdown) && $isAllowedEditManufacturerOptionsDropdown) {
                if ($this->request->data['Manufacturers']['enabled_sync_domains']) {
                    $this->request->data['Manufacturers']['enabled_sync_domains'] = implode(',', $this->request->data['Manufacturers']['enabled_sync_domains']);
                }
            }

            // remove post data that could be set by hacking attempt
            if ($this->AppAuth->isManufacturer()) {
                unset($this->request->data['Manufacturers']['bulk_orders_allowed']);
                unset($this->request->data['Manufacturers']['variable_member_fee']);
                unset($this->request->data['Manufacturers']['id_customer']);
            }

            // validate data - do not use $this->Manufacturer->saveAll()
            $this->Manufacturer->id = $manufacturerId;
            $this->Manufacturer->set($this->request->data['Manufacturers']);

            $this->Manufacturer->validator()['send_order_list'] = $this->Manufacturer->getNumberRangeConfigurationRule(0, 2);

            if (Configure::read('AppConfigDb.FCS_USE_VARIABLE_MEMBER_FEE')) {
                $this->Manufacturer->validator()['variable_member_fee'] = $this->Manufacturer->getNumberRangeConfigurationRule(0, 100);
            }
            if (!empty($this->request->data['Manufacturers']['send_order_list_cc'])) {
                $this->Manufacturer->validator()['send_order_list_cc'] = $this->Manufacturer->getMultipleEmailValidationRule(true);
            }

            $errors = array();
            if (! $this->Manufacturer->validates()) {
                $errors = array_merge($errors, $this->Manufacturer->validationErrors);
            }

            if (empty($errors)) {
                $this->Manufacturer->save($this->request->data['Manufacturers'], array(
                    'validate' => false
                ));

                $message = 'Die Einstellungen des Herstellers <b>' . $unsavedManufacturer['Manufacturers']['name'] . '</b>';
                if ($this->here == Configure::read('AppConfig.slugHelper')->getManufacturerMyOptions()) {
                    $message = 'Deine Einstellungen';
                    $this->renewAuthSession();
                }
                $message .= ' wurden erfolgreich gespeichert.';

                $this->Flash->success($message);

                $this->ActionLog = TableRegistry::get('ActionLogs');
                $this->ActionLog->customSave('manufacturer_options_changed', $this->AppAuth->getUserId(), $manufacturerId, 'manufacturers', $message);

                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind ' . count($errors) . ' Fehler aufgetreten!');
            }
        }

        $this->Tax = TableRegistry::get('Taxs');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());

        if (!$this->AppAuth->isManufacturer()) {
            $this->Customer = TableRegistry::get('Customers');
            $this->set('customersForDropdown', $this->Customer->getForDropdown());
        }
    }

    private function prepareInvoiceAndOrderList($manufacturerId, $groupType, $from, $to, $orderState, $saveParam = 'I')
    {
        $results = $this->Manufacturer->getOrderList($manufacturerId, $groupType, $from, $to, $orderState);
        if (empty($results)) {
            // do not throw exception because no debug mails wanted
            die('Keine Bestellungen im angegebenen Zeitraum vorhanden.');
        }

        $this->set('results_' . $groupType, $results);
        $this->set('manufacturerId', $manufacturerId);
        $this->set('from', date('d.m.Y', strtotime(str_replace('/', '-', $from))));
        $this->set('to', date('d.m.Y', strtotime(str_replace('/', '-', $to))));

        // only needed for order lists: format is english because it is used for filename => sorting!
        $this->set('deliveryDay', date('Y-m-d', strtotime('+' . Configure::read('AppConfig.deliveryDayDelta') . ' day')));

        // calculate sum of price
        $sumPriceIncl = 0;
        $sumPriceExcl = 0;
        $sumTax = 0;
        $sumAmount = 0;
        foreach ($results as $result) {
            $sumPriceIncl += $result['od']['PreisIncl'];
            $sumPriceExcl += $result['od']['PreisExcl'];
            $sumTax += $result['odt']['MWSt'];
            $sumAmount += $result['od']['Menge'];
        }
        $this->set('sumPriceExcl', $sumPriceExcl);
        $this->set('sumTax', $sumTax);
        $this->set('sumPriceIncl', $sumPriceIncl);
        $this->set('sumAmount', $sumAmount);

        $this->set('variableMemberFee', $this->getOptionVariableMemberFee($manufacturerId));
        $this->set('bulkOrdersAllowed', $this->getOptionBulkOrdersAllowed($manufacturerId));

        $this->set('saveParam', $saveParam);
        return $results;
    }

    public function getInvoice($manufacturerId, $from, $to)
    {
        $results = $this->prepareInvoiceAndOrderList($manufacturerId, 'customer', $from, $to, array(
            ORDER_STATE_OPEN,
            ORDER_STATE_CASH,
            ORDER_STATE_CASH_FREE
        ));
        if (empty($results)) {
            // do not throw exception because no debug mails wanted
            die('Keine Bestellungen im angegebenen Zeitraum vorhanden.');
        }
        $this->prepareInvoiceAndOrderList($manufacturerId, 'product', $from, $to, array(
            ORDER_STATE_OPEN,
            ORDER_STATE_CASH,
            ORDER_STATE_CASH_FREE
        ));
    }

    public function getOrderListByProduct($manufacturerId, $from, $to)
    {
        $orderStates = $this->getAllowedOrderStates($manufacturerId);
        $this->prepareInvoiceAndOrderList($manufacturerId, 'product', $from, $to, $orderStates);
    }

    public function getOrderListByCustomer($manufacturerId, $from, $to)
    {
        $orderStates = $this->getAllowedOrderStates($manufacturerId);
        $this->prepareInvoiceAndOrderList($manufacturerId, 'customer', $from, $to, $orderStates);
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
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturers.id_manufacturer' => $manufacturerId
            )
        ));

        $this->set('manufacturer', $manufacturer);

        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer['Manufacturers']['bulk_orders_allowed']);
        if ($bulkOrdersAllowed) {
            $orderStates = Configure::read('AppConfig.htmlHelper')->getOrderStateIds();
        } else {
            $orderStates = array(
                ORDER_STATE_OPEN
            );
        }

        return $orderStates;
    }
}
