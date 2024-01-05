<?php
declare(strict_types=1);

namespace App\Identifier;

use Authentication\Identifier\Resolver\ResolverAwareTrait;
use Cake\Database\Expression\QueryExpression;
use Authentication\Identifier\AbstractIdentifier;
use Cake\ORM\Locator\LocatorAwareTrait;
use Authentication\Identifier\IdentifierInterface;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.7.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class BarCodeIdentifier extends AbstractIdentifier
{
    use ResolverAwareTrait;
    use LocatorAwareTrait;

    protected $_defaultConfig = [
        'fields' => [
            IdentifierInterface::CREDENTIAL_TOKEN => 'barcode',
        ],
    ];
    
    public function getIdentifierField($table)
    {
        return $table->getBarcodeFieldString();
    }

    public function identify(array $credentials)
    {
        
        $barCode = $credentials[IdentifierInterface::CREDENTIAL_TOKEN] ?? '';
        if (empty($barCode)) {
            return null;
        }

        $table = $this->getTableLocator()->get($this->_config['resolver']['userModel']);
        $user =  $table->find($this->_config['resolver']['finder'])->where([
            (new QueryExpression())->eq($this->getIdentifierField($table), $barCode),
        ])->first();

        return $user;

    }
}
