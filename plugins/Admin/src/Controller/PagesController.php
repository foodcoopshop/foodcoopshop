<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use App\Services\SanitizeService;

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

    public function home(): void
    {
        $this->set('title_for_layout', __d('admin', 'Home'));
    }

    public function add(): void
    {
        $pagesTable = $this->getTableLocator()->get('Pages');
        $page = $pagesTable->newEntity(
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

    public function edit($pageId): void
    {
        if ($pageId === null) {
            throw new NotFoundException;
        }

        $pagesTable = $this->getTableLocator()->get('Pages');
        $page = $pagesTable->find('all', conditions: [
            $pagesTable->aliasField('id_page') => $pageId,
        ])->first();

        if (empty($page)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_page'));

        $pageChildren = $pagesTable->find('all', conditions: [
            'Pages.active > ' . APP_DEL
        ])
        ->find('children', for: $pageId);

        $disabledSelectPageIds = [(int) $pageId];
        foreach ($pageChildren as $pageChild) {
            $disabledSelectPageIds[] = $pageChild->id_page;
        }
        $this->set('disabledSelectPageIds', $disabledSelectPageIds);

        $this->_processForm($page, true);
    }

    private function _processForm($page, $isEditMode): void
    {
        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('App.fullBaseUrl') . "/files/kcfinder/pages",
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/pages"
        ];
        $pagesTable = $this->getTableLocator()->get('Pages');
        $this->set('pagesForSelect', $pagesTable->getForSelect($page->id_page));
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->getRequest()->getData())) {
            $this->set('page', $page);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['content'])));

        $this->setRequest($this->getRequest()->withData('Pages.extern_url', StringComponent::addProtocolToUrl($this->getRequest()->getData('Pages.extern_url'))));
        $this->setRequest($this->getRequest()->withData('Pages.id_customer', $this->identity->getId()));

        if ($this->getRequest()->getData('Pages.id_parent') == '') {
            $this->request = $this->request->withData('Pages.id_parent', 0);
        }

        $page = $pagesTable->patchEntity($page, $this->getRequest()->getData());
        if ($page->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('page', $page);
            $this->render('edit');
        } else {
            $page = $pagesTable->save($page);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'page_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'page_changed';
            }

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('Pages.delete_page'))) {
                $page = $pagesTable->patchEntity($page, ['active' => APP_DEL]);
                $pagesTable->save($page);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'page_deleted';
            }
            $message = __d('admin', 'The_page_{0}_has_been_{1}.', ['<b>' . $page->title . '</b>', $messageSuffix]);
            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $page->id_page, 'pages', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $page->id_page);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('page', $page);
    }

    public function index(): void
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

        $pagesTable = $this->getTableLocator()->get('Pages');
        $totalPagesCount = $pagesTable->find('all', conditions: $conditions)->count();
        $this->set('totalPagesCount', $totalPagesCount);

        $pages = $pagesTable->getThreaded($conditions);
        $this->set('pages', $pages);

        $this->set('title_for_layout', __d('admin', 'Pages'));

        $customersTable = $this->getTableLocator()->get('Customers');
        $this->set('customersForDropdown', $customersTable->getForDropdown());
    }
}
