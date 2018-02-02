<?php
/**
 * CakePHP(tm) :  Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakefoundation.org CakePHP(tm) Project
 * @since         1.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Log\Engine;

use App\Mailer\AppEmail;
use App\Network\AppSession;
use Cake\Core\Configure;
use Cake\Log\Engine\FileLog;
use Cake\Network\Exception\SocketException;
use Cake\Utility\Text;

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class FileAndEmailLog extends FileLog
{

    public function log($level, $message, array $context = [])
    {
        
        $result = parent::log($level, $message, $context);
        
        if (Configure::read('app.emailErrorLoggingEnabled')) {
            $this->sendEmailWithErrorInformation($message);
        }
        
        return $result;
    }
    
    private function sendEmailWithErrorInformation($message)
    {
        
        $ignoredExceptionsRegex = '/(MissingController|MissingAction|RecordNotFound)Exception/';
        if (preg_match($ignoredExceptionsRegex, $message)) {
            return false;
        }
        
        $session = new AppSession();
        $loggedUser = [];
        if ($session->read('Auth.User.id_customer') !== null) {
            $loggedUser = $session->read('Auth');
        }
        
        $subject = Configure::read('app.cakeServerName') . ' ' . Text::truncate($message, 90) . ' ' . date('Y-m-d H:i:s');
        try {
            $email = new AppEmail();
            $email->setProfile('debug');
            $email->setTo(Configure::read('app.debugEmail'))
            ->setTemplate('debug')
            ->setSubject($subject)
            ->setViewVars(array(
                'message' => $message,
                'loggedUser' => $loggedUser
            ))
            ->send();
        } catch (SocketException $e) {
            return false;
        }
        
    }

}
