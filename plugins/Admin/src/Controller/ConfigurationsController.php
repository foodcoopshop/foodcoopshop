<?php
namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Mailer\AppEmail;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

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

        $this->helpers[] = 'Configuration';

        if ($configurationId === null) {
            throw new NotFoundException;
        }

        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $configuration = $this->Configuration->find('all', [
            'conditions' => [
                'Configurations.id_configuration' => $configurationId
            ]
        ])->first();

        if (empty($configuration)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', 'Einstellung bearbeiten');

        if (in_array($configuration->type, ['textarea_big'])) {
            $_SESSION['KCFINDER'] = [
                'uploadURL' => Configure::read('app.cakeServerName') . "/files/kcfinder/configurations/",
                'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/configurations/"
            ];
        }

        $this->setFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('configuration', $configuration);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));

        if (!in_array($configuration->type, ['textarea', 'textarea_big'])) {
            $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData())));
        }
        if ($configuration->name == 'FCS_FACEBOOK_URL') {
            $this->setRequest($this->getRequest()->withData('Configurations.value', StringComponent::addHttpToUrl($this->getRequest()->getData('Configurations.value'))));
        }

        $validationName = Inflector::camelize(strtolower($configuration->name));
        $validatorExists = false;
        if (method_exists($this->Configuration, 'validation'.$validationName)) {
            $validatorExists = true;
        }

        $configuration = $this->Configuration->patchEntity(
            $configuration,
            $this->getRequest()->getData(),
            [
                'validate' => $validatorExists ? $validationName : false
            ]
        );

        if (!empty($configuration->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            $this->set('configuration', $configuration);
        } else {
            $configuration = $this->Configuration->save($configuration);
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->Flash->success('Die Einstellung wurde erfolgreich geändert.');
            $this->ActionLog->customSave('configuration_changed', $this->AppAuth->getUserId(), $configuration->id_configuration, 'configurations', 'Die Einstellung "' . $configuration->name . '" wurde geändert in <i>"' . $configuration->value . '"</i>');
            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('configuration', $configuration);
    }

    public function previewEmail($configurationName)
    {
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $this->Configuration->getConfigurations();
        $email = new AppEmail();
        $email
            ->setViewVars([
                'appAuth' => $this->AppAuth
            ]);

        switch ($configurationName) {
            case 'FCS_REGISTRATION_EMAIL_TEXT':
                if (Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                    $template = 'customer_registered_active';
                } else {
                    $template = 'customer_registered_inactive';
                }
                $email->setTemplate($template);
                $email->setViewVars([
                    'data' => (object) [
                        'firstname' => 'Vorname',
                        'lastname' => 'Nachname',
                        'email' => 'vorname.nachname@example.com'
                    ],
                    'newPassword' => 'DeinNeuesPasswort'
                ]);
                break;
        }
        $html = $email->_renderTemplates(null)['html'];
        if ($html != '') {
            echo $html;
            exit;
        }
        throw new RecordNotFoundException('no email template defined for configuration: ' . $configurationName);
    }

    public function index()
    {
        $this->helpers[] = 'Configuration';
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $this->set('configurations', $this->Configuration->getConfigurations());
        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
        $defaultTax = $this->Tax->find('all', [
            'conditions' => [
                'Taxes.id_tax' => Configure::read('app.defaultTaxId')
            ]
        ])->first();
        $this->set('defaultTax', $defaultTax);

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) {
            $this->set('versionNetworkPlugin', $this->Configuration->getVersion('Network'));
            $this->helpers[] = 'Network.Network';
            $this->SyncDomain = TableRegistry::getTableLocator()->get('Network.SyncDomains');
            $syncDomains = $this->SyncDomain->getSyncDomains(APP_OFF);
            $this->set('syncDomains', $syncDomains);
        }
        $this->set('versionFoodCoopShop', $this->Configuration->getVersion());

        try {
            $query = 'SELECT * FROM phinxlog ORDER by version DESC LIMIT 1;';
            $lastMigration = $this->Configuration->getConnection()->query($query)->fetch('assoc');
            $this->set('lastMigration', $lastMigration);
        } catch (\PDOException  $e) {
        }

        $this->set('title_for_layout', 'Einstellungen');
    }

    public function sendTestEmail()
    {
        $this->set('title_for_layout', 'Test E-Mail versenden');
        $email = new AppEmail(false);
        $success = $email->setTo(Configure::read('app.hostingEmail'))
            ->setSubject('Test E-Mail')
            ->setTemplate('send_test_email_template')
            ->setAttachments([
                WWW_ROOT . DS . 'files' . DS . 'images' . DS. 'logo.jpg'
            ])
            ->send();
        $this->set('success', $success);
    }
}
