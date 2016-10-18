<?php

App::uses('AppCakeTestCase', 'Test');
App::uses('Component', 'Controller');
App::uses('StringComponent', 'Controller/Component');

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class StringComponentTest extends AppCakeTestCase
{

    public function testSlugify()
    {
        $tests = array(
            array(
                'name' => 'Getränke alkoholisch',
                'slug' => 'getraenke-alkoholisch'
            ),
            array(
                'name' => 'Die Äpfel der letzten Saison',
                'slug' => 'die-aepfel-der-letzten-saison'
            ),
            array(
                'name' => 'Öle und Essig',
                'slug' => 'oele-und-essig'
            )
        );
        
        foreach ($tests as $test) {
            $result = StringComponent::slugify($test['name']);
            $this->assertEquals($test['slug'], $result);
        }
    }

    public function testRemoveIdFromSlug()
    {
        $tests = array(
            array(
                'url' => '1-bla-bla-bla',
                'slug' => 'bla-bla-bla'
            ),
            array(
                'url' => '25-getraenke-alkoholisch',
                'slug' => 'getraenke-alkoholisch'
            ),
            array(
                'url' => '29-heilmassage-mittermeier',
                'slug' => 'heilmassage-mittermeier'
            )
        );
        
        foreach ($tests as $test) {
            $result = StringComponent::removeIdFromSlug($test['url']);
            $this->assertEquals($test['slug'], $result);
        }
    }
}

?>