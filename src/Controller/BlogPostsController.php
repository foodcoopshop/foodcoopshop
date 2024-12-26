<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

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

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'index',
            'detail',
        ]);
    }

    public function detail()
    {
        $blogPostId = (int) $this->getRequest()->getParam('idAndSlug');

        $conditions = [
            'BlogPosts.active' => APP_ON
        ];
        $conditions['BlogPosts.id_blog_post'] = $blogPostId; // needs to be last element of conditions

        $blogPostsTable = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPost = $blogPostsTable->find('all',
            conditions: $conditions,
            contain: [
                'Manufacturers',
            ]
        )->first();

        if (empty($blogPost)) {
            throw new RecordNotFoundException('blogPost not found');
        }

        $correctSlug = StringComponent::slugify($blogPost->title);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('idAndSlug'));
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getBlogPostDetail($blogPostId, $blogPost->title));
        }

        $this->set('blogPost', $blogPost);

        // START find neighbors
        array_pop($conditions); // do not filter last condition element blogPostId
        if ($this->identity === null) {
            $conditions['BlogPosts.is_private'] = APP_OFF;
            $conditions[] = '(Manufacturers.is_private IS NULL OR Manufacturers.is_private = ' . APP_OFF.')';
        }

        $modified = $blogPost->modified->i18nFormat(Configure::read('DateFormat.DatabaseWithTime'));
        $showOnStartPage = !is_null($blogPost->show_on_start_page_until) && !$blogPost->show_on_start_page_until->isPast();

        $prevBlogPost = $blogPostsTable->find()->contain('Manufacturers')->where($conditions);
        $prevBlogPost = $blogPostsTable->getConditionShowOnStartPage($prevBlogPost, $showOnStartPage);
        $prevBlogPost = $prevBlogPost->orderByAsc($blogPostsTable->aliasField('modified'))->where([$blogPostsTable->aliasField('modified >') => $modified]);

        $nextBlogPost = $blogPostsTable->find()->contain('Manufacturers')->where($conditions);
        $nextBlogPost = $blogPostsTable->getConditionShowOnStartPage($nextBlogPost, $showOnStartPage);
        $nextBlogPost = $nextBlogPost->orderByDesc($blogPostsTable->aliasField('modified'))->where([$blogPostsTable->aliasField('modified <') => $modified]);
        
        $neighbors = [
            'prev' => $prevBlogPost->first(),
            'next' => $nextBlogPost->first(),
        ];
        $this->set('neighbors', $neighbors);

        $this->set('title_for_layout', $blogPost->title);
    }

    public function index()
    {
        $blogPostsTable = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $blogPostsTable->findBlogPosts(null, false);
        $this->set('blogPosts', $blogPosts);
        $this->set('title_for_layout', __('Blog_archive'));
    }
}
