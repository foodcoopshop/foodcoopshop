<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use Cake\Core\Configure;

class SelfServiceControllerTest extends AppCakeTestCase
{
    
    public function testPageSelfService()
    {
        $this->loginAsSuperadmin();
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $testUrls = [
            $this->Slug->getSelfService()
        ];
        $this->assertPagesForErrors($testUrls);
    }
    
    public function testBarCodeLoginAsSuperadminIfNotEnabled()
    {
        $this->doBarCodeLogin();
        $this->assertRegExpWithUnquotedString(__('Signing_in_failed_account_inactive_or_password_wrong?'), $this->httpClient->getContent());
    }
    
    public function testBarCodeLoginAsSuperadminValid()
    {
        $this->changeConfiguration('FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED', 1);
        $this->doBarCodeLogin();
        $this->assertNotRegExpWithUnquotedString(__('Signing_in_failed_account_inactive_or_password_wrong?'), $this->httpClient->getContent());
    }
    
    private function doBarCodeLogin()
    {
        $this->httpClient->loginEmail = Configure::read('test.loginEmailSuperadmin');
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->post($this->Slug->getLogin(), [
            'barCode' => Configure::read('test.superadminBarCode')
        ]);
    }
    
}
