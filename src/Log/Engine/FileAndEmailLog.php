<?php

namespace App\Log\Engine;

use App\Mailer\AppMailer;
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
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class FileAndEmailLog extends FileLog
{

    public function log($level, $message, array $context = []): void
    {
        parent::log($level, $message, $context);
        if (Configure::read('app.emailErrorLoggingEnabled')) {
            $this->sendEmailWithErrorInformation($message);
        }
    }

    private function sendEmailWithErrorInformation($message)
    {

        $ignoredPatterns = [
            'MissingControllerException',
            'MissingActionException',
            'RecordNotFoundException',
            'MissingRouteException',
            'UnauthorizedException',
            'cancellation_terms_accepted',
            'general_terms_and_conditions_accepted',
            'terms_of_use_accepted_date_checkbox',
            '{"passwd_old',
            '{"passwd_1',
            '{"passwd_2',
            '{"pickup_day":{"allow-only-one-weekday"',
            '{"email":{"unique"',
            '{"email":{"exists"',
            '{"delivery_rhythm_',
            '{"quantity_in_units"',
            '{"email":{"account_inactive"',
            '{"short_description":{"maxLength":',
            '{"firstname":{"_empty":',
            '{"lastname":{"_empty":',
            __('You_are_not_signed_in.'),
            '{"default_quantity_after_sending_order_lists":{"greaterThanOrEqual":',
            '{"quantity_limit":{"lessThanOrEqual":',
            '{"phone_mobile":{"_empty":',
            '{"promise_to_pickup_products":{"equals":',
        ];
        $ignoredExceptionsRegex = '/('.join('|', $ignoredPatterns).')/';
        if (preg_match($ignoredExceptionsRegex, $message)) {
            return false;
        }

        $session = new AppSession();
        $loggedUser = [];
        if ($session->read('Auth.User.id_customer') !== null) {
            $loggedUser = $session->read('Auth');
        }

        $subject = Configure::read('app.cakeServerName') . ' ' . Text::truncate($message, 90) . ' ' . date(Configure::read('DateFormat.DatabaseWithTimeAlt'));
        try {
            $email = new AppMailer(false);
            $email->setProfile('debug');
            $email->setTransport('debug');
            $email->setTo(Configure::read('app.debugEmail'))
            ->viewBuilder()->setTemplate('debug');
            $email->setSubject($subject)
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
