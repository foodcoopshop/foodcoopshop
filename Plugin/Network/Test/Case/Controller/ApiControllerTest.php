<?php

App::uses('AppCakeTestCase', 'Test');

/**
 * ApiControllerTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ApiControllerTest extends AppCakeTestCase
{

    const API_URL = '/api/getProducts';

    public function testBasicAuthLoggedOut()
    {
        $this->browser->get(self::API_URL);
        $this->assertEquals('Basic', $this->browser->getAuthentication(), 'page not protected with basic auth');
        $this->assertWrongCredentials();
    }

    public function testWrongLogin()
    {
        $this->browser->get(self::API_URL);
        $this->browser->authenticate(
            Configure::read('test.loginEmailMeatManufacturer'),
            'wrong-password'
        );
        $this->assertWrongCredentials();
    }

    public function testLoginAndResponse()
    {
        $this->loginWithBaseAuth();
        $response = $this->assertJsonOK();
        $this->assertEquals($response->loggedUser->email, Configure::read('test.loginEmailMeatManufacturer'), 'user not set in response');
        $this->assertRegExpWithUnquotedString($response->app->domain, Configure::read('app.cakeServerName'), 'domain not set in response');
        $this->assertRegExpWithUnquotedString($response->app->name, Configure::read('app.db_config_FCS_APP_NAME'), 'app name not set in response');
        $this->assertEquals(count($response->products), 16, 'product count not correct');
    }

    /**
     * this function uses real json response
     * parent function asserts not perfectly implemented "json" response
     * this is the correct way!
     * @see AppCakeTestCase::assertJsonOk()
     */
    protected function assertJsonOk()
    {
        $response = $this->browser->getJsonDecodedContent();
        $this->assertEquals('stdClass', get_class($response), 'json not valid');
        $this->assertRegExpWithUnquotedString('Content-Type: application/json;', $this->browser->getHeaders(), 'no json header set');
        return $response;
    }

    private function assertWrongCredentials()
    {
        $this->assert401UnauthorizedHeader();
        $response = $this->browser->getJsonDecodedContent();
        $this->assertRegExpWithUnquotedString($response->name, 'Unauthorized');
        $this->assertRegExpWithUnquotedString($response->message, 'Unauthorized');
        $this->assertRegExpWithUnquotedString($response->url, self::API_URL);
    }

    private function loginWithBaseAuth()
    {
        $this->browser->get(self::API_URL);
        $this->browser->authenticate(
            Configure::read('test.loginEmailMeatManufacturer'),
            Configure::read('test.loginPassword')
        );
    }
}
