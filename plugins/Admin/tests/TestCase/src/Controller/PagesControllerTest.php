<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Model\Entity\Block;

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
        $this->assertResponseContains('Blöcke (nur für uneingeloggte User)');
    }

    public function testEditHomePost(): void
    {
        $this->loginAsSuperadmin();

        $this->post($this->Slug->getPageEditHome(), [
            'Pages' => [
                'content' => '<p>Neue Startseite</p>',
            ],
            'Blocks' => [
                'block-1' => [
                    'id' => 1,
                    'image_position' => Block::IMAGE_POSITION_LEFT,
                    'heading' => 'Neuer Block',
                    'content' => '<p>Blockinhalt</p>',
                    'position' => 5,
                    'active' => 1,
                ],
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

        $blocksTable = $this->getTableLocator()->get('Blocks');
        $block = $blocksTable->get(1);
        $this->assertEquals('Neuer Block', $block->heading);
        $this->assertEquals('<p>Blockinhalt</p>', $block->content);
        $this->assertEquals(Block::IMAGE_POSITION_LEFT, (int)$block->image_position);
        $this->assertEquals(5, $block->position);
        $this->assertEquals(1, $block->active);

        $mapConfiguration = $configurationsTable->find('all', conditions: [
            'Configurations.name' => 'FCS_FOODCOOPS_MAP_ENABLED',
        ])->first();

        $this->assertEquals('0', $mapConfiguration->value);
    }

    public function testEditHomePostIgnoresTemplateRow(): void
    {
        $this->loginAsSuperadmin();

        $this->post($this->Slug->getPageEditHome(), [
            'Pages' => [
                'content' => '<p>Neue Startseite</p>',
            ],
            'Blocks' => [
                'block-1' => [
                    'id' => 1,
                    'image_position' => Block::IMAGE_POSITION_LEFT,
                    'heading' => 'Neuer Block',
                    'content' => '<p>Blockinhalt</p>',
                    'position' => 5,
                    'active' => 1,
                ],
                '__INDEX__' => [
                    'id' => '',
                    'tmp_image' => '',
                    'image_position' => Block::IMAGE_POSITION_LEFT,
                    'heading' => '',
                    'content' => '',
                    'position' => '',
                    'active' => 0,
                ],
            ],
            'Configurations' => [
                'FCS_FOODCOOPS_MAP_ENABLED' => 0,
            ],
            'referer' => '/',
        ]);

        $this->assertFlashMessage('Die Startseite wurde erfolgreich geändert.');
        $this->assertRedirect('/');

        $blocksTable = $this->getTableLocator()->get('Blocks');
        $this->assertEquals(1, $blocksTable->find()->count());
    }

    public function testEditHomePostAllowsExistingImageOnlyBlock(): void
    {
        $this->loginAsSuperadmin();

        $blocksTable = $this->getTableLocator()->get('Blocks');
        $block = $blocksTable->get(1);
        $block = $blocksTable->patchEntity($block, [
            'image' => 'demo.jpg',
            'heading' => '',
            'content' => '',
        ], [
            'validate' => false,
        ]);
        $blocksTable->saveOrFail($block, [
            'validate' => false,
        ]);

        $this->post($this->Slug->getPageEditHome(), [
            'Pages' => [
                'content' => '<p>Neue Startseite</p>',
            ],
            'Blocks' => [
                'block-1' => [
                    'id' => 1,
                    'tmp_image' => '',
                    'image_position' => Block::IMAGE_POSITION_LEFT,
                    'heading' => '',
                    'content' => '',
                    'position' => 5,
                    'active' => 1,
                ],
            ],
            'Configurations' => [
                'FCS_FOODCOOPS_MAP_ENABLED' => 0,
            ],
            'referer' => '/',
        ]);

        $this->assertFlashMessage('Die Startseite wurde erfolgreich geändert.');
        $this->assertRedirect('/');

        $savedBlock = $blocksTable->get(1);
        $this->assertSame('demo.jpg', $savedBlock->image);
        $this->assertSame('', $savedBlock->heading);
        $this->assertSame('', $savedBlock->content);
    }
}
