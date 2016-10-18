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
                return $this->AppAuth->isManufacturer();
                break;
            case 'index':
            case 'add':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
                break;
            case 'edit':
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
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
            // default values for new manufacturers
            $unsavedManufacturer = array(
                'Manufacturer' => array(
                    'active' => APP_OFF,
                    'holiday' => APP_OFF
                )
            );
            // default value
            $unsavedManufacturer['Manufacturer']['active'] = APP_ON;
        }
        $this->set('unsavedManufacturer', $unsavedManufacturer);
        $this->set('manufacturerId', $manufacturerId);
        $this->set('title_for_layout', 'Hersteller bearbeiten');
        
        if (empty($this->request->data)) {
            $this->request->data = $unsavedManufacturer;
        } else {
            
            // html could be manipulated and checkbox disabled attribute removed
            if ($this->AppAuth->isManufacturer()) {
                unset($this->request->data['Manufacturer']['active']);
            }
            
            // validate data - do not use $this->Manufacturer->saveAll()
            $this->Manufacturer->id = $manufacturerId;
            $this->Manufacturer->set($this->request->data['Manufacturer']);
            
            // for making regex work, remove whitespace
            $this->request->data['Manufacturer']['iban'] = str_replace(' ', '', $this->request->data['Manufacturer']['iban']);
            $this->request->data['Manufacturer']['bic'] = str_replace(' ', '', $this->request->data['Manufacturer']['bic']);
            
            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['Manufacturer'] as &$data) {
                $data = strip_tags($data);
            }
            
            foreach ($this->request->data['ManufacturerLang'] as $key => &$data) {
                if (! in_array($key, array(
                    'description',
                    'short_description'
                ))) {
                    $data = strip_tags($data);
                }
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
                $this->AppSession->setFlashMessage('Der Hersteller wurde erfolgreich gespeichert.');
                
                if ($this->here == Configure::read('slugHelper')->getManufacturerProfile()) {
                    $this->renewAuthSession();
                }
                
                $this->redirect($this->data['referer']);
            } else {
                $this->AppSession->setFlashError('Beim Speichern sind ' . count($errors) . ' Fehler aufgetreten!');
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
        
        $this->AppSession->setFlashMessage($message);
        
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
            )
        ), $this->Paginator->settings);
        $manufacturers = $this->Paginator->paginate('Manufacturer');
        
        $this->loadModel('Product');
        $i = 0;
        foreach ($manufacturers as $manufacturer) {
            $manufacturers[$i]['product_count'] = $this->Product->getCountByManufacturerId($manufacturer['Manufacturer']['id_manufacturer']);
            $i ++;
        }
        $this->set('manufacturers', $manufacturers);
        
        $this->loadModel('Tax');
        $this->set('taxesForDropdown', $this->Tax->getForDropdown());
        
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
            
            $this->AppSession->setFlashMessage('Rechnung für Hersteller "' . $manufacturer['Manufacturer']['name'] . '" erfolgreich versendet an ' . $manufacturer['Address']['email'] . '.</a>');
            
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
            
            $sendEmail = $this->Manufacturer->getOptionSendInvoice($manufacturer['Address']['other']);
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
                    'appAuth' => $this->AppAuth
                ));
                
                if (Configure::read('app.invoiceMailBcc')) {
                    $email->addBcc(Configure::read('app.invoiceMailBcc'));
                }
                $email->send();
            }
        }
        
        $this->redirect($this->referer());
    }

    private function getCompensationPercentage($manufacturerId)
    {
        $this->Manufacturer->recursive = 2; // for email
        $manufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        
        $addressOther = StringComponent::decodeJsonFromForm($manufacturer['Address']['other']);
        $compensationPercentage = Configure::read('app.defaultCompensationPercentage');
        if (isset($addressOther['compensationPercentage'])) {
            $compensationPercentage = (int) $addressOther['compensationPercentage'];
        }
        return $compensationPercentage;
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
            
            $sendEmail = $this->Manufacturer->getOptionSendOrderList($manufacturer['Address']['other']);
            $ccRecipients = $this->Manufacturer->getOptionSendOrderListCc($manufacturer['Address']['other']);
            
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
                    'appAuth' => $this->AppAuth
                ));
                
                if (Configure::read('app.orderListMailBcc')) {
                    $email->addBcc(Configure::read('app.orderListMailBcc'));
                }
                $email->send();
            }
        }
        
        $flashMessage .= '.';
        $this->AppSession->setFlashMessage($flashMessage);
        $this->redirect($this->referer());
        exit(); // important, on dev it happend that the url was called twice (browser-call)
    }

    public function editOptions()
    {
        if ($this->AppAuth->isManufacturer()) {
            throw new MissingActionException('no access for manufacturers!');
        }
        
        $manufacturerId = (int) $this->params['data']['manufacturerId'];
        $compensationPercentage = (int) $this->params['data']['compensationPercentage'];
        $sendInvoice = $this->params['data']['sendInvoice'];
        $sendOrderList = $this->params['data']['sendOrderList'];
        $defaultTaxId = (int) $this->params['data']['defaultTaxId'];
        $sendOrderListCc = $this->params['data']['sendOrderListCc'];
        $bulkOrdersAllowed = $this->params['data']['bulkOrdersAllowed'];
        
        $oldManufacturer = $this->Manufacturer->find('first', array(
            'conditions' => array(
                'Manufacturer.id_manufacturer' => $manufacturerId
            )
        ));
        
        if (empty($oldManufacturer)) {
            throw new MissingActionException('wrong manufacturer');
        }
        
        // check compensationPercentage
        if ($compensationPercentage != '' && ($compensationPercentage < 0 || $compensationPercentage > 99)) {
            $msg = 'Aufwandsentschädigung muss zwischen 0 und 99 liegen!';
            $this->log($msg);
            die(json_encode(array(
                'status' => 0,
                'msg' => $msg
            )));
        }
        
        // check sendInvoice
        if ($sendInvoice != '' && ($sendInvoice < 0 || $sendInvoice > 1)) {
            $msg = 'Rechnung als PDF versenden muss zwischen 0 und 1 liegen!';
            $this->log($msg);
            die(json_encode(array(
                'status' => 0,
                'msg' => $msg
            )));
        }
        
        // check sendOrderList
        if ($sendOrderList != '' && ($sendOrderList < 0 || $sendOrderList > 1)) {
            $msg = 'Bestell-Listen als PDF versenden muss zwischen 0 und 1 liegen!';
            $this->log($msg);
            die(json_encode(array(
                'status' => 0,
                'msg' => $msg
            )));
        }
        
        // check defaultTaxId
        $this->loadModel('Tax');
        $tax = $this->Tax->find('first', array(
            'conditions' => array(
                'Tax.id_tax' => $defaultTaxId
            )
        ));
        if ($defaultTaxId != 0 && empty($tax)) {
            $msg = 'steuersatz falsch bzw. nicht gefunden';
            $this->log($msg);
            die(json_encode(array(
                'status' => 0,
                'msg' => $msg
            )));
        }
        
        // check sendOrderListCc
        App::uses('Validation', 'Utility');
        if ($sendOrderListCc != '') {
            $splittedSendOrderListCc = explode(';', $sendOrderListCc);
            foreach ($splittedSendOrderListCc as $email) {
                if (! Validation::email($email)) {
                    $msg = 'Falsche Eingabe beim Feld "CC-Empfänger für Bestell-Listen-Versand".';
                    $this->log($msg);
                    die(json_encode(array(
                        'status' => 0,
                        'msg' => $msg
                    )));
                }
            }
        }
        
        // check bulkOrdersAllowed
        if ($bulkOrdersAllowed != '' && ($bulkOrdersAllowed < 0 || $bulkOrdersAllowed > 1)) {
            $msg = 'Sammelbestellung möglich muss zwischen 0 und 1 liegen!';
            $this->log($msg);
            die(json_encode(array(
                'status' => 0,
                'msg' => $msg
            )));
        }
        
        // saving data and setting default values
        $otherFields = json_encode(array(
            'compensationPercentage' => $compensationPercentage,
            'sendInvoice' => $sendInvoice == '' ? 1 : $sendInvoice,
            'sendOrderList' => $sendOrderList == '' ? 1 : $sendOrderList,
            'defaultTaxId' => $defaultTaxId,
            'sendOrderListCc' => $sendOrderListCc,
            'bulkOrdersAllowed' => $bulkOrdersAllowed == '' ? 1 : $bulkOrdersAllowed
        ));
        
        // special format for db saving (no escaping and no " on start and end of string
        $preparedOtherFields = json_encode($otherFields);
        $preparedOtherFields = str_replace('\\', '', $preparedOtherFields);
        $preparedOtherFields = substr($preparedOtherFields, 1, - 1);
        
        $this->Manufacturer->Address->id = $oldManufacturer['Address']['id_address'];
        $this->Manufacturer->Address->save(array(
            'other' => $preparedOtherFields
        ));
        
        $message = 'Die Einstellungen des Herstellers "' . $oldManufacturer['Manufacturer']['name'] . '" wurden geändert.';
        $this->AppSession->setFlashMessage($message);
        
        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave('manufacturer_options_changed', $this->AppAuth->getUserId(), 0, 'manufacturers', $message);
        
        die(json_encode(array(
            'status' => 1,
            'msg' => 'Speichern erfolgreich.'
        )));
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
        $this->AppSession->setFlashMessage($message);
        
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
        $this->set('sumPriceExcl', number_format($sumPriceExcl, 2, ',', '.'));
        $this->set('sumTax', number_format($sumTax, 2, ',', '.'));
        $this->set('sumPriceIncl', number_format($sumPriceIncl, 2, ',', '.'));
        $this->set('sumAmount', $sumAmount);
        
        $this->set('compensationPercentage', $this->getCompensationPercentage($manufacturerId));
        
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
        
        $bulkOrdersAllowed = $this->Manufacturer->getOptionBulkOrdersAllowed($manufacturer['Address']['other']);
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

?>