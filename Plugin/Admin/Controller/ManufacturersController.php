<?php
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
                return $this->AppAuth->loggedIn();
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

    public function edit($manufacturerId = null)
    {
        $this->setFormReferer();

        if ($manufacturerId > 0) {
            $unsavedManufacturer = $this->Manufacturer->find('first', array(
                'conditions' => array(
                    'Manufacturer.id_manufacturer' => $manufacturerId
                )
            ));

            $_SESSION['KCFINDER'] = array(
                'uploadURL' => Configure::read('app.cakeServerName') . "/files/kcfinder/manufacturers/" . $manufacturerId,
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
            $this->request->data['Manufacturer']['iban'] = str_replace(' ', '', $this->request->data['Manufacturer']['iban']);
            $this->request->data['Manufacturer']['bic'] = str_replace(' ', '', $this->request->data['Manufacturer']['bic']);
            $this->request->data['Manufacturer']['homepage'] = StringComponent::addHttpToUrl($this->request->data['Manufacturer']['homepage']);

            $this->Manufacturer->set($this->request->data['Manufacturer']);

            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['Manufacturer'] as &$data) {
                $data = strip_tags(trim($data));
            }

            foreach ($this->request->data['ManufacturerLang'] as $key => &$data) {
                if (! in_array($key, array(
                    'description',
                    'short_description'
                ))) {
                    $data = strip_tags(trim($data));
                }
            }

            foreach ($this->request->data['Address'] as &$data) {
                $data = strip_tags(trim($data));
            }

            $errors = array();
            if (! $this->Manufacturer->validates()) {
                $errors = array_merge($errors, $this->Manufacturer->validationErrors);
            }
            $this->Manufacturer->Address->set($this->request->data['Address']);

            if (! $this->Manufacturer->Address->validates()) {
                $errors = array_merge($errors, $this->Manufacturer->Address->validationErrors);
            }
            $this->Manufacturer->ManufacturerLang->set($this->request->data['ManufacturerLang']);
            if (! $this->Manufacturer->ManufacturerLang->validates()) {
                $errors = array_merge($errors, $this->Manufacturer->ManufacturerLang->validationErrors);
            }

            if (empty($errors)) {
                $this->loadModel('CakeActionLog');

                if (is_null($manufacturerId)) {
                    // default value for new manufacturer
                    $this->request->data['Manufacturer']['active'] = APP_ON;
                }
                $this->Manufacturer->save($this->request->data['Manufacturer'], array(
                    'validate' => false
                ));
                $this->request->data['ManufacturerLang']['id_manufacturer'] = $this->Manufacturer->id;
                $this->request->data['ManufacturerLang']['id_lang'] = Configure::read('app.langId');

                if (is_null($manufacturerId)) {
                    $customer = array();
                    $this->request->data['Address']['id_manufacturer'] = $this->Manufacturer->id;
                    $this->request->data['Address']['alias'] = 'manufacturer';
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'manufacturer_added';
                } else {
                    $customer = $this->Manufacturer->getCustomerRecord($unsavedManufacturer);
                    $this->Manufacturer->ManufacturerLang->id = $this->Manufacturer->id;
                    $this->Manufacturer->Address->id = $unsavedManufacturer['Address']['id_address'];
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'manufacturer_changed';
                }

                // update or create customer record (for login)
                // customer might also be missing for existing manufacturers
                $this->loadModel('Customer');
                if (! empty($customer)) {
                    $this->Customer->id = $customer['Customer']['id_customer'];
                } else {
                    $this->Customer->id = null;
                }
                $customerData = array(
                    'id_customer' => $this->Customer->id,
                    'email' => $this->data['Address']['email'],
                    'firstname' => $this->data['Address']['firstname'],
                    'lastname' => $this->data['Address']['lastname'],
                    'active' => APP_ON,
                    'id_lang' => Configure::read('app.langId')
                );
                $this->Customer->save($customerData, false);

                $this->Manufacturer->ManufacturerLang->save($this->request->data, array(
                    'validate' => false
                ));
                $this->Manufacturer->Address->save($this->request->data, array(
                    'validate' => false
                ));

                if ($this->request->data['Manufacturer']['tmp_image'] != '') {
                    $this->saveUploadedImage($this->Manufacturer->id, $this->request->data['Manufacturer']['tmp_image'], Configure::read('htmlHelper')->getManufacturerThumbsPath(), Configure::read('app.manufacturerImageSizes'));
                }

                if ($this->request->data['Manufacturer']['delete_image']) {
                    $this->deleteUploadedImage($this->Manufacturer->id, Configure::read('htmlHelper')->getManufacturerThumbsPath(), Configure::read('app.manufacturerImageSizes'));
                }

                $message = 'Der Hersteller "' . $this->request->data['Manufacturer']['name'] . '" wurde ' . $messageSuffix;
                $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Manufacturer->id, 'manufacturers', $message);
                $this->Flash->success('Der Hersteller wurde erfolgreich gespeichert.');

                if ($this->here == Configure::read('slugHelper')->getManufacturerProfile()) {
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
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));

        $message = 'Der Hersteller "' . $manufacturer['Manufacturer']['name'] . '" wurde erfolgreich ' . $statusText;
        $message .= '.';

        $this->Flash->success($message);

        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $manufacturerId, 'manufacturer', $message);

        $this->redirect($this->referer());
    }

    public function index()
    {
        $dateFrom = Configure::read('timeHelper')->getOrderPeriodFirstDay();
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = Configure::read('timeHelper')->getOrderPeriodLastDay();
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
                'Manufacturer.active' => $active
            );
        }

        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'Manufacturer.name' => 'ASC'
            ),
            'fields' => array('Manufacturer.*', 'Customer.*', 'Address.*', '!'.$this->Manufacturer->getManufacturerHolidayConditions().' as IsHolidayActive')
        ), $this->Paginator->settings);
        $manufacturers = $this->Paginator->paginate('Manufacturer');

        $this->loadModel('Product');
        $this->loadModel('CakePayment');
        $this->loadModel('OrderDetail');

        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            $manufacturers[$i]['product_count'] = $this->Product->getCountByManufacturerId($manufacturer['Manufacturer']['id_manufacturer']);
            $sumDepositDelivered = $this->OrderDetail->getDepositSum($manufacturer['Manufacturer']['id_manufacturer'], false);
            $sumDepositReturned = $this->CakePayment->getMonthlyDepositSumByManufacturer($manufacturer['Manufacturer']['id_manufacturer'], false);
            $manufacturers[$i]['sum_deposit_delivered'] = $sumDepositDelivered[0][0]['sumDepositDelivered'];
            $manufacturers[$i]['deposit_credit_balance'] = $sumDepositDelivered[0][0]['sumDepositDelivered'] - $sumDepositReturned[0][0]['sumDepositReturned'];
            if (Configure::read('app.db_config_FCS_USE_VARIABLE_MEMBER_FEE')) {
                $manufacturers[$i]['Manufacturer']['variable_member_fee'] = $this->Manufacturer->getOptionVariableMemberFee($manufacturer['Manufacturer']['variable_member_fee']);
            }
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
                'Manufacturer.id_manufacturer' => $manufacturerId
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
            if (! empty($manufacturer['CakeInvoices'])) {
                $invoiceNumber = $manufacturer['CakeInvoices'][0]['invoice_number'] + 1;
            }
            $newInvoiceNumber = $this->Manufacturer->formatInvoiceNumber($invoiceNumber);
            $this->set('newInvoiceNumber', $newInvoiceNumber);

            $this->RequestHandler->renderAs($this, 'pdf');
            $customer_results = $this->prepareInvoiceAndOrderList($manufacturerId, 'customer', $from, $to, array(
                ORDER_STATE_CASH,
                ORDER_STATE_CASH_FREE
            ), 'F');

            // generate invoice
            $this->render('get_invoice');
            $invoicePdfUrl = Configure::read('htmlHelper')->getInvoiceLink($manufacturer['Manufacturer']['name'], $manufacturerId, date('Y-m-d'), $newInvoiceNumber);
            $invoicePdfFile = $invoicePdfUrl;

            $this->Flash->success('Rechnung für Hersteller "' . $manufacturer['Manufacturer']['name'] . '" erfolgreich versendet an ' . $manufacturer['Address']['email'] . '.</a>');

            $loggedUser = $this->AppAuth->user();
            $invoice2Save = array(
                'id_manufacturer' => $manufacturerId,
                'send_date' => date('Y-m-d H:i:s'),
                'invoice_number' => $invoiceNumber,
                'user_id' => $loggedUser['id_customer']
            );
            $this->Manufacturer->CakeInvoices->id = null;
            $this->Manufacturer->CakeInvoices->save($invoice2Save);

            $invoicePeriodMonthAndYear = Configure::read('timeHelper')->getLastMonthNameAndYear();

            $sendEmail = $this->Manufacturer->getOptionSendInvoice($manufacturer['Manufacturer']['send_invoice']);
            if ($sendEmail) {
                $email->template('Admin.send_invoice')
                    ->to($manufacturer['Address']['email'])
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
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        return $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer['Manufacturer']['bulk_orders_allowed']);
    }

    private function getOptionVariableMemberFee($manufacturerId)
    {
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        return $this->Manufacturer->getOptionVariableMemberFee($manufacturer['Manufacturer']['variable_member_fee']);
    }

    public function sendOrderList($manufacturerId, $from, $to)
    {
        Configure::read('timeHelper')->recalcDeliveryDayDelta();

        $this->Manufacturer->recursive = 2; // for email
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
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
            $productPdfUrl = Configure::read('htmlHelper')->getOrderListLink($manufacturer['Manufacturer']['name'], $manufacturerId, date('Y-m-d', strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day')), 'Artikel');
            $productPdfFile = $productPdfUrl;

            // generate order list by customer
            $customerResults = $this->prepareInvoiceAndOrderList($manufacturerId, 'customer', $from, $to, array(
                ORDER_STATE_OPEN
            ), 'F');
            $this->render('get_order_list_by_customer');
            $customerPdfUrl = Configure::read('htmlHelper')->getOrderListLink($manufacturer['Manufacturer']['name'], $manufacturerId, date('Y-m-d', strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day')), 'Mitglied');
            $customerPdfFile = $customerPdfUrl;

            $sendEmail = $this->Manufacturer->getOptionSendOrderList($manufacturer['Manufacturer']['send_order_list']);
            $ccRecipients = $this->Manufacturer->getOptionSendOrderListCc($manufacturer['Manufacturer']['send_order_list_cc']);

            $flashMessage = 'Bestelllisten für Hersteller "' . $manufacturer['Manufacturer']['name'] . '" erfolgreich generiert';

            if ($sendEmail) {
                $flashMessage .= ' und an ' . $manufacturer['Address']['email'] . ' versendet';
                $email->template('Admin.send_order_list')
                    ->to($manufacturer['Address']['email'])
                    ->emailFormat('html')
                    ->cc($ccRecipients)
                    -> // works also with empty array!
                        attachments(array(
                    $productPdfFile,
                    $customerPdfFile
                        ))
                    ->subject('Bestellungen für den ' . date('d.m.Y', strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day')))
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
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));

        if (empty($unsavedManufacturer)) {
            throw new MissingActionException('manufacturer does not exist');
        }

        // set default data if manufacturer options are null
        if (Configure::read('app.db_config_FCS_USE_VARIABLE_MEMBER_FEE') && $unsavedManufacturer['Manufacturer']['variable_member_fee'] == '') {
            $unsavedManufacturer['Manufacturer']['variable_member_fee'] = Configure::read('app.db_config_FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE');
        }
        if ($unsavedManufacturer['Manufacturer']['send_order_list'] == '') {
            $unsavedManufacturer['Manufacturer']['send_order_list'] = Configure::read('app.defaultSendOrderList');
        }
        if ($unsavedManufacturer['Manufacturer']['send_invoice'] == '') {
            $unsavedManufacturer['Manufacturer']['send_invoice'] = Configure::read('app.defaultSendInvoice');
        }
        if ($unsavedManufacturer['Manufacturer']['default_tax_id'] == '') {
            $unsavedManufacturer['Manufacturer']['default_tax_id'] = Configure::read('app.defaultTaxId');
        }
        if (!$this->AppAuth->isManufacturer() && $unsavedManufacturer['Manufacturer']['bulk_orders_allowed'] == '') {
            $unsavedManufacturer['Manufacturer']['bulk_orders_allowed'] = Configure::read('app.defaultBulkOrdersAllowed');
        }
        if ($unsavedManufacturer['Manufacturer']['send_shop_order_notification'] == '') {
            $unsavedManufacturer['Manufacturer']['send_shop_order_notification'] = Configure::read('app.defaultSendShopOrderNotification');
        }
        if ($unsavedManufacturer['Manufacturer']['send_ordered_product_deleted_notification'] == '') {
            $unsavedManufacturer['Manufacturer']['send_ordered_product_deleted_notification'] = Configure::read('app.defaultSendOrderedProductDeletedNotification');
        }
        if ($unsavedManufacturer['Manufacturer']['send_ordered_product_price_changed_notification'] == '') {
            $unsavedManufacturer['Manufacturer']['send_ordered_product_price_changed_notification'] = Configure::read('app.defaultSendOrderedProductPriceChangedNotification');
        }
        if ($unsavedManufacturer['Manufacturer']['send_ordered_product_quantity_changed_notification'] == '') {
            $unsavedManufacturer['Manufacturer']['send_ordered_product_quantity_changed_notification'] = Configure::read('app.defaultSendOrderedProductQuantityChangedNotification');
        }

        $unsavedManufacturer['Manufacturer']['holiday_from'] = Configure::read('timeHelper')->prepareDbDateForDatepicker($unsavedManufacturer['Manufacturer']['holiday_from']);
        $unsavedManufacturer['Manufacturer']['holiday_to'] = Configure::read('timeHelper')->prepareDbDateForDatepicker($unsavedManufacturer['Manufacturer']['holiday_to']);

        $this->set('unsavedManufacturer', $unsavedManufacturer);
        $this->set('manufacturerId', $manufacturerId);
        $this->set('title_for_layout', $unsavedManufacturer['Manufacturer']['name'].': Einstellungen bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedManufacturer;
        } else {
            // html could be manipulated and checkbox disabled attribute removed
            if ($this->AppAuth->isManufacturer()) {
                unset($this->request->data['Manufacturer']['active']);
            }

            $this->request->data['Manufacturer']['holiday_from'] = Configure::read('timeHelper')->formatForSavingAsDate($this->request->data['Manufacturer']['holiday_from']);
            $this->request->data['Manufacturer']['holiday_to'] = Configure::read('timeHelper')->formatForSavingAsDate($this->request->data['Manufacturer']['holiday_to']);

            // values that are the same as default values => null
            if (Configure::read('app.db_config_FCS_USE_VARIABLE_MEMBER_FEE') && $this->request->data['Manufacturer']['variable_member_fee'] == Configure::read('app.db_config_FCS_DEFAULT_VARIABLE_MEMBER_FEE_PERCENTAGE')) {
                $this->request->data['Manufacturer']['variable_member_fee'] = null;
            }
            if ($this->request->data['Manufacturer']['default_tax_id'] == Configure::read('app.defaultTaxId')) {
                $this->request->data['Manufacturer']['default_tax_id'] = null;
            }
            if ($this->request->data['Manufacturer']['send_order_list'] == Configure::read('app.defaultSendOrderList')) {
                $this->request->data['Manufacturer']['send_order_list'] = null;
            }
            if ($this->request->data['Manufacturer']['send_invoice'] == Configure::read('app.defaultSendInvoice')) {
                $this->request->data['Manufacturer']['send_invoice'] = null;
            }
            if (!$this->AppAuth->isManufacturer() && $this->request->data['Manufacturer']['bulk_orders_allowed'] == Configure::read('app.defaultBulkOrdersAllowed')) {
                $this->request->data['Manufacturer']['bulk_orders_allowed'] = null;
            }
            if ($this->request->data['Manufacturer']['send_shop_order_notification'] == Configure::read('app.defaultSendShopOrderNotification')) {
                $this->request->data['Manufacturer']['send_shop_order_notification'] = null;
            }
            if ($this->request->data['Manufacturer']['send_ordered_product_deleted_notification'] == Configure::read('app.defaultSendOrderedProductDeletedNotification')) {
                $this->request->data['Manufacturer']['send_ordered_product_deleted_notification'] = null;
            }
            if ($this->request->data['Manufacturer']['send_ordered_product_price_changed_notification'] == Configure::read('app.defaultSendOrderedProductPriceChangedNotification')) {
                $this->request->data['Manufacturer']['send_ordered_product_price_changed_notification'] = null;
            }
            if ($this->request->data['Manufacturer']['send_ordered_product_quantity_changed_notification'] == Configure::read('app.defaultSendOrderedProductQuantityChangedNotification')) {
                $this->request->data['Manufacturer']['send_ordered_product_quantity_changed_notification'] = null;
            }

            // remove post data that could be set by hacking attempt
            if ($this->AppAuth->isManufacturer()) {
                unset($this->request->data['Manufacturer']['bulk_orders_allowed']);
                unset($this->request->data['Manufacturer']['variable_member_fee']);
                unset($this->request->data['Manufacturer']['id_customer']);
            }

            // validate data - do not use $this->Manufacturer->saveAll()
            $this->Manufacturer->id = $manufacturerId;
            $this->Manufacturer->set($this->request->data['Manufacturer']);

            $this->Manufacturer->validator()['send_order_list'] = $this->Manufacturer->getNumberRangeConfigurationRule(0, 2);

            if (Configure::read('app.db_config_FCS_USE_VARIABLE_MEMBER_FEE')) {
                $this->Manufacturer->validator()['variable_member_fee'] = $this->Manufacturer->getNumberRangeConfigurationRule(0, 100);
            }
            if (!empty($this->request->data['Manufacturer']['send_order_list_cc'])) {
                $this->Manufacturer->validator()['send_order_list_cc'] = $this->Manufacturer->getMultipleEmailValidationRule(true);
            }

            $errors = array();
            if (! $this->Manufacturer->validates()) {
                $errors = array_merge($errors, $this->Manufacturer->validationErrors);
            }

            if (empty($errors)) {
                $this->Manufacturer->save($this->request->data['Manufacturer'], array(
                    'validate' => false
                ));

                $message = 'Die Einstellungen des Herstellers <b>' . $unsavedManufacturer['Manufacturer']['name'] . '</b>';
                if ($this->here == Configure::read('slugHelper')->getManufacturerMyOptions()) {
                    $message = 'Deine Einstellungen';
                    $this->renewAuthSession();
                }
                $message .= ' wurden erfolgreich gespeichert.';

                $this->Flash->success($message);

                $this->loadModel('CakeActionLog');
                $this->CakeActionLog->customSave('manufacturer_options_changed', $this->AppAuth->getUserId(), $manufacturerId, 'manufacturers', $message);

                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind ' . count($errors) . ' Fehler aufgetreten!');
            }
        }

        $this->loadModel('Tax');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());

        if (!$this->AppAuth->isManufacturer()) {
            $this->loadModel('Customer');
            $this->set('customersForDropdown', $this->Customer->getForDropdown());
        }
    }

    public function changeProductStatusByManufacturer($manufacturerId, $status)
    {
        if (! in_array($status, array(
            APP_OFF,
            APP_ON
        ))) {
            throw new MissingActionException('Status muss 0 oder 1 sein!');
        }

        // if logged user is manufacturer, then get param manufacturer id is NOT used
        // but logged user id for security reasons
        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }

        $sql = "UPDATE ".$this->Manufactuer->tablePrefix."product p, ".$this->Manufacturer->tablePrefix."product_shop ps 
                SET p.active  = " . $status . ",
                    ps.active = " . $status . "
                WHERE p.id_product = ps.id_product
                AND p.id_manufacturer = " . $manufacturerId . ";";
        $result = $this->Manufacturer->query($sql);
        $affectedRows = $this->Manufacturer->getAffectedRows() / 2; // two tables affected...

        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));

        $statusText = 'deaktiviert';
        $actionLogType = 'product_set_inactive';
        if ($status) {
            $statusText = 'aktiviert';
            $actionLogType = 'product_set_active';
        }

        $message = 'Alle Artikel des Herstellers "' . $manufacturer['Manufacturer']['name'] . '" wurden ' . $statusText . '. Veränderte Artikel: ' . $affectedRows;
        $this->Flash->success($message);

        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), 0, 'products', $message);

        $this->redirect($this->referer());
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
        $this->set('deliveryDay', date('Y-m-d', strtotime('+' . Configure::read('app.deliveryDayDelta') . ' day')));

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
        $this->set('sumPriceExcl', Configure::read('htmlHelper')->formatAsDecimal($sumPriceExcl));
        $this->set('sumTax', Configure::read('htmlHelper')->formatAsDecimal($sumTax));
        $this->set('sumPriceIncl', Configure::read('htmlHelper')->formatAsDecimal($sumPriceIncl));
        $this->set('sumAmount', $sumAmount);

        $this->set('variableMemberFee', $this->getOptionVariableMemberFee($manufacturerId));
        $this->set('bulkOrdersAllowed', $this->getOptionBulkOrdersAllowed($manufacturerId));

        $this->set('saveParam', $saveParam);
        return $results;
    }

    public function getInvoice($manufacturerId, $from, $to)
    {
        $results = $this->prepareInvoiceAndOrderList($manufacturerId, 'customer', $from, $to, array(
            ORDER_STATE_CASH,
            ORDER_STATE_CASH_FREE
        ));
        if (empty($results)) {
            // do not throw exception because no debug mails wanted
            die('Keine Bestellungen im angegebenen Zeitraum vorhanden.');
        }
        $this->prepareInvoiceAndOrderList($manufacturerId, 'product', $from, $to, array(
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
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));

        $this->set('manufacturer', $manufacturer);

        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer['Manufacturer']['bulk_orders_allowed']);
        if ($bulkOrdersAllowed) {
            $orderStates = Configure::read('htmlHelper')->getOrderStateIds();
        } else {
            $orderStates = array(
                ORDER_STATE_OPEN
            );
        }

        return $orderStates;
    }
}
