<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

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
class PagesController extends AdminAppController
{

    protected $Customer;
    protected $Page;
    protected $Sanitize;

    public function isAuthorized($user)
    {
        return match($this->getRequest()->getParam('action')) {
            'home' => $this->identity->isLoggedIn(),
             default => $this->identity->isSuperadmin() || $this->identity->isAdmin(),
        };
    }

    public function home()
    {
        $this->set('title_for_layout', __d('admin', 'Home'));
    }

    public function add()
    {
        $this->Page = $this->getTableLocator()->get('Pages');
        $page = $this->Page->newEntity(
            [
                'active' => APP_ON,
                'is_private' => Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') ? APP_OFF : APP_ON,
                'position' => 10
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('admin', 'Add_page'));

        $this->set('disabledSelectPageIds', []);

        $this->_processForm($page, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($pageId)
    {
        if ($pageId === null) {
            throw new NotFoundException;
        }

        $this->Page = $this->getTableLocator()->get('Pages');
        $page = $this->Page->find('all', [
            'conditions' => [
                'Pages.id_page' => $pageId
            ]
        ])->first();

        if (empty($page)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_page'));

        $pageChildren = $this->Page->find('all', [
            'conditions' => [
                'Pages.active > ' . APP_DEL
            ]
        ])
        ->find('children', ['for' => $pageId]);

        $disabledSelectPageIds = [(int) $pageId];
        foreach ($pageChildren as $pageChild) {
            $disabledSelectPageIds[] = $pageChild->id_page;
        }
        $this->set('disabledSelectPageIds', $disabledSelectPageIds);

        $this->_processForm($page, true);
    }

    private function _processForm($page, $isEditMode)
    {
        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('App.fullBaseUrl') . "/files/kcfinder/pages",
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/pages"
        ];
        $this->set('pagesForSelect', $this->Page->getForSelect($page->id_page));
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('page', $page);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['content'])));

        $this->setRequest($this->getRequest()->withData('Pages.extern_url', StringComponent::addHttpToUrl($this->getRequest()->getData('Pages.extern_url'))));
        $this->setRequest($this->getRequest()->withData('Pages.id_customer', $this->identity->getUserId()));

        if ($this->getRequest()->getData('Pages.id_parent') == '') {
            $this->request = $this->request->withData('Pages.id_parent', 0);
        }

        $page = $this->Page->patchEntity($page, $this->getRequest()->getData());
        if ($page->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('page', $page);
            $this->render('edit');
        } else {
            $page = $this->Page->save($page);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'page_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'page_changed';
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('Pages.delete_page'))) {
                $page = $this->Page->patchEntity($page, ['active' => APP_DEL]);
                $this->Page->save($page);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'page_deleted';
            }
            $message = __d('admin', 'The_page_{0}_has_been_{1}.', ['<b>' . $page->title . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->identity->getUserId(), $page->id_page, 'pages', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $page->id_page);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('page', $page);
    }

    public function index()
    {
        $conditions = [];

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
            $conditions = [
                'Pages.id_customer' => $customerId
            ];
        }
        $this->set('customerId', $customerId);

        $conditions[] = 'Pages.active > ' . APP_DEL;

        $this->Page = $this->getTableLocator()->get('Pages');
        $totalPagesCount = $this->Page->find('all', [
            'conditions' => $conditions
        ])->count();
        $this->set('totalPagesCount', $totalPagesCount);

        $pages = $this->Page->getThreaded($conditions);
        $this->set('pages', $pages);

        $this->set('title_for_layout', __d('admin', 'Pages'));

        $this->Customer = $this->getTableLocator()->get('Customers');
        $this->set('customersForDropdown', $this->Customer->getForDropdown());
    }
}
