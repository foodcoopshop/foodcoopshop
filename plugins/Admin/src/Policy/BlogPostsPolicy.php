<?php
declare(strict_types=1);

namespace Admin\Policy;

use AssetCompress\Factory;
use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Datasource\Exception\RecordNotFoundException;

class BlogPostsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request)
    {

        if ($identity === null) {
            return false;
        }

        if (!Configure::read('app.isBlogFeatureEnabled')) {
            return false;
        }

        switch ($request->getParam('action')) {
            case 'edit':
                if ($identity->isSuperadmin() || $identity->isAdmin()) {
                    return true;
                }
                // manufacturer owner check
                if ($identity->isManufacturer()) {
                    $blogPostTable = FactoryLocator::get('Table')->get('BlogPosts');
                    $blogPost = $blogPostTable->find('all', [
                        'conditions' => [
                            'BlogPosts.id_blog_post' => $request->getParam('pass')[0],
                        ],
                    ])->first();
                    if (empty($blogPost)) {
                        throw new RecordNotFoundException();
                    }
                    if ($blogPost->id_manufacturer != $identity->getManufacturerId()) {
                        return false;
                    }
                    return true;
                }
                break;
            default:
                return $identity->isSuperadmin() || $identity->isAdmin() || $identity->isManufacturer();
                break;
        }

        return false;

    }

}