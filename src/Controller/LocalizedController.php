<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

namespace App\Controller;

use App\Services\OutputFilter\OutputFilterService;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\I18n\I18n;

/**
 * Emits the JavaScript counterpart of CakePHP's i18n machinery:
 *   foodcoopshop.translations  flat msgid -> translation map for the default gettext domain
 *   foodcoopshop.config        environment-derived values (locale, currency, route prefixes, ...)
 *
 * The runtime helper `__()` lives in webroot/js/i18n.js and is loaded via the
 * asset pipeline.
 *
 * Request flow is unchanged compared to the legacy LocalizedController:
 *   /js/localized-javascript.js -> renderAsJsFile()
 *   In production, the result is baked into webroot/cache/localized-javascript-static.js
 *   by App\Command\SavedLocalizedJsAsStaticFileCommand.
 */
class LocalizedController extends Controller
{

    /**
     * Environment-derived values that are not translations but used by JS code.
     * Keys must be plain identifiers (no dots) and are exposed under foodcoopshop.config.
     *
     * @return array<string, mixed>
     */
    private function getConfig(): array
    {
        $defaultLocale = (string)Configure::read('appDb.FCS_DEFAULT_LOCALE');

        return [
            'dateFormat' => Configure::read('DateFormat.DateForDatepicker'),
            'defaultLocale' => $defaultLocale,
            'defaultLocaleShort' => substr($defaultLocale, 0, 2),
            'defaultLocaleInBCP47' => str_replace('_', '-', $defaultLocale),
            'CurrencySymbol' => Configure::read('appDb.FCS_CURRENCY_SYMBOL'),
            'CurrencyName' => Configure::read('app.currencyName'),
            'routeAllCategories' => Configure::read('app.slugHelper')->getAllProducts(),
            'routeCartFinished' => '/' . __('route_cart') . '/' . __('route_cart_finished'),
            'CopiedData' => __('Categories') . ', ' . __('Descriptions') . ', ' . __('Amount') . ', ' . __('Price') . ', ' . __('Tax_rate') . ', ' . __('Deposit') . ', ' . __('Delivery_rhythm') . ', ' . __('Storage_location'),
            'DocsUrlProductDeclaration' => Configure::read('app.htmlHelper')->getDocsUrl(__('docs_route_product_declaration')),
            'DocsUrlOrderHandling' => Configure::read('app.htmlHelper')->getDocsUrl(__('docs_route_order_handling')),
        ];
    }

    /**
     * Build the msgid -> translation map for the default domain.
     *
     * Loads the default translator (forces the underlying gettext catalog to
     * be parsed) and flattens its message package. Plural / contextual forms
     * are reduced to a single string since current JS callers do not perform
     * plural selection.
     *
     * @return array<string, string>
     */
    private function getTranslations(): array
    {
        $locale = I18n::getLocale();
        $messages = I18n::getTranslator('default', $locale)->getPackage()->getMessages();
        $flat = [];
        foreach ($messages as $msgid => $value) {
            if (is_array($value)) {
                if (isset($value['_context']) && is_array($value['_context'])) {
                    $first = reset($value['_context']);
                    $value = is_string($first) ? $first : '';
                } else {
                    $first = $value[0] ?? reset($value);
                    $value = is_string($first) ? $first : '';
                }
            }
            if ($value === '') {
                continue;
            }
            $flat[(string)$msgid] = $value;
        }
        return $flat;
    }

    public function renderAsJsFile(): void
    {
        $this->response = $this->response->withType('application/javascript')->withCharset('UTF-8');
        $this->viewBuilder()->setLayout('ajax');
        $this->set('translations', $this->getTranslations());
        $this->set('jsConfig', $this->getConfig());
    }

    public function afterFilter(EventInterface $event): void
    {
        parent::afterFilter($event);
        if (Configure::check('app.outputStringReplacements')) {
            $newOutput = OutputFilterService::replace($this->response->getBody()->__toString(), Configure::read('app.outputStringReplacements'));
            $this->response = $this->response->withStringBody($newOutput);
        }
    }
}