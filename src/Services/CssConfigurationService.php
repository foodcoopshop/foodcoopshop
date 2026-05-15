<?php
declare(strict_types=1);

namespace App\Services;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;

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
class CssConfigurationService
{

    public function format(string $css): string
    {
        $css = trim($css);
        if ($css === '') {
            return '';
        }

        try {
            $document = (new Parser($css))->parse();
        } catch (\Throwable) {
            return $css . "\n";
        }

        return trim($document->render(OutputFormat::createPretty())) . "\n";
    }

}