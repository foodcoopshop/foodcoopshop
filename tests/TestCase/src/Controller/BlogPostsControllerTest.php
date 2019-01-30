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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\ORM\TableRegistry;

class BlogPostsControllerTest extends AppCakeTestCase
{

    public $BlogPost;

    public function setUp()
    {
        parent::setUp();
        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
    }

    public function testBlogPostDetailOnlinePublicLoggedOut()
    {
        $this->httpClient->get($this->Slug->getBlogPostDetail(2, 'Demo Blog Artikel'));
        $this->assert200OkHeader();
    }

    public function testBlogPostDetailOfflinePublicLoggedOut()
    {
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 0, 0, 0);
        $this->httpClient->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assert404NotFoundHeader();
    }

    public function testBlogPostDetailOnlinePrivateLoggedOut()
    {
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 1);
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testBlogPostDetailOnlinePrivateLoggedIn()
    {
        $this->loginAsCustomer();
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, 1);
        $this->httpClient->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assert200OkHeader();
    }

    public function testBlogPostDetaiNonExistingLoggedOut()
    {
        $blogPostId = 3;
        $this->httpClient->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assert404NotFoundHeader();
    }

    public function testBlogPostDetailOnlinePublicManufacturerPrivateLoggedOut()
    {
        $blogPostId = 2;
        $manufacturerId = 15;
        $this->changeBlogPost($blogPostId, 0, $manufacturerId);
        $this->changeManufacturer($manufacturerId, 'is_private', 1);
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertAccessDeniedWithRedirectToLoginForm();
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
