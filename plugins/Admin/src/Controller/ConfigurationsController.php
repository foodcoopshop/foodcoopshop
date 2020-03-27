<?php
namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Mailer\AppMailer;
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
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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

        $this->viewBuilder()->setHelpers(['Configuration']);

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
        $this->set('title_for_layout', __d('admin', 'Edit_setting'));

        if (in_array($configuration->type, ['textarea_big'])) {
            $_SESSION['ELFINDER'] = [
                'uploadUrl' => Configure::read('app.cakeServerName') . "/files/kcfinder/configurations/",
                'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/configurations/"
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
            $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData())));
        }
        if ($configuration->name == 'FCS_FACEBOOK_URL') {
            $this->setRequest($this->getRequest()->withData('Configurations.value', StringComponent::addHttpToUrl($this->getRequest()->getData('Configurations.value'))));
        }
        if (in_array($configuration->type, ['multiple_dropdown'])) {
            if ($this->getRequest()->getData('Configurations.value') != '') {
                $this->setRequest($this->getRequest()->withData('Configurations.value', implode(',', $this->getRequest()->getData('Configurations.value'))));
            }
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

        if ($configuration->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('configuration', $configuration);
        } else {
            $configuration = $this->Configuration->save($configuration);
            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            $this->Flash->success(__d('admin', 'The_setting_has_been_changed_successfully.'));
            $this->ActionLog->customSave('configuration_changed', $this->AppAuth->getUserId(), $configuration->id_configuration, 'configurations', __d('admin', 'The_setting_{0}_has_been_changed_to_{1}.', ['"' . $configuration->name . '"', '<i>"' . $configuration->value . '"</i>']));
            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('configuration', $configuration);
    }

    public function previewEmail($configurationName)
    {
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $this->Configuration->getConfigurations();
        $email = new AppMailer();
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
                $email->viewBuilder()->setTemplate($template);
                $data = (object) [
                    'firstname' => 'Vorname',
                    'lastname' => 'Nachname',
                ];
                $data->address_customer = (object) [
                    'email' => 'vorname.nachname@example.com'
                ];
                $email->setViewVars([
                    'data' => $data,
                    'newPassword' => 'password'
                ]);
                break;
        }
        echo $email->render()->getMessage()->getBodyString();
        exit;
    }

    public function index()
    {
        $this->viewBuilder()->setHelpers(['Configuration']);
        $this->Configuration = TableRegistry::getTableLocator()->get('Configurations');
        $this->set('configurations', $this->Configuration->getConfigurations(['type != "hidden"']));
        $this->Tax = TableRegistry::getTableLocator()->get('Taxes');
        $defaultTax = $this->Tax->find('all', [
            'conditions' => [
                'Taxes.id_tax' => Configure::read('app.defaultTaxId')
            ]
        ])->first();
        $this->set('defaultTax', $defaultTax);

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) {
            $this->viewBuilder()->setHelpers(['Network.Network']);
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

        $this->set('title_for_layout', __d('admin', 'Settings'));
    }

    public function sendTestEmail()
    {
        $this->set('title_for_layout', __d('admin', 'Send_test_email'));
        $email = new AppMailer(false);
        $success = $email->setTo(Configure::read('app.hostingEmail'))
        ->setSubject(__d('admin', 'Test_email'))
        ->viewBuilder()->setTemplate('send_test_email_template');
        $email->setAttachments([
                WWW_ROOT . DS . 'files' . DS . 'images' . DS. 'logo.jpg'
            ])
            ->send();
        $this->set('success', $success);
    }
}
