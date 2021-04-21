<?php
namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;

/**
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

class BlogPostsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        if (!Configure::read('app.isBlogFeatureEnabled')) {
            return false;
        }
        switch ($this->getRequest()->getParam('action')) {
            case 'edit':
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                // manufacturer owner check
                if ($this->AppAuth->isManufacturer()) {
                    $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
                    $blogPost = $this->BlogPost->find('all', [
                        'conditions' => [
                            'BlogPosts.id_blog_post' => $this->getRequest()->getParam('pass')[0]
                        ]
                    ])->first();
                    if (empty($blogPost)) {
                        throw new RecordNotFoundException();
                    }
                    if ($blogPost->id_manufacturer != $this->AppAuth->getManufacturerId()) {
                        return false;
                    }
                    return true;
                }
                break;
            default:
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isManufacturer();
        }
    }

    public function add()
    {
        $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
        $blogPost = $this->BlogPost->newEntity(
            [
                'active' => APP_ON,
                'is_private' => APP_ON,
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

        $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
        $blogPost = $this->BlogPost->find('all', [
            'conditions' => [
                'BlogPosts.id_blog_post' => $blogPostId
            ]
        ])->first();

        // defaults for edit
        $showOnStartPageUntil = $blogPost->show_on_start_page_until;
        if ($blogPost->show_on_start_page_until && $blogPost->show_on_start_page_until->isPast()) {
            $showOnStartPageUntil = null;
        }
        $blogPost = $this->BlogPost->patchEntity($blogPost, [
            'update_modified_field' => true,
            'show_on_start_page_until' => $showOnStartPageUntil,
        ]);

        if (empty($blogPost)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_blog_post'));
        $this->_processForm($blogPost, true);
    }

    private function _processForm($blogPost, $isEditMode)
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());

        $_SESSION['ELFINDER'] = [
            'uploadUrl' => Configure::read('app.cakeServerName') . "/files/kcfinder/blog_posts",
            'uploadPath' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/blog_posts"
        ];

        if (empty($this->getRequest()->getData())) {
            $this->set('blogPost', $blogPost);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsAndPurifyRecursive($this->getRequest()->getData(), ['content'])));

        $this->setRequest($this->getRequest()->withData('BlogPosts.id_customer', $this->AppAuth->getUserId()));

        if ($this->AppAuth->isManufacturer()) {
            $this->setRequest($this->getRequest()->withData('BlogPosts.id_manufacturer', $this->AppAuth->getManufacturerId()));
        }

        if (!$this->getRequest()->getData('BlogPosts.update_modified_field') && !$this->AppAuth->isManufacturer() && $isEditMode) {
            $this->BlogPost->removeBehavior('Timestamp');
        }

        $this->setRequest(
            $this->getRequest()->withData('BlogPosts.show_on_start_page_until',
            FrozenDate::createFromFormat(Configure::read('app.timeHelper')->getI18Format('DatabaseAlt'), Configure::read('app.timeHelper')->formatToDbFormatDate($this->getRequest()->getData('BlogPosts.show_on_start_page_until')))
        ));
        $blogPost = $this->BlogPost->patchEntity($blogPost, $this->getRequest()->getData());

        if ($blogPost->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('blogPost', $blogPost);
            $this->render('edit');
        } else {
            $blogPost = $this->BlogPost->save($blogPost);

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
                $this->deleteUploadedImage($blogPost->id_blog_post, Configure::read('app.htmlHelper')->getBlogPostThumbsPath(), Configure::read('app.blogPostImageSizes'));
            }

            $this->ActionLog = $this->getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('BlogPosts.delete_blog_post'))) {
                $blogPost = $this->BlogPost->patchEntity($blogPost, ['active' => APP_DEL]);
                $this->BlogPost->save($blogPost);
                $messageSuffix = __d('admin', 'deleted');
                $actionLogType = 'blog_post_deleted';
            }
            $message = __d('admin', 'The_blog_post_{0}_has_been_{1}.', ['<b>' . $blogPost->title . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $blogPost->id_blog_post, 'blog_posts', $message);
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

        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }
        if ($manufacturerId != '') {
            $conditions = [
                'BlogPosts.id_manufacturer' => $manufacturerId
            ];
        }

        $conditions[] = 'BlogPosts.active > ' . APP_DEL;

        $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
        $query = $this->BlogPost->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Customers',
                'Manufacturers'
            ]
        ]);
        $blogPosts = $this->paginate($query, [
            'sortableFields' => [
                'BlogPosts.show_on_start_page_until', 'BlogPosts.is_private', 'BlogPosts.title', 'BlogPosts.short_description', 'Customers.' . Configure::read('app.customerMainNamePart'), 'Manufacturers.name', 'BlogPosts.modified', 'BlogPosts.active'
            ],
            'order' => [
                'BlogPosts.modified' => 'DESC'
            ]
        ])->toArray();

        foreach ($blogPosts as $blogPost) {
            if (!empty($blogPost->customer)) {
                $manufacturerRecord = $this->BlogPost->Customers->getManufacturerRecord($blogPost->customer);
            }
            if (!empty($manufacturerRecord->manufacturer)) {
                $blogPost->customer->manufacturer = $manufacturerRecord->manufacturer;
            }
        }

        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', __d('admin', 'Blog_posts'));

        $this->Customer = $this->getTableLocator()->get('Customers');

        $this->Manufacturer = $this->getTableLocator()->get('Manufacturers');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());
    }
}
