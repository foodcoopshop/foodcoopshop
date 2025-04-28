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
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use App\Test\TestCase\AppCakeTestCase;
use App\Test\TestCase\Traits\AppIntegrationTestTrait;
use App\Test\TestCase\Traits\LoginTrait;

class CategoriesControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testEdit(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getCategoryEdit(16),
            [
                'Categories' => [
                    'id_parent' => 20,
                    'name' => 'new category name',
                    'description' => 'asd',
                    'active' => 0,
                ],
            ]
        );

        $categoriesTable = $this->getTableLocator()->get('Categories');
        $category = $categoriesTable->find('all',
            conditions: [
                'Categories.id_category' => 16,
            ],
        )->first();

        $this->assertEquals(20, $category->id_parent);
        $this->assertEquals('new category name', $category->name);
        $this->assertEquals('asd', $category->description);

    }

}