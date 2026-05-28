<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use App\Services\SanitizeService;
use App\Model\Entity\Page;
use App\Model\Entity\Block;
use App\Model\Entity\HeaderPromo;
use App\Model\Table\HeaderPromosTable;
use Cake\Http\Response;
use Admin\Traits\UploadTrait;

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

    use UploadTrait;

    public function home(): void
    {
        $this->set('title_for_layout', __('Home'));
    }

    public function add(): ?Response
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
        $this->set('title_for_layout', __('Add_page'));
        $this->set('disabledSelectPageIds', []);
        $this->_processForm($page, false);
        if (empty($this->getRequest()->getData())) {
            return $this->render('edit');
        }
        return null;
    }

    public function edit(int $pageId): void
    {
        $pagesTable = $this->getTableLocator()->get('Pages');
        $page = $pagesTable->find('all', conditions: [
            $pagesTable->aliasField('id_page') => $pageId,
        ])->first();

        if (empty($page)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __('Edit_page'));

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

    public function editHome(): ?Response
    {
        $configurationsTable = $this->getTableLocator()->get('Configurations');
        $blocksTable = $this->getTableLocator()->get('Blocks');
        $pagesTable = $this->getTableLocator()->get('Pages');
        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $configuration = $configurationsTable->find('all', conditions: [
            'Configurations.name' => 'FCS_HOME_TEXT',
        ])->first();

        if (empty($configuration)) {
            throw new NotFoundException();
        }

        $mapConfiguration = $configurationsTable->find('all', conditions: [
            'Configurations.name' => 'FCS_FOODCOOPS_MAP_ENABLED',
        ])->first();

        if (empty($mapConfiguration)) {
            throw new NotFoundException();
        }

        $homeBlocks = $blocksTable->find('all', order: [
            'Blocks.position' => 'ASC',
            'Blocks.id' => 'ASC',
        ])->toArray();

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('App.fullBaseUrl') . '/files/kcfinder/pages',
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . '/files/kcfinder/pages',
        ];

        $this->set('title_for_layout', __('homepage'));
        $this->setFormReferer();

        $headerPromo = $this->resolveHeaderPromo($headerPromosTable, Page::PAGE_ID_HOME);
        $page = new Page([
            'id_page' => Page::PAGE_ID_HOME,
            'header_promo' => $headerPromo,
        ]);

        if (empty($this->getRequest()->getData())) {
            $this->set('homeText', $configuration->value);
            $this->set('foodcoopsMapEnabled', (bool) $mapConfiguration->value);
            $this->set('blocks', $homeBlocks);
            $this->set('page', $page);
            return $this->render('edit_home');
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['content'])));

        $homeText = (string) $this->getRequest()->getData('Pages.content');
        $submittedBlocksRaw = (array) $this->getRequest()->getData('Blocks');
        $submittedBlocks = [];
        foreach ($submittedBlocksRaw as $rowKey => $submittedBlock) {
            if ((string) $rowKey === '__INDEX__' || !is_array($submittedBlock)) {
                continue;
            }

            $blockId = (int)($submittedBlock['id'] ?? 0);
            $tmpImage = trim((string)($submittedBlock['tmp_image'] ?? ''));
            $image = trim((string)($submittedBlock['image'] ?? ''));
            $heading = trim(strip_tags((string)($submittedBlock['heading'] ?? '')));
            $content = trim(strip_tags((string)($submittedBlock['content'] ?? '')));
            $deleteImage = !empty($submittedBlock['delete_image']);
            $hasUserContent = $tmpImage !== '' || $image !== '' || $heading !== '' || $content !== '' || $deleteImage;

            if ($blockId === 0 && !$hasUserContent) {
                continue;
            }

            $submittedBlocks[] = $submittedBlock;
        }
        $configuration = $configurationsTable->patchEntity(
            $configuration,
            [
                'value' => $homeText,
            ],
            [
                'validate' => false,
            ],
        );

        $foodcoopsMapEnabled = $this->getRequest()->getData('Configurations.FCS_FOODCOOPS_MAP_ENABLED') ? '1' : '0';
        $mapConfiguration = $configurationsTable->patchEntity(
            $mapConfiguration,
            [
                'value' => $foodcoopsMapEnabled,
            ],
            [
                'validate' => false,
            ],
        );

        $headerPromoData = $this->getHeaderPromoDataFromRequest();
        $hasHeaderPromoData = $this->hasHeaderPromoData($headerPromoData);
        $headerPromo = $this->resolveHeaderPromo($headerPromosTable, Page::PAGE_ID_HOME);
        if ($hasHeaderPromoData) {
            $headerPromo = $headerPromosTable->patchEntity($headerPromo, $headerPromoData);
        }
        $page->set('header_promo', $headerPromo);

        $blockEntities = [];
        $blockErrors = false;
        $homeBlocksById = [];
        foreach ($homeBlocks as $homeBlock) {
            $homeBlocksById[(int) $homeBlock->id] = $homeBlock;
        }
        foreach ($submittedBlocks as $submittedBlock) {
            $blockId = (int)($submittedBlock['id'] ?? 0);
            if ($blockId > 0 && isset($homeBlocksById[$blockId])) {
                if (
                    empty($submittedBlock['delete_image'])
                    && trim((string)($submittedBlock['tmp_image'] ?? '')) === ''
                    && trim((string)($submittedBlock['image'] ?? '')) === ''
                    && !empty($homeBlocksById[$blockId]->image)
                ) {
                    $submittedBlock['image'] = (string)$homeBlocksById[$blockId]->image;
                }
                $blockEntity = $blocksTable->patchEntity($homeBlocksById[$blockId], $submittedBlock);
            } else {
                $blockEntity = $blocksTable->newEntity($submittedBlock);
            }
            /** @var Block $blockEntity */
            if ($blockEntity->hasErrors()) {
                $blockErrors = true;
            }
            $blockEntities[] = $blockEntity;
        }

        if ($configuration->hasErrors() || $mapConfiguration->hasErrors() || $blockErrors || ($hasHeaderPromoData && $headerPromo->hasErrors())) {
            return $this->renderEditHomeError($homeText, (bool) $foodcoopsMapEnabled, $blockEntities, $page);
        }

        $saved = $configurationsTable->save($configuration);
        $mapSaved = $configurationsTable->save($mapConfiguration);
        if (empty($saved) || empty($mapSaved)) {
            return $this->renderEditHomeError($homeText, (bool) $foodcoopsMapEnabled, $blockEntities, $page);
        }

        if ($hasHeaderPromoData) {
            $headerPromo = $headerPromosTable->patchEntity($headerPromo, [
                'page_id' => Page::PAGE_ID_HOME,
            ], [
                'validate' => false,
            ]);
            $headerPromoSaved = $headerPromosTable->save($headerPromo);
            if (empty($headerPromoSaved)) {
                return $this->renderEditHomeError($homeText, (bool) $foodcoopsMapEnabled, $blockEntities, $page);
            }
        }

        $thumbsPath = Configure::read('app.htmlHelper')->getPageThumbsPath();

        if (!empty($this->getRequest()->getData('Pages.tmp_image'))) {
            $this->saveUploadedImage(
                Page::PAGE_ID_HOME,
                (string) $this->getRequest()->getData('Pages.tmp_image'),
                $thumbsPath,
                Configure::read('app.pageImageSizes'),
                true,
            );
        }

        if (!empty($this->getRequest()->getData('Pages.delete_image'))) {
            $this->deleteUploadedImage(Page::PAGE_ID_HOME, $thumbsPath);
        }

        $savedBlockIds = [];
        $blockThumbsPath = Configure::read('app.htmlHelper')->getBlockThumbsPath();
        foreach ($blockEntities as $index => $blockEntity) {
            $savedBlock = $blocksTable->save($blockEntity);
            if (empty($savedBlock)) {
                return $this->renderEditHomeError($homeText, (bool) $foodcoopsMapEnabled, $blockEntities, $page);
            }
            /** @var Block $savedBlock */
            $savedBlockIds[] = (int) $savedBlock->id;

            $submittedBlock = $submittedBlocks[$index] ?? [];
            if (!empty($submittedBlock['tmp_image'])) {
                $filename = $this->saveUploadedImage(
                    $savedBlock->id,
                    (string) $submittedBlock['tmp_image'],
                    $blockThumbsPath,
                    Configure::read('app.blockImageSizes'),
                );
                if ($filename !== false) {
                    $savedBlock = $blocksTable->patchEntity($savedBlock, ['image' => $filename]);
                    $blocksTable->save($savedBlock);
                }
            }

            if (!empty($submittedBlock['delete_image'])) {
                $this->deleteUploadedImage($savedBlock->id, $blockThumbsPath);
                $savedBlock = $blocksTable->patchEntity($savedBlock, ['image' => null]);
                $blocksTable->save($savedBlock);
            }
        }

        foreach ($homeBlocks as $homeBlock) {
            if (!in_array((int) $homeBlock->id, $savedBlockIds, true)) {
                $this->deleteUploadedImage((int) $homeBlock->id, $blockThumbsPath);
                $blocksTable->delete($homeBlock);
            }
        }

        $this->Flash->success(__('The homepage has been changed successfully.'));
        return $this->redirect($this->getPreparedReferer());
    }

    private function _processForm(Page $page, bool $isEditMode): ?Response
    {
        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('App.fullBaseUrl') . "/files/kcfinder/pages",
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/pages"
        ];
        $pagesTable = $this->getTableLocator()->get('Pages');
        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $this->set('pagesForSelect', $pagesTable->getForSelect($page->id_page));
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        $headerPromo = $this->resolveHeaderPromo($headerPromosTable, $page->id_page !== null ? (int) $page->id_page : null);
        $page->set('header_promo', $headerPromo);

        if (empty($this->getRequest()->getData())) {
            $this->set('page', $page);
            return null;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['content'])));

        $this->setRequest($this->getRequest()->withData('Pages.extern_url', StringComponent::addProtocolToUrl($this->getRequest()->getData('Pages.extern_url'))));
        $this->setRequest($this->getRequest()->withData('Pages.id_customer', $this->identity->getId()));

        if ($this->getRequest()->getData('Pages.id_parent') == '') {
            $this->request = $this->request->withData('Pages.id_parent', 0);
        }

        $headerPromoData = $this->getHeaderPromoDataFromRequest();
        $hasHeaderPromoData = $this->hasHeaderPromoData($headerPromoData);
        $headerPromo = $this->resolveHeaderPromo($headerPromosTable, $page->id_page !== null ? (int) $page->id_page : null);
        if ($hasHeaderPromoData) {
            $headerPromo = $headerPromosTable->patchEntity($headerPromo, $headerPromoData);
        }
        $page->set('header_promo', $headerPromo);

        $page = $pagesTable->patchEntity($page, $this->getRequest()->getData());
        if ($page->hasErrors() || ($hasHeaderPromoData && $headerPromo->hasErrors())) {
            $this->Flash->error(__('Errors_while_saving!_admin'));
            $this->set('page', $page);
            return $this->render('edit');
        } else {
            $page->unset('header_promo');
            $page = $pagesTable->saveOrFail($page, [
                'associated' => [],
            ]);

            if ($hasHeaderPromoData) {
                $headerPromo = $headerPromosTable->patchEntity($headerPromo, [
                    'page_id' => (int) $page->id_page,
                ], [
                    'validate' => false,
                ]);
                $headerPromoSaved = $headerPromosTable->save($headerPromo);
                if (empty($headerPromoSaved)) {
                    $this->Flash->error(__('Errors_while_saving!_admin'));
                    $this->set('page', $page);
                    return $this->render('edit');
                }
            }

            if (!$isEditMode) {
                $messageSuffix = __('created');
                $actionLogType = 'page_added';
            } else {
                $messageSuffix = __('changed');
                $actionLogType = 'page_changed';
            }

            if (!empty($this->getRequest()->getData('Pages.tmp_image'))) {
                $this->saveUploadedImage(
                    $page->id_page,
                    (string) $this->getRequest()->getData('Pages.tmp_image'),
                    Configure::read('app.htmlHelper')->getPageThumbsPath(),
                    Configure::read('app.pageImageSizes'),
                    true,
                );
            }

            if (!empty($this->getRequest()->getData('Pages.delete_image'))) {
                $this->deleteUploadedImage($page->id_page, Configure::read('app.htmlHelper')->getPageThumbsPath());
            }

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('Pages.delete_page'))) {
                $this->deleteUploadedImage($page->id_page, Configure::read('app.htmlHelper')->getPageThumbsPath());
                $page = $pagesTable->patchEntity($page, ['active' => APP_DEL]);
                $pagesTable->save($page);
                $messageSuffix = __('deleted_admin');
                $actionLogType = 'page_deleted';
            }
            $message = __('The_page_{0}_has_been_{1}.', ['<b>' . $page->title . '</b>', $messageSuffix]);
            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $page->id_page, 'pages', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $page->id_page);
            return $this->redirect($this->getPreparedReferer());
        }

    }

    private function getHeaderPromoByPageId(HeaderPromosTable $headerPromosTable, int $pageId): ?HeaderPromo
    {
        return $headerPromosTable->find('all', conditions: [
            'HeaderPromos.page_id' => $pageId,
        ])->first();
    }

    private function resolveHeaderPromo(HeaderPromosTable $headerPromosTable, ?int $pageId): HeaderPromo
    {
        if ($pageId === null || $pageId === 0) {
            return $headerPromosTable->newEntity([]);
        }

        return $this->getHeaderPromoByPageId($headerPromosTable, $pageId) ?? $headerPromosTable->newEntity([]);
    }

    /**
     * @param array<int, Block> $blocks
     */
    private function renderEditHomeError(string $homeText, bool $foodcoopsMapEnabled, array $blocks, Page $page): Response
    {
        $this->Flash->error(__('Errors_while_saving!_admin'));
        $this->set('homeText', $homeText);
        $this->set('foodcoopsMapEnabled', $foodcoopsMapEnabled);
        $this->set('blocks', $blocks);
        $this->set('page', $page);
        return $this->render('edit_home');
    }

    /**
     * @return array<string, mixed>
     */
    private function getHeaderPromoDataFromRequest(): array
    {
        $headerPromoData = (array)$this->getRequest()->getData('header_promo', []);
        if ($headerPromoData === []) {
            $headerPromoData = (array)$this->getRequest()->getData('HeaderPromo', []);
        }

        return $headerPromoData;
    }

    /**
     * @param array<string, mixed> $headerPromoData
     */
    private function hasHeaderPromoData(array $headerPromoData): bool
    {
        foreach (['title', 'lead_text', 'text', 'primary_label', 'primary_href', 'secondary_label', 'secondary_href'] as $field) {
            if (trim((string)($headerPromoData[$field] ?? '')) !== '') {
                return true;
            }
        }

        return false;
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

        $this->set('title_for_layout', __('Pages'));

        $customersTable = $this->getTableLocator()->get('Customers');
        $this->set('customersForDropdown', $customersTable->getForDropdown());
    }
}
