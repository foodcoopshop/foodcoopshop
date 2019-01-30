<?php

/**
 * SyncControllerTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace Network\Test\TestCase;

use App\Test\TestCase\AppCakeTestCase;
use Cake\View\View;
use Network\View\Helper\NetworkHelper;

class SyncDomainsControllerTest extends AppCakeTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->Network = new NetworkHelper(new View());
        $this->loginAsSuperadmin();
    }

    public function testAddSyncDomainWithHttp()
    {
        $this->addSyncDomain('http://www.example.com');
        $this->assertRegExpWithUnquotedString('Die Domain muss mit https:// beginnen.', $this->httpClient->getContent());
    }

    public function testAddSyncDomainWithEmptyDomain()
    {
        $this->addSyncDomain('');
        $this->assertRegExpWithUnquotedString('Bitte gib eine Domain ein, sie muss mit https:// beginnen.', $this->httpClient->getContent());
    }

    public function testAddSyncDomainWithTrailingSlash()
    {
        $this->addSyncDomain('https://www.example.com/');
        $this->assertRegExpWithUnquotedString('Die Domain darf nur aus https:// und dem Hostnamen bestehen (ohne / am Ende).', $this->httpClient->getContent());
    }

    public function testAddSyncDomainAlreadyExisting()
    {
        $this->addSyncDomain('https://www.example.com');
        $this->addSyncDomain('https://www.example.com');
        $this->assertRegExpWithUnquotedString('Die Domain ist bereits vorhanden.', $this->httpClient->getContent());
    }

    public function testAddSyncDomainWithHttps()
    {
        $this->addSyncDomain('https://www.valid-domain.com');
        $this->assertRegExpWithUnquotedString('Die Remote-Foodcoop <b>https://www.valid-domain.com</b> wurde erstellt.', $this->httpClient->getContent());
    }

    /**
     * @param string $domain
     */
    private function addSyncDomain($domain)
    {
        $this->httpClient->followOneRedirectForNextRequest();
        $this->httpClient->post($this->Network->getSyncDomainAdd(), [
            'SyncDomains' =>
            [
                'domain' => $domain
            ],
            'referer' => '/'
        ]);
    }
}
