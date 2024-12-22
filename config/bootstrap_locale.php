<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.6.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;

mb_internal_encoding('UTF-8');
try {
    // on fresh installations there is no configurations table yet when first migrations run
    TableRegistry::getTableLocator()->get('Configurations')->loadConfigurations();
} catch(\Exception $e) {}
if (in_array(Configure::read('appDb.FCS_DEFAULT_LOCALE'), Configure::read('app.implementedLocales'))) {
    ini_set('intl.default_locale', Configure::read('appDb.FCS_DEFAULT_LOCALE'));
    locale_set_default(Configure::read('appDb.FCS_DEFAULT_LOCALE'));
    I18n::setLocale(Configure::read('appDb.FCS_DEFAULT_LOCALE'));
    Configure::load('Locale' . DS . Configure::read('appDb.FCS_DEFAULT_LOCALE') . DS . 'date', 'default');
    setlocale(LC_CTYPE, Configure::read('appDb.FCS_DEFAULT_LOCALE').'.UTF-8');
    setlocale(LC_COLLATE, Configure::read('appDb.FCS_DEFAULT_LOCALE').'.UTF-8');
}

// gettext not available in app_config
Configure::load('localized_config', 'default');

if (file_exists(CONFIG.DS.'localized_custom_config.php')) {
    Configure::load('localized_custom_config', 'default');
}

?>