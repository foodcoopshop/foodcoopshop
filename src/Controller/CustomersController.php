<?php

App::uses('FrontendController', 'Controller');

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
class CustomersController extends FrontendController
{

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->AppAuth->allow('login', 'logout', 'new_password_request', 'registration_successful');
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

    public function acceptUpdatedTermsOfUse()
    {

        if (!$this->request->is('post')) {
            $this->redirect('/');
        }

        $checkboxErrors = false;
        if (!isset($this->request->data['Customer']['terms_of_use_accepted_date']) || $this->request->data['Customer']['terms_of_use_accepted_date'] != 1) {
            $this->Customer->invalidate('terms_of_use_accepted_date', 'Bitte akzeptiere die Nutzungsbedingungen.');
            $checkboxErrors = true;
        }

        $this->Customer->set($this->request->data['Customer']);
        if (!$checkboxErrors) {
            $this->Customer->id = $this->AppAuth->getUserId();
            $this->request->data['Customer']['terms_of_use_accepted_date'] = date('Y-m-d');
            $this->Customer->save($this->request->data['Customer'], false);
            $this->Flash->success('Das Akzeptieren der Nutzungsbedingungen wurde gespeichert. Vielen Dank.');
            $this->renewAuthSession();
            $this->redirect($this->referer());
        } else {
            $this->Flash->error('Bitte akzeptiere die Nutzungsbedingungen.');
            $this->set('title_for_layout', 'Nutzungsbedingungen akzeptieren');
        }
    }

    public function newPasswordRequest()
    {
        $this->set(array(
            'title_for_layout' => 'Neues Passwort anfordern'
        ));

        if (empty($this->request->data)) {
            return;
        }

        $this->Customer->set($this->request->data);

        unset($this->Customer->validate['email']['unique']); // unique not needed here

        if ($this->Customer->validates()) {
            $customer = $this->Customer->findByEmail($this->request->data['Customer']['email']);

            if (empty($customer)) {
                $this->Customer->invalidate('email', 'Wir haben diese E-Mail-Adresse nicht gefunden.');
                return false;
            }

            if ($customer['Customer']['active'] !== true) {
                $this->Flash->error('Dein Mitgliedskonto ist nicht mehr aktiv. Falls du es wieder aktivieren möchtest, schreib uns bitte eine E-Mail.');
                return false;
            }

            $changePasswordCode = StringComponent::createRandomString(12);
            $customer2save = array(
                'change_password_code' => $changePasswordCode
            );
            $this->Customer->id = $customer['Customer']['id_customer'];
            $this->Customer->save($customer2save);

            // send email
            $email = new AppEmail();
            $email->template('new_password_request_successful')
                ->emailFormat('html')
                ->subject('Anfrage für neues Passwort für ' . Configure::read('AppConfig.db_config_FCS_APP_NAME'))
                ->to($this->request->data['Customer']['email'])
                ->viewVars(array(
                'changePasswordCode' => $changePasswordCode,
                'customer' => $customer
                ));

            if ($email->send()) {
                $this->Flash->success('Wir haben dir einen Link zugeschickt, mit dem du dein neues Passwort generieren kannst.');
            }

            $this->redirect('/');
        }
    }

    public function generateNewPassword()
    {
        $changePasswordCode = $this->params['changePasswordCode'];

        if (!isset($changePasswordCode)) {
            throw new MissingActionException('change password code not passed');
        }

        $this->Customer->recursive = -1;
        $customer = $this->Customer->find('first', array(
            'conditions' => array(
                'Customer.change_password_code' => $changePasswordCode
            )
        ));

        if (empty($customer)) {
            throw new MissingActionException('change password code not found');
        }

        $newPassword = $this->Customer->setNewPassword($customer['Customer']['id_customer']);

        // send email
        $email = new AppEmail();
            $email->template('new_password_set_successful')
            ->emailFormat('html')
            ->subject('Neues Passwort für ' . Configure::read('AppConfig.db_config_FCS_APP_NAME') . ' generiert')
            ->to($customer['Customer']['email'])
            ->viewVars(array(
                'password' => $newPassword,
                'customer' => $customer
            ));


        if ($email->send()) {
            $this->Flash->success('Wir haben dir dein neues Passwort zugeschickt.');
        }

        // reset change password code
        $customer2save = array(
            'change_password_code' => null
        );
        $this->Customer->id = $customer['Customer']['id_customer'];
        $this->Customer->save($customer2save);

        $this->redirect(Configure::read('slugHelper')->getLogin());
    }

    public function login()
    {
        $this->set('title_for_layout', 'Anmelden');

        if (! $this->request->is('post') && $this->here == Configure::read('slugHelper')->getRegistration()) {
            $this->redirect(Configure::read('slugHelper')->getLogin());
        }

        /**
         * login start
         */
        if ($this->here == Configure::read('slugHelper')->getLogin()) {
            if ($this->AppAuth->loggedIn()) {
                $this->Flash->error('Du bist bereits angemeldet.');
            }
            if ($this->request->is('post')) {
                if ($this->AppAuth->login()) {
                    // was remember me checkbox selected? not set if login happens automatically in AppShell
                    if (isset($this->request->data['remember_me']) && $this->request->data['remember_me'] == 1) {
                        unset($this->request->data['remember_me']);
                        App::uses('AppPasswordHasher', 'Controller/Component/Auth');
                        $ph = new AppPasswordHasher();
                        $this->request->data['Customer']['passwd'] = $ph->hash($this->request->data['Customer']['passwd']);
                        $this->Cookie->write('remember_me_cookie', $this->request->data['Customer'], true, '6 days');
                    }

                    $this->redirect($this->AppAuth->redirect());
                } else {
                    $this->Flash->error('Anmelden ist fehlgeschlagen. Vielleicht ist dein Konto noch nicht aktiviert oder das Passwort stimmt nicht?');
                }
            }
        }

        /**
         * registration start
         */
        if ($this->here == Configure::read('slugHelper')->getRegistration()) {
            if ($this->AppAuth->loggedIn()) {
                $this->Flash->error('Du bist bereits angemeldet.');
                $this->redirect(Configure::read('slugHelper')->getLogin());
            }

            // prevent spam
            // http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches?lq=1
            if ($this->request->data['antiSpam'] == 'lalala' || $this->request->data['antiSpam'] < 3) {
                $this->Flash->error('S-p-a-m-!');
                $this->redirect(Configure::read('slugHelper')->getLogin());
            }

            if (! empty($this->request->data)) {
                // validate data - do not use $this->Customer->saveAll()
                $this->Customer->set($this->request->data['Customer']);

                // quick and dirty solution for stripping html tags, use html purifier here
                foreach ($this->request->data['Customer'] as &$data) {
                    $data = strip_tags(trim($data));
                }
                foreach ($this->request->data['AddressCustomer'] as &$data) {
                    $data = strip_tags(trim($data));
                }

                // create email, firstname and lastname in adress record
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

                $checkboxErrors = false;
                if (!isset($this->request->data['Customer']['terms_of_use_accepted_date']) || $this->request->data['Customer']['terms_of_use_accepted_date'] != 1) {
                    $this->Customer->invalidate('terms_of_use_accepted_date', 'Bitte akzeptiere die Nutzungsbedingungen.');
                    $checkboxErrors = true;
                }

                if (empty($errors) && !$checkboxErrors) {
                    // save customer
                    $this->Customer->id = null;
                    $this->request->data['Customer']['active'] = Configure::read('AppConfig.db_config_FCS_DEFAULT_NEW_MEMBER_ACTIVE');
                    $this->request->data['Customer']['id_default_group'] = Configure::read('AppConfig.db_config_FCS_CUSTOMER_GROUP');
                    $this->request->data['Customer']['terms_of_use_accepted_date'] = date('Y-m-d');

                    $newCustomer = $this->Customer->save($this->request->data['Customer'], array(
                        'validate' => false
                    ));

                    // set new password (after customer save!)
                    $newPassword = $this->Customer->setNewPassword($newCustomer['Customer']['id_customer']);
                    $this->request->data['Customer']['passwd'] = $newPassword;

                    // save address
                    $this->request->data['AddressCustomer']['id_customer'] = $newCustomer['Customer']['id_customer'];
                    $this->request->data['AddressCustomer']['id_country'] = Configure::read('AppConfig.countryId');
                    $this->Customer->AddressCustomer->set($this->request->data['AddressCustomer']);
                    $this->Customer->AddressCustomer->save($this->request->data['Customer'], array(
                        'validate' => false
                    ));

                    // write action log
                    $this->loadModel('ActionLog');
                    $message = 'Das Mitglied ' . $this->request->data['Customer']['firstname'] . ' ' . $this->request->data['Customer']['lastname'] . ' hat ein Mitgliedskonto erstellt.';
                    $this->ActionLog->customSave('customer_registered', $newCustomer['Customer']['id_customer'], $newCustomer['Customer']['id_customer'], 'customers', $message);

                    // START send confirmation email to customer
                    $attachments = array();
                    $email = new AppEmail();
                    if (Configure::read('AppConfig.db_config_FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                        $template = 'customer_registered_active';
                        $email->addAttachments(array('Nutzungsbedingungen.pdf' => array('data' => $this->generateTermsOfUsePdf($newCustomer['Customer']), 'mimetype' => 'application/pdf')));
                    } else {
                        $template = 'customer_registered_inactive';
                    }
                    $email->template($template)
                        ->emailFormat('html')
                        ->to($this->request->data['Customer']['email'])
                        ->subject('Willkommen')
                        ->viewVars(array(
                        'appAuth' => $this->AppAuth,
                        'data' => $this->request->data,
                        'newPassword' => $newPassword
                        ));
                    $email->send();
                    // END send confirmation email to customer

                    // START send notification email
                    if (! empty(Configure::read('AppConfig.registrationNotificationEmails'))) {
                        $email = new AppEmail();
                        $email->template('customer_registered_notification')
                            ->emailFormat('html')
                            ->to(Configure::read('AppConfig.registrationNotificationEmails'))
                            ->subject('Neue Registrierung: ' . $this->request->data['Customer']['firstname'] . ' ' . $this->request->data['Customer']['lastname'])
                            ->viewVars(array(
                            'appAuth' => $this->AppAuth,
                            'data' => $this->request->data
                            ))
                            ->send();
                    }
                    // END

                    $this->Flash->success('Deine Registrierung war erfolgreich.');
                    $this->redirect('/registrierung/abgeschlossen');
                } else {
                    $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
                }
            }
        }
    }

    public function registrationSuccessful()
    {
        $this->set('title_for_layout', 'Mitgliedskonto erfolgreich erstellt');

        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);
    }

    public function logout()
    {
        $this->Flash->success('Du hast dich erfolgreich abgemeldet.');
        $this->Cookie->delete('remember_me_cookie');
        $this->destroyShopOrderCustomer();
        $this->AppAuth->logout();
        $this->redirect('/');
    }
}
