<?php

namespace Network\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * SyncsController
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SyncDomainsController extends AppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin();
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->setLayout('Admin.default');
        $this->helpers[] = 'Network.Network';
        $this->SyncDomain = TableRegistry::getTableLocator()->get('Network.SyncDomains');
    }

    public function add()
    {
        $syncDomain = $this->SyncDomain->newEntity(
            ['active' => APP_ON],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('network', 'Add_remote_foodcoop'));
        $this->_processForm($syncDomain, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($syncDomainId)
    {
        if ($syncDomainId === null) {
            throw new NotFoundException;
        }

        $syncDomain = $this->SyncDomain->find('all', [
            'conditions' => [
                'SyncDomains.id' => $syncDomainId
            ]
        ])->first();

        if (empty($syncDomain)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('network', 'Edit_remote_foodcoop'));
        $this->_processForm($syncDomain, true);
    }

    private function _processForm($syncDomain, $isEditMode)
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('syncDomain', $syncDomain);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData())));

        $syncDomain = $this->SyncDomain->patchEntity(
            $syncDomain,
            $this->getRequest()->getData()
        );
        if ($syncDomain->hasErrors()) {
            $this->Flash->error(__d('network', 'Errors_while_saving!'));
            $this->set('syncDomain', $syncDomain);
            $this->render('edit');
        } else {
            $syncDomain = $this->SyncDomain->save($syncDomain);

            if (!$isEditMode) {
                $messageSuffix = __d('network', 'created');
                $actionLogType = 'remote_foodcoop_added';
            } else {
                $messageSuffix = __d('network', 'changed');
                $actionLogType = 'remote_foodcoop_changed';
            }

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('SyncDomains.delete_sync_domain'))) {
                $this->SyncDomain->delete($syncDomain);
                $messageSuffix = __d('network', 'deleted');
                $actionLogType = 'remote_foodcoop_deleted';
            }
            $message = __d('network', 'The_remote_foodcoop_{0}_has_been_{1}.', ['<b>' . $syncDomain->domain. '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $syncDomain->id, 'sync_domains', $message);
            $this->Flash->success($message);

            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('attribute', $syncDomain);
    }
}
