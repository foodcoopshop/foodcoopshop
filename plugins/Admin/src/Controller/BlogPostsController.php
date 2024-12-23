<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Admin\Traits\UploadTrait;
use App\Services\SanitizeService;
use Cake\I18n\Date;

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

class BlogPostsController extends AdminAppController
{

    use UploadTrait;

    public function add()
    {
        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPost = $blogPostsTable->newEntity(
            [
                'active' => APP_ON,
                'is_private' => Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS') ? APP_OFF : APP_ON,
                'show_on_start_page_until' => Configure::read('app.timeHelper')->getInXDaysForDatabase(30),
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('admin', 'Add_blog_post'));
        $this->_processForm($blogPost, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($blogPostId)
    {
        if ($blogPostId === null) {
            throw new NotFoundException;
        }

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPost = $blogPostsTable->find('all', conditions: [
            'BlogPosts.id_blog_post' => $blogPostId
        ])->first();

        if (empty($blogPost)) {
            throw new NotFoundException;
        }

        // defaults for edit
        $showOnStartPageUntil = $blogPost->show_on_start_page_until;
        if ($blogPost->show_on_start_page_until && $blogPost->show_on_start_page_until->isPast()) {
            $showOnStartPageUntil = null;
        }
        $blogPost = $blogPostsTable->patchEntity($blogPost, [
            'update_modified_field' => true,
            'show_on_start_page_until' => $showOnStartPageUntil,
        ]);

        $this->set('title_for_layout', __d('admin', 'Edit_blog_post'));
        $this->_processForm($blogPost, true);
    }

    private function _processForm($blogPost, $isEditMode)
    {
        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $manufacturersTable->getForDropdown());

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('App.fullBaseUrl') . "/files/kcfinder/blog_posts",
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/blog_posts"
        ];

        if (empty($this->getRequest()->getData())) {
            $this->set('blogPost', $blogPost);
            return;
        }

        $sanitizeService = new SanitizeService();
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($sanitizeService->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['content'])));

        $this->setRequest($this->getRequest()->withData('BlogPosts.id_customer', $this->identity->getId()));

        if ($this->identity->isManufacturer()) {
            $this->setRequest($this->getRequest()->withData('BlogPosts.id_manufacturer', $this->identity->getManufacturerId()));
        }

        if (!$this->getRequest()->getData('BlogPosts.update_modified_field') && !$this->identity->isManufacturer() && $isEditMode) {
            $blogPostsTable->removeBehavior('Timestamp');
        }

        $this->setRequest(
            $this->getRequest()->withData('BlogPosts.show_on_start_page_until',
            Date::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), Configure::read('app.timeHelper')->formatToDbFormatDate($this->getRequest()->getData('BlogPosts.show_on_start_page_until')))
        ));
        $blogPost = $blogPostsTable->patchEntity($blogPost, $this->getRequest()->getData());

        if ($blogPost->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('blogPost', $blogPost);
            $this->render('edit');
        } else {
            $blogPost = $blogPostsTable->save($blogPost);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'blog_post_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'blog_post_changed';
            }

            if (!empty($this->getRequest()->getData('BlogPosts.tmp_image'))) {
                $this->saveUploadedImage($blogPost->id_blog_post, $this->getRequest()->getData('BlogPosts.tmp_image'), Configure::read('app.htmlHelper')->getBlogPostThumbsPath(), Configure::read('app.blogPostImageSizes'));
            }

            if (!empty($this->getRequest()->getData('BlogPosts.delete_image'))) {
                $this->deleteUploadedImage($blogPost->id_blog_post, Configure::read('app.htmlHelper')->getBlogPostThumbsPath());
            }

            $actionLogsTable = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('BlogPosts.delete_blog_post'))) {
                $this->deleteUploadedImage($blogPost->id_blog_post, Configure::read('app.htmlHelper')->getBlogPostThumbsPath());
                $blogPost = $blogPostsTable->patchEntity($blogPost, ['active' => APP_DEL]);
                $blogPostsTable->save($blogPost);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'blog_post_deleted';
            }
            $message = __d('admin', 'The_blog_post_{0}_has_been_{1}.', ['<b>' . $blogPost->title . '</b>', $messageSuffix]);
            $actionLogsTable->customSave($actionLogType, $this->identity->getId(), $blogPost->id_blog_post, 'blog_posts', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $blogPost->id_blog_post);
            $this->redirect($this->getPreparedReferer());
        }

        $this->set('blogPost', $blogPost);
    }

    public function index()
    {
        $conditions = [];

        $customerId = '';
        if (! empty($this->getRequest()->getQuery('customerId'))) {
            $customerId = h($this->getRequest()->getQuery('customerId'));
            $conditions = [
                'BlogPosts.id_customer' => $customerId
            ];
        }
        $this->set('customerId', $customerId);

        $manufacturerId = '';
        if (! empty($this->getRequest()->getQuery('manufacturerId'))) {
            $manufacturerId = h($this->getRequest()->getQuery('manufacturerId'));
        }
        $this->set('manufacturerId', $manufacturerId);

        if ($this->identity->isManufacturer()) {
            $manufacturerId = $this->identity->getManufacturerId();
        }
        if ($manufacturerId != '') {
            $conditions = [
                'BlogPosts.id_manufacturer' => $manufacturerId
            ];
        }

        $conditions[] = 'BlogPosts.active > ' . APP_DEL;

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $customersTable = $this->getTableLocator()->get('Customers');

        $query = $blogPostsTable->find('all',
        conditions: $conditions,
        contain: [
            'Customers',
            'Manufacturers'
        ]);
        $blogPosts = $this->paginate($query, [
            'sortableFields' => [
                'BlogPosts.show_on_start_page_until', 'BlogPosts.is_private', 'BlogPosts.title', 'BlogPosts.short_description', 'Customers.' . Configure::read('app.customerMainNamePart'), 'Manufacturers.name', 'BlogPosts.modified', 'BlogPosts.active'
            ],
            'order' => [
                'BlogPosts.modified' => 'DESC'
            ]
        ]);

        foreach ($blogPosts as $blogPost) {
            if (!empty($blogPost->customer)) {
                $manufacturerRecord = $customersTable->getManufacturerRecord($blogPost->customer);
            }
            if (!empty($manufacturerRecord->manufacturer)) {
                $blogPost->customer->manufacturer = $manufacturerRecord->manufacturer;
            }
        }

        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', __d('admin', 'Blog_posts'));

        $manufacturersTable = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $manufacturersTable->getForDropdown());
    }
}
