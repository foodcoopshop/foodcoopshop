<?php
/**
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;

class BlogPostsControllerTest extends AppCakeTestCase
{

    use IntegrationTestTrait;

    public $BlogPost;

    public function setUp(): void
    {
        parent::setUp();
        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
    }

    public function testBlogPostDetailOnlinePublicLoggedOut()
    {
        $_SERVER['REQUEST_URI'] = $this->Slug->getBlogPostDetail(2, 'Demo Blog Artikel');
        $this->get($_SERVER['REQUEST_URI']);
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
        $this->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->Slug->getLogin($blogPostId, 'Demo Blog Artikel');
        $this->assertFlashMessage('Zugriff verweigert, bitte melde dich an.');
    }

    public function testBlogPostDetailOnlinePrivateLoggedIn()
    {
        $this->loginAsCustomer();
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 1);
        $_SERVER['REQUEST_URI'] = $this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel');
        $this->get($_SERVER['REQUEST_URI']);
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
        $this->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertRedirectContains('/anmelden?redirect=%2Faktuelles%2F2-Demo-Blog-Artikel');
        $this->assertFlashMessage('Zugriff verweigert, bitte melde dich an.');
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
        $query = 'UPDATE ' . $this->BlogPost->getTable() . ' SET is_private = :isPrivate, id_manufacturer = :manufacturerId, active = :active WHERE id_blog_post = :blogPostId;';
        $params = [
            'blogPostId' => $blogPostId,
            'isPrivate' => $isPrivate,
            'manufacturerId' => $manufacturerId,
            'active' => $active
        ];
        $statement = $this->dbConnection->prepare($query);
        $statement->execute($params);
    }
}
