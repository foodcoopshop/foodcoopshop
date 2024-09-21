<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Services\PdfWriter\TermsOfUsePdfWriterService;
use App\Mailer\AppMailer;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Http\Exception\NotFoundException;
use App\Services\OrderCustomerService;
use App\Controller\Traits\RenewAuthSessionTrait;
use App\Services\SanitizeService;
use App\Model\Entity\Customer;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class CustomersController extends FrontendController
{

    protected $Customer;
    protected $BlogPost;
    protected $ActionLog;

    use RenewAuthSessionTrait;

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated([
            'login',
            'logout',
            'registration',
            'registrationSuccessful',
            'newPasswordRequest',
            'activateNewPassword',
            'activateEmailAddress',
            'profileImage',
        ]);

    }

    public function profileImage()
    {
        if ($this->identity === null || $this->identity->isManufacturer() || empty($this->request->getParam('imageSrc'))) {
            throw new NotFoundException('image not found');
        }

        // customer exists check (if customer was deleted and somehow files were not deleted)
        $customerId = explode('-', $this->request->getParam('imageSrc'));
        $customerId = $customerId[0];
        $extension = strtolower(pathinfo($this->request->getParam('imageSrc'), PATHINFO_EXTENSION));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', conditions: [
            'Customers.id_customer' => $customerId
        ])->first();
        if (empty($customer)) {
            throw new NotFoundException('customer not found');
        }

        $this->request = $this->request->withParam('_ext', $extension);
        $imagePath = Configure::read('app.customerImagesDir') . DS . $this->request->getParam('imageSrc');
        if (!file_exists($imagePath)) {
            throw new NotFoundException('image not found');
        }
        $this->set('imagePath', $imagePath);

        $response = $this->response->withType($extension);
        $response = $response->withStringBody(file_get_contents($imagePath));
        return $response;
    }

    private function generateTermsOfUsePdf()
    {
        $pdfWriter = new TermsOfUsePdfWriterService();
        return $pdfWriter->writeAttachment();
    }

    public function acceptUpdatedTermsOfUse()
    {

        if (!$this->getRequest()->is('post')) {
            $this->redirect('/');
        }

        $this->set('title_for_layout', __('Accept_terms_of_use'));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $patchedEntity = $this->Customer->patchEntity(
            $this->Customer->get($this->identity->getId()),
            [
                'Customers' => [
                    'terms_of_use_accepted_date_checkbox' => $this->getRequest()->getData('Customers.terms_of_use_accepted_date_checkbox'),
                    'terms_of_use_accepted_date' => date('Y-m-d'),
                ]
            ],
            ['validate' => 'termsOfUse']
        );

        $errors = $patchedEntity->getErrors();
        if (isset($errors['terms_of_use_accepted_date'])) {
            $this->Flash->error($errors['terms_of_use_accepted_date']['equals']);
        }

        if (empty($errors)) {
            $this->Customer->save($patchedEntity);
            $this->Flash->success(__('Accepting_the_terms_of_use_have_been_saved.'));
            $this->renewAuthSession();
            $this->redirect($this->referer());
        }
    }

    public function activateEmailAddress()
    {
        $emailActivationCode = h($this->getRequest()->getParam('pass')[0]);

        if (!isset($emailActivationCode)) {
            throw new RecordNotFoundException('activation code not passed');
        }

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all',
        conditions: [
            'Customers.activate_email_code' => $emailActivationCode,
        ],
        contain: [
            'AddressCustomers',
        ])->first();

        if (empty($customer)) {
            $this->Flash->success(__('Your_email_address_was_already_activated_or_the_activation_code_was_not_valid.'));
        } else {
            $customer->activate_email_code = null;
            $customer->active = 1;
            $this->Customer->save($customer);

            $newPassword = $this->Customer->setNewPassword($customer->id_customer);

            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('email_address_activated');
            $email->setTo($customer->email)
            ->setSubject(__('Your_email_address_has_been_activated_successfully.'))
            ->setViewVars([
                'identity' => $this->identity,
                'data' => $customer,
                'newPassword' => $newPassword,
            ]);

            if (Configure::read('app.termsOfUseEnabled')) {
                $email->addAttachments([__('Filename_Terms-of-use').'.pdf' => ['data' => $this->generateTermsOfUsePdf(), 'mimetype' => 'application/pdf']]);
            }
            $email->addToQueue();

            $this->Flash->success(__('Your_email_address_has_been_activated_successfully._Your_password_has_been_sent_to_you.'));
        }

        $this->redirect('/');

    }

    public function newPasswordRequest()
    {
        $this->set([
            'title_for_layout' => __('Request_new_password')
        ]);

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->newEntity([]);

        if (!empty($this->getRequest()->getData())) {

            $sanitizeService = new SanitizeService();
            $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
            $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

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
                $entity = $this->Customer->get($this->getRequest()->getData('Customers.email'));
                $this->Customer->setPrimaryKey($originalPrimaryKey);
                $activateNewPasswordCode = $entity->activate_new_password_code;

                if ($activateNewPasswordCode == '') {
                    $activateNewPasswordCode = StringComponent::createRandomString(12);
                }
                $entity->activate_new_password_code = $activateNewPasswordCode;
                $this->Customer->save($entity);

                // send email
                $email = new AppMailer();
                $email->viewBuilder()->setTemplate('new_password_request_successful');
                $email->setSubject(__('New_password_request_for_{0}', [Configure::read('appDb.FCS_APP_NAME')]))
                    ->setTo($this->getRequest()->getData('Customers.email'))
                    ->setViewVars([
                        'activateNewPasswordCode' => $activateNewPasswordCode,
                        'customer' => $entity,
                    ]);
                $email->addToQueue();

                $this->Flash->success(__('We_successfully_sent_the_activation_link_for_your_new_password_to_you.'));

                $this->redirect('/');
            }
        }

        $this->set('customer', $customer);
    }

    public function activateNewPassword()
    {
        $activateNewPasswordCode = h($this->getRequest()->getParam('pass')[0]);

        if (!isset($activateNewPasswordCode)) {
            throw new RecordNotFoundException('activate new password code not passed');
        }

        $this->Customer = $this->getTableLocator()->get('Customers');
        $customer = $this->Customer->find('all', conditions: [
            'Customers.activate_new_password_code' => $activateNewPasswordCode,
        ])->first();

        if (empty($customer)) {
            $this->Flash->success(__('Your_new_password_was_already_activated_or_the_activation_code_was_not_valid.'));
        } else {

            $newPassword = StringComponent::createRandomString(12);
            $patchedEntity = $this->Customer->patchEntity(
                $customer,
                [
                    'passwd' => (new DefaultPasswordHasher())->hash($newPassword),
                    'tmp_new_passwd' => null,
                    'activate_new_password_code' => null,
                ]
            );
            $this->Customer->save($patchedEntity);
            $this->Flash->success(__('Your_new_password_was_successfully_activated_and_sent_to_you.'));

            // send email
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('new_password_activation_successful');
            $email->setSubject(__('New_password_for_{0}', [Configure::read('appDb.FCS_APP_NAME')]))
                ->setTo($customer->email)
                ->setViewVars([
                    'newPassword' => $newPassword,
                    'customer' => $customer,
                ]);
            $email->addToQueue();

        }

        $this->redirect('/');
    }

    public function login()
    {
        $this->Customer = $this->getTableLocator()->get('Customers');
        $title = __('Sign_in');
        $enableRegistrationForm = true;
        $enableBarCodeLogin = false;
        $enableSelfServiceLoginAsCustomerButton = false;

        $orderCustomerService = new OrderCustomerService();
        if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')
            && ($orderCustomerService->isSelfServiceModeByUrl() || $orderCustomerService->isSelfServiceModeByReferer())
            ) {
                $this->viewBuilder()->setLayout('self_service');
                $title = __('Sign_in_for_self_service');
                $enableRegistrationForm = false;
                $enableBarCodeLogin = true;
                $enableSelfServiceLoginAsCustomerButton = true;
            }
        $this->set('enableRegistrationForm', $enableRegistrationForm);
        $this->set('enableBarCodeLogin', $enableBarCodeLogin);
        $this->set('enableSelfServiceLoginAsCustomerButton', $enableSelfServiceLoginAsCustomerButton);

        $this->set('title_for_layout', $title);

        if ($this->getRequest()->is('post')) {
            // no spam protected email output in input field when login or registration fails
            $this->protectEmailAddresses = false;
        }

        /**
         * login start
         */
        if ($this->getRequest()->getUri()->getPath() == Configure::read('app.slugHelper')->getLogin()) {

            if ($this->getRequest()->is('get')) {
                if ($this->identity !== null) {
                    $this->Flash->error(__('You_are_already_signed_in.'));
                }
            }

            if ($this->getRequest()->is('post')) {
                $result = $this->Authentication->getResult();
                if ($result->isValid()) {
                    $target = $this->Authentication->getLoginRedirect() ?? Configure::read('app.slugHelper')->getHome();
                    $this->redirect($target);
                } else {
                    $errorMessageSigningInFailed = __('Signing_in_failed_account_inactive_or_password_wrong?');
                    if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')
                    && ($orderCustomerService->isSelfServiceModeByUrl() || $orderCustomerService->isSelfServiceModeByReferer())
                    && !empty(Configure::read('app.selfServiceLoginCustomers'))) {
                        $errorMessageSigningInFailed .= '</br></br>'.__('Signing_in_failed_info_click_location_button_for_self_service');
                    }
                    $this->Flash->error($errorMessageSigningInFailed);
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
                    'active' => 0,
                    'id_default_group' => Customer::GROUP_MEMBER,
                    'terms_of_use_accepted_date' => date('Y-m-d'),
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

            if ($this->identity !== null) {
                $this->Flash->error(__('You_are_already_signed_in.'));
                $this->redirect(Configure::read('app.slugHelper')->getLogin());
            }

            if (! empty($this->getRequest()->getData())) {

                $sanitizeService = new SanitizeService();
                $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
                $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));

                $this->setRequest($this->getRequest()->withData('Customers.email', $this->getRequest()->getData('Customers.address_customer.email')));
                $this->setRequest($this->getRequest()->withData('Customers.address_customer.firstname', $this->getRequest()->getData('Customers.firstname')));
                $this->setRequest($this->getRequest()->withData('Customers.address_customer.lastname', $this->getRequest()->getData('Customers.lastname')));

                $this->setRequest($this->getRequest()->withoutData('Customers.active'));
                $this->setRequest($this->getRequest()->withoutData('Customers.id_default_group'));

                if (Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                    $this->setRequest($this->getRequest()->withData('Customers.activate_email_code', StringComponent::createRandomString(12)));
                }

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
                    $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
                    $fullname = $this->getRequest()->getData('Customers.firstname') . ' ' . $this->getRequest()->getData('Customers.lastname');
                    if (Configure::read('app.customerMainNamePart') == 'lastname') {
                        $fullname = $this->getRequest()->getData('Customers.lastname') . ' ' . $this->getRequest()->getData('Customers.firstname');
                    }
                    if ($this->getRequest()->getData('Customers.is_company')) {
                        $fullname = $this->getRequest()->getData('Customers.firstname');
                    }
                    $message = __('{0}_created_an_account.', [$fullname]);

                    $this->ActionLog->customSave('customer_registered', $newCustomer->id_customer, $newCustomer->id_customer, 'customers', $message);

                    // START send confirmation email to customer
                    $email = new AppMailer();
                    if (Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                        $template = 'customer_registered_active';
                    } else {
                        $template = 'customer_registered_inactive';
                    }
                    $email->viewBuilder()->setTemplate($template);
                    $email->setTo($this->getRequest()->getData('Customers.address_customer.email'))
                        ->setSubject(__('Welcome'))
                        ->setViewVars([
                        'identity' => $this->identity,
                        'data' => $newCustomer,
                        'newsletterCustomer' => $newCustomer,
                        'newPassword' => $newPassword
                        ]);
                    $email->addToQueue();
                    // END send confirmation email to customer

                    // START send notification email
                    if (! empty(Configure::read('appDb.FCS_REGISTRATION_NOTIFICATION_EMAILS'))) {
                        $email = new AppMailer();
                        $email->viewBuilder()->setTemplate('customer_registered_notification');
                        $email->setTo(explode(',', Configure::read('appDb.FCS_REGISTRATION_NOTIFICATION_EMAILS')))
                            ->setSubject(__('New_registration_{0}', [$newCustomer->firstname . ' ' . $newCustomer->lastname]))
                            ->setViewVars([
                            'identity' => $this->identity,
                                'data' => $newCustomer
                            ])
                            ->addToQueue();
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

        $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts(null, true);
        $this->set('blogPosts', $blogPosts);
    }

    public function logout()
    {
        $this->getRequest()->getSession()->destroy();
        $this->Flash->success(__('You_have_been_signed_out.'));

        $this->Authentication->logout();
        $redirectUrl = '/';
        if ($this->request->getQuery('redirect')) {
            $redirectUrl = $this->request->getQuery('redirect');
        }
        $this->redirect($redirectUrl);

    }
}
