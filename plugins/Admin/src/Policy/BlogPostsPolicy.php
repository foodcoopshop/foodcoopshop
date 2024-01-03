<?php
declare(strict_types=1);

namespace Admin\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
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