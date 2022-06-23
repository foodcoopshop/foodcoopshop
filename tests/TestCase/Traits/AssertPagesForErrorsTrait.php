<?php

namespace App\Test\TestCase\Traits;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
trait AssertPagesForErrorsTrait
{

    /**
     * array $testPages
     * asserts html for errors or missing elements that need to occur
     */
    protected function assertPagesForErrors($testPages): void
    {
        foreach ($testPages as $url) {
            $this->get($url);
            $this->assertResponseNotRegExp('/class="cake-stack-trace"|class="cake-error"|\bFatal error\b|exception \'[^\']+\' with message|\<strong\>(Error|Exception)\s*:\s*\<\/strong\>|Parse error|Not Found|\/app\/views\/errors\/|error in your SQL syntax|ERROR!|^\<\/body\>/');
        }
    }

}
