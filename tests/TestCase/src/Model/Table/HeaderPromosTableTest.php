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
class HeaderPromosTableTest extends AppCakeTestCase
{

    /**
     * @return array<string, mixed>
     */
    private function getValidData(): array
    {
        return [
            'title' => 'Titel',
            'lead_text' => 'Untertitel',
            'text' => 'Text',
            'primary_label' => 'Mehr',
            'primary_href' => 'https://example.com/anmelden',
            'secondary_label' => 'Kontakt',
            'secondary_href' => 'https://example.com/kontakt',
        ];
    }

    public function testValidationMaxLengthRules(): void
    {
        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');

        $cases = [
            ['field' => 'title', 'maxLength' => 55, 'message' => 'Bitte gib für den Titel maximal 55 Zeichen ein.'],
            ['field' => 'lead_text', 'maxLength' => 100, 'message' => 'Bitte gib für den Untertitel maximal 100 Zeichen ein.'],
            ['field' => 'text', 'maxLength' => 300, 'message' => 'Bitte gib für den Text maximal 300 Zeichen ein.'],
            ['field' => 'primary_label', 'maxLength' => 25, 'message' => 'Bitte gib für die primäre Button-Beschriftung maximal 25 Zeichen ein.'],
            ['field' => 'secondary_label', 'maxLength' => 25, 'message' => 'Bitte gib für die sekundäre Button-Beschriftung maximal 25 Zeichen ein.'],
            ['field' => 'primary_href', 'maxLength' => 255, 'message' => 'Bitte gib für den primären Link maximal 255 Zeichen ein.'],
            ['field' => 'secondary_href', 'maxLength' => 255, 'message' => 'Bitte gib für den sekundären Link maximal 255 Zeichen ein.'],
        ];

        foreach ($cases as $case) {
            $data = $this->getValidData();
            $data[$case['field']] = str_repeat('x', $case['maxLength'] + 1);

            $entity = $headerPromosTable->newEntity($data);
            $errors = $entity->getErrors();

            $this->assertArrayHasKey($case['field'], $errors);
            $this->assertArrayHasKey('maxLength', $errors[$case['field']]);
            $this->assertSame($case['message'], $errors[$case['field']]['maxLength']);
        }
    }

    public function testValidationRejectsInvalidPrimaryAndSecondaryLinks(): void
    {
        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $entity = $headerPromosTable->newEntity([
            'primary_label' => 'Mehr',
            'primary_href' => 'ungueltiger-link',
            'secondary_label' => 'Kontakt',
            'secondary_href' => 'auch-ungueltig',
        ]);

        $errors = $entity->getErrors();

        $this->assertSame('Bitte gib einen gültigen Link ein.', $errors['primary_href']['urlWithProtocol']);
        $this->assertSame('Bitte gib einen gültigen Link ein.', $errors['secondary_href']['urlWithProtocol']);
    }

    public function testValidationRequiresPrimaryAndSecondaryPairs(): void
    {
        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $entity = $headerPromosTable->newEntity([
            'primary_label' => 'Mehr',
            'primary_href' => '',
            'secondary_label' => 'Kontakt',
            'secondary_href' => '',
        ]);

        $errors = $entity->getErrors();

        $this->assertSame(
            'Beschriftung und Link müssen entweder beide ausgefüllt oder beide leer sein.',
            $errors['primary_label']['primaryPair'],
        );
        $this->assertSame(
            'Beschriftung und Link müssen entweder beide ausgefüllt oder beide leer sein.',
            $errors['secondary_label']['secondaryPair'],
        );

        $entity = $headerPromosTable->newEntity([
            'primary_label' => '',
            'primary_href' => 'https://example.com/anmelden',
            'secondary_label' => '',
            'secondary_href' => 'https://example.com/kontakt',
        ]);

        $errors = $entity->getErrors();

        $this->assertSame(
            'Beschriftung und Link müssen entweder beide ausgefüllt oder beide leer sein.',
            $errors['primary_href']['primaryPair'],
        );
        $this->assertSame(
            'Beschriftung und Link müssen entweder beide ausgefüllt oder beide leer sein.',
            $errors['secondary_href']['secondaryPair'],
        );
    }

    public function testTextSanitizationAllowsOnlyConfiguredTags(): void
    {
        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $entity = $headerPromosTable->newEntity([
            'text' => '<p><strong>Bold</strong> <em>Italic</em> <b>Bold2</b> <i>Italic2</i><br>line</p><h2>Nope</h2><script>alert(1)</script>',
        ]);

        $this->assertSame('<p><strong>Bold</strong> <em>Italic</em> <b>Bold2</b> <i>Italic2</i><br>line</p>Nopealert(1)', $entity->text);
    }

    public function testBeforeMarshalStripsTagsFromPlainStringFields(): void
    {
        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $entity = $headerPromosTable->newEntity([
            'title' => '<h2>Titel</h2>',
            'lead_text' => '<strong>Untertitel</strong>',
            'primary_label' => '<b>Mehr</b>',
            'primary_href' => '<i>https://example.com/anmelden</i>',
            'secondary_label' => '<script>Kontakt</script>',
            'secondary_href' => '<div>https://example.com/kontakt</div>',
            'text' => '<p><strong>Text</strong></p>',
        ]);

        $this->assertSame('Titel', $entity->title);
        $this->assertSame('Untertitel', $entity->lead_text);
        $this->assertSame('Mehr', $entity->primary_label);
        $this->assertSame('https://example.com/anmelden', $entity->primary_href);
        $this->assertSame('Kontakt', $entity->secondary_label);
        $this->assertSame('https://example.com/kontakt', $entity->secondary_href);
        $this->assertSame('<p><strong>Text</strong></p>', $entity->text);
    }
}
