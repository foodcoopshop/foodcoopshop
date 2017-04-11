<?php
/**
 * CustomersController
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
class CustomersController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->action) {
            case 'profile':
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isCustomer();
                break;
            case 'changePassword':
                return $this->AppAuth->loggedIn();
                break;
            default:
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
                break;
        }
    }

    public function ajaxEditGroup()
    {
        $customerId = (int) $this->params['data']['customerId'];
        $groupId = (int) $this->params['data']['groupId'];

        if (! in_array($groupId, array_keys(Configure::read('htmlHelper')->getAuthDependentGroups($this->AppAuth->getGroupId())))) {
            $message = 'user group not allowed: ' . $groupId;
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        $oldCustomer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $customerId
            )
        ));

        // eg. member is not allowed to change groupId of admin, not even to set a groupid he would be allowed to (member)
        if ($this->AppAuth->getGroupId() < $oldCustomer['Customer']['id_default_group']) {
            $message = 'logged user has lower groupId than the user he wants to edit: customerId: ' . $oldCustomer['Customer']['id_customer'] . ', groupId: ' . $oldCustomer['Customer']['id_default_group'];
            $this->log($message);
            die(json_encode(array(
                'status' => 0,
                'msg' => $message
            )));
        }

        $this->Customer->id = $customerId;
        $this->Customer->saveField('id_default_group', $groupId, false);

        $messageString = 'Die Gruppe des Mitglieds "' . $oldCustomer['Customer']['name'] . '" wurde von <b>' . Configure::read('htmlHelper')->getGroupName($oldCustomer['Customer']['id_default_group']) . '</b> auf <b>' . Configure::read('htmlHelper')->getGroupName($groupId) . '</b> geändert.';
        $this->AppSession->setFlashMessage($messageString);
        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave('customer_group_changed', $this->AppAuth->getUserId(), $customerId, 'customers', $messageString);

        die(json_encode(array(
            'status' => 1
        )));
    }

    public function changePassword()
    {
        $this->set(array(
            'title_for_layout' => __('Change your password')
        ));

        if (empty($this->request->data)) {
            return;
        }

        $password = $this->request->data['Customer']['passwd'];
        $passwordNew1 = $this->request->data['Customer']['passwd_new_1'];
        $passwordNew2 = $this->request->data['Customer']['passwd_new_2'];

        $error = 0;

        App::uses('AppPasswordHasher', 'Controller/Component/Auth');
        $ph = new AppPasswordHasher();

        if (! $this->Customer->isCustomerPassword($this->AppAuth->getUserId(), $ph->hash($password))) {
            $this->Customer->invalidate('passwd', __('Your old Password was wrong'));
            $error ++;
        }

        if ($password == $ph->hash('')) {
            $this->Customer->invalidate('passwd', __('Insert your new Password'));
            $error ++;
        }

        if (! preg_match(PASSWORD_REGEX, $passwordNew1)) {
            $errorMessage = __('The length of the password should be from 6 - 32 Chars');
            $this->Customer->invalidate('passwd_new_1', $errorMessage);
            $this->Customer->invalidate('passwd_new_2', $errorMessage);
            $error ++;
        }

        if ($passwordNew1 != $passwordNew2) {
            $errorMessage = __('the new passwords are not the same');
            $this->Customer->invalidate('passwd_new_1', $errorMessage);
            $this->Customer->invalidate('passwd_new_2', $errorMessage);
            $error ++;
        }

        if ($password == $ph->hash($passwordNew1)) {
            $errorMessage = __('your new and your old Password dont differ, please change');
            $this->Customer->invalidate('passwd_new_1', $errorMessage);
            $this->Customer->invalidate('passwd_new_2', $errorMessage);
            $error ++;
        }

        if ($passwordNew1 == '') {
            $errorMessage = __('Please set your password');
            $this->Customer->invalidate('passwd_new_1', $errorMessage);
            $this->Customer->invalidate('passwd_new_2', $errorMessage);
            $error ++;
        }

        if ($error > 0) {
            $this->AppSession->setFlashError(__('An error occurred, please check your form.'));
            return;
        }

        $customer2save = array(
            'passwd' => $ph->hash($passwordNew1)
        );

        $this->Customer->id = $this->AppAuth->getUserId();
        if ($this->Customer->save($customer2save, false)) {
            if ($this->AppAuth->isManufacturer()) {
                $message = 'Der Hersteller ' . $this->AppAuth->getManufacturerName();
                $actionLogType = 'manufacturer_password_changed';
                $actionLogId = $this->AppAuth->getManufacturerId();
                $actionLogModel = 'manufacturers';
            } else {
                $message = 'Das Mitglied ' . $this->AppAuth->getUsername();
                $actionLogType = 'customer_password_changed';
                $actionLogId = $this->AppAuth->getUserId();
                $actionLogModel = 'customers';
            }
            $message .= ' hat sein Passwort geändert.';

            $this->loadModel('CakeActionLog');
            $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $actionLogId, $actionLogModel, $message);
            $this->AppSession->setFlashMessage(__('your new password successfully set'));
            $this->redirect($this->referer());
        }
    }

    public function profile()
    {
        $customerId = $this->AppAuth->getUserId();

        $this->Customer->recursive = 1;
        $unsavedCustomer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $customerId
            )
        ));

        $this->set('title_for_layout', 'Profil ändern');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedCustomer;
        } else {
            // validate data - do not use $this->Customer->saveAll()
            $this->Customer->id = $customerId;
            $this->Customer->set($this->request->data['Customer']);

            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['Customer'] as &$data) {
                $data = strip_tags(trim($data));
            }
            foreach ($this->request->data['AddressCustomer'] as &$data) {
                $data = strip_tags(trim($data));
            }

            $this->Customer->AddressCustomer->id = $unsavedCustomer['AddressCustomer']['id_address'];
            // also update email, firstname and lastname in adress record
            $this->request->data['AddressCustomer']['firstname'] = $this->request->data['Customer']['firstname'];
            $this->request->data['AddressCustomer']['lastname'] = $this->request->data['Customer']['lastname'];
            $this->request->data['AddressCustomer']['email'] = $this->request->data['Customer']['email'];

            $this->Customer->AddressCustomer->set($this->request->data['AddressCustomer']);

            $errors = array();
            if (! $this->Customer->validates()) {
                $errors = array_merge($errors, $this->Customer->validationErrors);
            }

            if (! $this->Customer->AddressCustomer->validates()) {
                $errors = array_merge($errors, $this->Customer->AddressCustomer->validationErrors);
            }

            if (empty($errors)) {
                $this->Customer->save($this->request->data['Customer'], array(
                    'validate' => false
                ));
                $this->Customer->AddressCustomer->save($this->request->data['Customer'], array(
                    'validate' => false
                ));

                $this->renewAuthSession();

                $this->loadModel('CakeActionLog');
                $message = 'Das Mitglied ' . $unsavedCustomer['Customer']['name'] . ' hat sein Profil geändert.';
                $this->CakeActionLog->customSave('customer_profile_changed', $this->AppAuth->getUserId(), $customerId, 'customers', $message);

                $this->AppSession->setFlashMessage('Deine Änderungen wurden erfolgreich gepeichert.');
                $this->redirect($this->referer());
            } else {
                $this->AppSession->setFlashError('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }

    /**
     * generates pdf on-the-fly
     */
    private function generateTermsOfUsePdf($customer)
    {
        $this->set('customer', $customer);
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        return $this->render('generateTermsOfUsePdf');
    }

    public function changeStatus($customerId, $status, $sendEmail)
    {
        if (! in_array($status, array(
            APP_OFF,
            APP_ON
        ))) {
            throw new MissingActionException('Status muss 0 oder 1 sein!');
        }

        $this->Customer->id = $customerId;
        $this->Customer->save(array(
            'active' => $status
        ));

        $this->Customer->recursive = - 1;
        $customer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $customerId
            )
        ));

        $statusText = 'deaktiviert';
        $actionLogType = 'customer_set_inactive';
        if ($status) {
            $statusText = 'aktiviert';
            $actionLogType = 'customer_set_active';
        }

        $message = 'Das Mitglied "' . $customer['Customer']['name'] . '" wurde erfolgreich ' . $statusText;

        if ($sendEmail) {
            // set new password
            $newPassword = $this->Customer->setNewPassword($customer['Customer']['id_customer']);

            $email = new AppEmail();
            $email->template('customer_activated')
                ->emailFormat('html')
                ->to($customer['Customer']['email'])
                ->subject('Dein Mitgliedskonto wurde aktiviert.')
                ->viewVars(array(
                'appAuth' => $this->AppAuth,
                'data' => $customer,
                'newPassword' => $newPassword
                ));

            $email->addAttachments(array('Nutzungsbedingungen.pdf' => array('data' => $this->generateTermsOfUsePdf($customer['Customer']))));
            $email->send();

            $message .= ' und eine Info-Mail an ' . $customer['Customer']['email'] . ' versendet';
        }

        $message .= '.';

        $this->AppSession->setFlashMessage($message);

        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $customerId, 'customer', $message);

        $this->redirect($this->referer());
    }

    public function editComment()
    {
        $this->RequestHandler->renderAs($this, 'ajax');

        $customerId = $this->params['data']['customerId'];
        $customerComment = htmlspecialchars_decode($this->params['data']['customerComment']);

        $oldCustomer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.id_customer' => $customerId
            )
        ));

        $customerAddress2update = array(
            'other' => $customerComment
        );

        $this->Customer->AddressCustomer->id = $oldCustomer['AddressCustomer']['id_address'];
        $this->Customer->AddressCustomer->save($customerAddress2update);

        $this->AppSession->setFlashMessage('Der Kommentar wurde erfolgreich geändert.');

        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave('customer_comment_changed', $this->AppAuth->getUserId(), $customerId, 'customers', 'Der Kommentar des Mitglieds "' . $oldCustomer['Customer']['name'] . '" wurde geändert: <br /><br /> alt: <div class="changed">' . $oldCustomer['AddressCustomer']['other'] . '</div>neu: <div class="changed">' . $customerComment . ' </div>');

        die(json_encode(array(
            'status' => 1,
            'msg' => 'ok'
        )));
    }

    public function index()
    {
        $active = 1; // default value
        if (isset($this->params['named']['active'])) { // klappt bei orderState auch mit !empty( - hier nicht... strange
            $active = $this->params['named']['active'];
        }
        $this->set('active', $active);

        $validOrdersCountFrom = ''; // default value
        if (isset($this->params['named']['validOrdersCountFrom'])) {
            $validOrdersCountFrom = $this->params['named']['validOrdersCountFrom'];
        }
        $this->set('validOrdersCountFrom', $validOrdersCountFrom);

        $validOrdersCountTo = ''; // default value
        if (isset($this->params['named']['validOrdersCountTo'])) {
            $validOrdersCountTo = $this->params['named']['validOrdersCountTo'];
        }
        $this->set('validOrdersCountTo', $validOrdersCountTo);

        $dateFrom = '';
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = '';
        if (! empty($this->params['named']['dateTo'])) {
            $dateTo = $this->params['named']['dateTo'];
        }
        $this->set('dateTo', $dateTo);

        $conditions = array();
        if ($active != 'all') {
            $conditions = array(
                'Customer.active' => $active
            );
        }

        $conditions[] = $this->Customer->getConditionToExcludeHostingUser();

        $this->Customer->hasMany['ValidOrder']['limit'] = null; // to get all valid orders
        $this->Customer->dropManufacturersInNextFind();
        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'Customer.name' => 'ASC'
            ),
            'contain' => array(
                'Customer.*',
                'ValidOrder.*'
            )
        ), $this->Paginator->settings);

        $customers = $this->Paginator->paginate('Customer');

        $i = 0;
        $this->loadModel('CakePayment');
        $this->loadModel('Order');
        foreach ($customers as $customer) {
            if (Configure::read('htmlHelper')->paymentIsCashless()) {
                $paymentProductSum = $this->CakePayment->getSum($customer['Customer']['id_customer'], 'product');
                $paymentPaybackSum = $this->CakePayment->getSum($customer['Customer']['id_customer'], 'payback');
                $paymentDepositSum = $this->CakePayment->getSum($customer['Customer']['id_customer'], 'deposit');

                $sumTotalProduct = 0;
                $sumTotalDeposit = 0;
                foreach ($customer['PaidCashFreeOrders'] as $paidCashFreeOrder) {
                    $sumTotalProduct += $paidCashFreeOrder['total_paid'];
                    if (Configure::read('app.isDepositPaymentCashless') && strtotime($paidCashFreeOrder['date_add']) > strtotime(Configure::read('app.depositPaymentCashlessStartDate'))) {
                        $sumTotalDeposit += $paidCashFreeOrder['total_deposit'];
                    }
                }
                // sometimes strange values like 2.8421709430404E-14 appear
                $customers[$i]['payment_product_delta'] = round($paymentProductSum - $paymentPaybackSum - $sumTotalProduct, 2);
                $customers[$i]['payment_deposit_delta'] = round($paymentDepositSum - $sumTotalDeposit, 2);

                // combine deposit delta in product delta to show same credit balance in list like in personal payment product page
                if (Configure::read('app.isDepositPaymentCashless')) {
                    $customers[$i]['payment_product_delta'] += $customers[$i]['payment_deposit_delta'];
                }
            }

            $customers[$i]['order_count'] = $this->Order->getCountByCustomerId($customer['Customer']['id_customer']);

            $voc = count($customer['ValidOrder']);
            $customers[$i]['valid_orders_count'] = $voc;

            $customers[$i]['last_valid_order_date'] = '';

            $validOrdersCountCondition = true;
            if ($validOrdersCountFrom != '') {
                $validOrdersCountCondition = $voc >= $validOrdersCountFrom;
            }
            if ($validOrdersCountTo != '') {
                if ($validOrdersCountCondition === true) {
                    $validOrdersCountCondition = $voc <= $validOrdersCountTo;
                }
            }

            if (! $validOrdersCountCondition) {
                unset($customers[$i]);
                $i ++;
                continue;
            }

            if (isset($customer['ValidOrder'][$voc - 1])) {
                $lastOrderDate = $customer['ValidOrder'][$voc - 1]['date_add'];
                $lastOrderDate = substr($lastOrderDate, 0, 10); // russian style... do not use time!

                $lastOrderDateCondition = true;
                if ($dateFrom != '') {
                    $lastOrderDateCondition = strtotime($dateFrom) <= strtotime($lastOrderDate);
                }
                if ($dateTo != '') {
                    if ($lastOrderDateCondition === true) {
                        $lastOrderDateCondition = strtotime($dateTo) >= strtotime($lastOrderDate);
                    }
                }

                $customers[$i]['last_valid_order_date'] = $lastOrderDate;

                if (! $lastOrderDateCondition) {
                    unset($customers[$i]);
                }
            }

            $i ++;
        }
        $this->set('customers', $customers);

        $this->set('manufacturerDepositMoneySum', $this->CakePayment->getManufacturerDepositMoneySum());
        $this->set('title_for_layout', 'Mitglieder');
    }
}
