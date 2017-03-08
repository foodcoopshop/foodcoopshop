<?php

App::uses('CakeLogInterface', 'Log');
App::uses('CakeText', 'Utility');
App::uses('CakeEmail', 'Network/Email');
App::uses('AppAuthComponent', 'Controller/Component');

/**
 * EmailLog
 * 
 * FoodCoopShop - The open source software for your foodcoop
 * 
 * CakePHP Email Storage stream for Logging
 * Send log by email using one of configured EmailConfig (app/Config/email.php)
 * 
 * Usage:
 * {{{
 *    CakeLog::config('email', array(
 *      'engine' => 'EmailLog.EmailLog',
 *      'emailConfig' => 'error',
 *      'subjectFormat' => ':type :date @ :host',
 *      'logTypes' => array('warrning', 'error'),
 *    ));
 * }}}
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
class EmailLog implements CakeLogInterface
{

    /**
     * Default options for logger
     * - emailConfig - One of configured EmailConfig. See app/Config/email.php.
     * - subjectFormat - Email subject format using available params.
     * - logTypes - List of log types to send by email (all by default) or array of log types like 'error', 'warrning'. 'info'.
     * 
     * @var array
     */
    protected $_options = array();

    /**
     *
     * Merge options
     *
     * @param array $options
     *            Options for logger
     * @return void
     */
    function __construct($options = array())
    {
        $this->_options = array_merge($this->_options, $options);
    }

    /**
     * Send email log
     *
     * @param string $type
     *            The type of log you are making.
     * @param string $message
     *            The message you want to log.
     * @return boolean success of write.
     */
    public function write($type, $message)
    {
        if (! empty($this->_options['logTypes']) && ! in_array($type, $this->_options['logTypes'])) {
            return false;
        }
        
        // never send emails for 404 exceptions
        // somehow in EmailLog.php status code is always 200, so no check for 404 possible
        $ignoredExceptionsRegex = '/\[(MissingController|MissingAction)Exception\]/';
        if (preg_match($ignoredExceptionsRegex, $message)) {
            return false;
        }
        
        $params = array(
            'type' => ucfirst($type),
            'date' => date('Y-m-d H:i:s'),
            'host' => env('SERVER_NAME'),
            'message' => CakeText::truncate($message, 70)
        );
        $subjectFormat = ':type :host :message';
        $subject = CakeText::insert($subjectFormat, $params);
        
        try {
            $Email = new CakeEmail(Configure::read('debugEmailConfig'));
            $Email->to($this->_options['to'])
                ->template('debug')
                ->emailFormat('html')
                ->subject($subject)
                ->viewVars(array(
                'message' => $message,
                'loggedUser' => AppAuthComponent::user()
            ))
                ->send();
        } catch (SocketException $e) {
            return false;
        }
    }
}