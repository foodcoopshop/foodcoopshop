<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Shell;

use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Http\Client;

class SavedLocalizedJsAsStaticFileShell extends AppShell
{

    /**
     * do not call parent::main because db connection might not be available
     * run this script to generate a static file for production use
     * @see AppShell::main()
     */
    public function main()
    {
        $url = parse_url(Configure::read('app.cakeServerName'));
        $httpClient = new Client([
            'host' => $url['host'],
        ]);
        $response = $httpClient->get('/js/localized-javascript.js');
        $jsFile = new File(WWW_ROOT . '/js/localized-javascript-static.js');
        $jsFile->write($response->getStringBody());
    }

}
