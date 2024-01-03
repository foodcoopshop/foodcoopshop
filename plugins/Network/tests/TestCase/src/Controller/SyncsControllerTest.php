<?php
declare(strict_types=1);

namespace Network\Test\TestCase;

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Core\Configure;
use Cake\View\View;
use Network\View\Helper\NetworkHelper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class SyncsControllerTest extends AppCakeTestCase
{

    public $Network;

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->Network = new NetworkHelper(new View());
        $this->correctSyncDomain();
    }

    protected function correctSyncDomain()
    {
        $this->dbConnection->execute("UPDATE fcs_sync_domains SET domain = REPLACE(domain, '{{serverName}}', '" . Configure::read('App.fullBaseUrl') . "');");
    }

    public function testDenyAccessIfVariableMemberFeeEnabled()
    {
        $this->loginAsMeatManufacturer();
        $this->get($this->Network->getSyncProducts());
        $this->assertResponseCode(403);
    }

    public function testDenyAccessIfVariableMemberFeeDisabledAndManufacturerHasNoSyncDomains()
    {
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'enabled_sync_domains', null);
        $this->loginAsVegetableManufacturer();
        $this->get($this->Network->getSyncProducts());
        $this->assertResponseCode(403);
    }

    public function testAllowAccessProductsIfVariableMemberFeeDisabled()
    {
        $this->disableVariableMemberFee();
        $this->loginAsVegetableManufacturer();
        $this->get($this->Network->getSyncProducts());
        $this->assertResponseOk();
    }

    public function testAllowAccessProductDataIfVariableMemberFeeDisabled()
    {
        $this->disableVariableMemberFee();
        $this->loginAsVegetableManufacturer();
        $this->get($this->Network->getSyncProductData());
        $this->assertResponseOk();
    }

    public function testSaveProductAssociationWithWrongDomain()
    {
        $this->disableVariableMemberFee();
        $this->loginAsVegetableManufacturer();
        $domain = 'http://www.not-available-domain.at';
        $response = $this->saveProductRelation(152, 152, '', $domain);

        $this->assertFalse((bool) $response->status);
        $this->assertRegExpWithUnquotedString($domain, $response->msg);
        $this->assertResponseCode(500);
    }

    public function testSaveProductAssociationForProductThatIsNotOwnedByLoggedInManufacturer()
    {
        $this->disableVariableMemberFee();
        $this->loginAsVegetableManufacturer();
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));

        $productId = 47; // joghurt, owner: milk manufactuer
        $productName = 'Joghurt';
        $response = $this->saveProductRelation($productId, $productId, $productName, Configure::read('App.fullBaseUrl'));

        $this->assertFalse((bool) $response->status);
        $this->assertRegExpWithUnquotedString('product ' . $productId . ' is not associated with manufacturer ' . $manufacturerId, $response->msg);
        $this->assertResponseCode(500);
    }

    public function testCorrectSaveProductAssociation()
    {
        $this->disableVariableMemberFee();
        $this->loginAsVegetableManufacturer();

        $productId = 339;
        $productName = 'Kartoffel';
        $response = $this->saveProductRelation($productId, $productId, $productName, Configure::read('App.fullBaseUrl'));
        $this->assertTrue($response->status);
        $this->assertNotEmpty($response->product);
        $this->assertEquals($response->product->localProductId, $productId);
        $this->assertEquals($response->product->remoteProductId, $productId);
        $this->assertEquals($response->product->domain, Configure::read('App.fullBaseUrl'));
        $this->assertEquals($response->product->productName, strip_tags($productName, '<span>'));
        $this->assertResponseOk();
    }

    public function testCorrectDeleteProductAssociation()
    {
        $this->disableVariableMemberFee();
        $this->loginAsVegetableManufacturer();

        $productId = 339;
        $productName = 'Kartoffel';
        $this->saveProductRelation($productId, $productId, $productName, Configure::read('App.fullBaseUrl'));

        $response = $this->deleteProductRelation($productId, $productId, $productName);
        $this->assertTrue($response->status);
        $this->assertNotEmpty($response->syncProduct);
        $this->assertEquals($response->syncProduct->local_product_id, $productId);
        $this->assertEquals($response->syncProduct->remote_product_id, $productId);
        $this->assertEquals($response->syncProduct->local_product_attribute_id, 0);
        $this->assertEquals($response->syncProduct->remote_product_attribute_id, 0);
        $this->assertResponseOk();
    }

    private function deleteProductRelation($localProductId, $remoteProductId, $productName)
    {
        $this->ajaxPost($this->Network->getDeleteProductRelation(), [
            'product' =>
                [
                    'localProductId' => $localProductId,
                    'remoteProductId' => $remoteProductId,
                    'domain' => Configure::read('App.fullBaseUrl'),
                    'productName' => $productName
                ]
            ]);
        return $this->getJsonDecodedContent();
    }

    private function saveProductRelation($localProductId, $remoteProductId, $productName, $domain)
    {
        $this->ajaxPost($this->Network->getSaveProductRelation(), [
            'product' =>
                [
                    'localProductId' => $localProductId,
                    'remoteProductId' => $remoteProductId,
                    'domain' => $domain,
                    'productName' => $productName
                ]
            ]);
        return $this->getJsonDecodedContent();
    }

    private function disableVariableMemberFee()
    {
        $this->changeConfiguration('FCS_USE_VARIABLE_MEMBER_FEE', 0);
        $manufacturerId = $this->Customer->getManufacturerIdByCustomerId(Configure::read('test.vegetableManufacturerId'));
        $this->changeManufacturer($manufacturerId, 'variable_member_fee', 0);
    }
}
