<?php
/**
 * AllNetworkTestsTest
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
class AllNetworkTestsTest extends PHPUnit_Framework_TestSuite
{

    /**
     * suite method, defines tests for this suite.
     *
     * @return void
     */
    public static function suite()
    {
        $suite = new CakeTestSuite('All Tests');
        $suite->addTestDirectoryRecursive(CakePlugin::path('Network') . 'Test' . DS . 'Case');
        return $suite;
    }
}
