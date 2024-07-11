<?php
declare(strict_types=1);

namespace App\Log\Engine;

use Cake\Core\Configure;
use Cake\Log\Engine\FileLog;
use Cake\Mailer\Mailer;
use Cake\Network\Exception\SocketException;
use Cake\Utility\Text;
use Cake\Routing\Router;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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
            'MissingTemplateException',
            'UnauthorizedException',
            'UnauthenticatedException',
            'InvalidCsrfTokenException',
            'cancellation_terms_accepted',
            'general_terms_and_conditions_accepted',
            'terms_of_use_accepted_date_checkbox',
            '{"passwd_old',
            '{"passwd_1',
            '{"passwd_2',
            '{"pickup_day":{"allow-only-one-weekday"',
            '{"email":{"unique"',
            '{"email":{"exists"',
            '{"email":{"_empty"',
            '{"email":{"email":',
            '{"delivery_rhythm_',
            '{"quantity_in_units"',
            '{"email":{"account_inactive"',
            '{"short_description":{"maxLength":',
            '{"firstname":{"_empty":',
            '{"firstname":{"custom":',
            '{"lastname":{"_empty":',
            '{"lastname":{"custom":',
            '{"name":{"lengthBetween":',
            __('You_are_not_signed_in.'),
            '{"default_quantity_after_sending_order_lists":{"greaterThanOrEqual":',
            '{"phone":{"_empty":',
            '{"phone":{"validFormat"',
            '{"phone_mobile":{"_empty":',
            '{"phone_mobile":{"validFormat"',
            '{"promise_to_pickup_products":{"equals":',
            '{"id_customer":{"_required":',
            '{"id_customer":{"numeric":',
            '{"id_customer":{"greaterThan":',
            '{"name":{"_empty":"Bitte gib einen Namen ein."',
            '{"name":{"unique":',
            '{"name":{"minLength":"Der Name des Produktes',
            '{"title":{"_empty":"Bitte gib einen Titel ein."}}',
            '{"quantity":{"_empty":"Bitte gib eine Zahl zwischen -5.000',
            '{"quantity_limit":{"_empty":',
            '{"quantity_limit":{"lessThanOrEqual":',
            '{"amount":{"greaterThanOrEqual":',
            '{"amount":{"numeric":"Bitte gib eine korrekte Zahl ein.',
            '{"no_delivery_days":',
            '{"price_incl_per_unit":{"greaterThan":',
            'Form tampering protection token validation failed',
            'General error: 1205 Lock wait timeout exceeded',
            'Communication link failure: 1053 Server shutdown in progress',
            '{"barcode":{"lengthBetween":',
            '`FormProtector` instance has not been created.',
            'invalid-image',
            '{"postcode":{"validFormat"',
            '{"value":{"noDeliveryDaysOrdersExist"',
            'Undefined variable \$isMobile', //mostly caused by bots
        ];
        $ignoredExceptionsRegex = '/('.join('|', $ignoredPatterns).')/';
        if (preg_match($ignoredExceptionsRegex, $message)) {
            return false;
        }

        $identity = null;
        $request = Router::getRequest();
        if ($request !== null) {
            $identity = $request->getAttribute('identity');
        }

        $subject = Configure::read('App.fullBaseUrl') . ' ' . Text::truncate($message, 90) . ' ' . date(Configure::read('DateFormat.DatabaseWithTimeAlt'));
        try {
            $email = new Mailer(null);
            $email->setProfile('debug');
            $email->setTo(Configure::read('app.debugEmail'))
            ->viewBuilder()->setTemplate('debug');
            $email->setSubject($subject)
            ->setViewVars([
                'message' => $message,
                'identity' => $identity,
            ])
            ->send();
        } catch (SocketException $e) {
            return false;
        }
    }
}
