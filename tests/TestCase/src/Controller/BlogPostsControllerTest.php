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

    public function testBlogPostDetailOnlinePublicLoggedOut(): void
    {
        $this->get($this->Slug->getBlogPostDetail(2, 'Demo Blog Artikel'));
        $this->assertResponseCode(200);
    }

    public function testBlogPostDetailOfflinePublicLoggedOut(): void
    {
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 0, 0, 0);
        $this->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertResponseCode(404);
    }

    public function testBlogPostDetailOnlinePrivateLoggedOut(): void
    {
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 1);
        $requestUrl = $this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel');
        $this->get($requestUrl);
        $this->assertRedirectContains($this->Slug->getLogin($requestUrl));
        $this->assertAccessDeniedFlashMessage();
    }

    public function testBlogPostDetailOnlinePrivateLoggedIn(): void
    {
        $this->loginAsCustomer();
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 1);
        $this->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertResponseCode(200);
    }

    public function testBlogPostDetaiNonExistingLoggedOut(): void
    {
        $blogPostId = 3;
        $this->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertResponseCode(404);
    }

    public function testBlogPostDetailOnlinePublicManufacturerPrivateLoggedOut(): void
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

    public function testBlogPostDetailChangeNullManufacturer(): void
    {
        $blogPostId = 2;
        $manufacturerId = null;
        $this->changeBlogPost($blogPostId, 0, $manufacturerId);
    }

    public function testAddBlogPostWithEmoji(): void
    {
        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPostsTable->save(
            $blogPostsTable->newEntity([
                'title' => 'test title',
                'short_description' => 'test title',
                'content' => 'This is a text with an emoji: 😟'
            ])
        );
    }

    protected function changeBlogPost($blogPostId, $isPrivate = 0, $manufacturerId = 0, $active = 1): void
    {
        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPost = $blogPostsTable->get($blogPostId);
        $blogPost->is_private = $isPrivate;
        $blogPost->id_manufacturer = $manufacturerId;
        $blogPost->active = $active;
        $blogPostsTable->save($blogPost);
    }
}
