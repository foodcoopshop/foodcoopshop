<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;

class BlogPostsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testEdit(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getBlogPostEdit(2),
            [
                'BlogPosts' => [
                    'id_blog_post' => 2,
                    'title' => 'xadsf',
                    'short_description' => 'yyy',
                    'content' => '<p>zzz</p>',
                    'id_manufacturer' => 5,
                    'is_private' => 1,
                    'active' => 0,
                    'show_on_start_page_until' => '30.03.2040',
                ],
            ]
        );

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPost = $blogPostsTable->find('all',
            conditions: [
                'BlogPosts.id_blog_post' => 2,
            ],
        )->first();

        $this->assertEquals('xadsf', $blogPost->title);
        $this->assertEquals('yyy', $blogPost->short_description);
        $this->assertEquals('<p>zzz</p>', $blogPost->content);
        $this->assertEquals(5, $blogPost->id_manufacturer);
        $this->assertEquals(1, $blogPost->is_private);
        $this->assertEquals(0, $blogPost->active);
        $this->assertEquals('2040-03-30', $blogPost->show_on_start_page_until->i18nFormat('yyyy-MM-dd'));

    }

}