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
            $this->Slug->getLogin()
        );
        
        foreach ($testUrls as $url) {
            $this->browser->get($url);
            if ($this->hasPageErrors()) {
                echo '<a href="' . $url . '">' . $url . '</a><br />';
                echo $this->browser->getContent();
            }
        }
    }
    
    public function testAllSuperadminUrls()
    {
        $this->browser->doFoodCoopShopLogin();
        
        $testUrls = array(
            $this->Slug->getCartDetail(),
            $this->Slug->getPagesListAdmin(),
            $this->Slug->getPageAdd(),
            $this->Slug->getPageEdit(3),
            $this->Slug->getDepositList(4),
            $this->Slug->getDepositDetail(4, '2016-11'),
            $this->Slug->getCreditBalance(),
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
    
        foreach ($testUrls as $url) {
            $this->browser->get($url);
            if ($this->hasPageErrors()) {
                echo '<a href="' . $url . '">' . $url . '</a><br />';
                echo $this->browser->getContent();
            }
        }
        
        $this->browser->doFoodCoopShopLogout();
    }

    public function test404Pages()
    {
        $testUrls = array(
            '/xxx',
            $this->Slug->getProductDetail(4234, 'not valid product name'),
            $this->Slug->getManufacturerDetail(4234, 'not valid manufacturer name'),
            $this->Slug->getPageDetail(4234, 'not valid page name'),
            $this->Slug->getCategoryDetail(4234, 'not valid category name')
        );
        
        foreach ($testUrls as $url) {
            $this->browser->get($url);
            if (! $this->is404Page()) {
                echo '<a href="' . $url . '">' . $url . '</a><br />';
                echo $this->browser->getContent();
            }
        }
    }

    private function is404Page()
    {
        $fail = false;
        $html = $this->browser->getContent();
        $fail |= ! $this->assertRegExp('/wurde leider nicht gefunden./', $html);
        $headers = $this->browser->getHeaders();
        $fail |= ! $this->assertRegExp("/404 Not Found/", $headers);
        return $fail;
    }

    /**
     * prueft html auf Fehlermeldungen.
     * 
     * @return boolean
     */
    private function hasPageErrors()
    {
        $fail = false;
        $html = $this->browser->getContent();
        $fail |= ! $this->assertNotRegExp('/class="cake-stack-trace"/', $html);
        $fail |= ! $this->assertNotRegExp('/class="cake-error"/', $html);
        $fail |= ! $this->assertNotRegExp('/\bFatal error\b/', $html);
        $fail |= ! $this->assertNotRegExp('/undefined/', $html);
        $fail |= ! $this->assertNotRegExp('/exception \'[^\']+\' with message/', $html); // alle Exceptions, die irgendwie nicht abgefangen werden
        $fail |= ! $this->assertNotRegExp('/\<strong\>(Error|Exception)\s*:\s*\<\/strong\>/', $html);
        $fail |= ! $this->assertNotRegExp('/Parse error/', $html);
        $fail |= ! $this->assertNotRegExp('/Not Found/', $html); // if element to render does not exist
        $fail |= ! $this->assertNotRegExp('/\/app\/views\/errors\//', $html); // for catching cake error messages (missing view / missing controller...)
        $fail |= ! $this->assertNotRegExp('/error in your SQL syntax/', $html); // SQL syntax fehler
        $fail |= ! $this->assertNotRegExp('/ERROR!/', $html); // manuelle fehlermeldung
        $fail |= ! $this->assertRegExp('/\<\/body\>/', $html); // weiße seite? nicht fertig gerendert?
        return $fail;
    }
}

?>