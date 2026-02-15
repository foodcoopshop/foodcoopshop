<?php
declare(strict_types=1);

namespace App\Authenticator;

use Authentication\Authenticator\FormAuthenticator;
use Psr\Http\Message\ServerRequestInterface;
use Authentication\Identifier\PasswordIdentifier;
use Authentication\Identifier\TokenIdentifier;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppFormAuthenticator extends FormAuthenticator
{

    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'urlChecker' => 'Authentication.Default',
        'fields' => [
            PasswordIdentifier::CREDENTIAL_USERNAME => 'email',
            PasswordIdentifier::CREDENTIAL_PASSWORD => 'passwd',
            TokenIdentifier::CREDENTIAL_TOKEN => 'barcode',
        ],
    ];

    /**
     * @return array<string, mixed>|null
     */
    protected function _getData(ServerRequestInterface $request): ?array
    {
        $fields = $this->_config['fields'];

        $body = $request->getParsedBody();

        /** @var array<string, mixed> $data */
        $data = [];
        foreach ($fields as $key => $field) {
            $value = $body[$field] ?? '';
            $data[$key] = $value;
        }
        return $data;
    }

}
