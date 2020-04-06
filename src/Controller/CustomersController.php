<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Mailer\AppMailer;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Http\Cookie\Cookie;
use Cake\I18n\Date;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use DateTime;
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
class CustomersController extends FrontendController
{
    
    public function profileImage()
    {
        if (!$this->AppAuth->user() || $this->AppAuth->isManufacturer() || empty($this->request->getParam('imageSrc'))) {
            throw new NotFoundException('image not found');
        }
        
        // customer exists check (if customer was deleted and somehow files were not deleted)
        $customerId = explode('-', $this->request->getParam('imageSrc'));
        $customerId = $customerId[0];
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.id_customer' => $customerId
            ],
        ])->first();
        if (empty($customer)) {
            throw new NotFoundException('image not found');
        }
        
        $this->RequestHandler->renderAs($this, 'jpg');
        $imagePath = Configure::read('app.customerImagesDir') . DS . $this->request->getParam('imageSrc') . '.jpg';
        if (!file_exists($imagePath)) {
            throw new NotFoundException('image not found');
        }
        $this->set('imagePath', $imagePath);
    }

    /**
     * generates pdf on-the-fly
     */
    private function generateTermsOfUsePdf($customer)
    {
        $this->set('customer', $customer);
        $this->set('saveParam', 'I');
        $this->RequestHandler->renderAs($this, 'pdf');
        $response = $this->render('generateTermsOfUsePdf');
        return $response->__toString();
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
        $customer = $this->Customer->newEntity([]);

        if (!empty($this->getRequest()->getData())) {

            $this->loadComponent('Sanitize');
            $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
            $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

            $customer = $this->Customer->patchEntity(
                $customer,
                $this->getRequest()->getData(),
                [
                    'validate' => 'newPasswordRequest'
                ]
            );

            if ($customer->hasErrors()) {
                $this->Flash->error(__('Errors_while_saving!'));
            } else {
                
                $originalPrimaryKey = $this->Customer->getPrimaryKey();
                $this->Customer->setPrimaryKey('email');
                $oldEntity = $this->Customer->get($this->getRequest()->getData('Customers.email'));
                $activateNewPasswordCode = $oldEntity->activate_new_password_code;
                $data2save = [];
                
                if ($activateNewPasswordCode == '') {
                    $activateNewPasswordCode = StringComponent::createRandomString(12);
                    $data2save['activate_new_password_code'] = $activateNewPasswordCode;
                }
                
                // always generate new password as it's saved as hash and cannot be sent in clear text
                $tmpNewPassword = StringComponent::createRandomString(12);
                $ph = new DefaultPasswordHasher();
                $data2save['tmp_new_passwd'] = $ph->hash($tmpNewPassword);
                
                $this->Customer->setPrimaryKey($originalPrimaryKey);
                $patchedEntity = $this->Customer->patchEntity(
                    $oldEntity,
                    $data2save
                );
                $this->Customer->save($patchedEntity);
                
                // send email
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('new_password_request_successful');
                $email->setSubject(__('New_password_for_{0}', [Configure::read('appDb.FCS_APP_NAME')]))
                    ->setTo($this->getRequest()->getData('Customers.email'))
                    ->setViewVars([
                        'activateNewPasswordCode' => $activateNewPasswordCode,
                        'tmpNewPassword' => $tmpNewPassword,
                        'customer' => $oldEntity
                    ]);

                if ($email->send()) {
                    $this->Flash->success(__('We_sent_your_new_password_to_you_it_needs_to_be_activated.'));
                }

                $this->redirect('/');
            }
        }

        $this->set('customer', $customer);
    }

    public function activateNewPassword()
    {
        $activateNewPasswordCode = $this->getRequest()->getParam('pass')[0];

        if (!isset($activateNewPasswordCode)) {
            throw new RecordNotFoundException('activate new password code not passed');
        }

        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', [
            'conditions' => [
                'Customers.activate_new_password_code' => $activateNewPasswordCode
            ],
        ])->first();

        if (empty($customer)) {
            $this->Flash->success(__('Your_new_password_was_already_activated_or_the_activation_code_was_not_valid.'));
        } else {
        
            $patchedEntity = $this->Customer->patchEntity(
                $customer,
                [
                    'passwd' => $customer->tmp_new_passwd,
                    'tmp_new_passwd' => null,
                    'activate_new_password_code' => null
                ]
            );
            $this->Customer->save($patchedEntity);
            $this->AppAuth->setUser($patchedEntity);
            $this->Flash->success(__('Your_new_password_was_successfully_activated.'));
        }
        
        $this->redirect('/');
    }

    public function login()
    {
        $this->Customer = TableRegistry::getTableLocator()->get('Customers');
        $title = __('Sign_in');
        $enableRegistrationForm = true;
        $enableBarCodeLogin = false;
        $enableAutoLogin = true;
        
        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')
            && ($this->AppAuth->isSelfServiceModeByUrl() || $this->AppAuth->isSelfServiceModeByReferer())
            ) {
                $this->viewBuilder()->setLayout('self_service');
                $title = __('Sign_in_for_self_service');
                $enableRegistrationForm = false;
                $enableBarCodeLogin = true;
                $enableAutoLogin = false;
            }
        $this->set('enableRegistrationForm', $enableRegistrationForm);
        $this->set('enableBarCodeLogin', $enableBarCodeLogin);
        $this->set('enableAutoLogin', $enableAutoLogin);
        
        $this->set('title_for_layout', $title);
        
        /**
         * login start
         */
        if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getLogin()) {
            if ($this->AppAuth->user()) {
                $this->Flash->error(__('You_are_already_signed_in.'));
            }

            if ($this->getRequest()->is('post')) {
                $customer = $this->AppAuth->identify();
                if ($customer) {
                    $this->AppAuth->setUser($customer);
                    $this->redirect($this->AppAuth->redirectUrl());
                } else {
                    $this->Flash->error(__('Signing_in_failed_account_inactive_or_password_wrong?'));
                }

                if (!empty($this->getRequest()->getData('remember_me')) && $this->getRequest()->getData('remember_me') && !is_null($customer['id_customer'])) {
                    $customer = $this->Customer->get($customer['id_customer']);
                    if ($customer->auto_login_hash == '') {
                        $customer->auto_login_hash = Security::hash(rand());
                        $this->Customer->save($customer);
                    }
                    $cookie = (new Cookie('remember_me'))
                    ->withValue(
                        [
                            'auto_login_hash' => $customer->auto_login_hash
                        ]
                    )
                    ->withExpiry(new DateTime('+30 day'));
                    $this->setResponse($this->getResponse()->withCookie($cookie));
                }
            }
        }

        /**
         * registration start
         */
        $ph = new DefaultPasswordHasher();
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
            // prevent spam: http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches?lq=1
            if (!empty($this->getRequest()->getData()) && ($this->getRequest()->getData('antiSpam') == '' || $this->getRequest()->getData('antiSpam') < 3)) {
                $this->Flash->error('S-p-a-m-!');
                Log::write('error', 'potential registration spam attack');
                $this->redirect('/');
                return;
            }

            if ($this->AppAuth->user()) {
                $this->Flash->error(__('You_are_already_signed_in.'));
                $this->redirect(Configure::read('app.slugHelper')->getLogin());
            }

            if (! empty($this->getRequest()->getData())) {

                $this->loadComponent('Sanitize');
                $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
                $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

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

                if ($customer->hasErrors()) {
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
                    $email = new AppMailer();
                    if (Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                        $template = 'customer_registered_active';
                        if (Configure::read('app.termsOfUseEnabled')) {
                            $email->addAttachments([__('Filename_Terms-of-use').'.pdf' => ['data' => $this->generateTermsOfUsePdf($newCustomer), 'mimetype' => 'application/pdf']]);
                        }
                    } else {
                        $template = 'customer_registered_inactive';
                    }
                    $email->viewBuilder()->setTemplate($template);
                    $email->setTo($this->getRequest()->getData('Customers.address_customer.email'))
                        ->setSubject(__('Welcome'))
                        ->setViewVars([
                        'appAuth' => $this->AppAuth,
                        'data' => $newCustomer,
                        'newPassword' => $newPassword
                        ]);
                    $email->send();
                    // END send confirmation email to customer

                    // START send notification email
                    if (! empty(Configure::read('appDb.FCS_REGISTRATION_NOTIFICATION_EMAILS'))) {
                        $email = new AppMailer();
                        $email->viewBuilder()->setTemplate('customer_registered_notification');
                        $email->setTo(explode(',', Configure::read('appDb.FCS_REGISTRATION_NOTIFICATION_EMAILS')))
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
        $redirectUrl = '/';
        if ($this->request->getQuery('redirect')) {
            $redirectUrl = $this->request->getQuery('redirect');
        }
        $this->redirect($redirectUrl);
        
    }
}
