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
use App\Model\Entity\Page;

class AdminPagesControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testEditHomeGet(): void
    {
        $this->changeConfiguration('FCS_HOME_META_TITLE', 'Regional | Local farms');
        $this->changeConfiguration('FCS_HOME_META_DESCRIPTION', 'Fresh food from local farms.');
        $this->loginAsSuperadmin();

        $this->get($this->Slug->getPageEditHome());

        $this->assertResponseOk();
        $this->assertResponseContains('id="pages-content"');
        $this->assertResponseContains('Blöcke (nur für uneingeloggte User)');
        $this->assertResponseContains('name="Configurations[FCS_HOME_META_TITLE]"');
        $this->assertResponseContains('for="configurations-fcs-home-meta-title">Titel</label>');
        $this->assertResponseContains('home-edit-section-metadata');
        $this->assertResponseRegExp('/home-edit-section-map.*<\/section>\s*<section class="home-edit-section home-edit-section-metadata">\s*<hr>\s*<h2>.*<\/form>/s');
        $this->assertResponseContains('value="Regional | Local farms"');
        $this->assertResponseContains('Fresh food from local farms.');
    }

    public function testEditHomeEmptyTitleShowsDefaultPlaceholder(): void
    {
        $this->changeConfiguration('FCS_APP_NAME', 'Demo Shop');
        $this->changeConfiguration('FCS_HOME_META_TITLE', '');
        $this->loginAsSuperadmin();

        $this->get($this->Slug->getPageEditHome());

        $this->assertResponseOk();
        $this->assertResponseContains('placeholder="' . __('Welcome') . ' - Demo Shop"');
        $this->assertSame('', $this->getTableLocator()->get('Configurations')->get('FCS_HOME_META_TITLE')->value);
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
                    'primary_label' => 'Mehr Infos',
                    'primary_href' => 'https://www.example.com/neuigkeiten',
                    'secondary_label' => 'Kontakt',
                    'secondary_href' => 'https://www.example.com/kontakt',
                    'position' => 5,
                    'active' => 1,
                ],
            ],
            'Configurations' => [
                'FCS_FOODCOOPS_MAP_ENABLED' => 0,
                'FCS_HOME_META_TITLE' => '  Regional | Local farms  ',
                'FCS_HOME_META_DESCRIPTION' => '  Fresh food from local farms.  ',
            ],
            'HeaderPromo' => [
                'title' => 'Startseiten Titel',
                'lead_text' => 'Startseiten Lead',
                'text' => '<p><strong>Startseiten Text</strong></p>',
                'primary_label' => 'Primar',
                'primary_href' => 'https://www.example.com/anmelden',
                'secondary_label' => 'Sekundar',
                'secondary_href' => 'https://www.example.com',
            ],
            'referer' => '/',
        ]);

        $this->assertFlashMessage(__('The homepage has been changed successfully.'));
        $this->assertRedirect('/');

        $configurationsTable = $this->getTableLocator()->get('Configurations');
        $configuration = $configurationsTable->find('all', conditions: [
            'Configurations.name' => 'FCS_HOME_TEXT',
        ])->first();

        $this->assertEquals('<p>Neue Startseite</p>', $configuration->value);
        $this->assertSame('Regional | Local farms', $configurationsTable->get('FCS_HOME_META_TITLE')->value);
        $this->assertSame('Fresh food from local farms.', $configurationsTable->get('FCS_HOME_META_DESCRIPTION')->value);
        $this->assertSame('hidden', $configurationsTable->get('FCS_HOME_META_TITLE')->type);
        $this->assertSame('hidden', $configurationsTable->get('FCS_HOME_META_DESCRIPTION')->type);

        $blocksTable = $this->getTableLocator()->get('Blocks');
        $block = $blocksTable->get(1);
        $this->assertEquals('Neuer Block', $block->heading);
        $this->assertEquals('<p>Blockinhalt</p>', $block->content);
        $this->assertSame('Mehr Infos', $block->primary_label);
        $this->assertSame('https://www.example.com/neuigkeiten', $block->primary_href);
        $this->assertSame('Kontakt', $block->secondary_label);
        $this->assertSame('https://www.example.com/kontakt', $block->secondary_href);
        $this->assertEquals(Block::IMAGE_POSITION_LEFT, (int)$block->image_position);
        $this->assertEquals(5, $block->position);
        $this->assertEquals(1, $block->active);

        $mapConfiguration = $configurationsTable->find('all', conditions: [
            'Configurations.name' => 'FCS_FOODCOOPS_MAP_ENABLED',
        ])->first();

        $this->assertEquals('0', $mapConfiguration->value);

        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $headerPromo = $headerPromosTable->find('all', conditions: [
            'HeaderPromos.page_id' => Page::PAGE_ID_HOME,
        ])->first();
        $this->assertNotNull($headerPromo);
        $this->assertSame('Startseiten Titel', $headerPromo->title);
        $this->assertSame('Startseiten Lead', $headerPromo->lead_text);
        $this->assertSame('<p><strong>Startseiten Text</strong></p>', $headerPromo->text);
        $this->assertSame('Primar', $headerPromo->primary_label);
        $this->assertSame('https://www.example.com/anmelden', $headerPromo->primary_href);
        $this->assertSame('Sekundar', $headerPromo->secondary_label);
        $this->assertSame('https://www.example.com', $headerPromo->secondary_href);
    }

    public function testEditHomeMetadataPreservesAmpersands(): void
    {
        $this->loginAsSuperadmin();
        $metadata = [
            'FCS_HOME_META_TITLE' => 'Food & farms | Regional',
            'FCS_HOME_META_DESCRIPTION' => 'Fresh food & local farms "nearby".',
        ];

        foreach ([1, 2] as $attempt) {
            $this->post($this->Slug->getPageEditHome(), [
                'Pages' => ['content' => ''],
                'Configurations' => $metadata,
                'referer' => '/',
            ]);
            $this->assertRedirect('/');

            $configurationsTable = $this->getTableLocator()->get('Configurations');
            foreach ($metadata as $name => $value) {
                $this->assertSame($value, $configurationsTable->get($name)->value);
            }

            $this->get($this->Slug->getPageEditHome());
            $this->assertResponseOk();
            $this->assertResponseContains('value="Food &amp; farms | Regional"');
            $this->assertResponseContains('Fresh food &amp; local farms &quot;nearby&quot;.');
            $this->assertResponseNotContains('&amp;amp;');
        }
    }

    public function testEditHomePostShowsHeaderPromoTitleValidationError(): void
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
                'FCS_HOME_META_TITLE' => 'Retained title',
                'FCS_HOME_META_DESCRIPTION' => 'Retained description.',
            ],
            'header_promo' => [
                'title' => str_repeat('x', 56),
            ],
            'referer' => '/',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Bitte gib für den Titel maximal 55 Zeichen ein.');
        $this->assertResponseContains('value="Retained title"');
        $this->assertResponseContains('Retained description.');
    }

    public function testEditHomeClearsMetadata(): void
    {
        $this->changeConfiguration('FCS_HOME_META_TITLE', 'Regional | Local farms');
        $this->changeConfiguration('FCS_HOME_META_DESCRIPTION', 'Fresh food.');
        $this->loginAsSuperadmin();

        $this->post($this->Slug->getPageEditHome(), [
            'Pages' => ['content' => ''],
            'Configurations' => [
                'FCS_HOME_META_TITLE' => '',
                'FCS_HOME_META_DESCRIPTION' => '',
            ],
            'referer' => '/',
        ]);

        $this->assertRedirect('/');
        $configurationsTable = $this->getTableLocator()->get('Configurations');
        $this->assertSame('', $configurationsTable->get('FCS_HOME_META_TITLE')->value);
        $this->assertSame('', $configurationsTable->get('FCS_HOME_META_DESCRIPTION')->value);
    }

    public function testEditPagePostSavesHeaderPromo(): void
    {
        $this->loginAsSuperadmin();

        $pagesTable = $this->getTableLocator()->get('Pages');
        $page = $pagesTable->get(3);

        $this->post($this->Slug->getPageEdit(3), [
            'Pages' => [
                'title' => (string)$page->title,
                'menu_type' => (string)$page->menu_type,
                'id_parent' => (int)$page->id_parent,
                'position' => (int)$page->position,
                'full_width' => (int)$page->full_width,
                'extern_url' => (string)$page->extern_url,
                'is_private' => (int)$page->is_private,
                'active' => (int)$page->active,
                'content' => (string)$page->content,
            ],
            'HeaderPromo' => [
                'title' => 'Page Header Titel',
                'lead_text' => 'Page Header Lead',
                'text' => 'Page Header Text',
                'primary_label' => 'Mehr Infos',
                'primary_href' => 'https://www.example.com/neuigkeiten',
                'secondary_label' => 'Kontakt',
                'secondary_href' => 'https://www.example.com/kontakt',
            ],
            'referer' => '/',
        ]);

        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $headerPromo = $headerPromosTable->find('all', conditions: [
            'HeaderPromos.page_id' => 3,
        ])->first();
        $this->assertNotNull($headerPromo);
        $this->assertSame('Page Header Titel', $headerPromo->title);
        $this->assertSame('Page Header Lead', $headerPromo->lead_text);
        $this->assertSame('Page Header Text', $headerPromo->text);
        $this->assertSame('Mehr Infos', $headerPromo->primary_label);
        $this->assertSame('https://www.example.com/neuigkeiten', $headerPromo->primary_href);
        $this->assertSame('Kontakt', $headerPromo->secondary_label);
        $this->assertSame('https://www.example.com/kontakt', $headerPromo->secondary_href);
    }

    public function testEditHomePostWithoutHeaderPromoDataDoesNotCreateHeaderPromo(): void
    {
        $this->loginAsSuperadmin();

        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $headerPromosTable->deleteAll([
            'page_id' => Page::PAGE_ID_HOME,
        ]);

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

        $this->assertFlashMessage(__('The homepage has been changed successfully.'));
        $this->assertRedirect('/');

        $headerPromo = $headerPromosTable->find('all', conditions: [
            'HeaderPromos.page_id' => Page::PAGE_ID_HOME,
        ])->first();
        $this->assertNull($headerPromo);
    }

    public function testEditPagePostWithoutHeaderPromoDataDoesNotCreateHeaderPromo(): void
    {
        $this->loginAsSuperadmin();

        $pagesTable = $this->getTableLocator()->get('Pages');
        $page = $pagesTable->get(3);

        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $headerPromosTable->deleteAll([
            'page_id' => 3,
        ]);

        $this->post($this->Slug->getPageEdit(3), [
            'Pages' => [
                'title' => (string)$page->title,
                'menu_type' => (string)$page->menu_type,
                'id_parent' => (int)$page->id_parent,
                'position' => (int)$page->position,
                'full_width' => (int)$page->full_width,
                'extern_url' => (string)$page->extern_url,
                'is_private' => (int)$page->is_private,
                'active' => (int)$page->active,
                'content' => (string)$page->content,
            ],
            'referer' => '/',
        ]);

        $this->assertRedirect('/');

        $headerPromo = $headerPromosTable->find('all', conditions: [
            'HeaderPromos.page_id' => 3,
        ])->first();
        $this->assertNull($headerPromo);
    }

    public function testDeletePage(): void
    {
        $this->loginAsSuperadmin();

        $pagesTable = $this->getTableLocator()->get('Pages');
        $page = $pagesTable->get(3);

        $this->post($this->Slug->getPageEdit(3), [
            'Pages' => [
                'title' => (string) $page->title,
                'menu_type' => (string) $page->menu_type,
                'id_parent' => (int) $page->id_parent,
                'position' => (int) $page->position,
                'full_width' => (int) $page->full_width,
                'extern_url' => (string) $page->extern_url,
                'is_private' => (int) $page->is_private,
                'active' => (int) $page->active,
                'content' => (string) $page->content,
                'delete_page' => 1,
            ],
            'referer' => '/',
        ]);

        $this->assertRedirect('/');

        $page = $pagesTable->get(3);
        $this->assertSame(APP_DEL, (int) $page->active);
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

        $this->assertFlashMessage(__('The homepage has been changed successfully.'));
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

        $this->assertFlashMessage(__('The homepage has been changed successfully.'));
        $this->assertRedirect('/');

        $savedBlock = $blocksTable->get(1);
        $this->assertSame('demo.jpg', $savedBlock->image);
        $this->assertSame('', $savedBlock->heading);
        $this->assertSame('', $savedBlock->content);
    }
}
