<?php
declare(strict_types=1);

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

namespace Network\Test\TestCase;

use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\View\View;
use Network\View\Helper\NetworkHelper;

class SyncDomainsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->Network = new NetworkHelper(new View());
        $this->loginAsSuperadmin();
    }

    public function testAddSyncDomainWithHttp(): void
    {
        $this->addSyncDomain('http://www.example.com');
        $this->assertResponseContains('Die Domain muss mit https:// beginnen.');
    }

    public function testAddSyncDomainWithEmptyDomain(): void
    {
        $this->addSyncDomain('');
        $this->assertResponseContains('Bitte gib eine Domain ein, sie muss mit https:// beginnen.');
    }

    public function testAddSyncDomainWithTrailingSlash(): void
    {
        $this->addSyncDomain('https://www.example.com/');
        $this->assertResponseContains('Die Domain darf nur aus https:// und dem Hostnamen bestehen (ohne / am Ende).');
    }

    public function testAddSyncDomainAlreadyExisting(): void
    {
        $this->addSyncDomain('https://www.example.com');
        $this->addSyncDomain('https://www.example.com');
        $this->assertResponseContains('Die Domain ist bereits vorhanden.');
    }

    public function testAddSyncDomainWithHttpsAndCapitalLetter(): void
    {
        $this->addSyncDomain('https://www.valid-Domain.com');
        $this->assertFlashMessage('Die Remote-Foodcoop <b>https://www.valid-domain.com</b> wurde erstellt.');
    }

    public function testAddSyncDomainWithHttps(): void
    {
        $this->addSyncDomain('https://www.valid-domain.com');
        $this->assertFlashMessage('Die Remote-Foodcoop <b>https://www.valid-domain.com</b> wurde erstellt.');
    }

    /**
     * @param string $domain
     */
    private function addSyncDomain($domain): void
    {
        $this->post($this->Network->getSyncDomainAdd(), [
            'SyncDomains' =>
            [
                'domain' => $domain
            ],
            'referer' => '/'
        ]);
    }
}
