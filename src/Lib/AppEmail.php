<?php

App::uses('CakeEmail', 'Network/Email');
App::uses('EmailLog', 'Model');

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

    private $_originalSubject = '';

    /**
     * Get/Set Subject.
     *
     * Subject is encode by CakeEmail.
     * In order to access it in clear text, it's stored in $_originalSubject
     *
     * @param string $subject Subject string.
     * @return string|self
     */
    public function subject($subject = null)
    {
        $this->_originalSubject = $subject;
        return parent::subject($subject);
    }

    public function __construct($config = null)
    {
        parent::__construct('default');

        if (Configure::read('AppConfig.db_config_FCS_BACKUP_EMAIL_ADDRESS_BCC') != '') {
            $this->addBcc(Configure::read('AppConfig.db_config_FCS_BACKUP_EMAIL_ADDRESS_BCC'));
        }
    }

    /**
     * declaring this method public enables rendering an email (for preview)
     * {@inheritDoc}
     * @see CakeEmail::_renderTemplates()
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
    public function logEmailInDatabase($content)
    {
        $emailLogModel = new EmailLog();
        $email2save = array(
            'from_address' => json_encode($this->from()),
            'to_address' => json_encode($this->to()),
            'cc_address' => json_encode($this->cc()),
            'bcc_address' => json_encode($this->bcc()),
            'subject' => $this->_originalSubject,
            'headers' => json_encode($this->getHeaders()),
            'message' => $this->_renderTemplates($content)['html']
        );
        $emailLogModel->id = null;
        return $emailLogModel->save($email2save);
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
            if (Configure::read('AppConfig.db_config_FCS_EMAIL_LOG_ENABLED')) {
                $this->logEmailInDatabase($content);
            }
            return parent::send($content);
        } catch (Exception $e) {
            
            CakeLog::write('error', $e->getMessage());

            if (Configure::check('fallbackEmailConfig')) {
                $fallbackEmailConfig = Configure::read('fallbackEmailConfig');
                $originalFrom = $this->from();

                // resend the email with the fallbackEmailConfig
                // avoid endless loops if this email also not works
                if ($this->from() != $fallbackEmailConfig['from']) {
                    $this->config($fallbackEmailConfig);
                    $this->from(array(
                        key($this->from()) => Configure::read('AppConfig.db_config_FCS_APP_NAME')
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
