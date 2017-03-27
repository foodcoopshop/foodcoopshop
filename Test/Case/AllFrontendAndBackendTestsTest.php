<?php
/**
 * AllFrontendAndBackendTestsTest
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AllFrontendAndBackendTestsTest extends PHPUnit_Framework_TestSuite
{

    /**
     * suite method, defines tests for this suite.
     *
     * @return void
     */
    public static function suite()
    {
        $suite = new CakeTestSuite('All Frontend and Backend Tests');
        // TODO maybe there is a more elegant way to include the defined tests of existing suites than copying them
        $suite->addTestDirectoryRecursive(TESTS . 'Case');
        $suite->addTestDirectoryRecursive(CakePlugin::path('Admin') . 'Test' . DS . 'Case');
        return $suite;
    }
}
