<?php
declare(strict_types=1);

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
use Cake\Core\Configure;
use Cake\I18n\I18n;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".ModalText.init('#RegistrationForm .input.checkbox label a');".
    Configure::read('app.jsNamespace').".Helper.initLoginForm();"
]);
if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Helper.initRegistrationAsCompany();"
    ]);
}
?>
<div id="login-form" class="form">

<h1><?php echo $title_for_layout; ?></h1>

    <?php

    echo $this->Form->create(
        null,
        [
            'url' => $this->Slug->getLogin($this->request->getQuery('redirect')),
            'id' => 'LoginForm',
        ]
    );

    if ($enableBarCodeLogin) {
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace').".SelfService.initLoginForm();"
        ]);
        echo $this->Form->control('barcode', ['type' => 'text', 'label' => __('Scan_member_card')]);
        echo '<h2><span>'.__('or').'</span></h2>';
    }

    echo $this->Form->control('email', ['label' => __('Email')]);
    echo $this->Form->control('passwd', ['label' => __('Password')]);

    if (!$enableBarCodeLogin) {
        echo '<div class="remember-me-wrapper">';
            echo $this->Form->control('remember_me', [
                'type' => 'checkbox',
                'label' => __('Stay_signed_in'),
                'escape' => false
            ]);
        echo '</div>';
    }

    ?>

    <div class="sc"></div>
    <?php
        echo '<a style="float:left;" target="_blank" href="' . $this->Slug->getNewPasswordRequest() . '">'.__('Forgot_password?').'</a>';
    ?>

    <div class="sc"></div>
    <button type="submit" class="btn btn-outline-light"><i class="fas fa-sign-in-alt"></i> <?php echo __('Sign_in'); ?></button>

    <?php echo $this->Form->end(); ?>


    <?php if ($identity === null && $enableRegistrationForm) { ?>
        <?php
            $this->element('addScript', ['script' =>
                Configure::read('app.jsNamespace').".Helper.initRegistrationForm('".$this->request->is('post')."');"
            ]);
       ?>
    <div class="sc"></div>

    <div class="registration-form-wrapper">
        <h1 class="h1-registration"><?php echo __('Create_account'); ?></h1>
            <?php
                echo $this->Form->create(
                    $customer,
                    [
                        'url' => $this->Slug->getRegistration(),
                        'id' => 'RegistrationForm',
                        'novalidate' => 'novalidate'
                    ]
                );

                  $this->Form->unlockField('antiSpam');

                  echo $this->Form->control('Customers.address_customer.email', ['label' => __('Email'), 'id' => 'RegistraionFormEmail', 'required' => true]); // id: avoid duplicate id (login form has field "email" too)

                  echo '<div class="detail-form">';

                      if (Configure::read('appDb.FCS_REGISTRATION_INFO_TEXT') != '') {
                          echo '<p>'.Configure::read('appDb.FCS_REGISTRATION_INFO_TEXT').'</p>';
                      }

                      echo $this->Form->control('Customers.firstname', [
                          'label' => __('Firstname'),
                          'required' => true, // required should not be necessary here
                      ]);
                      echo $this->Form->control('Customers.lastname', [
                          'label' => __('Lastname'),
                          'required' => true,
                      ]);

                      if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
                          echo $this->Form->control('Customers.is_company', [
                              'label' => __('Register_as_company?'),
                              'type' => 'checkbox',
                              'class' => 'one-line',
                          ]);
                      }

                      echo $this->Form->control('Customers.address_customer.address1', [
                          'label' => __('Street_and_number'),
                      ]);
                      echo $this->Form->control('Customers.address_customer.address2', [
                          'label' => __('Additional_address_information'),
                          'required' => false,
                      ]);

                      echo $this->Form->control('Customers.address_customer.postcode', ['label' => __('Zip')]);
                      echo $this->Form->control('Customers.address_customer.city', ['label' => __('City')]);

                      echo $this->Form->control('Customers.address_customer.phone_mobile', ['label' => __('Mobile')]);
                      echo $this->Form->control('Customers.address_customer.phone', ['label' => __('Phone')]);

                      if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
                        echo $this->Form->control('Customers.newsletter_enabled', ['label' => __('Want_to_receive_the_newsletter?'), 'type' => 'checkbox']);
                        }

                      if (Configure::read('app.emailOrderReminderEnabled')) {
                          echo $this->Form->control('Customers.email_order_reminder_enabled', ['label' => __('Want_to_receive_reminder_emails?'), 'type' => 'checkbox']);
                      }

                      if (Configure::read('app.termsOfUseEnabled')) {
                          echo '<div id="terms-of-use" class="hide">';
                            echo $this->element('legal/' . I18n::getLocale() . '/' . $this->Html->getLegalTextsSubfolder() . '/termsOfUse');
                          echo '</div>';
                          $termsOfUseLink = '<a href="javascript:void(0);" data-element-selector="#terms-of-use">'.__('terms_of_use').'</a>';
                          echo $this->Form->control('Customers.terms_of_use_accepted_date_checkbox', [
                              'label' => __('I_accept_the_{0}', [$termsOfUseLink]),
                              'type' => 'checkbox',
                              'escape' => false
                          ]);
                      }
                    ?>

                  <div class="sc"></div>
                  <br />
                  <button type="submit" class="btn btn-success"><i class="fas fa-user"></i> <?php echo __('Create_account'); ?></button>

              </div>
            <?php echo $this->Form->end(); ?>
        </div> <?php // .registration-form-wrapper ?>
    <?php } ?>

</div>

<div class="sc"></div>
