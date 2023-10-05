<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

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
class BlogPostsController extends FrontendController
{

    protected $BlogPost;

    public function beforeFilter(EventInterface $event)
    {

        parent::beforeFilter($event);

        switch ($this->getRequest()->getParam('action')) {
            case 'detail':
                $blogPostId = (int) $this->getRequest()->getParam('pass')[0];
                $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
                $blogPost = $this->BlogPost->find('all', [
                    'conditions' => [
                        'BlogPosts.id_blog_post' => $blogPostId,
                        'BlogPosts.active' => APP_ON
                    ],
                    'contain' => [
                        'Manufacturers'
                    ]
                ])->first();
                if (!empty($blogPost) && !$this->AppAuth->user()
                    && ($blogPost->is_private || (!empty($blogPost->manufacturer) && $blogPost->manufacturer->is_private))
                    ) {
                        $this->AppAuth->deny($this->getRequest()->getParam('action'));
                }
                break;
        }
    }

    public function detail()
    {
        $blogPostId = (int) $this->getRequest()->getParam('pass')[0];

        $conditions = [
            'BlogPosts.active' => APP_ON
        ];
        $conditions['BlogPosts.id_blog_post'] = $blogPostId; // needs to be last element of conditions

        $blogPost = $this->BlogPost->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Manufacturers'
            ]
        ])->first();

        if (empty($blogPost)) {
            throw new RecordNotFoundException('blogPost not found');
        }

        $correctSlug = StringComponent::slugify($blogPost->title);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]);
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getBlogPostDetail($blogPostId, $blogPost->title));
        }

        $this->set('blogPost', $blogPost);

        // START find neighbors
        array_pop($conditions); // do not filter last condition element blogPostId
        if (!$this->AppAuth->user()) {
            $conditions['BlogPosts.is_private'] = APP_OFF;
            $conditions[] = '(Manufacturers.is_private IS NULL OR Manufacturers.is_private = ' . APP_OFF.')';
        }

        $options = [
            'modified' => $blogPost->modified->i18nFormat(Configure::read('DateFormat.DatabaseWithTime')),
            'showOnStartPage' => !is_null($blogPost->show_on_start_page_until) && !$blogPost->show_on_start_page_until->isPast(),
        ];
        $neighbors = [
            'prev' => $this->BlogPost->find('neighborPrev', $options)->contain('Manufacturers')->where($conditions)->first(),
            'next' => $this->BlogPost->find('neighborNext', $options)->contain('Manufacturers')->where($conditions)->first(),
        ];
        $this->set('neighbors', $neighbors);

        $this->set('title_for_layout', $blogPost->title);
    }

    public function index()
    {
        $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findBlogPosts($this->AppAuth, null, false);
        $this->set('blogPosts', $blogPosts);
        $this->set('title_for_layout', __('Blog_archive'));
    }
}
