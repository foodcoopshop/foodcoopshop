<?php
declare(strict_types=1);

use App\Test\TestCase\AppCakeTestCase;

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
class BlocksTableTest extends AppCakeTestCase
{

    public function testValidationAllowsTmpImageAsContent(): void
    {
        $blocksTable = $this->getTableLocator()->get('Blocks');
        $entity = $blocksTable->newEntity([
            'tmp_image' => '/tmp/images/demo.jpg',
            'image' => '',
            'heading' => '',
            'content' => '',
            'position' => 0,
            'active' => 1,
        ]);

        $this->assertFalse($entity->hasErrors());
    }

    public function testValidationAllowsButtonOnlyBlock(): void
    {
        $blocksTable = $this->getTableLocator()->get('Blocks');
        $entity = $blocksTable->newEntity([
            'image' => '',
            'heading' => '',
            'content' => '',
            'primary_label' => 'Mehr',
            'primary_href' => 'https://example.com/anmelden',
            'position' => 0,
            'active' => 1,
        ]);

        $this->assertFalse($entity->hasErrors());
    }

    public function testValidationRejectsCompletelyEmptyBlock(): void
    {
        $blocksTable = $this->getTableLocator()->get('Blocks');
        $entity = $blocksTable->newEntity([
            'image' => '',
            'heading' => '',
            'content' => '',
            'position' => 0,
            'active' => 1,
        ]);

        $this->assertTrue($entity->hasErrors());
        $this->assertArrayHasKey('position', $entity->getErrors());
    }

    public function testValidationRejectsInvalidButtonLinksAndPairs(): void
    {
        $blocksTable = $this->getTableLocator()->get('Blocks');
        $entity = $blocksTable->newEntity([
            'image' => '',
            'heading' => 'Heading',
            'content' => 'Text',
            'primary_label' => 'Mehr',
            'primary_href' => 'ungueltiger-link',
            'secondary_label' => '',
            'secondary_href' => 'https://example.com/kontakt',
            'position' => 0,
            'active' => 1,
        ]);

        $errors = $entity->getErrors();

        $this->assertSame('Bitte gib einen gültigen Link ein.', $errors['primary_href']['urlWithProtocol']);
        $this->assertSame('Beschriftung und Link müssen entweder beide ausgefüllt oder beide leer sein.', $errors['secondary_href']['secondaryPair']);
    }

    public function testBeforeMarshalStripsDisallowedBlockTags(): void
    {
        $blocksTable = $this->getTableLocator()->get('Blocks');
        $entity = $blocksTable->newEntity([
            'heading' => '<h2>Heading</h2>',
            'primary_label' => '<b>Mehr</b>',
            'primary_href' => '<i>https://example.com/anmelden</i>',
            'secondary_label' => '<script>Kontakt</script>',
            'secondary_href' => '<div>https://example.com/kontakt</div>',
            'content' => '<h2>Title</h2><h3>Subtitle</h3><p><b>Bold</b> <i>Italic</i></p><ul><li>Item</li></ul><ol><li>Item 2</li></ol><hr><div>Ignored</div>',
            'position' => 0,
            'active' => 1,
        ]);

        $this->assertSame('Heading', $entity->heading);
        $this->assertSame('Mehr', $entity->primary_label);
        $this->assertSame('https://example.com/anmelden', $entity->primary_href);
        $this->assertSame('Kontakt', $entity->secondary_label);
        $this->assertSame('https://example.com/kontakt', $entity->secondary_href);
        $this->assertSame('<h2>Title</h2><h3>Subtitle</h3><p><b>Bold</b> <i>Italic</i></p><ul><li>Item</li></ul><ol><li>Item 2</li></ol><hr>Ignored', $entity->content);
    }
}
