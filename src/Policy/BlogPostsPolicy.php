<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Datasource\FactoryLocator;

class BlogPostsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        switch ($request->getParam('action')) {
            case 'detail':
                $blogPostId = (int) $request->getParam('pass')[0];
                $blogPostTable = FactoryLocator::get('Table')->get('BlogPosts');
                $blogPost = $blogPostTable->find('all', [
                    'conditions' => [
                        'BlogPosts.id_blog_post' => $blogPostId,
                        'BlogPosts.active' => APP_ON
                    ],
                    'contain' => [
                        'Manufacturers'
                    ]
                ])->first();
                if (!empty($blogPost) && $identity === null
                    && ($blogPost->is_private || (!empty($blogPost->manufacturer) && $blogPost->manufacturer->is_private))
                    ) {
                        return false;
                }
                break;
        }

        return true;
    }

}