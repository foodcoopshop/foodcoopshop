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

class AttributesControllerTest extends AppCakeTestCase
{

    use AppIntegrationTestTrait;
    use LoginTrait;

    public function testEdit(): void
    {
        $this->loginAsSuperadmin();
        $this->post(
            $this->Slug->getAttributeEdit(33),
            [
                'Attributes' => [
                    'name' => '0,4l',
                    'can_be_used_as_unit' => 1,
                    'active' => 0,
                ],
            ]
        );

        $attributesTable = $this->getTableLocator()->get('Attributes');
        $attribute = $attributesTable->find('all',
            conditions: [
                'Attributes.id_attribute' => 33,
            ],
        )->first();

        $this->assertEquals('0,4l', $attribute->name);
        $this->assertEquals(1, $attribute->can_be_used_as_unit);
        $this->assertEquals(0, $attribute->active);

    }

}