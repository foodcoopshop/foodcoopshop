<?php

namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;

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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppEmail extends Email
{
    
    public function __construct($config = null)
    {
        parent::__construct('default');

        if (Configure::read('appDb.FCS_BACKUP_EMAIL_ADDRESS_BCC') != '') {
            $this->addBcc(Configure::read('appDb.FCS_BACKUP_EMAIL_ADDRESS_BCC'));
        }
    }
    
    public function getHtmlMessage()
    {
        return $this->_htmlMessage;
    }

    /**
     * method needs to be called *before* send-method to be able to work with travis-ci
     * travis-ci uses an email mock
     * @param string|array $content
     * @return mixed|boolean|array
     */
    public function logEmailInDatabase($content)
    {
        $emailLogModel = TableRegistry::get('EmailLogs');
        $email2save = [
            'from_address' => json_encode($this->getFrom()),
            'to_address' => json_encode($this->getTo()),
            'cc_address' => json_encode($this->getCc()),
            'bcc_address' => json_encode($this->getBcc()),
            'subject' => $this->getSubject(),
            'headers' => json_encode($this->getHeaders()),
            'message' => $this->getHtmlMessage()
        ];
        return $emailLogModel->save($emailLogModel->newEntity($email2save));
    }

    /**
     * fallback if email config is wrong (e.g.
     * password changed from third party)
     */
    public function send($content = null)
    {
        try {
            if (Configure::read('appDb.FCS_EMAIL_LOG_ENABLED')) {
                $this->logEmailInDatabase($content);
            }
            return parent::send($content);
        } catch (Exception $e) {
            
//             CakeLog::write('error', $e->getMessage());

            if (0 && Configure::check('app.fallbackEmailConfig')) {

                // resend the email with the app.fallbackEmailConfig
                AppEmail::dropTransport('default');
                AppEmail::setConfig('default');
                $this->setConfig(Configure::read('app.fallbackEmailConfig')['EmailTransport']['Email']);
                $this->setConfigTransport(Configure::read('app.fallbackEmailConfig')['EmailTransport']);
                $this->setFrom([
                    key($this->getFrom()) => Configure::read('appDb.FCS_APP_NAME')
                ]);
//                 CakeLog::write('info', 'email was sent with fallback config');
//                 return $this->send($content);
            } else {
                throw $e;
            }
        }
    }
}
