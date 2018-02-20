<?php

namespace Admin\Controller;
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * PagesController
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
class PagesController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->request->action) {
            case 'home':
                if ($this->AppAuth->user()) {
                    return true;
                }
                break;
            default:
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
        }
    }

    public function home()
    {
        $this->set('title_for_layout', 'Home');
    }

    public function add()
    {
        $this->Page = TableRegistry::get('Pages');
        $page = $this->Page->newEntity(
            [
                'active' => APP_ON,
                'position' => 10
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', 'Seite erstellen');
        $this->_processForm($page, false);
        
        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }

    public function edit($pageId)
    {
        if ($pageId === null) {
            throw new NotFoundException;
        }
        
        $this->Page = TableRegistry::get('Pages');
        $page = $this->Page->find('all', [
            'conditions' => [
                'Pages.id_page' => $pageId
            ]
        ])->first();
        
        if (empty($page)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', 'Seite bearbeiten');
        $this->_processForm($page, true);
    }
    
    private function _processForm($page, $isEditMode)
    {
        $_SESSION['KCFINDER'] = [
            'uploadURL' => Configure::read('app.cakeServerName') . "/files/kcfinder/pages",
            'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/pages"
        ];
        $this->set('mainPagesForDropdown', $this->Page->getMainPagesForDropdown($page->id_page));
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);
        
        if (empty($this->request->getData())) {
            $this->set('page', $page);
            return;
        }
        
        $this->loadComponent('Sanitize');
        $this->request->data = $this->Sanitize->trimRecursive($this->request->data);
        $this->request->data = $this->Sanitize->stripTagsRecursive($this->request->data);
        
        $this->request->data['Pages']['extern_url'] = StringComponent::addHttpToUrl($this->request->getData('Pages.extern_url'));
        $this->request->data['Pages']['id_customer'] = $this->AppAuth->getUserId();
        
        $page = $this->Page->patchEntity($page, $this->request->getData());
        if (!empty($page->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            $this->set('page', $page);
            $this->render('edit');
        } else {
            $page = $this->Page->save($page);
            
            if (!$isEditMode) {
                $messageSuffix = 'erstellt';
                $actionLogType = 'page_added';
            } else {
                $messageSuffix = 'geändert';
                $actionLogType = 'page_changed';
            }
            
            $this->ActionLog = TableRegistry::get('ActionLogs');
            if (!empty($this->request->getData('Pages.delete_page'))) {
                $page = $this->Page->patchEntity($page, ['active' => APP_DEL]);
                $this->Page->save($page);
                $messageSuffix = 'gelöscht';
                $actionLogType = 'page_deleted';
            }
            $message = 'Die Seite <b>' . $page->title . '</b> wurde ' . $messageSuffix . '.';
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $page->id_page, 'Pages', $message);
            $this->Flash->success($message);
            
            $this->request->getSession()->write('highlightedRowId', $page->id_page);
            $this->redirect($this->request->getData('referer'));
            
        }
        
        $this->set('page', $page);
        
    }
    
    public function index()
    {
        $conditions = [];

        $customerId = '';
        if (! empty($this->request->getQuery('customerId'))) {
            $customerId = $this->request->getQuery('customerId');
            $conditions = [
                'Pages.id_customer' => $customerId
            ];
        }
        $this->set('customerId', $customerId);

        $conditions[] = 'Pages.active > ' . APP_DEL;

        $this->Page = TableRegistry::get('Pages');
        $totalPagesCount = $this->Page->find('all', [
            'conditions' => $conditions
        ])->count();
        $this->set('totalPagesCount', $totalPagesCount);

        $query = $this->Page->findAllGroupedByMenu($conditions);
        $pages = $this->paginate($query);
        $this->set('pages', $pages);

        $this->set('title_for_layout', 'Seiten');

        $this->Customer = TableRegistry::get('Customers');
        $this->set('customersForDropdown', $this->Customer->getForDropdown());
    }
}
