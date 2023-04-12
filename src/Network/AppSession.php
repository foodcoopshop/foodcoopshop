<?php
declare(strict_types=1);

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
    public function read(?string $name = null, $default = null)
    {

        if (!isset($_SESSION)) {
            return null;
        }

        if ($name === null) {
            return $_SESSION ?: [];
        }

        return Hash::get($_SESSION, $name, $default);
    }
}
