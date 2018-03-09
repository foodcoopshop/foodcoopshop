<?php

namespace App\Controller;

use App\Auth\AppPasswordHasher;
use App\Controller\Component\StringComponent;
use App\Mailer\AppEmail;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Cookie\Cookie;
use Cake\I18n\Date;
use Cake\ORM\TableRegistry;
use DateTime;

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

        $this->set('title_for_layout', 'Nutzungsbedingungen akzeptieren');

        $this->Customer = TableRegistry::get('Customers');
        $patchedEntity = $this->Customer->patchEntity(
            $this->Customer->get($this->AppAuth->getUserId()),
            [
                'Customers' => [
                    'terms_of_use_accepted_date_checkbox' => $this->request->getData('Customers.terms_of_use_accepted_date_checkbox'),
                    'terms_of_use_accepted_date' => Date::now()
                ]
            ],
            ['validate' => 'termsOfUse']
        );

        $errors = $patchedEntity->getErrors();
        if (isset($errors['terms_of_use_accepted_date'])) {
            $this->AppFlash->setFlashError($errors['terms_of_use_accepted_date']['equals']);
        }

        if (empty($errors)) {
            $this->Customer->save($patchedEntity);
            $this->Flash->success('Das Akzeptieren der Nutzungsbedingungen wurde gespeichert. Vielen Dank.');
            $this->renewAuthSession();
            $this->redirect($this->referer());
        }
    }

    public function newPasswordRequest()
    {
        $this->set([
            'title_for_layout' => 'Neues Passwort anfordern'
        ]);

        $this->Customer = TableRegistry::get('Customers');
        $customer = $this->Customer->newEntity();

        if (!empty($this->request->getData())) {
            $this->loadComponent('Sanitize');
            $this->request->data = $this->Sanitize->trimRecursive($this->request->getData());
            $this->request->data = $this->Sanitize->stripTagsRecursive($this->request->getData());

            $customer = $this->Customer->patchEntity(
                $customer,
                $this->request->getData(),
                [
                    'validate' => 'newPasswordRequest'
                ]
            );

            if (!empty($customer->getErrors())) {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            } else {
                $changePasswordCode = StringComponent::createRandomString(12);
                $originalPrimaryKey = $this->Customer->getPrimaryKey();
                $this->Customer->setPrimaryKey('email');
                $oldEntity = $this->Customer->get($this->request->getData('Customers.email'));
                $this->Customer->setPrimaryKey($originalPrimaryKey);
                $patchedEntity = $this->Customer->patchEntity(
                    $oldEntity,
                    [
                        'change_password_code' => $changePasswordCode
                    ]
                );
                $this->Customer->save($patchedEntity);

                // send email
                $email = new AppEmail();
                $email->setTemplate('new_password_request_successful')
                    ->setSubject('Anfrage für neues Passwort für ' . Configure::read('appDb.FCS_APP_NAME'))
                    ->setTo($this->request->getData('Customers.email'))
                    ->setViewVars([
                        'changePasswordCode' => $changePasswordCode,
                        'customer' => $oldEntity
                    ]);

                if ($email->send()) {
                    $this->Flash->success('Wir haben dir einen Link zugeschickt, mit dem du dein neues Passwort generieren kannst.');
                }

                $this->redirect('/');
            }
        }

        $this->set('customer', $customer);
    }

    public function generateNewPassword()
    {
        $changePasswordCode = $this->request->getParam('pass')[0];

        if (!isset($changePasswordCode)) {
            throw new RecordNotFoundException('change password code not passed');
        }

        $this->Customer = TableRegistry::get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.change_password_code' => $changePasswordCode
            ],
        ])->first();

        if (empty($customer)) {
            throw new RecordNotFoundException('change password code not found');
        }

        $newPassword = $this->Customer->setNewPassword($customer->id_customer);

        // send email
        $email = new AppEmail();
            $email->setTemplate('new_password_set_successful')
            ->setSubject('Neues Passwort für ' . Configure::read('appDb.FCS_APP_NAME') . ' generiert')
            ->setTo($customer->email)
            ->setViewVars([
                'password' => $newPassword,
                'customer' => $customer
            ]);

        if ($email->send()) {
            $this->Flash->success('Wir haben dir dein neues Passwort zugeschickt.');
        }

        $this->redirect(Configure::read('app.slugHelper')->getLogin());
    }

    public function login()
    {
        $this->set('title_for_layout', 'Anmelden');

        /**
         * login start
         */
        if ($this->request->here == Configure::read('app.slugHelper')->getLogin()) {
            if ($this->AppAuth->user()) {
                $this->Flash->error('Du bist bereits angemeldet.');
            }

            if ($this->request->is('post')) {
                $user = $this->AppAuth->identify();
                if ($user) {
                    $this->AppAuth->setUser($user);
                    $this->redirect($this->AppAuth->redirectUrl());
                } else {
                    $this->Flash->error('Anmelden ist fehlgeschlagen. Vielleicht ist dein Konto noch nicht aktiviert oder das Passwort stimmt nicht?');
                }

                if (!empty($this->request->getData('remember_me')) && $this->request->getData('remember_me')) {
                    $ph = new AppPasswordHasher();
                    $cookie = (new Cookie('remember_me'))
                    ->withValue(
                        [
                            'passwd' => $ph->hash($this->request->getData('passwd')),
                            'email' => $this->request->getData('email')
                        ]
                    )
                    ->withExpiry(new DateTime('+6 day'));
                    $this->response = $this->response->withCookie($cookie);
                }
            }
        }

        /**
         * registration start
         */
        $this->Customer = TableRegistry::get('Customers');
        $ph = new AppPasswordHasher();
        $newPassword = StringComponent::createRandomString(12);
        $customer = $this->Customer->newEntity(
            [
                'Customers' => [
                    'active' => Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE'),
                    'id_default_group' => Configure::read('appDb.FCS_CUSTOMER_GROUP'),
                    'terms_of_use_accepted_date' => Date::now(),
                    'passwd' => $ph->hash($newPassword)
                ]
            ]
        );

        if ($this->request->here == Configure::read('app.slugHelper')->getRegistration()) {
            // prevent spam
            // http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches?lq=1
            if (!empty($this->request->getData()) && ($this->request->getData('antiSpam') == 'lalala' || $this->request->getData('antiSpam') < 3)) {
                $this->Flash->error('S-p-a-m-!');
                $this->redirect(Configure::read('app.slugHelper')->getLogin());
                return;
            }

            if ($this->AppAuth->user()) {
                $this->Flash->error('Du bist bereits angemeldet.');
                $this->redirect(Configure::read('app.slugHelper')->getLogin());
            }

            if (! empty($this->request->getData())) {
                $this->loadComponent('Sanitize');
                $this->request->data = $this->Sanitize->trimRecursive($this->request->getData());
                $this->request->data = $this->Sanitize->stripTagsRecursive($this->request->getData());

                $this->request->data['Customers']['email'] = $this->request->getData('Customers.address_customer.email');
                $this->request->data['Customers']['address_customer']['firstname'] = $this->request->getData('Customers.firstname');
                $this->request->data['Customers']['address_customer']['lastname'] = $this->request->getData('Customers.lastname');

                $customer = $this->Customer->patchEntity(
                    $customer,
                    $this->request->getData(),
                    [
                        'validate' => 'registration',
                        'associated' => [
                            'AddressCustomers'
                        ]
                    ]
                );

                if (!empty($customer->getErrors())) {
                    $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
                } else {
                    $newCustomer = $this->Customer->save(
                        $customer,
                        [
                            'associated' => [
                                'AddressCustomers'
                            ]
                        ]
                    );

                    // write action log
                    $this->ActionLog = TableRegistry::get('ActionLogs');
                    $message = 'Das Mitglied ' . $this->request->getData('Customers.firstname') . ' ' . $this->request->getData('Customers.lastname') . ' hat ein Mitgliedskonto erstellt.';
                    $this->ActionLog->customSave('customer_registered', $newCustomer->id_customer, $newCustomer->id_customer, 'customers', $message);

                    // START send confirmation email to customer
                    $email = new AppEmail();
                    if (Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                        $template = 'customer_registered_active';
                        $email->addAttachments(['Nutzungsbedingungen.pdf' => ['data' => $this->generateTermsOfUsePdf($newCustomer), 'mimetype' => 'application/pdf']]);
                    } else {
                        $template = 'customer_registered_inactive';
                    }
                    $email->setTemplate($template)
                        ->setTo($this->request->getData('Customers.address_customer.email'))
                        ->setSubject('Willkommen')
                        ->setViewVars([
                        'appAuth' => $this->AppAuth,
                        'data' => $newCustomer,
                        'newPassword' => $newPassword
                        ]);
                    $email->send();
                    // END send confirmation email to customer

                    // START send notification email
                    if (! empty(Configure::read('app.registrationNotificationEmails'))) {
                        $email = new AppEmail();
                        $email->setTemplate('customer_registered_notification')
                            ->setTo(Configure::read('app.registrationNotificationEmails'))
                            ->setSubject('Neue Registrierung: ' . $newCustomer->firstname . ' ' . $newCustomer->lastname)
                            ->setViewVars([
                            'appAuth' => $this->AppAuth,
                                'data' => $newCustomer
                            ])
                            ->send();
                    }
                    // END

                    $this->Flash->success('Deine Registrierung war erfolgreich.');
                    $this->redirect('/registrierung/abgeschlossen');
                }
            }
        }
        $this->set('customer', $customer);
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
        $this->response = $this->response->withCookie((new Cookie('remember_me')));
        $this->destroyShopOrderCustomer();
        $this->AppAuth->logout();
        $this->redirect('/');
    }
}
