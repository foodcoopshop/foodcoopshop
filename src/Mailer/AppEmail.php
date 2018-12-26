<?php

namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Log\Log;
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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppEmail extends Email
{

    public function __construct($addBccBackupAddress = true)
    {
        parent::__construct(null);

        if ($addBccBackupAddress && Configure::read('appDb.FCS_BACKUP_EMAIL_ADDRESS_BCC') != '') {
            $this->addBcc(Configure::read('appDb.FCS_BACKUP_EMAIL_ADDRESS_BCC'));
        }
    }

    /**
     * declaring this method public enables rendering an email (for preview)
     * {@inheritDoc}
     * @see Email::_renderTemplates()
     */
    public function _renderTemplates($content)
    {
        return parent::_renderTemplates($content);
    }

    /**
     * method needs to be called *before* send-method to be able to work with travis-ci
     * travis-ci uses an email mock
     * @param string|array $content
     * @return mixed|boolean|array
     */
    public function logEmailInDatabase($email)
    {
        $emailLogModel = TableRegistry::getTableLocator()->get('EmailLogs');
        $email2save = [
            'from_address' => json_encode($this->getFrom()),
            'to_address' => json_encode($this->getTo()),
            'cc_address' => json_encode($this->getCc()),
            'bcc_address' => json_encode($this->getBcc()),
            'subject' => $this->getOriginalSubject(),
            'headers' => json_encode($this->getHeaders()),
            'message' => $email['message']
        ];
        return $emailLogModel->save($emailLogModel->newEntity($email2save));
    }

    /**
     * uses fallback transport config if default email transport config is wrong (e.g. password changed party)
     * @see credentials.php
     */
    public function send($content = null)
    {
        try {
            $email = parent::send($content);
            if (Configure::read('appDb.FCS_EMAIL_LOG_ENABLED')) {
                $this->logEmailInDatabase($email);
            }
            return $email;
        } catch (Exception $e) {
            if (Configure::check('app.EmailTransport.fallback')) {
                $this->getTransport()->setConfig(Configure::consume('app.EmailTransport.fallback'));
                Log::write('error', 'Email config was wrong, tried to send e-mail with fallback config.');
                return $this->send($content);
            } else {
                throw $e;
            }
        }
    }
}
