<?php

App::uses('AppCakeTestCase', 'Test');

/**
 * PagesControllerTest
 *
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
class PagesControllerTest extends AppCakeTestCase
{

    public function testAllPublicUrls()
    {
        $testUrls = array(
            $this->Slug->getHome(),
            $this->Slug->getManufacturerList(),
            $this->Slug->getManufacturerDetail(4, 'Demo Gemüse-Hersteller'),
            $this->Slug->getManufacturerBlogList(4, 'Demo Gemüse-Hersteller'),
            $this->Slug->getBlogList(),
            $this->Slug->getCategoryDetail(16, 'Fleischprodukte'),
            $this->Slug->getProductDetail(339, 'Kartoffel'),
            $this->Slug->getBlogPostDetail(2, 'Demo Blog Artikel'),
            $this->Slug->getNewPasswordRequest(),
            $this->Slug->getPageDetail(9, 'Impressum'),
            $this->Slug->getLogin(),
            $this->Slug->getTermsOfUse(),
            $this->Slug->getPrivacyPolicy()
        );
        $this->assertPagesForErrors($testUrls);
    }

    /**
     * test urls that are only available for superadmins
     */
    public function testAllSuperadminUrls()
    {
        $this->loginAsSuperadmin();

        $testUrls = array(
            $this->Slug->getCartDetail(),
            $this->Slug->getPagesListAdmin(),
            $this->Slug->getPageAdd(),
            $this->Slug->getPageEdit(3),
            $this->Slug->getDepositList(4),
            $this->Slug->getDepositDetail(4, '2016-11'),
            $this->Slug->getCreditBalance(88),
            $this->Slug->getChangePassword(),
            $this->Slug->getCustomerProfile(),
            $this->Slug->getReport('product'),
            $this->Slug->getReport('deposit'),
            $this->Slug->getBlogPostListAdmin(),
            $this->Slug->getBlogPostAdd(),
            $this->Slug->getBlogPostEdit(2),
            $this->Slug->getAttributesList(),
            $this->Slug->getAttributeAdd(),
            $this->Slug->getAttributeEdit(32),
            $this->Slug->getCategoriesList(),
            $this->Slug->getCategoryAdd(),
            $this->Slug->getCategoryEdit(17),
            $this->Slug->getTaxesList(),
            $this->Slug->getTaxAdd(),
            $this->Slug->getTaxEdit(2),
            $this->Slug->getSlidersList(),
            $this->Slug->getSliderAdd(),
            $this->Slug->getSliderEdit(6),
            $this->Slug->getConfigurationsList(),
            $this->Slug->getConfigurationEdit(544)
        );

        $this->assertPagesForErrors($testUrls);

        $this->browser->doFoodCoopShopLogout();
    }

    /**
     * test urls that are only available for manufacturers
     */
    public function testAllManufacturerUrls()
    {
        $this->loginAsMeatManufacturer();

        $testUrls = array(
            $this->Slug->getManufacturerMyOptions(),
            $this->Slug->getMyDepositList(),
            $this->Slug->getManufacturerProfile()
        );

        $this->assertPagesForErrors($testUrls);

        $this->browser->doFoodCoopShopLogout();
    }


    public function test404PagesLoggedOut()
    {
        $testUrls = array(
            '/xxx',
            $this->Slug->getManufacturerDetail(4234, 'not valid manufacturer name'),
            $this->Slug->getPageDetail(4234, 'not valid page name'),
        );
        $this->assertPagesFor404($testUrls);
    }

    /**
     * products and categories are not visible for guests in the test settings
     * to test the correct 404 page, a valid login is required
     */
    public function test404PagesLoggedIn()
    {
        $this->loginAsSuperadmin();

        $testUrls = array(
            $this->Slug->getProductDetail(4234, 'not valid product name'),
            $this->Slug->getCategoryDetail(4234, 'not valid category name')
        );
        $this->assertPagesFor404($testUrls);
        $this->browser->doFoodCoopShopLogout();
    }
}
