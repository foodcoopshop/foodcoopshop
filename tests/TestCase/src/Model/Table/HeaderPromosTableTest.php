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
            'primary_href' => '/anmelden',
            'secondary_label' => 'Kontakt',
            'secondary_href' => '/kontakt',
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

        $this->assertSame('Bitte gib einen gültigen primären Link ein.', $errors['primary_href']['validPrimaryHref']);
        $this->assertSame('Bitte gib einen gültigen sekundären Link ein.', $errors['secondary_href']['validSecondaryHref']);
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
            'Bei der primären Aktion müssen Beschriftung und Link entweder beide ausgefüllt oder beide leer sein.',
            $errors['primary_label']['primaryPair'],
        );
        $this->assertSame(
            'Bei der sekundären Aktion müssen Beschriftung und Link entweder beide ausgefüllt oder beide leer sein.',
            $errors['secondary_label']['secondaryPair'],
        );
    }
}
