<?php

App::uses('AppCakeTestCase', 'Test');

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class BlogsControllerTest extends AppCakeTestCase
{

    public function testBlogDetailOnlinePublicLoggedOut()
    {
        $this->browser->get($this->Slug->getBlogPostDetail(2, 'Demo Blog Artikel'));
        $this->assert200OkHeader();
    }

    public function testBlogDetailOfflinePublicLoggedOut()
    {
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, null, null, false);
        $this->browser->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assert404NotFoundHeader();
    }

    public function testBlogDetailOnlinePrivateLoggedOut()
    {
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, true);
        $this->browser->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    public function testBlogDetailOnlinePrivateLoggedIn()
    {
        $this->loginAsCustomer();
        $blogPostId = 2;
        $this->changeBlogPost($blogPostId, true);
        $this->browser->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assert200OkHeader();
    }

    public function testBlogDetailPublicNonExistingLoggedOut()
    {
        $blogPostId = 3;
        $this->browser->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assert404NotFoundHeader();
    }

    public function testBlogDetailOnlinePublicManufacturerPrivateLoggedOut()
    {
        $blogPostId = 2;
        $manufacturerId = 15;
        $this->changeBlogPost($blogPostId, null, $manufacturerId);
        $this->changeManufacturer($manufacturerId, 'is_private', true);
        $this->browser->get($this->Slug->getBlogPostDetail($blogPostId, 'Demo Blog Artikel'));
        $this->assertAccessDeniedWithRedirectToLoginForm();
    }

    protected function changeBlogPost($blogPostId, $isPrivate = 0, $manufacturerId = 0, $active = 1)
    {
        $sql = 'UPDATE fcs_smart_blog_post SET is_private = :isPrivate, id_manufacturer = :manufacturerId, active = :active WHERE id_smart_blog_post = :blogPostId;';
        $params = array(
            'blogPostId' => $blogPostId,
            'isPrivate' => $isPrivate,
            'manufacturerId' => $manufacturerId,
            'active' => $active
        );
        $this->Customer->getDataSource()->fetchAll($sql, $params);
    }
}
