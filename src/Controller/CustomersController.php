<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Controller\Exception\MissingActionException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

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

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
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
        if (!isset($this->request->data['Customers']['terms_of_use_accepted_date']) || $this->request->data['Customers']['terms_of_use_accepted_date'] != 1) {
            $this->Customer->invalidate('terms_of_use_accepted_date', 'Bitte akzeptiere die Nutzungsbedingungen.');
            $checkboxErrors = true;
        }

        $this->Customer->set($this->request->data['Customers']);
        if (!$checkboxErrors) {
            $this->Customer->id = $this->AppAuth->getUserId();
            $this->request->data['Customers']['terms_of_use_accepted_date'] = date('Y-m-d');
            $this->Customer->save($this->request->data['Customers'], false);
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
        $this->set([
            'title_for_layout' => 'Neues Passwort anfordern'
        ]);

        if (empty($this->request->data)) {
            return;
        }

        $this->Customer->set($this->request->data);

        unset($this->Customer->validate['email']['unique']); // unique not needed here

        if ($this->Customer->validates()) {
            $customer = $this->Customer->findByEmail($this->request->data['Customers']['email']);

            if (empty($customer)) {
                $this->Customer->invalidate('email', 'Wir haben diese E-Mail-Adresse nicht gefunden.');
                return false;
            }

            if ($customer['Customers']['active'] !== true) {
                $this->Flash->error('Dein Mitgliedskonto ist nicht mehr aktiv. Falls du es wieder aktivieren möchtest, schreib uns bitte eine E-Mail.');
                return false;
            }

            $changePasswordCode = StringComponent::createRandomString(12);
            $customer2save = [
                'change_password_code' => $changePasswordCode
            ];
            $this->Customer->id = $customer['Customers']['id_customer'];
            $this->Customer->save($customer2save);

            // send email
            $email = new AppEmail();
            $email->template('new_password_request_successful')
                ->emailFormat('html')
                ->subject('Anfrage für neues Passwort für ' . Configure::read('AppConfigDb.FCS_APP_NAME'))
                ->to($this->request->data['Customers']['email'])
                ->viewVars([
                'changePasswordCode' => $changePasswordCode,
                'customer' => $customer
                ]);

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

        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.change_password_code' => $changePasswordCode
            ]
        ])->first();

        if (empty($customer)) {
            throw new MissingActionException('change password code not found');
        }

        $newPassword = $this->Customer->setNewPassword($customer['Customers']['id_customer']);

        // send email
        $email = new AppEmail();
            $email->template('new_password_set_successful')
            ->emailFormat('html')
            ->subject('Neues Passwort für ' . Configure::read('AppConfigDb.FCS_APP_NAME') . ' generiert')
            ->to($customer['Customers']['email'])
            ->viewVars([
                'password' => $newPassword,
                'customer' => $customer
            ]);


        if ($email->send()) {
            $this->Flash->success('Wir haben dir dein neues Passwort zugeschickt.');
        }

        // reset change password code
        $customer2save = [
            'change_password_code' => null
        ];
        $this->Customer->id = $customer['Customers']['id_customer'];
        $this->Customer->save($customer2save);

        $this->redirect(Configure::read('AppConfig.slugHelper')->getLogin());
    }

    public function login()
    {
        $this->set('title_for_layout', 'Anmelden');
        
        if (! $this->request->is('post') && $this->request->here == Configure::read('AppConfig.slugHelper')->getRegistration()) {
            $this->redirect(Configure::read('AppConfig.slugHelper')->getLogin());
        }

        /**
         * login start
         */
        if ($this->request->here == Configure::read('AppConfig.slugHelper')->getLogin()) {
            if ($this->AppAuth->user()) {
                $this->Flash->error('Du bist bereits angemeldet.');
            }
            
            if ($this->request->is('post')) {
                $user = $this->AppAuth->identify();
                if ($user) {
                    $this->AppAuth->setUser($user);
                    $this->Flash->success('Du hast dich erfolgreich angemeldet.');
                }
                // was remember me checkbox selected? not set if login happens automatically in AppShell
                /*
                if (isset($this->request->data['remember_me']) && $this->request->data['remember_me'] == 1) {
                    unset($this->request->data['remember_me']);
                    $ph = new AppPasswordHasher();
                    $this->request->data['Customers']['passwd'] = $ph->hash($this->request->data['Customers']['passwd']);
                    $this->Cookie->write('remember_me_cookie', $this->request->data['Customers'], true, '6 days');
                }
                */
                $this->redirect($this->AppAuth->redirectUrl());
            }
                
        }

        /**
         * registration start
         */
        if ($this->request->here == Configure::read('AppConfig.slugHelper')->getRegistration()) {
            if ($this->AppAuth->user()) {
                $this->Flash->error('Du bist bereits angemeldet.');
                $this->redirect(Configure::read('AppConfig.slugHelper')->getLogin());
            }

            // prevent spam
            // http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches?lq=1
            if ($this->request->data['antiSpam'] == 'lalala' || $this->request->data['antiSpam'] < 3) {
                $this->Flash->error('S-p-a-m-!');
                $this->redirect(Configure::read('AppConfig.slugHelper')->getLogin());
            }

            if (! empty($this->request->data)) {
                // validate data - do not use $this->Customer->saveAll()
                $this->Customer->set($this->request->data['Customers']);

                // quick and dirty solution for stripping html tags, use html purifier here
                foreach ($this->request->data['Customers'] as &$data) {
                    $data = strip_tags(trim($data));
                }
                foreach ($this->request->data['AddressCustomer'] as &$data) {
                    $data = strip_tags(trim($data));
                }

                // create email, firstname and lastname in adress record
                $this->request->data['AddressCustomer']['firstname'] = $this->request->data['Customers']['firstname'];
                $this->request->data['AddressCustomer']['lastname'] = $this->request->data['Customers']['lastname'];
                $this->request->data['AddressCustomer']['email'] = $this->request->data['Customers']['email'];
                $this->Customer->AddressCustomer->set($this->request->data['AddressCustomer']);

                $errors = [];
                if (! $this->Customer->validates()) {
                    $errors = array_merge($errors, $this->Customer->validationErrors);
                }

                if (! $this->Customer->AddressCustomer->validates()) {
                    $errors = array_merge($errors, $this->Customer->AddressCustomer->validationErrors);
                }

                $checkboxErrors = false;
                if (!isset($this->request->data['Customers']['terms_of_use_accepted_date']) || $this->request->data['Customers']['terms_of_use_accepted_date'] != 1) {
                    $this->Customer->invalidate('terms_of_use_accepted_date', 'Bitte akzeptiere die Nutzungsbedingungen.');
                    $checkboxErrors = true;
                }

                if (empty($errors) && !$checkboxErrors) {
                    // save customer
                    $this->Customer->id = null;
                    $this->request->data['Customers']['active'] = Configure::read('AppConfigDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE');
                    $this->request->data['Customers']['id_default_group'] = Configure::read('AppConfigDb.FCS_CUSTOMER_GROUP');
                    $this->request->data['Customers']['terms_of_use_accepted_date'] = date('Y-m-d');

                    $newCustomer = $this->Customer->save($this->request->data['Customers'], [
                        'validate' => false
                    ]);

                    // set new password (after customer save!)
                    $newPassword = $this->Customer->setNewPassword($newCustomer['Customers']['id_customer']);
                    $this->request->data['Customers']['passwd'] = $newPassword;

                    // save address
                    $this->request->data['AddressCustomer']['id_customer'] = $newCustomer['Customers']['id_customer'];
                    $this->request->data['AddressCustomer']['id_country'] = Configure::read('AppConfig.countryId');
                    $this->Customer->AddressCustomer->set($this->request->data['AddressCustomer']);
                    $this->Customer->AddressCustomer->save($this->request->data['Customers'], [
                        'validate' => false
                    ]);

                    // write action log
                    $this->ActionLog = TableRegistry::get('ActionLogs');
                    $message = 'Das Mitglied ' . $this->request->data['Customers']['firstname'] . ' ' . $this->request->data['Customers']['lastname'] . ' hat ein Mitgliedskonto erstellt.';
                    $this->ActionLog->customSave('customer_registered', $newCustomer['Customers']['id_customer'], $newCustomer['Customers']['id_customer'], 'customers', $message);

                    // START send confirmation email to customer
                    $email = new AppEmail();
                    if (Configure::read('AppConfigDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                        $template = 'customer_registered_active';
                        $email->addAttachments(['Nutzungsbedingungen.pdf' => ['data' => $this->generateTermsOfUsePdf($newCustomer['Customers']), 'mimetype' => 'application/pdf']]);
                    } else {
                        $template = 'customer_registered_inactive';
                    }
                    $email->template($template)
                        ->emailFormat('html')
                        ->to($this->request->data['Customers']['email'])
                        ->subject('Willkommen')
                        ->viewVars([
                        'appAuth' => $this->AppAuth,
                        'data' => $this->request->data,
                        'newPassword' => $newPassword
                        ]);
                    $email->send();
                    // END send confirmation email to customer

                    // START send notification email
                    if (! empty(Configure::read('AppConfig.registrationNotificationEmails'))) {
                        $email = new AppEmail();
                        $email->template('customer_registered_notification')
                            ->emailFormat('html')
                            ->to(Configure::read('AppConfig.registrationNotificationEmails'))
                            ->subject('Neue Registrierung: ' . $this->request->data['Customers']['firstname'] . ' ' . $this->request->data['Customers']['lastname'])
                            ->viewVars([
                            'appAuth' => $this->AppAuth,
                            'data' => $this->request->data
                            ])
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

        $this->BlogPost = TableRegistry::get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);
    }

    public function logout()
    {
        $this->Flash->success('Du hast dich erfolgreich abgemeldet.');
        //$this->Cookie->delete('remember_me_cookie');
        $this->destroyShopOrderCustomer();
        $this->AppAuth->logout();
        $this->redirect('/');
    }
}
