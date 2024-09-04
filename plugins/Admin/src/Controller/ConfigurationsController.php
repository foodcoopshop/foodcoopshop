<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use App\Services\OutputFilter\OutputFilterService;
use App\Mailer\AppMailer;
use App\Model\Table\ConfigurationsTable;
use App\Model\Table\TaxesTable;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Inflector;
use App\Services\SanitizeService;
use Network\Model\Table\SyncDomainsTable;

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

class ConfigurationsController extends AdminAppController
{

    protected ConfigurationsTable $Configuration;
    protected SyncDomainsTable $SyncDomain;
    protected TaxesTable $Tax;
    
    public function edit($name)
    {

        $this->viewBuilder()->addHelper('Configuration');

        if ($name === null) {
            throw new NotFoundException;
        }

        $this->Configuration = $this->getTableLocator()->get('Configurations');
        $configuration = $this->Configuration->find('all', conditions: [
            'Configurations.name' => $name,
            'Configurations.type NOT IN' => ['hidden', 'readonly'],
        ])->first();

        if (empty($configuration)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_setting'));

        if (in_array($configuration->type, ['textarea_big'])) {
            $_SESSION['ELFINDER'] = [
                'uploadUrl' => Configure::read('App.fullBaseUrl') . "/files/kcfinder/configurations/",
                'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/configurations/"
            ];
        }

        $this->setFormReferer();

        if (empty($this->getRequest()->getData())) {
            $this->set('configuration', $configuration);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));

        if (!in_array($configuration->type, ['textarea', 'textarea_big'])) {
            $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData())));
        }
        if (in_array($configuration->name, ['FCS_FACEBOOK_URL', 'FCS_INSTAGRAM_URL'])) {
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
            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            $this->Flash->success(__d('admin', 'The_setting_has_been_changed_successfully.'));
            $this->ActionLog->customSave('configuration_changed', $this->identity->getId(), 0, 'configurations', __d('admin', 'The_setting_{0}_has_been_changed_to_{1}.', ['"' . $configuration->name . '"', '<i>"' . $configuration->value . '"</i>']));
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('configuration', $configuration);
    }

    public function previewEmail($configurationName)
    {

        $this->disableAutoRender();

        $this->Configuration = $this->getTableLocator()->get('Configurations');
        $this->Configuration->getConfigurations();
        $email = new AppMailer();
        $email
            ->setViewVars([
                'identity' => $this->identity
            ]);

        switch ($configurationName) {
            case 'FCS_REGISTRATION_EMAIL_TEXT':
                if (Configure::read('appDb.FCS_DEFAULT_NEW_MEMBER_ACTIVE')) {
                    $template = 'email_address_activated';
                } else {
                    $template = 'customer_registered_inactive';
                }
                $email->viewBuilder()->setTemplate($template);
                $data = (object) [
                    'firstname' => 'Vorname',
                    'lastname' => 'Nachname',
                    'is_company' => false,
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

        $output = $email->render()->getMessage()->getBodyString();

        if (Configure::check('app.outputStringReplacements')) {
            $output = OutputFilterService::replace($output, Configure::read('app.outputStringReplacements'));
        }

        echo $output;
    }

    public function index()
    {
        $this->viewBuilder()->addHelper('Configuration');
        $this->Configuration = $this->getTableLocator()->get('Configurations');
        $this->set('configurations', $this->Configuration->getConfigurations(['type != "hidden"']));
        $this->Tax = $this->getTableLocator()->get('Taxes');
        $defaultTax = $this->Tax->find('all', conditions: [
            'Taxes.id_tax' => Configure::read('app.defaultTaxId')
        ])->first();
        $this->set('defaultTax', $defaultTax);

        if (Configure::read('appDb.FCS_NETWORK_PLUGIN_ENABLED')) {
            $this->viewBuilder()->addHelper('Network.Network');
            $this->SyncDomain = $this->getTableLocator()->get('Network.SyncDomains');
            $syncDomains = $this->SyncDomain->getSyncDomains(APP_OFF);
            $this->set('syncDomains', $syncDomains);
        }
        $this->set('versionFoodCoopShop', $this->Configuration->getVersion());

        try {
            $query = 'SELECT migration_name, version FROM phinxlog WHERE start_time IS NOT NULL ORDER by version DESC LIMIT 1;';
            $lastMigration = $this->Configuration->getConnection()->execute($query)->fetchAll();
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
                WWW_ROOT . DS . 'files' . DS . 'images' . DS . Configure::read('app.logoFileName'),
            ])
        ->addToQueue();
        $this->set('success', $success);
    }
}
