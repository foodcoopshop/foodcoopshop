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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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

        if (!$this->getRequest()->is('post')) {
            $this->redirect('/');
        }

        $this->set('title_for_layout', __('Accept_terms_of_use'));

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $patchedEntity = $this->Customer->patchEntity(
            $this->Customer->get($this->AppAuth->getUserId()),
            [
                'Customers' => [
                    'terms_of_use_accepted_date_checkbox' => $this->getRequest()->getData('Customers.terms_of_use_accepted_date_checkbox'),
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
            $this->Flash->success(__('Accepting_the_terms_of_use_have_been_saved.'));
            $this->renewAuthSession();
            $this->redirect($this->referer());
        }
    }

    public function newPasswordRequest()
    {
        $this->set([
            'title_for_layout' => __('Request_new_password')
        ]);

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->newEntity();

        if (!empty($this->getRequest()->getData())) {

            $this->loadComponent('Sanitize');
            $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
            $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData())));

            $customer = $this->Customer->patchEntity(
                $customer,
                $this->getRequest()->getData(),
                [
                    'validate' => 'newPasswordRequest'
                ]
            );

            if (!empty($customer->getErrors())) {
                $this->Flash->error(__('Errors_while_saving!'));
            } else {
                $changePasswordCode = StringComponent::createRandomString(12);
                $originalPrimaryKey = $this->Customer->getPrimaryKey();
                $this->Customer->setPrimaryKey('email');
                $oldEntity = $this->Customer->get($this->getRequest()->getData('Customers.email'));
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
                    ->setSubject(__('Request_for_new_password_for_{0}', [Configure::read('appDb.FCS_APP_NAME')]))
                    ->setTo($this->getRequest()->getData('Customers.email'))
                    ->setViewVars([
                        'changePasswordCode' => $changePasswordCode,
                        'customer' => $oldEntity
                    ]);

                if ($email->send()) {
                    $this->Flash->success(__('We_sent_a_link_to_you_for_generating_a_new_password.'));
                }

                $this->redirect('/');
            }
        }

        $this->set('customer', $customer);
    }

    public function generateNewPassword()
    {
        $changePasswordCode = $this->getRequest()->getParam('pass')[0];

        if (!isset($changePasswordCode)) {
            throw new RecordNotFoundException('change password code not passed');
        }

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
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
            ->setSubject(__('Generated_new_password_for_{0}', [Configure::read('appDb.FCS_APP_NAME')]))
            ->setTo($customer->email)
            ->setViewVars([
                'password' => $newPassword,
                'customer' => $customer
            ]);

        if ($email->send()) {
            $this->Flash->success(__('We_sent_a_new_password_to_you.'));
        }

        $this->redirect(Configure::read('app.slugHelper')->getLogin());
    }

    public function login()
    {
        $this->set('title_for_layout', __('Sign_in'));

        /**
         * login start
         */
        if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getLogin()) {
            if ($this->AppAuth->user()) {
                $this->Flash->error(__('You_are_already_signed_in.'));
            }

            if ($this->getRequest()->is('post')) {
                $user = $this->AppAuth->identify();
                if ($user) {
                    $this->AppAuth->setUser($user);
                    $this->redirect($this->AppAuth->redirectUrl());
                } else {
                    $this->Flash->error(__('Signing_in_failed_account_inactive_or_password_wrong?'));
                }

                if (!empty($this->getRequest()->getData('remember_me')) && $this->getRequest()->getData('remember_me')) {
                    $ph = new AppPasswordHasher();
                    $cookie = (new Cookie('remember_me'))
                    ->withValue(
                        [
                            'passwd' => $ph->hash($this->getRequest()->getData('passwd')),
                            'email' => $this->getRequest()->getData('email')
                        ]
                    )
                    ->withExpiry(new DateTime('+6 day'));
                    $this->setResponse($this->getResponse()->withCookie($cookie));
                }
            }
        }

        /**
         * registration start
         */
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
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

        if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getRegistration()) {
            // prevent spam
            // http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches?lq=1
            if (!empty($this->getRequest()->getData()) && ($this->getRequest()->getData('antiSpam') == 'lalala' || $this->getRequest()->getData('antiSpam') < 3)) {
                $this->Flash->error('S-p-a-m-!');
                $this->redirect(Configure::read('app.slugHelper')->getLogin());
                return;
            }

            if ($this->AppAuth->user()) {
                $this->Flash->error(__('You_are_already_signed_in.'));
                $this->redirect(Configure::read('app.slugHelper')->getLogin());
            }

            if (! empty($this->getRequest()->getData())) {

                $this->loadComponent('Sanitize');
                $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
                $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData())));

                $this->setRequest($this->getRequest()->withData('Customers.email', $this->getRequest()->getData('Customers.address_customer.email')));
                $this->setRequest($this->getRequest()->withData('Customers.address_customer.firstname', $this->getRequest()->getData('Customers.firstname')));
                $this->setRequest($this->getRequest()->withData('Customers.address_customer.lastname', $this->getRequest()->getData('Customers.lastname')));

                $customer = $this->Customer->patchEntity(
                    $customer,
                    $this->getRequest()->getData(),
                    [
                        'validate' => 'registration',
                        'associated' => [
                            'AddressCustomers'
                        ]
                    ]
                );

                if (!empty($customer->getErrors())) {
                    $this->Flash->error(__('Errors_while_saving!'));
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
                    $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
                    $message = __('Member_{0}_created_an_account.', [$this->getRequest()->getData('Customers.firstname') . ' ' . $this->getRequest()->getData('Customers.lastname')]);

                    $this->ActionLog->customSave('customer_registered', $newCustomer->id_customer, $newCustomer->id_customer, 'customers', $message);

                    // START send confirmation email to customer
                    $email = new AppEmail();
                    if (Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                        $template = 'customer_registered_active';
                        $email->addAttachments([__('Filename_Terms-of-use').'.pdf' => ['data' => $this->generateTermsOfUsePdf($newCustomer), 'mimetype' => 'application/pdf']]);
                    } else {
                        $template = 'customer_registered_inactive';
                    }
                    $email->setTemplate($template)
                        ->setTo($this->getRequest()->getData('Customers.address_customer.email'))
                        ->setSubject(__('Welcome'))
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
                            ->setSubject(__('New_registration_{0}', [$newCustomer->firstname . ' ' . $newCustomer->lastname]))
                            ->setViewVars([
                            'appAuth' => $this->AppAuth,
                                'data' => $newCustomer
                            ])
                            ->send();
                    }
                    // END

                    $this->Flash->success(__('Your_registration_was_successful.'));
                    $this->redirect(Configure::read('app.slugHelper')->getRegistrationSuccessful());
                }
            }
        }
        $this->set('customer', $customer);
    }

    public function registrationSuccessful()
    {
        $this->set('title_for_layout', __('Account_created_successfully'));

        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth);
        $this->set('blogPosts', $blogPosts);
    }

    public function logout()
    {
        $this->Flash->success(__('You_have_been_signed_out.'));
        $this->response = $this->response->withCookie((new Cookie('remember_me')));
        $this->destroyInstantOrderCustomer();
        $this->AppAuth->logout();
        $this->redirect('/');
    }
}
