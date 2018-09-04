<?php

use App\Controller\Component\StringComponent;
use App\Test\TestCase\AppCakeTestCase;

/**
 * StringComponentTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class StringComponentTest extends AppCakeTestCase
{

    public function setUp()
    {
        // do not import database - no database needed for this test
    }

    public function testSlugify()
    {
        $tests = [
            [
                'name' => 'Getränke alkoholisch',
                'slug' => 'getraenke-alkoholisch'
            ],
            [
                'name' => 'Die Äpfel der letzten Saison',
                'slug' => 'die-aepfel-der-letzten-saison'
            ],
            [
                'name' => 'Öle und Essig',
                'slug' => 'oele-und-essig'
            ]
        ];

        foreach ($tests as $test) {
            $result = StringComponent::slugify($test['name']);
            $this->assertEquals($test['slug'], $result);
        }
    }

    public function testAddHttpToUrl()
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

    public function testRemoveIdFromSlug()
    {
        $tests = [
            [
                'url' => '1-bla-bla-bla',
                'slug' => 'bla-bla-bla'
            ],
            [
                'url' => '25-getraenke-alkoholisch',
                'slug' => 'getraenke-alkoholisch'
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
