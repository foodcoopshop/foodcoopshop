<?php
declare(strict_types=1);

use App\Controller\Component\StringComponent;
use App\Test\TestCase\AppCakeTestCase;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class StringComponentTest extends AppCakeTestCase
{

    public function setUp(): void
    {
        // do not import database - no database needed for this test
    }

    public function testRemoveEmojis(): void
    {
        $tests = [
            [
                'text' => 'Test ❤ hello',
                'expected' => 'Test  hello'
            ],
            [
                'text' => 'Test 🎃 hello',
                'expected' => 'Test  hello'
            ],
        ];

        foreach ($tests as $test) {
            $result = StringComponent::removeEmojis($test['text']);
            $this->assertEquals($test['expected'], $result);
        }
    }

    public function testSlugify(): void
    {
        $tests = [
            [
                'name' => 'Getränke alkoholisch',
                'slug' => 'Getraenke-alkoholisch'
            ],
            [
                'name' => 'Die Äpfel der letzten Saison',
                'slug' => 'Die-Aepfel-der-letzten-Saison'
            ],
            [
                'name' => 'Champs-Élysées',
                'slug' => 'Champs-Elysees'
            ],
            [
                'name' => 'Öle und Essig',
                'slug' => 'Oele-und-Essig'
            ],
            [
                'name' => 'Smith &amp; Sons',
                'slug' => 'Smith-Sons'
            ],
            [
                'name' => 'Smith &gt; Sons',
                'slug' => 'Smith-Sons'
            ],
            [
                'name' => 'Smith &lt; Sons',
                'slug' => 'Smith-Sons'
            ],
            [
                'name' => 'Manufacturer "Name"',
                'slug' => 'Manufacturer-Name'
            ],
        ];

        foreach ($tests as $test) {
            $result = StringComponent::slugify($test['name']);
            $this->assertEquals($test['slug'], $result);
        }
    }

    public function testAddHttpToUrl(): void
    {
        $tests = [
            [
                'value' => '',
                'expected' => ''
            ],
            [
                'value' => 'http://www.orf.at',
                'expected' => 'http://www.orf.at'
            ],
            [
                'value' => 'www.orf.at',
                'expected' => 'http://www.orf.at'
            ],
            [
                'value' => 'https://www.orf.at',
                'expected' => 'https://www.orf.at'
            ]
        ];

        foreach ($tests as $test) {
            $result = StringComponent::addHttpToUrl($test['value']);
            $this->assertEquals($test['expected'], $result);
        }
    }

    public function testRemoveIdFromSlug(): void
    {
        $tests = [
            [
                'url' => '1-bla-bla-bla',
                'slug' => 'bla-bla-bla'
            ],
            [
                'url' => '25-Getraenke-alkoholisch',
                'slug' => 'Getraenke-alkoholisch'
            ],
            [
                'url' => '29-heilmassage-mittermeier',
                'slug' => 'heilmassage-mittermeier'
            ]
        ];

        foreach ($tests as $test) {
            $result = StringComponent::removeIdFromSlug($test['url']);
            $this->assertEquals($test['slug'], $result);
        }
    }
}
