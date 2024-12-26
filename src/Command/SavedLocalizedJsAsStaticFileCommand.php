<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\TestSuite\IntegrationTestTrait;

class SavedLocalizedJsAsStaticFileCommand extends AppCommand
{

    public const ROUTE = '/js/localized-javascript.js';

    protected array $appPluginsToLoad = [];

    use IntegrationTestTrait;

    /**
     * do not call parent::main because db connection might not be available
     *
     * this script was written to be executed in the deploy process
     * in order to get the javascript content from the tmp installation
     * (and not from App.fullBaseUrl where the new code is not yet available)
     * the built-in HttpClient from IntegrationTest is used
     *
     * run this script to generate a static file for production use
     *
     * @see AppCommand::main()
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->get(SELF::ROUTE);
        $jsFile = fopen(WWW_ROOT . '/cache/localized-javascript-static.js', 'w');
        fwrite($jsFile, $this->_response->getBody()->__toString());
        fclose($jsFile);
        return static::CODE_SUCCESS;
    }

}
