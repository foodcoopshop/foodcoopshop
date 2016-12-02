<?php

App::uses('CakeEmail', 'Network/Email');

/**
 * AppEmail
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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppEmail extends CakeEmail
{

    public function __construct($config = null)
    {
        parent::__construct('default');
        
        if (Configure::read('app.db_config_FCS_BACKUP_EMAIL_ADDRESS_BCC') != '') {
            $this->addBcc(Configure::read('app.db_config_FCS_BACKUP_EMAIL_ADDRESS_BCC'));
        }
    }

    /**
     * fallback if email config is wrong (e.g.
     * password changed from third party)
     * 
     * @see CakeEmail::send()
     */
    public function send($content = null)
    {
        try {
            
            return parent::send($content);
        } catch (Exception $e) {
            
            if (Configure::read('app.emailErrorLoggingEnabled')) {
                CakePlugin::load('EmailLog', array(
                    'bootstrap' => true
                ));
            }
            CakeLog::write('error', $e->getMessage());
            
            if (Configure::check('fallbackEmailConfig')) {
                
                $fallbackEmailConfig = Configure::read('fallbackEmailConfig');
                $originalFrom = $this->from();
                
                // resend the email with the fallbackEmailConfig
                // avoid endless loops if this email also not works
                if ($this->from() != $fallbackEmailConfig['from']) {
                    $this->config($fallbackEmailConfig);
                    $this->from(array(
                        key($this->from()) => Configure::read('app.name')
                    ));
                    CakeLog::write('info', 'email was sent with fallback config');
                    return $this->send($content);
                }
            } else {
                throw $e;
            }
        }
    }
}

?>