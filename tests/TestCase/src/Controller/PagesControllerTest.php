<?php
declare(strict_types=1);

use App\Command\SavedLocalizedJsAsStaticFileCommand;
use App\Model\Entity\Payment;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\AssertPagesForErrorsTrait;
use App\Test\TestCase\Traits\LoginTrait;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PagesControllerTest extends AppCakeTestCase
{

    protected $Page;
    public $Network;

    use AssertPagesForErrorsTrait;
    use AppIntegrationTestTrait;
    use LoginTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->Page = $this->getTableLocator()->get('Pages');
    }

    public function testAllPublicUrls()
    {
        $testUrls = [
            $this->Slug->getHome(),
            $this->Slug->getManufacturerList(),
            $this->Slug->getManufacturerDetail(4, 'Demo Fleisch-Hersteller'),
            $this->Slug->getBlogList(),
            $this->Slug->getBlogPostDetail(2, 'Demo Blog Artikel'),
            $this->Slug->getNewPasswordRequest(),
            $this->Slug->getPageDetail(3, 'Page'),
            $this->Slug->getLogin(),
            $this->Slug->getListOfAllergens(),
            $this->Slug->getTermsOfUse(),
            $this->Slug->getPrivacyPolicy(),
            SavedLocalizedJsAsStaticFileCommand::ROUTE,
        ];
        $this->assertPagesForErrors($testUrls);
    }

    /**
     * test urls that are only available for superadmins and / users that are logged in
     */
    public function testAllSuperadminUrls()
    {
        $this->loginAsSuperadmin();

        $testUrls = [
            $this->Slug->getProductSearch('artischocke'),
            $this->Slug->getNewProducts(),
            $this->Slug->getAdminHome(),
            $this->Slug->getCartDetail(),
            $this->Slug->getCartFinished(1),
            // $this->Slug->getOrderDetailsList(),
            // $this->Slug->getOrderDetailsList().'?groupBy=customer&pickupDay[]=02.02.2018',
            // $this->Slug->getOrderDetailsList().'?groupBy=manufacturer&pickupDay[]=02.02.2018',
            // $this->Slug->getOrderDetailsList().'?groupBy=product&pickupDay[]=02.02.2018',
            $this->Slug->getOrderLists(),
            $this->Slug->getActionLogsList(),
            $this->Slug->getPagesListAdmin(),
            $this->Slug->getPageAdd(),
            $this->Slug->getPageEdit(3),
            $this->Slug->getDepositList(4),
            $this->Slug->getDepositOverviewDiagram(),
            $this->Slug->getDepositDetail(4, '2016-11'),
            $this->Slug->getCreditBalance(88),
            $this->Slug->getCreditBalanceSum(),
            $this->Slug->getChangePassword(),
            $this->Slug->getCustomerListAdmin(),
            $this->Slug->getCustomerProfile(),
            $this->Slug->getCustomerEdit(88),
            $this->Slug->getProductAdmin(),
            $this->Slug->getProductAdmin('all'),
            $this->Slug->getReport(Payment::TYPE_PRODUCT),
            $this->Slug->getReport(Payment::TYPE_PAYBACK),
            $this->Slug->getReport(Payment::TYPE_DEPOSIT),
            $this->Slug->getMyCreditBalance(),
            $this->Slug->getPaymentEdit(1),
            $this->Slug->getBlogPostListAdmin(),
            $this->Slug->getBlogPostAdd(),
            $this->Slug->getBlogPostEdit(2),
            $this->Slug->getManufacturerAdmin(),
            $this->Slug->getManufacturerList(),
            $this->Slug->getManufacturerAdd(),
            $this->Slug->getManufacturerEdit(5),
            $this->Slug->getManufacturerEditOptions(5),
            $this->Slug->getAttributesList(),
            $this->Slug->getAttributeAdd(),
            $this->Slug->getAttributeEdit(33),
            $this->Slug->getCategoriesList(),
            $this->Slug->getCategoryAdd(),
            $this->Slug->getCategoryEdit(16),
            $this->Slug->getTaxesList(),
            $this->Slug->getTaxAdd(),
            $this->Slug->getTaxEdit(2),
            $this->Slug->getSlidersList(),
            $this->Slug->getSliderAdd(),
            $this->Slug->getSliderEdit(6),
            $this->Slug->getStatistics(4),
            $this->Network->getSyncDomainAdd(),
            $this->Network->getSyncDomainEdit(1),
            $this->Slug->getConfigurationsList(),
            $this->Slug->getConfigurationEdit('FCS_NO_DELIVERY_DAYS_GLOBAL'),
            $this->Slug->getCronjobsList(),
            $this->Slug->getCronjobEdit(1),
            $this->Slug->getProductImport(4),
        ];

        $this->assertPagesForErrors($testUrls);

        $this->logout();
    }

    /**
     * test urls that are only available for manufacturers or have different content
     */
    public function testAllManufacturerUrls()
    {
        $this->loginAsMeatManufacturer();

        $testUrls = [
            $this->Slug->getManufacturerMyOptions(),
            $this->Slug->getMyDepositList(),
            $this->Slug->getMyStatistics(),
            $this->Slug->getManufacturerProfile(),
            $this->Slug->getProductAdmin(),
            $this->Slug->getMyProductImport(),
        ];

        $this->assertPagesForErrors($testUrls);

        $this->logout();
    }

    public function testAllManufacturerUrlsAnonymized()
    {
        $this->changeManufacturer(4, 'anonymize_customers', 1);
        $this->loginAsMeatManufacturer();

        $testUrls = [
            $this->Slug->getOrderDetailsList() . '?pickupDay[]=02.02.2018',
        ];

        foreach ($testUrls as $url) {
            $this->get($url);
            $this->assertResponseNotContains('Demo Superadmin');
            $this->assertResponseContains('D.S. - ID 92');
        }

        $this->assertPagesForErrors($testUrls);
    }

    public function test404PagesLoggedOut()
    {
        $testUrls = [
            '/xxx',
            $this->Slug->getManufacturerDetail(4234, 'not valid manufacturer name'),
            $this->Slug->getPageDetail(4234, 'not valid page name'),
        ];
        $this->assertPagesFor404($testUrls);
    }

    /**
     * products and categories are not visible for guests in the test settings
     * to test the correct 404 page, a valid login is required
     */
    public function test404PagesLoggedIn()
    {
        $this->loginAsSuperadmin();
        $testUrls = [
            $this->Slug->getProductDetail(4234, 'not valid product name'),
            $this->Slug->getCategoryDetail(4234, 'not valid category name'),
            $this->Slug->getCategoryDetail(16, 'Fleischprodukte') . '?page=2',
            $this->Slug->getManufacturerDetail(4, 'Demo Fleisch-Hersteller') . '?page=2',
        ];
        $this->assertPagesFor404($testUrls);
        $this->logout();
    }

    public function testRedirectIfPage1()
    {
        $this->loginAsSuperadmin();
        $urls = [
            $this->Slug->getCategoryDetail(16, 'Fleischprodukte'),
            $this->Slug->getManufacturerDetail(4, 'Demo Fleisch-Hersteller'),
        ];
        foreach($urls as $url) {
            $this->get($url . '?page=1');
            $this->assertRedirectContains($url);
            $this->assertRedirectNotContains('?page=1');
        }
    }

    public function testPageDetailOnlinePublicLoggedOut()
    {
        $this->get($this->Slug->getPageDetail(3, 'Page'));
        $this->assertResponseCode(200);
    }

    public function testPageDetailOfflinePublicLoggedOut()
    {
        $pageId = 3;
        $this->changePage($pageId, 0, 0);
        $this->get($this->Slug->getPageDetail($pageId, 'Page'));
        $this->assertResponseCode(404);
    }

    public function testPageDetailOnlinePrivateLoggedOut()
    {
        $pageId = 3;
        $this->changePage($pageId, 1);
        $this->get($this->Slug->getPageDetail($pageId, 'Page'));
        $this->assertAccessDeniedFlashMessage();
    }

    public function testPageDetailOnlinePrivateLoggedIn()
    {
        $this->loginAsCustomer();
        $pageId = 3;
        $this->changePage($pageId, 1);
        $this->get($this->Slug->getPageDetail($pageId, 'Page'));
        $this->assertResponseCode(200);
    }

    public function testPageDetailNonExistingLoggedOut()
    {
        $pageId = 30;
        $this->get($this->Slug->getPageDetail($pageId, 'Demo Page'));
        $this->assertResponseCode(404);
    }

    protected function assertPagesFor404($testPages)
    {
        foreach ($testPages as $url) {
            $this->get($url);
            $this->assertResponseCode(404);
        }
    }

    protected function changePage($pageId, $isPrivate = 0, $active = 1)
    {
        $pageEntity = $this->Page->get($pageId);
        $pageEntity->active = $active;
        $pageEntity->is_private = $isPrivate;
        $this->Page->save($pageEntity);
    }
}
