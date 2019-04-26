<?php

namespace App\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Utility\Security;

class BarCodeAuthenticate extends BaseAuthenticate {

    public function authenticate(ServerRequest $request, Response $response){

        $fields = $this->_config['fields'];
        $user = $this->_findUser(
            $request->getData($fields['identifier'])
        );
        return $user;
  }
  
  public function getIdentifierField($table)
  {
      return 'SUBSTRING(SHA1(CONCAT(' . $table->aliasField('id_customer') .', "' .  Security::getSalt() . '")), 1, 13)';
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
  
  protected function _query($username)
  {
      $config = $this->_config;
      $table = $this->getTableLocator()->get($config['userModel']);
      
      $options = [
          'conditions' => [
              $this->getIdentifierField($table) . ' = "' . $username . '"'
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