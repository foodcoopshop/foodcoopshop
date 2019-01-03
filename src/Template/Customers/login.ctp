<?php
/**
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
use Cake\Core\Configure;
use Cake\I18n\I18n;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".AppFeatherlight.initLightboxForHref('#RegistrationForm .input.checkbox label a');".
    Configure::read('app.jsNamespace').".Helper.initLoginForm();"
]);
?>
<div id="login-form" class="form">  
    
    <?php
    $btnClass = 'btn-success';
    ?>
  
  <h1><?php echo __('Sign_in'); ?></h1>
  
  <form id="LoginForm" method="post" accept-charset="utf-8">
        
        <?php
          echo $this->Form->control('email', ['label' => __('Email')]);
          echo $this->Form->control('passwd', ['label' => __('Password')]);

          echo '<div class="remember-me-wrapper">';
              echo $this->Form->control('remember_me', [
                  'type' => 'checkbox',
                  'label' => __('Stay_signed_in').'<br /><small>'.__('and_accept_cookie').'</small>',
                  'escape' => false
              ]);
              echo '</div>';
        ?>
        
        <div class="sc"></div>
        <?php
            echo '<a style="float: left;" href="' . $this->Slug->getNewPasswordRequest() . '">'.__('Forgot_password?').'</a>';
        ?>
         
        <div class="sc"></div>
        <button type="submit" class="btn <?php echo $btnClass; ?>"><i class="fa fa-sign-in fa-lg"></i> <?php echo __('Sign_in'); ?></button>
        
  </form>
  
    <?php if (!$appAuth->user()) { ?>
    <?php
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace').".Helper.initRegistrationForm('".$this->request->is('post')."');"
        ]);
    ?>
      <div class="sc"></div>
      <h1 style="border-radius: 0;margin-top: 20px;padding-top: 20px;border-top: 1px solid #d6d4d4;"><?php echo __('Create_account'); ?></h1>
      
            <?php
            echo $this->Form->create(
                $customer,
                [
                    'url' => $this->Slug->getRegistration(),
                    'id' => 'RegistrationForm',
                    'novalidate' => 'novalidate'
                ]
            );
              echo $this->Form->control('Customers.address_customer.email', ['label' => __('Email'), 'id' => 'RegistraionFormEmail', 'required' => true]); // id: avoid duplicate id (login form has field "email" too)

              echo '<div class="detail-form">';

            if (Configure::read('appDb.FCS_AUTHENTICATION_INFO_TEXT') != '') {
                echo '<p>'.Configure::read('appDb.FCS_AUTHENTICATION_INFO_TEXT').'</p>';
            }

                  echo $this->Form->hidden('antiSpam', ['value' => 'lalala', 'id' => 'antiSpam']);

                  echo $this->Form->control('Customers.firstname', ['label' => __('Firstname'), 'required' => true]); // required should not be necessary here
                  echo $this->Form->control('Customers.lastname', ['label' => __('Lastname'), 'required' => true]); // required should not be necessary here

                  echo $this->Form->control('Customers.address_customer.address1', ['label' => __('Street')]);
                  echo $this->Form->control('Customers.address_customer.address2', ['label' => __('Additional_address_information')]);

                  echo $this->Form->control('Customers.address_customer.postcode', ['label' => __('Zip')]);
                  echo $this->Form->control('Customers.address_customer.city', ['label' => __('City')]);

                  echo $this->Form->control('Customers.address_customer.phone_mobile', ['label' => __('Mobile')]);
                  echo $this->Form->control('Customers.address_customer.phone', ['label' => __('Phone')]);

                  if (Configure::read('app.emailOrderReminderEnabled')) {
                      echo $this->Form->control('Customers.email_order_reminder', ['label' => __('Want_to_receive_reminder_emails?'), 'type' => 'checkbox']);
                  }

                  if (Configure::read('app.termsOfUseEnabled')) {
                      echo '<div id="terms-of-use" class="featherlight-overlay">';
                        echo $this->element('legal/'.I18n::getLocale().'/termsOfUse');
                      echo '</div>';
                      $termsOfUseLink = '<a href="#terms-of-use">'.__('terms_of_use').'</a>';
                      echo $this->Form->control('Customers.terms_of_use_accepted_date_checkbox', [
                          'label' => __('I_accept_the_{0}', [$termsOfUseLink]),
                          'type' => 'checkbox',
                          'escape' => false
                      ]);
                  }
                ?>
              
              <div class="sc"></div>
              <br />
              <button type="submit" class="btn btn-success"><i class="fa fa-user fa-lg"></i> <?php echo __('Create_account'); ?></button>
          
          </div>
        <?php echo $this->Form->end(); ?>
    <?php } ?>
  
</div>

<div class="sc"></div>
