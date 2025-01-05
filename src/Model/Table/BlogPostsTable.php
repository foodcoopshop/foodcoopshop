<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use Cake\Validation\Validator;
use Cake\Routing\Router;
use Cake\ORM\Query\SelectQuery;

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
class BlogPostsTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_blog_post');
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('title', __('Please_enter_a_title.'));
        $validator->minLength('title', 3, __('Please_enter_at_least_{0}_characters.', [3]));
        $validator->allowEmptyString('content');
        $validator->minLength('content', 3, __('Please_enter_at_least_{0}_characters.', [3]));
        $validator->allowEmptyString('short_description');
        $validator->maxLength('short_description', 100, __('Please_enter_max_{0}_characters.', [100]));
        $validator->allowEmptyDate('show_on_start_page_until');
        return $validator;
    }

    public function findBlogPosts($manufacturerId = null, $showOnStartPage = false): SelectQuery|array
    {

        if (!Configure::read('app.isBlogFeatureEnabled')) {
            return [];
        }

        $conditions = [
            $this->aliasField('active') => APP_ON,
        ];

        $identity = Router::getRequest()->getAttribute('identity');
        
        if ($identity === null) {
            $conditions[$this->aliasField('is_private')] = APP_OFF;
            $conditions[] = '(Manufacturers.is_private IS NULL OR Manufacturers.is_private = ' . APP_OFF.')';
        }
        if ($manufacturerId) {
            $conditions[$this->aliasField('id_manufacturer')] = $manufacturerId;
        }

        $blogPosts = $this->find('all',
            conditions: $conditions,
            order: [
                $this->aliasField('modified') => 'DESC',
            ],
            contain: [
                'Manufacturers',
            ],
        );

        return $this->getConditionShowOnStartPage($blogPosts, $showOnStartPage);
    }

    public function getConditionShowOnStartPage($query, $showOnStartPage): SelectQuery
    {
        $query->where(function (QueryExpression $exp, Query $q) use ($showOnStartPage) {
            $key = 'DATE_FORMAT(BlogPosts.show_on_start_page_until, "%Y-%m-%d")';
            $value = Configure::read('app.timeHelper')->getCurrentDateForDatabase();
            if ($showOnStartPage) {
                return $exp->gte($key, $value);
            } else {
                return $exp->or([
                    $q->newExpr()->lt($key, $value),
                    $q->newExpr()->isNull('BlogPosts.show_on_start_page_until'),
                ]);
            }
        });
        return $query;
    }

}
