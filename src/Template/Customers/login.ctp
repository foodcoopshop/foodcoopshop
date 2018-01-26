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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

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
  
  <h1>Anmelden</h1>
  
  <form action="<?php echo $this->Slug->getLogin(); ?>" id="LoginForm" method="post" accept-charset="utf-8">
        
        <?php
          echo $this->Form->input('email', ['label' => 'E-Mail']);
          echo $this->Form->input('passwd', ['label' => 'Passwort']);
          echo '<div class="remember-me-wrapper">';
              echo $this->Form->checkbox('remember_me') . '<label for="remember_me">Angemeldet bleiben</label>';
          echo '</div>';
        ?>
        
        <div class="sc"></div>
        <?php
            echo '<a style="margin-top: 10px;float: left;" href="/neues-passwort-anfordern">Passwort vergessen?</a>';
        ?>
         
        <div class="sc"></div>
        <button type="submit" class="btn <?php echo $btnClass; ?>"><i class="fa fa-sign-in fa-lg"></i> Anmelden</button>
        
  </form>
  
    <?php if (!$appAuth->user()) { ?>
    <?php
        $this->element('addScript', ['script' =>
            Configure::read('app.jsNamespace').".Helper.initRegistrationForm('".$this->request->is('post')."');"
        ]);
    ?>
      <div class="sc"></div>
      <h1 style="border-radius: 0;margin-top: 20px;padding-top: 20px;border-top: 1px solid #d6d4d4;">Mitgliedskonto erstellen</h1>
      <form action="/registrierung" id="RegistrationForm" method="post" accept-charset="utf-8" novalidate>
            <?php

              echo $this->Form->input('Customers.email', ['label' => 'E-Mail', 'id' => 'RegistraionFormEmail', 'required' => true]); // id: avoid duplicate id (login form has field "email" too)

              echo '<div class="detail-form">';

            if (Configure::read('appDb.FCS_AUTHENTICATION_INFO_TEXT') != '') {
                echo '<p>'.Configure::read('appDb.FCS_AUTHENTICATION_INFO_TEXT').'</p>';
            }

                  echo $this->Form->hidden('antiSpam', ['value' => 'lalala']);

                  echo $this->Form->input('Customers.firstname', ['label' => 'Vorname', 'required' => true]);
                  echo $this->Form->input('Customers.lastname', ['label' => 'Nachname', 'required' => true]);

                  echo $this->Form->input('AddressCustomers.address1', ['label' => 'Straße', 'required' => true]);
                  echo $this->Form->input('AddressCustomers.address2', ['label' => 'Adresszusatz']);

                  echo $this->Form->input('AddressCustomers.postcode', ['label' => 'PLZ', 'required' => true]);
                  echo $this->Form->input('AddressCustomers.city', ['label' => 'Ort', 'required' => true]);

                  echo $this->Form->input('AddressCustomers.phone_mobile', ['label' => 'Handy', 'required' => true]);
                  echo $this->Form->input('AddressCustomers.phone', ['label' => 'Telefon']);

            if (Configure::read('app.emailOrderReminderEnabled')) {
                echo $this->Form->input('Customers.newsletter', ['label' => 'Ich möchte wöchentlich per E-Mail ans Bestellen erinnert werden.', 'type' => 'checkbox']);
            }

                  echo '<div id="terms-of-use" class="featherlight-overlay">';
                    echo $this->element('legal/termsOfUse');
                  echo '</div>';
                  echo $this->Form->input('Customers.terms_of_use_accepted_date', [
                      'label' => 'Ich akzeptiere die <a href="#terms-of-use">Nutzungsbedingungen</a>',
                      'type' => 'checkbox',
                      'escape' => false
                  ]);

                ?>
              
              <div class="sc"></div>
              <br />
              <button type="submit" class="btn btn-success"><i class="fa fa-user fa-lg"></i> Mitgliedskonto erstellen</button>
          
          </div>
      </form>
    <?php } ?>
  
</div>

<div class="sc"></div>
