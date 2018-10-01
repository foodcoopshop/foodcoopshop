<?php

namespace App\Auth;

use Cake\Core\Configure;
use Cake\Auth\WeakPasswordHasher;

/**
 * 
 * Legacy Password Hasher for CakePHP2 - can be removed in v3
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
class LegacyPasswordHasher extends WeakPasswordHasher
{

    public function hash($password)
    {
        return md5(Configure::read('app.cookieKey') . $password);
    }

    public function check($password, $hashedPassword)
    {
        return $hashedPassword === $this->hash($password);
    }
}
