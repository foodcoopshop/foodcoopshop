<?php

namespace App\Mailer;

use App\Lib\OutputFilter\OutputFilter;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppMailer extends Mailer
{

    public function __construct($addBccBackupAddress = true)
    {
        parent::__construct(null);

        if ($addBccBackupAddress && Configure::read('appDb.FCS_BACKUP_EMAIL_ADDRESS_BCC') != '') {
            $this->addBcc(Configure::read('appDb.FCS_BACKUP_EMAIL_ADDRESS_BCC'));
        }
    }

    /**
     * uses fallback transport config if default email transport config is wrong (e.g. password changed party)
     * @see credentials.php
     */
    public function send(?string $action = null, array $args = [], array $headers = []): array
    {
        try {

            $this->render();

            if (Configure::check('app.outputStringReplacements')) {
                $replacedSubject = OutputFilter::replace($this->getOriginalSubject(), Configure::read('app.outputStringReplacements'));
                $this->setSubject($replacedSubject);
                $replacedBody = OutputFilter::replace($this->getMessage()->getBodyHtml(), Configure::read('app.outputStringReplacements'));
                $this->getMessage()->setBodyHtml($replacedBody);
            }

            // do not use parent:send() here because $replaced body would not be sent
            return $this->getTransport()->send($this->getMessage());

        } catch (Exception $e) {
            if (Configure::check('app.EmailTransport.fallback')) {
                // only try to reconfigure callback config once
                if (is_null(TransportFactory::getConfig('fallback'))) {
                    TransportFactory::setConfig('fallback', Configure::read('app.EmailTransport.fallback'));
                    $originalFrom = $this->getFrom();
                    $this->setConfig('fallback', Configure::read('app.Email.fallback'));
                    $this->setTransport('fallback');
                    // setFrom()  avoids "Sender address rejected: not owned by user" if email in from-address
                    // is not the same as the one in FallbackTransport
                    $this->setFrom([Configure::read('app.Email.fallback')['from'][0] => array_values($originalFrom)[0]]);
                }
                Log::error('The email could not be sent but was resent with the fallback configuration.<br /><br />' . $e->__toString());
                return $this->getTransport()->send($this->getMessage());
            } else {
                throw $e;
            }
        }
    }
}
