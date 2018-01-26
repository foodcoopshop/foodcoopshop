<?php

use App\Controller\FrontendController;
use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * BlogPostsController
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
class BlogPostsController extends FrontendController
{

    public function beforeFilter(Event $event)
    {

        parent::beforeFilter($event);

        switch ($this->request->action) {
            case 'detail':
                $blogPostId = (int) $this->request->getParam('pass')[0];
                $blogPost = $this->BlogPost->find('all', [
                    'conditions' => [
                        'BlogPosts.id_blog_post' => $blogPostId,
                        'BlogPosts.active' => APP_ON
                    ]
                ])->first();
                if (!empty($blogPost) && !$this->AppAuth->user()
                    && ($blogPost['BlogPosts']['is_private'] || (isset($blogPost['Manufacturers']) && $blogPost['Manufacturers']['is_private']))
                    ) {
                        $this->AppAuth->deny($this->request->action);
                }
                break;
        }
    }

    public function detail()
    {
        $blogPostId = (int) $this->request->getParam('pass')[0];

        $conditions = [
            'BlogPosts.active' => APP_ON
        ];
        $conditions['BlogPosts.id_blog_post'] = $blogPostId; // needs to be last element of conditions

        $blogPost = $this->BlogPost->find('all', [
            'conditions' => $conditions
        ])->first();

        if (empty($blogPost)) {
            throw new RecordNotFoundException('blogPost not found');
        }

        $correctSlug = Configure::read('app.slugHelper')->getBlogPostDetail($blogPostId, $blogPost['BlogPosts']['title']);
        if ($correctSlug != Configure::read('app.slugHelper')->getBlogPostDetail($blogPostId, StringComponent::removeIdFromSlug($this->request->getParam('pass')[0]))) {
            $this->redirect($correctSlug);
        }

        $this->set('blogPost', $blogPost);

        // START find neighbors
        array_pop($conditions); // do not filter last condition element blogPostId
        if (!$this->AppAuth->user()) {
            $conditions['BlogPosts.is_private'] = APP_OFF;
            $conditions[] = '(Manufacturers.is_private IS NULL OR Manufacturers.is_private = ' . APP_OFF.')';
        }
        $neighbors = $this->BlogPost->find('neighbors', [
            'field' => 'BlogPosts.modified',
            'value' => $blogPost['BlogPosts']['modified'],
            'conditions' => $conditions,
            'order' => [
                'BlogPosts.modified' => 'DESC'
            ]
        ]);
        $this->set('neighbors', $neighbors);

        $this->set('title_for_layout', $blogPost['BlogPosts']['title']);
    }

    public function index()
    {
        $conditions = [
            'BlogPosts.active' => APP_ON
        ];

        if (isset($this->params['manufacturerSlug'])) {
            $manufacturerId = (int) $this->params['manufacturerSlug'];
            $this->Manufacturer = TableRegistry::get('Manufacturers');
            $manufacturer = $this->Manufacturer->find('all', [
                'conditions' => [
                    'Manufacturers.id_manufacturer' => $manufacturerId,
                    'Manufacturers.active' => APP_ON
                ]
            ])->first();
            if (empty($manufacturer)) {
                throw new RecordNotFoundException('manufacturer not found or not active');
            }
            $this->set('manufacturer', $manufacturer);
            $conditions['BlogPosts.id_manufacturer'] = $manufacturerId;
        }

        if (! $this->AppAuth->user()) {
            $conditions['BlogPosts.is_private'] = APP_OFF;
            $conditions[] = '(Manufacturers.is_private IS NULL OR Manufacturers.is_private = ' . APP_OFF.')';
        }

        $blogPosts = $this->BlogPost->find('all', [
            'conditions' => $conditions,
            'order' => [
                'BlogPosts.modified' => 'DESC'
            ]
        ]);

        $this->set('blogPosts', $blogPosts);
        $this->set('title_for_layout', 'Aktuelles');
    }
}
