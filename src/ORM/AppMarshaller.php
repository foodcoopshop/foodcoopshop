<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\ORM;

use Cake\ORM\Marshaller;
use Cake\Log\Log;

class AppMarshaller extends Marshaller
{
    /**
     * {@inheritDoc}
     * @see \Cake\ORM\Marshaller::_validate()
     */
    protected function _validate($data, $options, $isNew)
    {
        $errors = parent::_validate($data, $options, $isNew);
        if (!empty($errors)) {
            Log::write('error', json_encode($errors));
        }
        return $errors;
    }
}
