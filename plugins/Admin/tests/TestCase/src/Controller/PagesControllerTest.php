<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 5.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;

class AdminPagesControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testEditHomeGet(): void
    {
        $this->loginAsSuperadmin();

        $this->get($this->Slug->getPageEditHome());

        $this->assertResponseOk();
        $this->assertResponseContains('id="pages-content"');
    }

    public function testEditHomePost(): void
    {
        $this->loginAsSuperadmin();

        $this->post($this->Slug->getPageEditHome(), [
            'Pages' => [
                'content' => '<p>Neue Startseite</p>',
            ],
            'Configurations' => [
                'FCS_FOODCOOPS_MAP_ENABLED' => 0,
            ],
            'referer' => '/',
        ]);

        $this->assertFlashMessage('Die Startseite wurde erfolgreich geändert.');
        $this->assertRedirect('/');

        $configurationsTable = $this->getTableLocator()->get('Configurations');
        $configuration = $configurationsTable->find('all', conditions: [
            'Configurations.name' => 'FCS_HOME_TEXT',
        ])->first();

        $this->assertEquals('<p>Neue Startseite</p>', $configuration->value);

        $mapConfiguration = $configurationsTable->find('all', conditions: [
            'Configurations.name' => 'FCS_FOODCOOPS_MAP_ENABLED',
        ])->first();

        $this->assertEquals('0', $mapConfiguration->value);
    }
}
