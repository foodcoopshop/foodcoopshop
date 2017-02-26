<?php
/**
 * ConfigurationsController
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
class ConfigurationsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin();
    }

    public function edit($configurationId)
    {
        $this->setFormReferer();
        
        $unsavedConfiguration = $this->Configuration->find('first', array(
            'conditions' => array(
                'Configuration.id_configuration' => $configurationId,
                'Configuration.active' => APP_ON
            )
        ));
        
        if (empty($unsavedConfiguration)) {
            throw new MissingActionException('configuration not found');
        }
        
        $this->set('unsavedConfiguration', $unsavedConfiguration);
        $this->set('configurationId', $configurationId);
        $this->set('title_for_layout', 'Einstellung bearbeiten');
        
        if ($unsavedConfiguration['Configuration']['type'] == 'textarea') {
            $_SESSION['KCFINDER'] = array(
                'uploadURL' => Configure::read('app.cakeServerName') . "/files/kcfinder/configurations/",
                'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/configurations/"
            );
        }
        
        if (empty($this->request->data)) {
            $this->request->data = $unsavedConfiguration;
        } else {
            
            // validate data - do not use $this->Configuration->saveAll()
            $this->Configuration->id = $configurationId;
            $this->Configuration->set($this->request->data['Configuration']);
            
            $this->Configuration->enableValidations($unsavedConfiguration['Configuration']['name']);
            
            // quick and dirty solution for stripping html tags, use html purifier here
            if ($unsavedConfiguration['Configuration']['type'] != 'textarea') {
                $data = strip_tags($this->request->data['Configuration']['value']);
            }
            
            $errors = array();
            if (! $this->Configuration->validates()) {
                $errors = array_merge($errors, $this->Configuration->validationErrors);
            }
            
            if (empty($errors)) {
                
                $this->Configuration->id = $configurationId;
                $this->Configuration->save($this->request->data['Configuration'], array(
                    'validate' => false
                ));
                
                $this->loadModel('CakeActionLog');
                $this->AppSession->setFlashMessage('Die Einstellung wurde erfolgreich geändert.');
                $this->CakeActionLog->customSave('configuration_changed', $this->AppAuth->getUserId(), $configurationId, 'configurations', 'Die Einstellung "' . $unsavedConfiguration['Configuration']['name'] . '" wurde geändert in <i>"' . $this->request->data['Configuration']['value'] . '"</i>');
                
                $this->redirect($this->data['referer']);
            } else {
                $this->AppSession->setFlashError('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }
    
    public function previewEmail($configurationName) {
        $configurations = $this->Configuration->getConfigurations();
        $email = new AppEmail();
        $email
            ->emailFormat('html')
            ->viewVars(array(
                'appAuth' => $this->AppAuth
            ));
        
        switch($configurationName) {
            case 'FCS_REGISTRATION_EMAIL_TEXT':
                if (Configure::read('app.db_config_FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                    $template = 'customer_registered_active';
                } else {
                    $template = 'customer_registered_inactive';
                }
                $email->template($template);
                $email->viewVars(array(
                    'data' => array('Customer' => array(
                        'firstname' => 'Vorname',
                        'lastname' => 'Nachname',
                        'email' => 'vorname.nachname@example.com'
                    )),
                    'newPassword' => 'DeinNeuesPasswort'
                ));
                break;
            
        }
        $html = $email->_renderTemplates(null)['html'];
        if ($html != '') {
            echo $html;
            exit;
        }
        throw new MissingActionException('no email template defined for configuration: ' . $configurationName);
    }

    public function index()
    {
        $this->set('configurations', $this->Configuration->getConfigurations());
        $this->loadModel('Tax');
        $defaultTax = $this->Tax->find('first', array(
            'conditions' => array(
                'Tax.id_tax' => Configure::read('app.defaultTaxId')
            )
        ));
        $this->set('defaultTax', $defaultTax);
        $this->set('title_for_layout', 'Einstellungen');
    }

    public function sendTestEmail()
    {
        $email = new AppEmail();
        $success = $email->to(Configure::read('app.hostingEmail'))
            ->subject('Test E-Mail')
            ->template('send_test_email_template')
            ->emailFormat('html')
            ->attachments(array(
                WWW_ROOT . DS . 'files' . DS . 'images' . DS. 'logo.jpg'
            ))
            ->send();
        $this->set('success', $success);
    }

}