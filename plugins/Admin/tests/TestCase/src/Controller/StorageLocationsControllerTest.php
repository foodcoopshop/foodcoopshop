<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.2.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Martin Hatlauf <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace TestCase\src\Controller;

use App\Model\Entity\StorageLocation;
use App\Test\Fixture\StorageLocationsFixture;
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;
use Cake\Log\Log;

class StorageLocationsControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testEditValidName(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getStorageLocationEdit(1),
            [
                'StorageLocations' => [
                    'name' => 'Haus',
                    'position' => 2,
                ],
            ]
        );

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->find('all',
            conditions: [
                'StorageLocations.id' => 1,
            ],
        )->first();

        $this->assertEquals('Haus', $storageLocation->name);
        $this->assertEquals(2, $storageLocation->position);

    }

    public function testEditInvalidName(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getStorageLocationEdit(1),
            [
                'StorageLocations' => [
                    'name' => '',
                ],
            ]
        );

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->find('all',
            conditions: [
                'StorageLocations.id' => 1,
            ],
        )->first();

        $this->assertEquals('Keine Kühlung', $storageLocation->name);

    }

    public function testEditDuplicateName(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getStorageLocationEdit(1),
            [
                'StorageLocations' => [
                    'name' => 'Kühlschrank',
                ],
            ]
        );

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->find('all',
            conditions: [
                'StorageLocations.id' => 1,
            ],
        )->first();

        $this->assertEquals('Keine Kühlung', $storageLocation->name);
    }

    public function testAddValidName(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getStorageLocationAdd(),
            [
                'StorageLocations' => [
                    'name' => 'Haus',
                    'position' => 4,
                ],
            ]
        );

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->find('all',
            conditions: [
                'StorageLocations.id' => 4,
            ],
        )->first();

        $this->assertEquals('Haus', $storageLocation->name);
        $this->assertEquals(4, $storageLocation->position);

    }

    public function testAddInvalidName(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getStorageLocationAdd(),
            [
                'StorageLocations' => [
                    'name' => '',
                ],
            ]
        );
        $this->assertResponseContains("Bitte gib einen Namen ein.");

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocationCount = $storageLocationsTable->find('all',
        )->count();

        $this->assertEquals(3, $storageLocationCount);

    }

    public function testAddDuplicateName(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getStorageLocationAdd(),
            [
                'StorageLocations' => [
                    'name' => 'Kühlschrank',
                ],
            ]
        );

        $this->assertResponseContains("Beim Speichern sind Fehler aufgetreten!");

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocationCount = $storageLocationsTable->find('all',
        )->count();

        $this->assertEquals(3, $storageLocationCount);
    }

    public function testDeleteWithoutAssociations(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getStorageLocationEdit(2),
            [
                'StorageLocations' => [
                    'delete_storage_location' =>  true,
                ],
            ]
        );

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->find('all',
            conditions: [
                'StorageLocations.id' => 2,
            ],
        )->first();

        $this->assertNull($storageLocation);
    }


    public function testDeleteWithAssociations(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getStorageLocationEdit(1),
            [
                'StorageLocations' => [
                    'delete_storage_location' =>  true,
                ],
            ]
        );

        $storageLocationsTable = $this->getTableLocator()->get('StorageLocations');
        $storageLocation = $storageLocationsTable->find('all',
            conditions: [
                'StorageLocations.id' => 1,
            ],
        )->first();

        $this->assertNotNull($storageLocation);
    }
}