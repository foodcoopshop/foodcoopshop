<?php
declare(strict_types=1);

/**
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\LoginTrait;

class BlogPostsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public $BlogPost;

    public function setUp(): void
    {
        parent::setUp();
        $this->BlogPost = $this->getTableLocator()->get('BlogPosts');
    }

    public function testBlogPostDetailOnlinePublicLoggedOut()
    {
        $this->get($this->Slug->getBlogPostDetail(2, 'Demo Blog Artikel'));
        $this->assertResponseCode(200);
    }

    public function testBlogPostDetailOfflinePublicLoggedOut()
    {
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 0, 0, 0);
        $this->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertResponseCode(404);
    }

    public function testBlogPostDetailOnlinePrivateLoggedOut()
    {
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 1);
        $requestUrl = $this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel');
        $this->get($requestUrl);
        $this->assertRedirectContains($this->Slug->getLogin($requestUrl));
        $this->assertAccessDeniedFlashMessage();
    }

    public function testBlogPostDetailOnlinePrivateLoggedIn()
    {
        $this->loginAsCustomer();
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 1);
        $this->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertResponseCode(200);
    }

    public function testBlogPostDetaiNonExistingLoggedOut()
    {
        $blogPostId = 3;
        $this->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertResponseCode(404);
    }

    public function testBlogPostDetailOnlinePublicManufacturerPrivateLoggedOut()
    {
        $blogPostId = 2;
        $manufacturerId = 15;
        $this->changeBlogPost($blogPostId, 0, $manufacturerId);
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $requestUrl = $this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel');
        $this->get($requestUrl);
        $this->assertRedirectContains($this->Slug->getLogin($requestUrl));
        $this->assertAccessDeniedFlashMessage();
    }

    public function testBlogPostDetailChangeNullManufacturer()
    {
        $blogPostId = 2;
        $manufacturerId = null;
        $this->changeBlogPost($blogPostId, 0, $manufacturerId);
    }

    public function testAddBlogPostWithEmoji()
    {
        $this->BlogPost->save(
            $this->BlogPost->newEntity([
                'title' => 'test title',
                'short_description' => 'test title',
                'content' => 'This is a text with an emoji: 😟'
            ])
        );
    }

    protected function changeBlogPost($blogPostId, $isPrivate = 0, $manufacturerId = 0, $active = 1)
    {
        $blogPost = $this->BlogPost->get($blogPostId);
        $blogPost->is_private = $isPrivate;
        $blogPost->id_manufacturer = $manufacturerId;
        $blogPost->active = $active;
        $this->BlogPost->save($blogPost);
    }
}
