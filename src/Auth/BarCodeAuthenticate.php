<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Query;
use Cake\Utility\Security;
use Cake\Database\Expression\QueryExpression;

class BarCodeAuthenticate extends BaseAuthenticate {

    public function authenticate(ServerRequest $request, Response $response) {
        $fields = $this->_config['fields'];
        $identifier = $request->getData($fields['identifier']);
        if ($identifier == '') {
            return null;
        }
        return $this->_findUser($identifier);
    }

  public function getIdentifierField($table)
  {
      return 'SUBSTRING(SHA1(CONCAT(' . $table->aliasField('id_customer') .', "' .  Security::getSalt() . '", "customer")), 1, 6)';
  }

  /**
   * Checks the fields to ensure they are supplied.
   *
   * @param \Cake\Http\ServerRequest $request The request that contains login information.
   * @param array $fields The fields to be checked.
   * @return bool False if the fields have not been supplied. True if they exist.
   */
  protected function _checkFields(ServerRequest $request, array $fields)
  {
      $value = $request->getData($fields['identifier']);
      if (empty($value) || !is_string($value)) {
          return false;
      }
      return true;
  }

  protected function _query(string $username): Query
  {
      $config = $this->_config;
      $table = $this->getTableLocator()->get($config['userModel']);

      $options = [
          'conditions' => [
            (new QueryExpression())->eq($this->getIdentifierField($table), $username),
          ]
      ];

      if (!empty($config['scope'])) {
          $options['conditions'] = array_merge($options['conditions'], $config['scope']);
      }
      if (!empty($config['contain'])) {
          $options['contain'] = $config['contain'];
      }

      $finder = $config['finder'];
      if (is_array($finder)) {
          $options += current($finder);
          $finder = key($finder);
      }

      return $table->find($finder, $options);
  }

  protected function _findUser($username, $password = null)
  {
      $result = $this->_query($username)->first();

      if (empty($result)) {
          return false;
      }

      return $result->toArray();
  }

}