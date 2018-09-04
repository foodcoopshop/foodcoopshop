<?php
/**
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
namespace App\Network;

use Cake\Http\Session;
use Cake\Utility\Hash;

class AppSession extends Session
{

    /**
     * quite tricky and hacky to get session into AppTable
     * {@inheritDoc}
     * @see \Cake\Http\Session::read()
     */
    public function read($name = null)
    {

        if (!isset($_SESSION)) {
            return null;
        }

        if ($name === null) {
            return isset($_SESSION) ? $_SESSION : [];
        }

        return Hash::get($_SESSION, $name);
    }
}
