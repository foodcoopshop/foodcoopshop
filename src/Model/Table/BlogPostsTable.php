<?php
namespace App\Model\Table;

use Cake\ORM\Query;

/**
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
class BlogPostsTable extends AppTable
{
    
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_blog_post');
        $this->belongsTo('Customers', [
            'foreignKey' => 'id_customer'
        ]);
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
    }
    
    /**
     * Find neighbors method
     */
    public function findNeighbors(Query $query, array $options)
    {
        $previous = $this->find()
            ->order(['id_blog_post ' => 'DESC'])
            ->where('id_blog_post < ' . $options['id']);
        $next = $this->find()
            ->order(['id_blog_post ' => 'ASC'])
            ->where('id_blog_post > ' . $options['id']);
        return ['prev' => $previous, 'next' => $next];
    }
    
    public $validate = [
        'title' => [
            'notBlank' => [
                'rule' => [
                    'notBlank'
                ],
                'message' => 'Bitte gib einen Titel an.'
            ],
            'minLength' => [
                'rule' => [
                    'minLength',
                    3
                ],
                'message' => 'Bitte gib mindestens 3 Zeichen ein.'
            ]
        ],
        'short_description' => [
            'maxLength' => [
                'rule' => [
                    'maxLength',
                    100
                ],
                'message' => 'Bitte gib maximal 100 Zeichen ein.'
            ]
        ],
        'content' => [
            'minLength' => [
                'rule' => [
                    'minLength',
                    3
                ],
                'message' => 'Bitte gib mindestens 3 Zeichen ein.',
                'allowEmpty' => true
            ]
        ]
    ];

    public function findFeatured($appAuth)
    {
        return $this->findBlogPosts($appAuth, null, null, true);
    }

    public function findBlogPosts($appAuth, $limit = null, $manufacturerId = null, $isFeatured = null)
    {
        $conditions = [
            'BlogPosts.active' => APP_ON
        ];
        if (! $appAuth->user()) {
            $conditions['BlogPosts.is_private'] = APP_OFF;
            $conditions[] = '(Manufacturers.is_private IS NULL OR Manufacturers.is_private = ' . APP_OFF.')';
        }
        if ($manufacturerId) {
            $conditions['BlogPosts.id_manufacturer'] = $manufacturerId;
        }
        if ($isFeatured) {
            $conditions['BlogPosts.is_featured'] = true;
        }

        $blogPosts = $this->find('all', [
            'conditions' => $conditions,
            'order' => [
                'BlogPosts.modified' => 'DESC'
            ],
            'contain' => [
                'Manufacturers'
            ],
            'limit' => $limit
        ]);

        return $blogPosts;
    }
}
