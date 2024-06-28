<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BlogPostsFixture extends TestFixture
{
    public string $table = 'fcs_blog_posts';

    public array $records = [
        [
            'id_blog_post' => 2,
            'title' => 'Demo Blog Artikel',
            'short_description' => 'Lorem ipsum dolor sit amet, consetetur sadipscing',
            'content' => '<p>Lorem ipsum dolor sit amet.</p>',
            'id_customer' => 88,
            'id_manufacturer' => 0,
            'is_private' => 0,
            'active' => 1,
            'created' => '2014-12-18 10:37:26',
            'modified' => '2015-03-16 12:41:46',
        ]
    ];

}
?>